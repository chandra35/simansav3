<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Sebagai Tidak Tersedia - SIMANSA</title>
    <style>
        * { box-sizing: border-box; }
        body { align-items:center; background:#f8fafc; color:#172554; display:flex; font-family:Arial,sans-serif; justify-content:center; margin:0; min-height:100vh; padding:1.5rem; }
        main { background:#fff; border:1px solid #fde68a; border-radius:1rem; box-shadow:0 1rem 2.5rem rgba(146,64,14,.12); max-width:34rem; padding:2rem; text-align:center; width:100%; }
        .icon { align-items:center; background:#fffbeb; border-radius:50%; color:#d97706; display:inline-flex; font-size:1.8rem; height:4rem; justify-content:center; width:4rem; }
        h1 { font-size:1.45rem; margin:1rem 0 .6rem; }
        p { color:#64748b; line-height:1.6; margin:.5rem 0; }
        .notice { background:#fffbeb; border-left:4px solid #f59e0b; border-radius:.5rem; color:#92400e; margin:1.25rem 0; padding:.85rem 1rem; text-align:left; }
        a { background:#4f46e5; border-radius:.55rem; color:#fff; display:inline-block; margin-top:.5rem; padding:.7rem 1rem; text-decoration:none; }
    </style>
</head>
<body>
    <main>
        <div class="icon">!</div>
        <h1>Login Sebagai GTK tidak tersedia</h1>
        <p>Akun <strong>{{ $target->name }}</strong> juga memiliki role <strong>{{ $blockedRole }}</strong>.</p>
        <div class="notice">Untuk keamanan, akun Admin atau Operator tidak dapat dibuka melalui Login Sebagai. Hapus role <strong>{{ $blockedRole }}</strong> dari akun tersebut jika ingin mengujinya sebagai GTK.</div>
        <a href="{{ $adminUrl }}">Kembali ke Data GTK</a>
    </main>
</body>
</html>
