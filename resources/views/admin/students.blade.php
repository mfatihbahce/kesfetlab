@extends('admin.layout')

@section('title', 'Öğrenci Yönetimi - Keşfet LAB')
@section('page-title', 'Öğrenci Yönetimi')

@section('content')
<div class="row">
    <div class="col-12">
        <!-- Filtreler -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.students') }}" class="row g-3">
                    <div class="col-md-3">
                        <label for="status" class="form-label">Kayıt Durumu</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">Tümü</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Beklemede</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Onaylandı</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Reddedildi</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="search" class="form-label">Arama</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               value="{{ request('search') }}" placeholder="Ad, soyad veya T.C. kimlik...">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-2"></i>Filtrele
                            </button>
                            <a href="{{ route('admin.students') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>Temizle
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Öğrenci Tablosu -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-user-graduate me-2"></i>
                    Öğrenci Listesi
                </h5>
                <span class="badge bg-primary fs-6">{{ $students->total() }} öğrenci</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 25%;">Öğrenci Bilgileri</th>
                                <th style="width: 20%;">Veli Bilgileri</th>
                                <th style="width: 20%;">Acil Durum Iletisim</th>
                                <th style="width: 10%;">Durum</th>
                                <th style="width: 10%;">Tarih</th>
                                <th style="width: 15%;">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($students as $student)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-3" 
                                             style="width: 40px; height: 40px;">
                                            <i class="fas fa-user text-white"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold">{{ $student->full_name }}</div>
                                            <div class="text-muted small">
                                                <i class="fas fa-id-card me-1"></i>{{ $student->tc_identity }}
                                            </div>
                                            <div class="text-muted small">
                                                <i class="fas fa-birthday-cake me-1"></i>{{ $student->birth_date->format('d.m.Y') }} ({{ $student->age }} yaş)
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-bold">{{ $student->parent_full_name }}</div>
                                    <div class="text-muted small">
                                        <i class="fas fa-phone me-1"></i>{{ $student->parent_phone }}
                                    </div>
                                    @if($student->parent_email)
                                    <div class="text-muted small">
                                        <i class="fas fa-envelope me-1"></i>{{ $student->parent_email }}
                                    </div>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-bold">{{ $student->emergency_contact_name }}</div>
                                    <div class="text-muted small">
                                        <i class="fas fa-phone me-1"></i>{{ $student->emergency_contact_phone }}
                                    </div>
                                    @if($student->emergency_contact_relation)
                                    <div class="text-muted small">
                                        <i class="fas fa-user-friends me-1"></i>{{ $student->emergency_contact_relation }}
                                    </div>
                                    @endif
                                </td>
                                <td>
                                    @if($student->registration_status == 'pending')
                                        <span class="badge bg-warning text-dark">
                                            <i class="fas fa-clock me-1"></i>Beklemede
                                        </span>
                                    @elseif($student->registration_status == 'approved')
                                        <span class="badge bg-success">
                                            <i class="fas fa-check me-1"></i>Onaylandı
                                        </span>
                                    @else
                                        <span class="badge bg-danger">
                                            <i class="fas fa-times me-1"></i>Reddedildi
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div>{{ $student->created_at->format('d.m.Y') }}</div>
                                    <div class="text-muted small">{{ $student->created_at->format('H:i') }}</div>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                                data-bs-toggle="modal" data-bs-target="#studentModal{{ $student->id }}"
                                                title="Detayları Görüntüle">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        
                                        @if($student->registration_status == 'pending')
                                        <button type="button" class="btn btn-sm btn-outline-success" 
                                                onclick="updateStudentStatus({{ $student->id }}, 'approved')"
                                                title="Onayla">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                                onclick="updateStudentStatus({{ $student->id }}, 'rejected')"
                                                title="Reddet">
                                            <i class="fas fa-times"></i>
                                        </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <i class="fas fa-user-graduate fa-3x text-muted mb-3"></i>
                                    <div class="text-muted">Henüz öğrenci bulunmuyor</div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            
            @if($students->hasPages())
            <div class="card-footer">
                {{ $students->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Öğrenci Detay Modalları -->
@foreach($students as $student)
<div class="modal fade" id="studentModal{{ $student->id }}" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-user-graduate me-2"></i>
                    {{ $student->full_name }} - Detaylar
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="border-bottom pb-2">
                            <i class="fas fa-user me-2"></i>Öğrenci Bilgileri
                        </h6>
                        <table class="table table-sm">
                            <tr><td><strong>Ad Soyad:</strong></td><td>{{ $student->full_name }}</td></tr>
                            <tr><td><strong>T.C. Kimlik:</strong></td><td>{{ $student->tc_identity }}</td></tr>
                            <tr><td><strong>Doğum Tarihi:</strong></td><td>{{ $student->birth_date->format('d.m.Y') }}</td></tr>
                            <tr><td><strong>Yaş:</strong></td><td>{{ $student->age }}</td></tr>
                            <tr><td><strong>Adres:</strong></td><td>{{ $student->address }}</td></tr>
                            @if($student->school_name)
                            <tr><td><strong>Okul Adi:</strong></td><td>{{ $student->school_name }}</td></tr>
                            @endif
                            @if($student->emergency_contact_relation)
                            <tr><td><strong>Yakinlik Derecesi:</strong></td><td>{{ $student->emergency_contact_relation }}</td></tr>
                            @endif
                            @if($student->health_condition)
                            <tr><td><strong>Saglik Problemi (Varsa):</strong></td><td>{{ $student->health_condition }}</td></tr>
                            @endif
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6 class="border-bottom pb-2">
                            <i class="fas fa-users me-2"></i>Veli Bilgileri
                        </h6>
                        <table class="table table-sm">
                            <tr><td><strong>Veli Adı:</strong></td><td>{{ $student->parent_full_name }}</td></tr>
                            <tr><td><strong>Telefon:</strong></td><td>{{ $student->parent_phone }}</td></tr>
                            @if($student->parent_email)
                            <tr><td><strong>E-posta:</strong></td><td>{{ $student->parent_email }}</td></tr>
                            @endif
                            @if($student->parent_profession)
                            <tr><td><strong>Meslek:</strong></td><td>{{ $student->parent_profession }}</td></tr>
                            @endif
                        </table>
                    </div>
                </div>
                
                <div class="row mt-3">
                    <div class="col-md-6">
                        <h6 class="border-bottom pb-2">
                            <i class="fas fa-ambulance me-2"></i>Acil Durum Kişisi
                        </h6>
                        <table class="table table-sm">
                            <tr><td><strong>Ad Soyad:</strong></td><td>{{ $student->emergency_contact_name }}</td></tr>
                            <tr><td><strong>Telefon:</strong></td><td>{{ $student->emergency_contact_phone }}</td></tr>
                            @if($student->emergency_contact_relation)
                            <tr><td><strong>Yakinlik Derecesi:</strong></td><td>{{ $student->emergency_contact_relation }}</td></tr>
                            @endif
                        </table>
                    </div>
                                                                <div class="col-md-6">
                                                <h6 class="border-bottom pb-2">
                                                    <i class="fas fa-info-circle me-2"></i>Kayıt Bilgileri
                                                </h6>
                                                <table class="table table-sm">
                                                    <tr><td><strong>Kayıt Tarihi:</strong></td><td>{{ $student->created_at->format('d.m.Y H:i') }}</td></tr>
                                                    <tr><td><strong>Durum:</strong></td><td>{{ $student->registration_status_text }}</td></tr>
                                                    @if($student->enrollments->count() > 0)
                                                    <tr>
                                                        <td><strong>Secilen Atolyeler:</strong></td>
                                                        <td>
                                                            @foreach($student->enrollments as $enrollment)
                                                                @if($enrollment->workshop)
                                                                    <span class="badge bg-light text-dark border mb-1">{{ $enrollment->workshop->name }}</span>
                                                                @endif
                                                            @endforeach
                                                        </td>
                                                    </tr>
                                                    @else
                                                    <tr><td><strong>Secilen Atolyeler:</strong></td><td><span class="text-muted">Henuz atölye secilmemis</span></td></tr>
                                                    @endif
                                                    @if($student->notes)
                                                    <tr><td><strong>Notlar:</strong></td><td>{{ $student->notes }}</td></tr>
                                                    @endif
                                                </table>
                                            </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
            </div>
        </div>
    </div>
</div>
@endforeach

<script>
function updateStudentStatus(studentId, status) {
    if (!confirm('Öğrenci durumunu güncellemek istediğinizden emin misiniz?')) return;

    const baseUrl = window.appBaseUrl || '';
    const url = `${baseUrl}/admin/students/${studentId}/status`;

    fetch(url, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        credentials: 'same-origin',
        body: JSON.stringify({ status })
    })
    .then(async (response) => {
        const data = await response.json().catch(() => ({ success: false, message: 'Sunucudan geçersiz yanıt' }));
        if (response.ok && data.success) {
            location.reload();
        } else {
            alert(data.message || 'Bir hata oluştu');
        }
    })
    .catch((error) => {
        console.error('Status update error:', error);
        alert('Bir hata oluştu');
    });
}
</script>
@endsection
