<!doctype html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Giris Yap | {{ $brandName ?? 'Kesfet LAB' }}</title>
    <style>
        * { box-sizing: border-box; }
        :root {
            --brand-orange: #ff7a00;
            --brand-yellow: #f5d100;
            --brand-dark: #2f3138;
            --brand-dark-soft: #464a55;
        }
        body {
            margin: 0;
            min-height: 100vh;
            font-family: "Segoe UI", Arial, sans-serif;
            background: linear-gradient(140deg, var(--brand-dark), var(--brand-dark-soft));
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
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.28);
            border-top: 5px solid var(--brand-orange);
        }
        .brand-logo {
            display: block;
            max-width: 220px;
            max-height: 86px;
            margin: 0 auto 12px;
            object-fit: contain;
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
            border-color: var(--brand-orange);
            box-shadow: 0 0 0 4px rgba(255, 122, 0, 0.16);
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
            color: var(--brand-orange);
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
            background: linear-gradient(120deg, var(--brand-orange), var(--brand-yellow));
            color: var(--brand-dark);
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: background .2s, transform .08s;
        }
        .btn:hover { filter: brightness(0.96); }
        .btn:active { transform: translateY(1px); }
    </style>
</head>
<body>
    <div class="login-card">
        @if(!empty($brandLogoPath))
            <img src="{{ asset(ltrim($brandLogoPath, '/')) }}" alt="{{ $brandName }}" class="brand-logo">
        @else
            <h1 class="title">{{ $brandName ?? 'Kesfet LAB' }}</h1>
        @endif
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
