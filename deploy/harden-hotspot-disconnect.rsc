# Kebijakan putus sesi SIMANSA: batasi perubahan hanya pada Hotspot.
# RADIUS Disconnect-ACK menghapus Hotspot Active dan dynamic queue.
# MAC-cookie dimatikan agar perangkat tidak langsung login otomatis kembali.
/ip hotspot profile set [find where name="hsprof1"] login-by=http-chap
/ip hotspot user profile set [find where name="default"] add-mac-cookie=no
/ip hotspot user profile set [find where name="profile-guru"] add-mac-cookie=no
/ip hotspot user profile set [find where name="profile-siswa"] add-mac-cookie=no
/ip hotspot user profile set [find where name="profile-tamu"] add-mac-cookie=no
/ip hotspot cookie remove [find]
