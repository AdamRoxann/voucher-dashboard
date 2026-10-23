<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login — Voucher Dashboard</title>
    <style>
        :root{--blue:#0758DD;--navy:#0B396F;--yellow:#FEB50E;--orange:#FE7222;--bg:#f5f4ee;--text:#172b45;--muted:#68778a}
        *{box-sizing:border-box}body{margin:0;min-height:100vh;display:grid;place-items:center;padding:24px;background:linear-gradient(135deg,#eef5ff 0%,var(--bg) 70%);font-family:Inter,Segoe UI,Arial,sans-serif;color:var(--text)}
        .shell{width:min(100%,430px)}.brand{background:linear-gradient(120deg,var(--navy),var(--blue));color:white;padding:28px 30px;border-radius:18px 18px 0 0;position:relative;overflow:hidden}.brand:after{content:"";position:absolute;width:110px;height:110px;border:18px solid var(--yellow);border-radius:50%;right:-42px;top:-48px;opacity:.95}.eyebrow{font-size:12px;letter-spacing:.13em;text-transform:uppercase;opacity:.8}.brand h1{margin:10px 0 5px;font-size:26px}.brand p{margin:0;font-size:14px;opacity:.9}.card{background:#fff;padding:28px 30px;border-radius:0 0 18px 18px;box-shadow:0 18px 50px #0b396f17;border-top:4px solid var(--orange)}label{display:block;font-weight:650;font-size:13px;margin:17px 0 7px}input[type=email],input[type=password]{width:100%;padding:12px 13px;border:1px solid #d6deea;border-radius:8px;font:inherit;outline:none}input:focus{border-color:var(--blue);box-shadow:0 0 0 3px #0758dd1a}.remember{display:flex;align-items:center;gap:8px;color:var(--muted);font-size:13px;margin:16px 0}.btn{width:100%;border:0;border-radius:8px;padding:13px;background:var(--blue);color:white;font-weight:700;font-size:15px;cursor:pointer}.btn:hover{background:var(--navy)}.error{color:#b42318;background:#fff1f0;border:1px solid #ffd0cc;border-radius:8px;padding:10px 12px;font-size:13px}.hint{color:var(--muted);font-size:12px;text-align:center;margin:18px 0 0}
    </style>
</head>
<body><main class="shell">
    <header class="brand"><div class="eyebrow">Voucher management</div><h1>Welcome back</h1><p>Sign in to manage campaign claims.</p></header>
    <section class="card">
        @if ($errors->any())<div class="error">{{ $errors->first() }}</div>@endif
        <form method="POST" action="{{ route('login.store') }}">@csrf
            <label for="email">Email address</label><input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" required autofocus>
            <label for="password">Password</label><input id="password" name="password" type="password" autocomplete="current-password" required>
            <label class="remember"><input type="checkbox" name="remember" value="1"> Remember me</label>
            <button class="btn" type="submit">Sign in</button>
        </form><p class="hint">Access is managed by your administrator. Public registration is disabled.</p>
    </section>
</main></body></html>
