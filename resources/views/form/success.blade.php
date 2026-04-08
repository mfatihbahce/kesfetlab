<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $brandName ?? 'Kesfet LAB' }} - Kayıt Başarılı</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --brand-orange: #ff7a00;
            --brand-yellow: #f5d100;
            --brand-dark: #2f3138;
            --brand-dark-soft: #464a55;
        }
        body {
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background:
                radial-gradient(circle at 12% 12%, rgba(245,209,0,.18), transparent 42%),
                radial-gradient(circle at 88% 82%, rgba(255,122,0,.2), transparent 42%),
                linear-gradient(140deg, var(--brand-dark), var(--brand-dark-soft));
            margin: 0;
            padding: 24px 0;
        }
        .success-container {
            background: #fff;
            border-radius: 22px;
            box-shadow: 0 20px 45px rgba(0,0,0,.22);
            max-width: 760px;
            margin: 0 auto;
            border: 1px solid rgba(255,122,0,.18);
            overflow: hidden;
            text-align: center;
        }
        .success-hero {
            background: linear-gradient(120deg, var(--brand-orange), var(--brand-yellow));
            color: #1f2937;
            padding: 22px 20px;
        }
        .brand-logo {
            max-width: 260px;
            max-height: 88px;
            object-fit: contain;
            margin-bottom: 8px;
        }
        .success-content {
            padding: 30px 26px 26px;
        }
        .success-icon {
            width: 92px;
            height: 92px;
            background: linear-gradient(125deg, var(--brand-orange), var(--brand-yellow));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.4rem;
            color: #2f3138;
            font-size: 2.4rem;
            box-shadow: 0 12px 26px rgba(255,122,0,.26);
        }
        .success-title {
            color: #1f2937;
            font-size: 2.05rem;
            font-weight: 700;
            margin-bottom: .7rem;
        }
        .success-message {
            color: #4b5563;
            font-size: 1.05rem;
            line-height: 1.6;
            margin-bottom: 1.2rem;
        }
        .info-box {
            background: #fff8f1;
            border-radius: 15px;
            padding: 1.5rem;
            margin: 1.3rem 0;
            border: 1px solid rgba(255,122,0,.22);
            text-align: left;
        }
        .info-box h5 {
            color: #2f3138;
            margin-bottom: .85rem;
            font-weight: 700;
        }
        .info-box i {
            color: #ff7a00;
        }
        .btn-brand {
            background: linear-gradient(120deg, var(--brand-orange), var(--brand-yellow));
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: 700;
            text-decoration: none;
            display: inline-block;
            color: #2f3138;
            box-shadow: 0 10px 20px rgba(255,122,0,.25);
        }
        .btn-brand:hover {
            filter: brightness(.97);
            color: #2f3138;
        }
        .trust-note {
            color: #6b7280;
            font-size: .9rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="success-container">
            <div class="success-hero">
                @if(!empty($brandLogoPath))
                    <img src="{{ asset(ltrim($brandLogoPath, '/')) }}" alt="{{ $brandName }}" class="brand-logo">
                @else
                    <h1 class="h3 mb-2 fw-bold">{{ $brandName ?? 'Kesfet LAB' }}</h1>
                @endif
                <p class="mb-0 fw-semibold">Öğrenci Kayıt Formu</p>
            </div>

            <div class="success-content">
                <div class="success-icon">
                    <i class="fas fa-check"></i>
                </div>
                
                <h1 class="success-title">Kayıt Başarılı!</h1>
                
                <p class="success-message">
                    Öğrenci kayıt formunuz başarıyla alınmıştır.
                    En kısa sürede sizinle iletişime geçilecektir.
                </p>

                @if(session('success'))
                    <div class="alert alert-success">
                        <i class="fas fa-info-circle me-2"></i>
                        {{ session('success') }}
                    </div>
                @endif

                <div class="info-box">
                    <h5><i class="fas fa-clock me-2"></i>Sonraki Adımlar</h5>
                    <ul class="list-unstyled mb-0">
                        <li><i class="fas fa-arrow-right me-2"></i>Kayıt formunuz incelenecek.</li>
                        <li><i class="fas fa-arrow-right me-2"></i>Uygun grup ve ders programı belirlenecek.</li>
                        <li><i class="fas fa-arrow-right me-2"></i>Telefon veya e-posta ile bilgi verilecek.</li>
                        <li><i class="fas fa-arrow-right me-2"></i>Kesin kayıt işlemleri tamamlanacak.</li>
                    </ul>
                </div>

                <div class="mt-4">
                    <a href="{{ route('form.index') }}" class="btn-brand">
                        <i class="fas fa-home me-2"></i>Ana Sayfaya Dön
                    </a>
                </div>

                <div class="mt-4">
                    <small class="trust-note">
                        <i class="fas fa-shield-alt me-1"></i>
                        Kişisel verileriniz güvenle saklanmaktadır.
                    </small>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
