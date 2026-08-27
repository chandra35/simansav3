# Kebijakan roaming SIMANSA: perubahan hanya pada Hotspot.
# RADIUS Disconnect-ACK menghapus Hotspot Active, dynamic queue, dan MAC-cookie target.
# MAC-cookie memulihkan akses pada perangkat yang sama setelah roaming AP/lost-service.
/ip hotspot profile set [find where name="hsprof1"] login-by=http-chap,mac-cookie
/ip hotspot user profile set [find where name="default"] add-mac-cookie=yes mac-cookie-timeout=1d
/ip hotspot user profile set [find where name="profile-guru"] add-mac-cookie=yes mac-cookie-timeout=1d
/ip hotspot user profile set [find where name="profile-siswa"] add-mac-cookie=yes mac-cookie-timeout=1d
/ip hotspot user profile set [find where name="profile-tamu"] add-mac-cookie=yes mac-cookie-timeout=1d
