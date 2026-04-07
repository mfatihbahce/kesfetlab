<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keşfet LAB - Öğrenci Kayıt Formu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .form-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            margin: 2rem auto;
            max-width: 800px;
        }
        .form-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            border-radius: 20px 20px 0 0;
            text-align: center;
        }
        .form-body {
            padding: 2rem;
        }
        .section-title {
            color: #667eea;
            border-bottom: 2px solid #667eea;
            padding-bottom: 0.5rem;
            margin-bottom: 1.5rem;
        }
        .form-label {
            font-weight: 600;
            color: #333;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: 600;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .alert {
            border-radius: 10px;
            border: none;
        }
        .required {
            color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-container">
            <div class="form-header">
                <h1><i class="fas fa-graduation-cap me-2"></i>Keşfet LAB</h1>
                <p class="mb-0">Öğrenci Kayıt Formu</p>
            </div>

            <div class="form-body">
                @if(session('error'))
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        {{ session('error') }}
                    </div>
                @endif

                <form action="{{ route('form.submit') }}" method="POST">
                    @csrf
                    
                    <!-- Öğrenci Bilgileri -->
                    <h4 class="section-title">
                        <i class="fas fa-user-graduate me-2"></i>Öğrenci Bilgileri
                    </h4>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="first_name" class="form-label">
                                Ad <span class="required">*</span>
                            </label>
                            <input type="text" class="form-control @error('first_name') is-invalid @enderror" 
                                   id="first_name" name="first_name" value="{{ old('first_name') }}" required>
                            @error('first_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="last_name" class="form-label">
                                Soyad <span class="required">*</span>
                            </label>
                            <input type="text" class="form-control @error('last_name') is-invalid @enderror" 
                                   id="last_name" name="last_name" value="{{ old('last_name') }}" required>
                            @error('last_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="tc_identity" class="form-label">
                                T.C. Kimlik Numarası <span class="required">*</span>
                            </label>
                            <input type="text" class="form-control @error('tc_identity') is-invalid @enderror" 
                                   id="tc_identity" name="tc_identity" value="{{ old('tc_identity') }}" 
                                   maxlength="11" pattern="[0-9]{11}" required>
                            @error('tc_identity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="birth_date" class="form-label">
                                Doğum Tarihi <span class="required">*</span>
                            </label>
                            <input type="date" class="form-control @error('birth_date') is-invalid @enderror" 
                                   id="birth_date" name="birth_date" value="{{ old('birth_date') }}" required>
                            @error('birth_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="address" class="form-label">
                            Adres <span class="required">*</span>
                        </label>
                        <textarea class="form-control @error('address') is-invalid @enderror" 
                                  id="address" name="address" rows="3" required>{{ old('address') }}</textarea>
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="health_condition" class="form-label">
                            Sağlık Durumu
                        </label>
                        <textarea class="form-control @error('health_condition') is-invalid @enderror" 
                                  id="health_condition" name="health_condition" rows="2">{{ old('health_condition') }}</textarea>
                        @error('health_condition')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Veli Bilgileri -->
                    <h4 class="section-title">
                        <i class="fas fa-users me-2"></i>Veli Bilgileri
                    </h4>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="parent_first_name" class="form-label">
                                Veli Adı <span class="required">*</span>
                            </label>
                            <input type="text" class="form-control @error('parent_first_name') is-invalid @enderror" 
                                   id="parent_first_name" name="parent_first_name" value="{{ old('parent_first_name') }}" required>
                            @error('parent_first_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="parent_last_name" class="form-label">
                                Veli Soyadı <span class="required">*</span>
                            </label>
                            <input type="text" class="form-control @error('parent_last_name') is-invalid @enderror" 
                                   id="parent_last_name" name="parent_last_name" value="{{ old('parent_last_name') }}" required>
                            @error('parent_last_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="parent_phone" class="form-label">
                                Telefon Numarası <span class="required">*</span>
                            </label>
                            <input type="tel" class="form-control @error('parent_phone') is-invalid @enderror" 
                                   id="parent_phone" name="parent_phone" value="{{ old('parent_phone') }}" required>
                            @error('parent_phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="parent_email" class="form-label">
                                E-posta Adresi
                            </label>
                            <input type="email" class="form-control @error('parent_email') is-invalid @enderror" 
                                   id="parent_email" name="parent_email" value="{{ old('parent_email') }}">
                            @error('parent_email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="parent_profession" class="form-label">
                            Veli Mesleği
                        </label>
                        <input type="text" class="form-control @error('parent_profession') is-invalid @enderror" 
                               id="parent_profession" name="parent_profession" value="{{ old('parent_profession') }}">
                        @error('parent_profession')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Acil Durum Kişisi -->
                    <h4 class="section-title">
                        <i class="fas fa-phone-alt me-2"></i>Acil Durum Kişisi
                    </h4>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="emergency_contact_name" class="form-label">
                                Ad Soyad <span class="required">*</span>
                            </label>
                            <input type="text" class="form-control @error('emergency_contact_name') is-invalid @enderror" 
                                   id="emergency_contact_name" name="emergency_contact_name" value="{{ old('emergency_contact_name') }}" required>
                            @error('emergency_contact_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="emergency_contact_phone" class="form-label">
                                Telefon Numarası <span class="required">*</span>
                            </label>
                            <input type="tel" class="form-control @error('emergency_contact_phone') is-invalid @enderror" 
                                   id="emergency_contact_phone" name="emergency_contact_phone" value="{{ old('emergency_contact_phone') }}" required>
                            @error('emergency_contact_phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="emergency_contact_relation" class="form-label">
                            İlişki
                        </label>
                        <input type="text" class="form-control @error('emergency_contact_relation') is-invalid @enderror" 
                               id="emergency_contact_relation" name="emergency_contact_relation" 
                               value="{{ old('emergency_contact_relation') }}" placeholder="Örn: Anne, Baba, Dayı">
                        @error('emergency_contact_relation')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Atölye Seçimi -->
                    <h4 class="section-title">
                        <i class="fas fa-flask me-2"></i>Atölye Seçimi
                    </h4>
                    
                    <div class="mb-3">
                        <label for="workshop_id" class="form-label">
                            Katılmak İstediğiniz Atölye <span class="required">*</span>
                        </label>
                        <select class="form-select @error('workshop_id') is-invalid @enderror" 
                                id="workshop_id" name="workshop_id" required>
                            <option value="">Atölye Seçiniz</option>
                            @foreach($workshops as $workshop)
                                <option value="{{ $workshop->id }}" {{ old('workshop_id') == $workshop->id ? 'selected' : '' }}>
                                    {{ $workshop->name }} 
                                    @if($workshop->price > 0)
                                        ({{ number_format($workshop->price, 2) }} ₺)
                                    @else
                                        (Ücretsiz)
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        @error('workshop_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- KVKK Bilgilendirmesi -->
                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle me-2"></i>Kişisel Verilerin Korunması</h6>
                        <p class="mb-0 small">
                            Bu form aracılığıyla toplanan kişisel verileriniz, 6698 sayılı Kişisel Verilerin Korunması Kanunu kapsamında 
                            sadece eğitim hizmetlerinin sağlanması amacıyla işlenecektir. Verileriniz güvenle saklanacak ve üçüncü taraflarla paylaşılmayacaktır.
                        </p>
                    </div>

                    <div class="text-center">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-paper-plane me-2"></i>Kayıt Formunu Gönder
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // T.C. Kimlik numarası sadece rakam girişi
        document.getElementById('tc_identity').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });

        // Telefon numaraları için format
        document.getElementById('parent_phone').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });

        document.getElementById('emergency_contact_phone').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    </script>
</body>
</html>
