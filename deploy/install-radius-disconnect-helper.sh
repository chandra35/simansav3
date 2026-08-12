#!/usr/bin/env bash
set -euo pipefail

install -o root -g root -m 0755 /tmp/simansa-radius-disconnect /usr/local/sbin/simansa-radius-disconnect
public_key="$(cat /tmp/simansa_radius_disconnect_ed25519.pub)"
authorized_line="from=\"172.16.250.7\",restrict,command=\"/usr/local/sbin/simansa-radius-disconnect\" ${public_key}"

mkdir -p /root/.ssh
chmod 700 /root/.ssh
touch /root/.ssh/authorized_keys
chmod 600 /root/.ssh/authorized_keys
grep -Fqx "$authorized_line" /root/.ssh/authorized_keys || printf '%s\n' "$authorized_line" >> /root/.ssh/authorized_keys

rm -f /tmp/simansa-radius-disconnect /tmp/simansa_radius_disconnect_ed25519.pub
