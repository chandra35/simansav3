<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login As Ditutup - SIMANSA</title>
    <style>
        * { box-sizing: border-box; }
        body {
            align-items: center;
            background: #eef3ff;
            color: #172554;
            display: flex;
            font-family: Arial, sans-serif;
            justify-content: center;
            margin: 0;
            min-height: 100vh;
            padding: 1.5rem;
        }
        main {
            background: #fff;
            border: 1px solid #dbe5ff;
            border-radius: 1rem;
            box-shadow: 0 1rem 2.5rem rgba(30, 64, 175, .12);
            max-width: 32rem;
            padding: 2rem;
            text-align: center;
            width: 100%;
        }
        .icon {
            align-items: center;
            background: #dcfce7;
            border-radius: 50%;
            color: #15803d;
            display: inline-flex;
            font-size: 1.75rem;
            height: 4rem;
            justify-content: center;
            width: 4rem;
        }
        h1 { font-size: 1.5rem; margin: 1rem 0 .5rem; }
        p { color: #64748b; line-height: 1.6; }
        a {
            background: #1d4ed8;
            border-radius: .55rem;
            color: #fff;
            display: inline-block;
            margin-top: .75rem;
            padding: .7rem 1rem;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <main>
        <div class="icon">✓</div>
        <h1>Mode Login As telah ditutup</h1>
        <p>Tab ini akan ditutup dan SIMANSA akan kembali memfokuskan tab admin Anda.</p>
        <a href="{{ $adminUrl }}">Buka halaman admin</a>
    </main>

    <script>
        (function () {
            const message = {
                type: 'simansa:impersonation-ended',
                targetType: @json($targetType)
            };

            if (window.opener && !window.opener.closed) {
                window.opener.postMessage(message, window.location.origin);
                window.opener.focus();
            }

            window.setTimeout(function () {
                window.close();
            }, 350);
        })();
    </script>
</body>
</html>
