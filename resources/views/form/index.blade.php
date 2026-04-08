<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $brandName ?? 'Kesfet LAB' }} - Öğrenci Kayıt Formu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --brand-orange: #ff7a00;
            --brand-yellow: #f5d100;
            --brand-dark: #2f3138;
            --brand-dark-soft: #464a55;
            --card-radius: 22px;
        }
        body {
            min-height: 100vh;
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background:
                radial-gradient(circle at 10% 10%, rgba(245,209,0,.18), transparent 40%),
                radial-gradient(circle at 90% 80%, rgba(255,122,0,.2), transparent 40%),
                linear-gradient(140deg, var(--brand-dark), var(--brand-dark-soft));
            padding: 24px 0;
        }
        .form-shell {
            max-width: 980px;
            margin: 0 auto;
            background: #fff;
            border-radius: var(--card-radius);
            box-shadow: 0 20px 45px rgba(0,0,0,.22);
            overflow: hidden;
            border: 1px solid rgba(255,122,0,.18);
        }
        .form-hero {
            background: linear-gradient(120deg, var(--brand-orange), var(--brand-yellow));
            color: #1f2937;
            text-align: center;
            padding: 26px 20px;
        }
        .brand-logo {
            max-width: 280px;
            max-height: 96px;
            object-fit: contain;
            margin-bottom: 8px;
        }
        .form-hero h1 {
            margin: 4px 0 2px;
            font-weight: 700;
            font-size: 2rem;
        }
        .form-hero p { margin: 0; font-weight: 500; }
        .form-content { padding: 24px; }
        .section {
            border: 1px solid #ececec;
            border-radius: 16px;
            padding: 18px;
            margin-bottom: 18px;
            background: #fff;
        }
        .section-title {
            margin: 0 0 14px;
            font-size: 1.08rem;
            color: var(--brand-orange);
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .form-label {
            font-weight: 600;
            color: #374151;
            margin-bottom: 6px;
        }
        .form-control, .form-check-input {
            border-radius: 10px;
            border-color: #d1d5db;
        }
        .form-control:focus {
            border-color: var(--brand-orange);
            box-shadow: 0 0 0 .2rem rgba(255,122,0,.18);
        }
        .required { color: #dc2626; }
        .workshop-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 10px;
        }
        .workshop-item {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 10px 12px;
            transition: .2s ease;
            background: #fafafa;
        }
        .workshop-item:hover {
            border-color: var(--brand-orange);
            background: #fff8f1;
        }
        .workshop-item .form-check-label {
            font-weight: 600;
            color: #374151;
            width: 100%;
            cursor: pointer;
        }
        .submit-wrap { text-align: center; margin-top: 8px; }
        .btn-submit {
            border: none;
            border-radius: 999px;
            padding: 13px 34px;
            font-weight: 700;
            color: #2f3138;
            background: linear-gradient(120deg, var(--brand-orange), var(--brand-yellow));
            box-shadow: 0 10px 20px rgba(255,122,0,.25);
        }
        .btn-submit:hover { filter: brightness(.97); }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-shell">
            <div class="form-hero">
                @if(!empty($brandLogoPath))
                    <img src="{{ asset(ltrim($brandLogoPath, '/')) }}" alt="{{ $brandName }}" class="brand-logo">
                @else
                    <h1>{{ $brandName ?? 'Kesfet LAB' }}</h1>
                @endif
                <p>Öğrenci Kayıt Formu</p>
            </div>

            <div class="form-content">
                @if(session('error'))
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
                    </div>
                @endif

                <form action="{{ route('form.submit') }}" method="POST">
                    @csrf

                    <div class="section">
                        <h4 class="section-title"><i class="fas fa-user-graduate"></i> Öğrenci Bilgileri</h4>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="first_name" class="form-label">Ad <span class="required">*</span></label>
                                <input type="text" class="form-control @error('first_name') is-invalid @enderror" id="first_name" name="first_name" value="{{ old('first_name') }}" required>
                                @error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="last_name" class="form-label">Soyad <span class="required">*</span></label>
                                <input type="text" class="form-control @error('last_name') is-invalid @enderror" id="last_name" name="last_name" value="{{ old('last_name') }}" required>
                                @error('last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="tc_identity" class="form-label">T.C. Kimlik Numarası <span class="required">*</span></label>
                                <input type="text" class="form-control @error('tc_identity') is-invalid @enderror" id="tc_identity" name="tc_identity" value="{{ old('tc_identity') }}" maxlength="11" pattern="[0-9]{11}" required>
                                @error('tc_identity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="birth_date" class="form-label">Doğum Tarihi <span class="required">*</span></label>
                                <input type="date" class="form-control @error('birth_date') is-invalid @enderror" id="birth_date" name="birth_date" value="{{ old('birth_date') }}" required>
                                @error('birth_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-12">
                                <label for="address" class="form-label">Adres <span class="required">*</span></label>
                                <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" rows="2" required>{{ old('address') }}</textarea>
                                @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="emergency_contact_relation" class="form-label">Yakınlık Derecesi</label>
                                <input type="text" class="form-control @error('emergency_contact_relation') is-invalid @enderror" id="emergency_contact_relation" name="emergency_contact_relation" value="{{ old('emergency_contact_relation') }}" placeholder="Örn: Anne, Baba, Dayı">
                                @error('emergency_contact_relation')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="school_name" class="form-label">Şuan Okuduğu Okul Adı <span class="required">*</span></label>
                                <input type="text" class="form-control @error('school_name') is-invalid @enderror" id="school_name" name="school_name" value="{{ old('school_name') }}" required>
                                @error('school_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-12">
                                <label for="health_condition" class="form-label">SAĞLIK PROBLEMİ (VARSA)</label>
                                <textarea class="form-control @error('health_condition') is-invalid @enderror" id="health_condition" name="health_condition" rows="2">{{ old('health_condition') }}</textarea>
                                @error('health_condition')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="section">
                        <h4 class="section-title"><i class="fas fa-users"></i> Veli Bilgileri</h4>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="parent_first_name" class="form-label">Veli Adı <span class="required">*</span></label>
                                <input type="text" class="form-control @error('parent_first_name') is-invalid @enderror" id="parent_first_name" name="parent_first_name" value="{{ old('parent_first_name') }}" required>
                                @error('parent_first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="parent_last_name" class="form-label">Veli Soyadı <span class="required">*</span></label>
                                <input type="text" class="form-control @error('parent_last_name') is-invalid @enderror" id="parent_last_name" name="parent_last_name" value="{{ old('parent_last_name') }}" required>
                                @error('parent_last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="parent_phone" class="form-label">Telefon Numarası <span class="required">*</span></label>
                                <input type="tel" class="form-control @error('parent_phone') is-invalid @enderror" id="parent_phone" name="parent_phone" value="{{ old('parent_phone') }}" maxlength="11" pattern="0[0-9]{10}" required>
                                @error('parent_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="parent_email" class="form-label">E-posta Adresi</label>
                                <input type="email" class="form-control @error('parent_email') is-invalid @enderror" id="parent_email" name="parent_email" value="{{ old('parent_email') }}">
                                @error('parent_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-12">
                                <label for="parent_profession" class="form-label">Veli Mesleği</label>
                                <input type="text" class="form-control @error('parent_profession') is-invalid @enderror" id="parent_profession" name="parent_profession" value="{{ old('parent_profession') }}">
                                @error('parent_profession')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="section">
                        <h4 class="section-title"><i class="fas fa-phone-alt"></i> ACİL DURUM İLETİŞİM</h4>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="emergency_contact_name" class="form-label">Ad Soyad <span class="required">*</span></label>
                                <input type="text" class="form-control @error('emergency_contact_name') is-invalid @enderror" id="emergency_contact_name" name="emergency_contact_name" value="{{ old('emergency_contact_name') }}" required>
                                @error('emergency_contact_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="emergency_contact_phone" class="form-label">Telefon Numarası <span class="required">*</span></label>
                                <input type="tel" class="form-control @error('emergency_contact_phone') is-invalid @enderror" id="emergency_contact_phone" name="emergency_contact_phone" value="{{ old('emergency_contact_phone') }}" maxlength="11" pattern="0[0-9]{10}" required>
                                @error('emergency_contact_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="section">
                        <h4 class="section-title"><i class="fas fa-flask"></i> Katılmak İstediğiniz Sınıflar</h4>
                        <div class="workshop-grid">
                            @foreach($workshops as $workshop)
                                <div class="workshop-item">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="{{ $workshop->id }}" id="workshop_{{ $workshop->id }}" name="workshop_ids[]" {{ in_array($workshop->id, old('workshop_ids', [])) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="workshop_{{ $workshop->id }}">
                                            {{ $workshop->name }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @error('workshop_ids')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
                        @error('workshop_ids.*')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
                    </div>

                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle me-2"></i>Kişisel Verilerin Korunması</h6>
                        <p class="mb-0 small">
                            Bu form aracılığıyla toplanan verileriniz, yalnızca eğitim hizmetinin sağlanması amacıyla kullanılacaktır.
                        </p>
                    </div>

                    <div class="submit-wrap">
                        <button type="submit" class="btn-submit">
                            <i class="fas fa-paper-plane me-2"></i>Kayıt Formunu Gönder
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('tc_identity').addEventListener('input', function () {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
        function bindPhoneValidation(id) {
            const input = document.getElementById(id);
            if (!input) return;

            input.addEventListener('input', function () {
                this.value = this.value.replace(/[^0-9]/g, '').slice(0, 11);
                this.setCustomValidity('');
            });

            input.addEventListener('blur', function () {
                let digits = this.value.replace(/[^0-9]/g, '');

                // Kullanici 10 hane ve basinda 0 olmadan girdiyse otomatik tamamla.
                if (digits.length === 10 && !digits.startsWith('0')) {
                    digits = '0' + digits;
                }

                this.value = digits;

                if (digits.startsWith('0') && digits.length !== 11) {
                    this.setCustomValidity('Telefon numarası 0 ile başlıyorsa 11 haneye tamamlanmalıdır.');
                } else if (digits.length > 0 && !/^0\d{10}$/.test(digits)) {
                    this.setCustomValidity('Telefon numarası 0 ile başlamalı ve 11 hane olmalıdır.');
                } else {
                    this.setCustomValidity('');
                }
            });
        }

        bindPhoneValidation('parent_phone');
        bindPhoneValidation('emergency_contact_phone');

        document.addEventListener('submit', function (event) {
            const form = event.target;
            if (!(form instanceof HTMLFormElement)) return;
            if (form.dataset.submitting === '1') {
                event.preventDefault();
                return;
            }
            form.dataset.submitting = '1';
            const submitButtons = form.querySelectorAll('button[type="submit"], input[type="submit"]');
            submitButtons.forEach((btn) => {
                btn.disabled = true;
                btn.classList.add('disabled');
            });
        }, true);
    </script>
</body>
</html>
