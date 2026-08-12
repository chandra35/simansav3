#!/usr/bin/env bash
set -euo pipefail

[[ $# -eq 5 ]] || { echo "invalid-arguments" >&2; exit 64; }
username="$1"; framed_ip="$2"; mac="$3"; session_id="$4"; nas_ip="$5"

[[ "$username" =~ ^[A-Za-z0-9._@-]{1,128}$ ]] || { echo "invalid-username" >&2; exit 64; }
[[ "$framed_ip" =~ ^[0-9.]{7,15}$ ]] || { echo "invalid-ip" >&2; exit 64; }
[[ "$mac" =~ ^[A-Fa-f0-9:-]{11,20}$ ]] || { echo "invalid-mac" >&2; exit 64; }
[[ "$session_id" =~ ^[A-Za-z0-9._:-]{1,128}$ ]] || { echo "invalid-session" >&2; exit 64; }
[[ "$nas_ip" == "172.16.250.1" ]] || { echo "invalid-nas" >&2; exit 64; }

payload="$(printf '%s\n%s\n%s\n%s\n%s\n' "$username" "$framed_ip" "$mac" "$session_id" "$nas_ip" | base64 -w0)"
exec ssh -i /root/.ssh/simansa_radius_disconnect_ed25519 -o IdentitiesOnly=yes -o BatchMode=yes -o ConnectTimeout=3 -o StrictHostKeyChecking=yes root@172.16.250.8 "simansa-radius-disconnect ${payload}"
