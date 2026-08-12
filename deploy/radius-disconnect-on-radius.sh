#!/usr/bin/env bash
set -euo pipefail

payload="${1:-}"
if [[ -z "$payload" && "${SSH_ORIGINAL_COMMAND:-}" =~ ^simansa-radius-disconnect\ ([A-Za-z0-9+/=]+)$ ]]; then
    payload="${BASH_REMATCH[1]}"
fi
[[ "$payload" =~ ^[A-Za-z0-9+/=]+$ ]] || { echo "invalid-payload" >&2; exit 64; }
mapfile -t values < <(printf '%s' "$payload" | base64 -d)

username="${values[0]:-}"
framed_ip="${values[1]:-}"
mac="${values[2]:-}"
session_id="${values[3]:-}"
nas_ip="${values[4]:-}"

[[ "$username" =~ ^[A-Za-z0-9._@-]{1,128}$ ]] || { echo "invalid-username" >&2; exit 64; }
[[ "$framed_ip" =~ ^[0-9.]{7,15}$ ]] || { echo "invalid-ip" >&2; exit 64; }
[[ "$mac" =~ ^[A-Fa-f0-9:-]{11,20}$ ]] || { echo "invalid-mac" >&2; exit 64; }
[[ "$session_id" =~ ^[A-Za-z0-9._:-]{1,128}$ ]] || { echo "invalid-session" >&2; exit 64; }
[[ "$nas_ip" == "172.16.250.1" ]] || { echo "invalid-nas" >&2; exit 64; }

secret="$(mysql --batch --skip-column-names radius -e "SELECT secret FROM nas WHERE nasname='${nas_ip}' LIMIT 1")"
[[ -n "$secret" ]] || { echo "nas-not-found" >&2; exit 66; }
secret_file="$(mktemp /run/simansa-radius-secret.XXXXXX)"
trap 'rm -f "$secret_file"' EXIT
chmod 600 "$secret_file"
printf '%s' "$secret" > "$secret_file"

request="$(printf 'User-Name = "%s"\nFramed-IP-Address = %s\nCalling-Station-Id = "%s"\nAcct-Session-Id = "%s"\nMessage-Authenticator = 0x00\n' "$username" "$framed_ip" "$mac" "$session_id")"
response="$(printf '%s' "$request" | radclient -x -S "$secret_file" "${nas_ip}:3799" disconnect 2>&1)" || true

if grep -q 'Received Disconnect-ACK' <<< "$response"; then
    echo "disconnect-ack"
    exit 0
fi

if grep -q 'Received Disconnect-NAK' <<< "$response"; then
    echo "disconnect-nak" >&2
    exit 69
fi

echo "radius-timeout" >&2
exit 70
