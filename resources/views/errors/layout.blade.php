<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $status }} — {{ $title }} | SIMANSA</title>
    <style>
        :root{--primary:#2563eb;--ink:#172033;--muted:#64748b;--line:#dce5f2;--surface:#fff;--page:#f4f7fb;--tone:{{ $tone }};--tone-soft:{{ $toneSoft }};}*{box-sizing:border-box}html,body{min-height:100%;margin:0}body{display:grid;place-items:center;padding:24px;background:radial-gradient(circle at 8% 0,rgba(37,99,235,.11),transparent 28%),var(--page);font-family:"Segoe UI",Arial,sans-serif;color:var(--ink)}.error-card{width:min(620px,100%);padding:clamp(28px,6vw,52px);background:var(--surface);border:1px solid var(--line);border-radius:20px;box-shadow:0 16px 38px rgba(15,23,42,.09)}.brand{display:flex;align-items:center;gap:9px;color:#334155;font-size:.8rem;font-weight:800;letter-spacing:.06em}.brand-mark{display:grid;place-items:center;width:30px;height:30px;border-radius:9px;background:#312e81;color:#fff;font-size:1rem}.status{display:inline-flex;align-items:center;gap:8px;margin-top:32px;padding:7px 10px;border-radius:7px;background:var(--tone-soft);color:var(--tone);font-size:.75rem;font-weight:800;letter-spacing:.05em}.status i{width:7px;height:7px;border-radius:50%;background:currentColor}h1{margin:18px 0 10px;font-size:clamp(1.7rem,5vw,2.35rem);line-height:1.15;letter-spacing:-.025em}p{margin:0;color:var(--muted);font-size:1rem;line-height:1.65}.error-code{margin:26px 0 0;padding-top:18px;border-top:1px solid var(--line);color:#94a3b8;font-size:.82rem}.error-code strong{color:var(--ink)}.actions{display:flex;flex-wrap:wrap;gap:10px;margin-top:28px}.btn{display:inline-flex;align-items:center;justify-content:center;min-height:42px;padding:0 15px;border:1px solid transparent;border-radius:7px;text-decoration:none;font-size:.9rem;font-weight:700;cursor:pointer}.btn-primary{background:var(--primary);color:#fff}.btn-secondary{border-color:#cbd5e1;background:#fff;color:#334155}@media(max-width:520px){body{padding:16px}.error-card{border-radius:16px}.actions{flex-direction:column}.btn{width:100%}}@media(prefers-reduced-motion:reduce){*{scroll-behavior:auto!important}}
    </style>
</head>
<body>
    <main class="error-card" role="main" aria-labelledby="error-title">
        <div class="brand"><span class="brand-mark" aria-hidden="true">A</span> SIMANSA · MAN 1 Metro</div>
        <div class="status"><i aria-hidden="true"></i>{{ $label }}</div>
        <h1 id="error-title">{{ $title }}</h1>
        <p>{{ $message }}</p>
        <div class="error-code">Kode status: <strong>{{ $status }}</strong></div>
        <div class="actions">
            <a class="btn btn-primary" href="{{ url('/') }}">Ke dashboard</a>
            <button class="btn btn-secondary" type="button" onclick="history.back()">Kembali</button>
        </div>
    </main>
    @if($status === 503)
        <script>window.setTimeout(function(){window.location.reload()},30000)</script>
    @endif
</body>
</html>
