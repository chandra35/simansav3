<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $status }} — {{ $title }} | SIMANSA</title>
    <style>
        :root{--primary:#2563eb;--ink:#172033;--muted:#64748b;--line:#dce5f2;--surface:#fff;--page:#f4f7fb;--tone:{{ $tone }};--tone-soft:{{ $toneSoft }};}
        *{box-sizing:border-box}html,body{width:100%;min-height:100%;margin:0}body{display:grid;grid-template-columns:minmax(0,1fr);place-items:center;padding:clamp(16px,3vw,40px);background:radial-gradient(circle at 8% 0,rgba(37,99,235,.11),transparent 28%),var(--page);font-family:"Segoe UI",Arial,sans-serif;color:var(--ink)}
        .error-card{width:min(100%,640px);min-width:0;padding:clamp(28px,6vw,52px);background:var(--surface);border:1px solid var(--line);border-radius:20px;box-shadow:0 16px 38px rgba(15,23,42,.09)}
        .brand{display:flex;align-items:center;gap:9px;min-width:0;color:#334155;font-size:.8rem;font-weight:800;letter-spacing:.06em}.brand-mark{display:grid;place-items:center;width:30px;height:30px;flex:0 0 30px;border-radius:9px;background:#312e81;color:#fff;font-size:1rem}.status{display:inline-flex;align-items:center;max-width:100%;gap:8px;margin-top:clamp(22px,4vw,32px);padding:7px 10px;border-radius:7px;background:var(--tone-soft);color:var(--tone);font-size:.75rem;font-weight:800;letter-spacing:.05em}.status i{width:7px;height:7px;flex:0 0 7px;border-radius:50%;background:currentColor}
        h1{margin:18px 0 10px;overflow-wrap:anywhere;font-size:clamp(1.7rem,5vw,2.35rem);line-height:1.15;letter-spacing:-.025em}p{margin:0;overflow-wrap:anywhere;color:var(--muted);font-size:clamp(.94rem,2.4vw,1rem);line-height:1.65}.error-code{margin:26px 0 0;padding-top:18px;border-top:1px solid var(--line);color:#94a3b8;font-size:.82rem}.error-code strong{color:var(--ink)}
        .actions{display:flex;flex-wrap:wrap;gap:10px;margin-top:28px}.btn{display:inline-flex;flex:0 1 auto;align-items:center;justify-content:center;min-height:42px;padding:0 15px;border:1px solid transparent;border-radius:7px;text-decoration:none;font-size:.9rem;font-weight:700;cursor:pointer}.btn-primary{background:var(--primary);color:#fff}.btn-secondary{border-color:#cbd5e1;background:#fff;color:#334155}
        @media (min-width:768px){.error-card{padding:44px 48px}.actions .btn{min-width:132px}}
        @media (max-width:767px){body{min-height:100dvh;place-items:start center}.error-card{margin:auto 0;border-radius:18px}.brand{font-size:.74rem}.status{margin-top:24px}}
        @media (max-width:520px){body{padding:16px}.error-card{padding:28px 24px;border-radius:16px}.actions{flex-direction:column}.btn{width:100%}}
        @media (max-height:620px) and (min-width:521px){body{place-items:start center}.error-card{margin:20px 0;padding:28px 36px}.status{margin-top:18px}.actions{margin-top:20px}.error-code{margin-top:20px}}
        @media(prefers-reduced-motion:reduce){*{scroll-behavior:auto!important}}
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
