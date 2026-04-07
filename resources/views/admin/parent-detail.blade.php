@extends('admin.layout')

@section('title', 'Veli Detayı - Keşfet LAB')

@section('page-title', 'Veli Detayı')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Veli Bilgileri</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Adı Soyadı:</strong> {{ $parent->full_name }}
                        </div>
                        <div class="col-md-6">
                            <strong>Telefon:</strong> {{ $parent->phone }}
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>E-posta:</strong> {{ $parent->email ?? 'Belirtilmemiş' }}
                        </div>
                        <div class="col-md-6">
                            <strong>TC Kimlik:</strong> {{ $parent->tc_identity ?? 'Belirtilmemiş' }}
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Adres:</strong> {{ $parent->address ?? 'Belirtilmemiş' }}
                        </div>
                        <div class="col-md-6">
                            <strong>Durum:</strong>
                            @if($parent->status == 'active')
                                <span class="badge bg-success">Aktif</span>
                            @else
                                <span class="badge bg-danger">Pasif</span>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Kayıt Tarihi:</strong> {{ $parent->created_at->format('d.m.Y H:i') }}
                        </div>
                        <div class="col-md-6">
                            <strong>Son Güncelleme:</strong> {{ $parent->updated_at->format('d.m.Y H:i') }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Bağlı Öğrenciler</h5>
                </div>
                <div class="card-body">
                    @if($parent->students->isEmpty())
                        <p>Bu veliye bağlı öğrenci bulunmamaktadır.</p>
                    @else
                        <ul class="list-group">
                            @foreach($parent->students as $student)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    {{ $student->full_name }} (TC: {{ $student->tc_identity }})
                                    <span class="badge bg-primary rounded-pill">{{ $student->pivot->relationship }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Giriş Kodu Yönetimi</h5>
                </div>
                <div class="card-body">
                    <p>Veliye özel giriş kodu oluşturabilir veya mevcut kodu görüntüleyebilirsiniz.</p>
                    <div class="mb-3">
                        <strong>Mevcut Kod:</strong>
                        <span id="tempCodeDisplay" class="badge bg-info text-dark fs-5">{{ $parent->temp_code ?? 'Yok' }}</span>
                    </div>
                    <div class="mb-3">
                        <strong>Şifre Değiştirildi mi?:</strong>
                        <span id="passwordChangedDisplay" class="badge {{ $parent->password_changed ? 'bg-success' : 'bg-warning' }}">
                            {{ $parent->password_changed ? 'Evet' : 'Hayır' }}
                        </span>
                    </div>
                    <button type="button" class="btn btn-primary w-100 mb-2" id="generateCodeBtn">
                        <i class="fas fa-sync-alt"></i> Yeni Kod Üret
                    </button>
                    <button type="button" class="btn btn-warning w-100" id="resetPasswordChangedBtn">
                        <i class="fas fa-undo"></i> Şifre Değiştirildi Durumunu Sıfırla
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        const parentId = {{ $parent->id }};
        const baseUrl = '{{ url('/') }}'; // Laravel'in base URL'sini al

        // Yeni Kod Üretme
        $('#generateCodeBtn').on('click', function() {
            if (confirm('Yeni bir giriş kodu üretmek istediğinizden emin misiniz? Mevcut kod değişecektir.')) {
                $.ajax({
                    url: `${baseUrl}/admin/parents/${parentId}/generate-code`,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#tempCodeDisplay').text(response.data.temp_code);
                            toastr.success(response.message);
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function(xhr) {
                        toastr.error('Kod üretilirken bir hata oluştu.');
                        console.error('Error generating code:', xhr.responseText);
                    }
                });
            }
        });

        // Şifre Değiştirildi Durumunu Sıfırlama
        $('#resetPasswordChangedBtn').on('click', function() {
            if (confirm('Veli şifresinin değiştirildi durumunu sıfırlamak istediğinizden emin misiniz? Veli bir sonraki girişinde şifresini değiştirmek zorunda kalacaktır.')) {
                $.ajax({
                    url: `${baseUrl}/admin/parents/${parentId}/reset-password-changed`,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#passwordChangedDisplay').text('Hayır').removeClass('bg-success').addClass('bg-warning');
                            toastr.success(response.message);
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function(xhr) {
                        toastr.error('Durum sıfırlanırken bir hata oluştu.');
                        console.error('Error resetting password changed status:', xhr.responseText);
                    }
                });
            }
        });
    });
</script>
@endsection
