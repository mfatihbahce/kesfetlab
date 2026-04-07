<!doctype html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Giris Yap | Kesfet Lab</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            font-family: "Segoe UI", Arial, sans-serif;
            background: linear-gradient(135deg, #1d4ed8, #7c3aed);
            display: grid;
            place-items: center;
            padding: 24px;
        }
        .login-card {
            width: 100%;
            max-width: 440px;
            background: #fff;
            border-radius: 20px;
            padding: 32px 28px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.18);
        }
        .title {
            margin: 0 0 6px 0;
            font-size: 28px;
            color: #111827;
            text-align: center;
        }
        .subtitle {
            margin: 0 0 24px 0;
            text-align: center;
            color: #6b7280;
            font-size: 14px;
        }
        .field { margin-bottom: 16px; }
        .label {
            display: block;
            margin-bottom: 8px;
            color: #374151;
            font-size: 14px;
            font-weight: 600;
        }
        .input {
            width: 100%;
            border: 1px solid #d1d5db;
            border-radius: 12px;
            height: 46px;
            padding: 0 14px;
            font-size: 15px;
            transition: border-color .2s, box-shadow .2s;
        }
        .input:focus {
            outline: none;
            border-color: #4f46e5;
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.12);
        }
        .input-error { border-color: #dc2626; }
        .error {
            color: #dc2626;
            font-size: 13px;
            margin-top: 6px;
        }
        .row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin: 6px 0 18px;
        }
        .remember {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            color: #374151;
        }
        .link {
            color: #4f46e5;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
        }
        .link:hover { text-decoration: underline; }
        .btn {
            width: 100%;
            height: 48px;
            border: 0;
            border-radius: 12px;
            background: #4f46e5;
            color: white;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: background .2s, transform .08s;
        }
        .btn:hover { background: #4338ca; }
        .btn:active { transform: translateY(1px); }
    </style>
</head>
<body>
    <div class="login-card">
        <h1 class="title">Yonetici Girisi</h1>
        <p class="subtitle">Panelinize erismek icin bilgilerinizi girin.</p>

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="field">
                <label class="label" for="email">E-posta</label>
                <input
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    class="input @error('email') input-error @enderror"
                    required
                    autocomplete="email"
                    autofocus
                    placeholder="ornek@kesfetlab.com"
                >
                @error('email')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="field">
                <label class="label" for="password">Sifre</label>
                <input
                    id="password"
                    type="password"
                    name="password"
                    class="input @error('password') input-error @enderror"
                    required
                    autocomplete="current-password"
                    placeholder="********"
                >
                @error('password')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="row">
                <label class="remember" for="remember">
                    <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                    Beni hatirla
                </label>
                @if (Route::has('password.request'))
                    <a class="link" href="{{ route('password.request') }}">Sifremi unuttum</a>
                @endif
            </div>

            <button type="submit" class="btn">Giris Yap</button>
        </form>
    </div>
</body>
</html>
