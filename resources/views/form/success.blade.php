<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keşfet LAB - Kayıt Başarılı</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .success-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            max-width: 600px;
            text-align: center;
            padding: 3rem;
        }
        .success-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            color: white;
            font-size: 3rem;
        }
        .success-title {
            color: #28a745;
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }
        .success-message {
            color: #666;
            font-size: 1.1rem;
            line-height: 1.6;
            margin-bottom: 2rem;
        }
        .info-box {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 1.5rem;
            margin: 2rem 0;
            border-left: 4px solid #667eea;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
            color: white;
        }
        .contact-info {
            background: #e3f2fd;
            border-radius: 15px;
            padding: 1.5rem;
            margin: 2rem 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="success-container">
            <div class="success-icon">
                <i class="fas fa-check"></i>
            </div>
            
            <h1 class="success-title">Kayıt Başarılı!</h1>
            
            <p class="success-message">
                Öğrenci kayıt formunuz başarıyla alınmıştır. 
                En kısa sürede size dönüş yapılacaktır.
            </p>

            @if(session('success'))
                <div class="alert alert-success">
                    <i class="fas fa-info-circle me-2"></i>
                    {{ session('success') }}
                </div>
            @endif

            <div class="info-box">
                <h5><i class="fas fa-clock me-2"></i>Sonraki Adımlar</h5>
                <ul class="list-unstyled text-start">
                    <li><i class="fas fa-arrow-right me-2 text-primary"></i>Kayıt formunuz incelenecek</li>
                    <li><i class="fas fa-arrow-right me-2 text-primary"></i>Uygun grup ve ders programı belirlenecek</li>
                    <li><i class="fas fa-arrow-right me-2 text-primary"></i>Size telefon veya e-posta ile bilgi verilecek</li>
                    <li><i class="fas fa-arrow-right me-2 text-primary"></i>Kesin kayıt işlemleri tamamlanacak</li>
                </ul>
            </div>

            <div class="contact-info">
                <h6><i class="fas fa-phone me-2"></i>İletişim Bilgileri</h6>
                <p class="mb-1"><strong>Telefon:</strong> +90 (XXX) XXX XX XX</p>
                <p class="mb-1"><strong>E-posta:</strong> info@kesfetlab.com</p>
                <p class="mb-0"><strong>Adres:</strong> Keşfet LAB Eğitim Merkezi</p>
            </div>

            <div class="mt-4">
                <a href="{{ route('form.index') }}" class="btn btn-primary">
                    <i class="fas fa-home me-2"></i>Ana Sayfaya Dön
                </a>
            </div>

            <div class="mt-4">
                <small class="text-muted">
                    <i class="fas fa-shield-alt me-1"></i>
                    Kişisel verileriniz güvenle saklanmaktadır.
                </small>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
