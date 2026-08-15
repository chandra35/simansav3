<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="refresh" content="20">
    <title>Kendala Sistem · SIMANSA</title>
    <style>
        :root { --ink:#102a56; --muted:#6179a4; --blue:#2563eb; --indigo:#4f46e5; --coral:#ef5d76; --line:rgba(63,103,180,.14); }
        * { box-sizing:border-box; }
        html,body { min-height:100%; margin:0; }
        body { display:grid; place-items:center; padding:28px; overflow:hidden; color:var(--ink); font-family:Inter,"Segoe UI",system-ui,sans-serif; background:linear-gradient(140deg,#f7faff,#eaf1ff 53%,#f8f7ff); }
        .orb { position:fixed; border-radius:50%; filter:blur(2px); pointer-events:none; }
        .orb--one { width:38rem; height:38rem; left:-15rem; top:-17rem; background:radial-gradient(circle,rgba(37,99,235,.2),transparent 67%); animation:drift 13s ease-in-out infinite alternate; }
        .orb--two { width:32rem; height:32rem; right:-12rem; bottom:-15rem; background:radial-gradient(circle,rgba(124,58,237,.16),transparent 68%); animation:drift 15s ease-in-out infinite alternate-reverse; }
        .shell { position:relative; width:min(1100px,100%); overflow:hidden; border:1px solid rgba(255,255,255,.85); border-radius:30px; background:rgba(255,255,255,.78); box-shadow:0 30px 80px rgba(35,69,131,.17); backdrop-filter:blur(20px); }
        .shell::before { content:""; position:absolute; inset:0 0 auto; height:4px; background:linear-gradient(90deg,var(--blue),var(--indigo),var(--coral)); }
        .layout { display:grid; grid-template-columns:1.08fr .92fr; min-height:590px; }
        .content { padding:58px 56px 48px; }
        .status { display:inline-flex; align-items:center; gap:10px; padding:9px 14px; border-radius:999px; background:#edf2ff; color:#3f66c8; font-size:12px; font-weight:800; letter-spacing:.07em; text-transform:uppercase; }
        .status i { width:9px; height:9px; border-radius:50%; background:var(--coral); box-shadow:0 0 0 0 rgba(239,93,118,.45); animation:ping 1.8s infinite; }
        h1 { max-width:650px; margin:23px 0 16px; font-size:clamp(2.3rem,4.5vw,4rem); line-height:1.04; letter-spacing:-.045em; }
        .lead { max-width:600px; margin:0; color:var(--muted); font-size:17px; line-height:1.75; }
        .facts { display:grid; grid-template-columns:repeat(3,1fr); gap:12px; margin-top:30px; }
        .fact { min-height:112px; padding:17px; border:1px solid var(--line); border-radius:17px; background:rgba(255,255,255,.82); }
        .fact span { display:block; color:#7186a9; font-size:11px; font-weight:800; letter-spacing:.06em; text-transform:uppercase; }
        .fact strong { display:block; margin-top:9px; font-size:15px; line-height:1.45; }
        .recovery { margin-top:18px; padding:18px 20px; border:1px solid rgba(79,70,229,.12); border-radius:18px; background:linear-gradient(115deg,#eff5ff,#f4f1ff); }
        .recovery__top { display:flex; align-items:center; justify-content:space-between; gap:12px; font-weight:800; font-size:14px; }
        .counter { white-space:nowrap; padding:7px 10px; border-radius:999px; color:#315fc7; background:#fff; font-size:12px; }
        .track { height:8px; margin:13px 0 8px; overflow:hidden; border-radius:99px; background:rgba(37,99,235,.12); }
        .track::before { content:""; display:block; width:38%; height:100%; border-radius:inherit; background:linear-gradient(90deg,var(--blue),var(--indigo),var(--coral)); animation:scan 2.8s ease-in-out infinite; }
        .recovery p { margin:0; color:var(--muted); font-size:13px; line-height:1.55; }
        .actions { display:flex; flex-wrap:wrap; gap:11px; margin-top:22px; }
        .button { display:inline-flex; align-items:center; justify-content:center; min-height:45px; padding:0 17px; border:1px solid transparent; border-radius:13px; color:inherit; font-size:14px; font-weight:800; text-decoration:none; transition:transform .2s,box-shadow .2s,background .2s; }
        .button:hover { transform:translateY(-2px); }
        .button--primary { color:#fff; background:linear-gradient(135deg,#2563eb,#4f46e5); box-shadow:0 12px 25px rgba(49,83,209,.28); }
        .button--plain { border-color:var(--line); background:rgba(255,255,255,.9); }
        .visual { position:relative; display:grid; place-items:center; padding:42px; background:linear-gradient(155deg,#163d88,#2d5fb4 65%,#6a6ad2); overflow:hidden; }
        .visual::before,.visual::after { content:""; position:absolute; border:1px solid rgba(255,255,255,.14); border-radius:50%; }
        .visual::before { width:380px; height:380px; animation:rotate 18s linear infinite; }
        .visual::after { width:270px; height:270px; animation:rotate 12s linear infinite reverse; }
        .constellation { position:relative; z-index:1; width:min(340px,100%); padding:22px; border:1px solid rgba(255,255,255,.17); border-radius:25px; background:rgba(10,32,81,.34); box-shadow:0 24px 50px rgba(8,25,69,.26); backdrop-filter:blur(12px); }
        .brand { display:flex; align-items:center; justify-content:space-between; color:rgba(255,255,255,.92); font-size:12px; font-weight:800; letter-spacing:.06em; }
        .mark { display:grid; place-items:center; width:42px; height:42px; border-radius:14px; background:rgba(255,255,255,.14); font-size:19px; animation:float 3.8s ease-in-out infinite; }
        .visual h2 { margin:31px 0 10px; color:#fff; font-size:30px; line-height:1.15; letter-spacing:-.03em; }
        .visual p { margin:0; color:rgba(238,244,255,.82); font-size:14px; line-height:1.7; }
        .checks { display:grid; gap:10px; margin-top:22px; }
        .check { display:flex; align-items:center; justify-content:space-between; padding:12px 13px; border-radius:13px; background:rgba(255,255,255,.1); color:rgba(255,255,255,.88); font-size:13px; }
        .check b { display:inline-flex; align-items:center; gap:6px; font-size:11px; text-transform:uppercase; letter-spacing:.05em; }
        .check b::before { content:""; width:7px; height:7px; border-radius:50%; background:#9ee7c0; box-shadow:0 0 12px #9ee7c0; animation:blink 1.4s infinite alternate; }
        @keyframes ping { 70% { box-shadow:0 0 0 11px rgba(239,93,118,0); } 100% { box-shadow:0 0 0 0 rgba(239,93,118,0); } }
        @keyframes scan { 0%,100% { transform:translateX(-40%); } 50% { transform:translateX(165%); } }
        @keyframes rotate { to { transform:rotate(360deg); } }
        @keyframes drift { to { transform:translate(45px,28px) scale(1.08); } }
        @keyframes float { 50% { transform:translateY(-7px); } }
        @keyframes blink { to { opacity:.4; transform:scale(.72); } }
        @media (max-width:850px) { body { overflow:auto; padding:16px; } .layout { grid-template-columns:1fr; } .content { padding:38px 28px 30px; } .visual { min-height:380px; padding:32px 24px; } }
        @media (max-width:540px) { .facts { grid-template-columns:1fr; } .content { padding:32px 22px 26px; } .shell { border-radius:22px; } .visual h2 { font-size:26px; } }
        @media (prefers-reduced-motion:reduce) { *,*::before,*::after { animation-duration:.01ms !important; animation-iteration-count:1 !important; } }
    </style>
</head>
<body>
    @php($homeUrl = function_exists('getDashboardRoute') ? getDashboardRoute() : url('/'))
    <div class="orb orb--one"></div><div class="orb orb--two"></div>
    <main class="shell" role="main">
        <div class="layout">
            <section class="content">
                <div class="status"><i></i> Status layanan</div>
                <h1>Halaman ini belum dapat dimuat dengan aman.</h1>
                <p class="lead">SIMANSA mendeteksi kendala internal dan menghentikan proses halaman ini untuk menjaga data tetap konsisten. Silakan muat ulang; sistem juga akan mencoba kembali secara otomatis.</p>
                <div class="facts">
                    <article class="fact"><span>Status</span><strong>500 Internal Server Error</strong></article>
                    <article class="fact"><span>Dampak</span><strong>Hanya halaman ini yang tertunda</strong></article>
                    <article class="fact"><span>Saran</span><strong>Periksa ulang dalam beberapa saat</strong></article>
                </div>
                <div class="recovery">
                    <div class="recovery__top"><span>Menyiapkan pemulihan halaman</span><span class="counter">Coba lagi <b id="seconds">20</b>d</span></div>
                    <div class="track"></div><p>Jika kendala bersifat sementara, halaman akan dimuat ulang otomatis. Tim teknis dapat menelusuri detailnya melalui log sistem.</p>
                </div>
                <div class="actions"><a class="button button--primary" href="{{ $homeUrl }}">Kembali ke Dashboard</a><a class="button button--plain" href="{{ url()->current() }}">Muat Ulang Sekarang</a></div>
            </section>
            <aside class="visual" aria-hidden="true"><div class="constellation"><div class="brand"><span class="mark">✦</span><span>SIMANSA · SERVICE DESK</span></div><h2>Diagnostik layanan sedang berjalan.</h2><p>Kami menjaga sesi dan data Anda tetap aman saat aplikasi memulihkan respons halaman.</p><div class="checks"><div class="check"><span>Koneksi aplikasi</span><b>Memeriksa</b></div><div class="check"><span>Cache & konfigurasi</span><b>Memeriksa</b></div><div class="check"><span>Keamanan data</span><b>Terjaga</b></div></div></div></aside>
        </div>
    </main>
    <script>(()=>{let s=20,e=document.getElementById('seconds');setInterval(()=>{s=Math.max(0,s-1);e.textContent=s},1000)})();</script>
</body>
</html>
