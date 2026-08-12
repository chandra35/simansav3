#!/bin/bash
# Script konfigurasi MikroTik untuk FreeRADIUS Hotspot
# Jalankan dengan: bash setup_mikrotik.sh
# Mengirim konfigurasi melalui SSH port yang ditentukan oleh MIKROTIK_SSH_PORT

: "${MIKROTIK:?Set MIKROTIK ke alamat router}"
: "${MIKROTIK_USER:?Set MIKROTIK_USER}"
: "${MIKROTIK_PASS:=}"
: "${RADIUS_IP:?Set RADIUS_IP}"
: "${RADIUS_SECRET:?Set RADIUS_SECRET melalui environment aman}"
MIKROTIK_SSH_PORT="${MIKROTIK_SSH_PORT:-3522}"

echo "=== Setup MikroTik untuk RADIUS Hotspot ==="
echo "Koneksi ke MikroTik $MIKROTIK..."

# Gunakan sshpass jika ada, atau ssh biasa
if [[ -n "$MIKROTIK_PASS" ]] && command -v sshpass &> /dev/null; then
    SSH_CMD=(sshpass -p "$MIKROTIK_PASS" ssh -p "$MIKROTIK_SSH_PORT" "$MIKROTIK_USER@$MIKROTIK")
else
    SSH_CMD=(ssh -p "$MIKROTIK_SSH_PORT" "$MIKROTIK_USER@$MIKROTIK")
fi

# Buat semua perintah RouterOS dalam satu script
cat << ROUTEROS_END | "${SSH_CMD[@]}"
# === 1. Buat IP Pool untuk masing-masing role ===
/ip pool
add name=pool-guru ranges=172.16.201.10-172.16.201.254
add name=pool-siswa ranges=172.16.202.10-172.16.202.254
add name=pool-tamu ranges=172.16.203.10-172.16.203.254

# === 2. Tambah RADIUS server ===
/radius
add address=$RADIUS_IP secret=$RADIUS_SECRET service=hotspot timeout=3s

# === 3. Buat Hotspot User Profile per role ===
/ip hotspot user profile
add name=profile-guru rate-limit=10M/10M address-pool=pool-guru idle-timeout=30m keepalive-timeout=none add-mac-cookie=no
add name=profile-siswa rate-limit=5M/5M address-pool=pool-siswa idle-timeout=20m keepalive-timeout=none add-mac-cookie=no
add name=profile-tamu rate-limit=2M/2M address-pool=pool-tamu idle-timeout=10m keepalive-timeout=none add-mac-cookie=no

# === 4. Update Hotspot Server Profile (hsprof1) ===
/ip hotspot profile
set [find name=hsprof1] use-radius=yes radius-accounting=yes radius-interim-update=10s login-by=http-chap

# === 5. DNS Hotspot: client memakai cache router, router meneruskan ke resolver publik ===
/ip dns
set servers=8.8.8.8,1.1.1.1 allow-remote-requests=yes
/ip dhcp-server network
set [find where address=10.10.0.0/22] dns-server=10.10.0.1

# === 6. Enable Hotspot ===
/ip hotspot enable hotspot1

ROUTEROS_END
