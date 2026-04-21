#!/bin/bash
# Script konfigurasi MikroTik untuk FreeRADIUS Hotspot
# Jalankan dengan: bash setup_mikrotik.sh
# Atau via SSH: ssh vscode@172.16.250.1 < setup_mikrotik.sh

MIKROTIK="172.16.250.1"
MIKROTIK_USER="vscode"
MIKROTIK_PASS="vscode"
RADIUS_IP="172.16.250.8"
RADIUS_SECRET="HotspotMAN1Metro2026"

echo "=== Setup MikroTik untuk RADIUS Hotspot ==="
echo "Koneksi ke MikroTik $MIKROTIK..."

# Gunakan sshpass jika ada, atau ssh biasa
if command -v sshpass &> /dev/null; then
    SSH_CMD="sshpass -p $MIKROTIK_PASS ssh -o StrictHostKeyChecking=no $MIKROTIK_USER@$MIKROTIK"
else
    SSH_CMD="ssh -o StrictHostKeyChecking=no $MIKROTIK_USER@$MIKROTIK"
fi

# Buat semua perintah RouterOS dalam satu script
cat << 'ROUTEROS_END'
# === 1. Buat IP Pool untuk masing-masing role ===
/ip pool
add name=pool-guru ranges=172.16.201.10-172.16.201.254
add name=pool-siswa ranges=172.16.202.10-172.16.202.254
add name=pool-tamu ranges=172.16.203.10-172.16.203.254

# === 2. Tambah RADIUS server ===
/radius
add address=172.16.250.8 secret=HotspotMAN1Metro2026 service=hotspot timeout=3s

# === 3. Buat Hotspot User Profile per role ===
/ip hotspot user profile
add name=profile-guru rate-limit=10M/10M address-pool=pool-guru idle-timeout=30m keepalive-timeout=none
add name=profile-siswa rate-limit=5M/5M address-pool=pool-siswa idle-timeout=20m keepalive-timeout=none
add name=profile-tamu rate-limit=2M/2M address-pool=pool-tamu idle-timeout=10m keepalive-timeout=none

# === 4. Update Hotspot Server Profile (hsprof1) ===
/ip hotspot profile
set [find name=hsprof1] use-radius=yes radius-accounting=yes

# === 5. Enable Hotspot ===
/ip hotspot enable hotspot1

ROUTEROS_END
