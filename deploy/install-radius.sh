#!/bin/bash
# Install FreeRADIUS + MariaDB untuk Hotspot MAN 1 Metro
# VM: 172.16.250.8 (radius-server), Ubuntu 22.04

set -e
export DEBIAN_FRONTEND=noninteractive

RADIUS_DB_PASS="Radius@Simansa2026"
SIMANSA_IP="172.16.250.7"
MIKROTIK_IP="172.16.250.1"
RADIUS_SECRET="HotspotMAN1Metro2026"

echo "============================================="
echo " FreeRADIUS + MariaDB Installer"
echo " MAN 1 Metro - $(date)"
echo "============================================="

echo ""
echo "=== [1/8] Update & upgrade packages ==="
apt-get update -qq
apt-get upgrade -y -qq

echo ""
echo "=== [2/8] Install MariaDB ==="
apt-get install -y -qq mariadb-server mariadb-client
systemctl enable mariadb
systemctl start mariadb

echo ""
echo "=== [3/8] Install FreeRADIUS ==="
apt-get install -y -qq freeradius freeradius-mysql freeradius-utils
systemctl stop freeradius || true

echo ""
echo "=== [4/8] Setup radius database ==="
mysql -e "
CREATE DATABASE IF NOT EXISTS radius CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
DROP USER IF EXISTS 'radius'@'localhost';
DROP USER IF EXISTS 'radius'@'${SIMANSA_IP}';
CREATE USER 'radius'@'localhost' IDENTIFIED BY '${RADIUS_DB_PASS}';
CREATE USER 'radius'@'${SIMANSA_IP}' IDENTIFIED BY '${RADIUS_DB_PASS}';
GRANT ALL PRIVILEGES ON radius.* TO 'radius'@'localhost';
GRANT ALL PRIVILEGES ON radius.* TO 'radius'@'${SIMANSA_IP}';
FLUSH PRIVILEGES;
"

echo ""
echo "=== [5/8] Import FreeRADIUS schema ==="
mysql radius < /etc/freeradius/3.0/mods-config/sql/main/mysql/schema.sql

echo ""
echo "=== [6/8] Insert default groups (guru/siswa/tamu) ==="
mysql radius -e "
-- Group reply: Framed-Pool per role
INSERT IGNORE INTO radgroupreply (groupname, attribute, op, value) VALUES
('guru',  'Framed-Pool',          ':=', 'pool-guru'),
('guru',  'Mikrotik-Group-Name',  ':=', 'profile-guru'),
('guru',  'Session-Timeout',      ':=', '86400'),
('siswa', 'Framed-Pool',          ':=', 'pool-siswa'),
('siswa', 'Mikrotik-Group-Name',  ':=', 'profile-siswa'),
('siswa', 'Session-Timeout',      ':=', '86400'),
('tamu',  'Framed-Pool',          ':=', 'pool-tamu'),
('tamu',  'Mikrotik-Group-Name',  ':=', 'profile-tamu'),
('tamu',  'Session-Timeout',      ':=', '28800');
"

echo ""
echo "=== [7/8] Configure FreeRADIUS ==="

# Konfigurasi SQL module
cat > /etc/freeradius/3.0/mods-available/sql << 'SQLCONF'
sql {
    driver = "rlm_sql_mysql"
    dialect = "mysql"

    server = "localhost"
    port = 3306
    login = "radius"
    password = "Radius@Simansa2026"
    radius_db = "radius"

    acct_table1 = "radacct"
    acct_table2 = "radacct"
    postauth_table = "radpostauth"
    authcheck_table = "radcheck"
    groupcheck_table = "radgroupcheck"
    authreply_table = "radreply"
    groupreply_table = "radgroupreply"
    usergroup_table = "radusergroup"
    delete_stale_sessions = yes
    pool {
        start = ${thread[pool].start_servers}
        min = ${thread[pool].min_spare_servers}
        max = ${thread[pool].max_servers}
        spare = ${thread[pool].max_spare_servers}
        uses = 0
        retry_delay = 30
        lifetime = 0
        idle_timeout = 60
    }
    read_clients = yes
    client_table = "nas"

    group_attribute = "SQL-Group"

    $INCLUDE ${modconfdir}/${.:name}/main/${dialect}/queries.conf
}
SQLCONF

# Enable SQL module
ln -sf /etc/freeradius/3.0/mods-available/sql /etc/freeradius/3.0/mods-enabled/sql 2>/dev/null || true

# Konfigurasi NAS client (MikroTik)
cat > /etc/freeradius/3.0/clients.conf << CLIENTCONF
# FreeRADIUS clients configuration
# MAN 1 Metro Hotspot

client localhost {
    ipaddr = 127.0.0.1
    secret = testing123
    require_message_authenticator = no
    shortname = localhost
}

client mikrotik-kampus1 {
    ipaddr = ${MIKROTIK_IP}
    secret = ${RADIUS_SECRET}
    require_message_authenticator = no
    shortname = mikrotik-kampus1
    nas_type = other
}
CLIENTCONF

# Konfigurasi default site - enable sql
sed -i 's/#\s*-sql/\t-sql/' /etc/freeradius/3.0/sites-available/default 2>/dev/null || true
# Aktifkan sql di authorize section
python3 - << 'PYEOF'
import re

with open('/etc/freeradius/3.0/sites-available/default', 'r') as f:
    content = f.read()

# Enable sql in authorize section (uncomment -sql)
content = re.sub(r'#\s*(-sql)', r'\1', content)

with open('/etc/freeradius/3.0/sites-available/default', 'w') as f:
    f.write(content)
print("sites/default updated")
PYEOF

# Fix ownership
chown -R freerad:freerad /etc/freeradius/3.0/

echo ""
echo "=== [8/8] Enable & start services ==="
# Test config
freeradius -XC 2>&1 | tail -5 || echo "WARNING: config test had issues, check manually"

systemctl enable freeradius
systemctl restart freeradius
systemctl status freeradius --no-pager -l | head -20

# Allow MariaDB from Simansa
ufw allow from ${SIMANSA_IP} to any port 3306 comment "Simansa radius DB" 2>/dev/null || true
ufw allow 1812/udp comment "RADIUS auth" 2>/dev/null || true
ufw allow 1813/udp comment "RADIUS acct" 2>/dev/null || true

echo ""
echo "============================================="
echo " INSTALASI SELESAI!"
echo "============================================="
echo " RADIUS Server  : $(hostname -I | awk '{print $1}'):1812"
echo " DB radius      : localhost:3306 / radius / ${RADIUS_DB_PASS}"
echo " MikroTik secret: ${RADIUS_SECRET}"
echo " Simansa DB user: radius@${SIMANSA_IP}"
echo "============================================="

# Verify tables
echo ""
echo "=== Tabel di database radius: ==="
mysql radius -e "SHOW TABLES;"
