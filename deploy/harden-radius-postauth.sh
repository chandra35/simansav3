#!/usr/bin/env bash
set -euo pipefail

queries=/etc/freeradius/3.0/mods-config/sql/main/mysql/queries.conf
test -f "$queries"
backup="$(mktemp)"
cp "$queries" "$backup"
trap 'cp "$backup" "$queries"; systemctl restart freeradius >/dev/null 2>&1 || true; rm -f "$backup"' ERR

python3 - "$queries" <<'PYEOF'
from pathlib import Path
import sys

path = Path(sys.argv[1])
content = path.read_text()
source = "'%{%{User-Password}:-%{Chap-Password}}', " + "\\"
replacement = "'', " + "\\"
if source in content:
    path.write_text(content.replace(source, replacement, 1))
elif replacement not in content:
    raise SystemExit('post-auth password expression was not found')
PYEOF

mysql radius -e "UPDATE radpostauth SET pass = '' WHERE pass <> '';"
freeradius -XC >/dev/null 2>&1
systemctl restart freeradius
systemctl is-active --quiet freeradius

remaining="$(mysql -N -B radius -e "SELECT COUNT(*) FROM radpostauth WHERE pass <> ''")"
test "$remaining" = "0"
rm -f "$backup"
trap - ERR
echo "postauth_password_storage=disabled"
echo "historical_password_values=cleared"
