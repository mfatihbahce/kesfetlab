@extends('admin.layout')

@section('title', 'Öğrenci Yönetimi - Keşfet LAB')
@section('page-title', 'Öğrenci Yönetimi')

@section('content')
<style>
    .students-shell { font-family: Inter, "Segoe UI", Roboto, Arial, sans-serif; }
    .students-card {
        background: #fff;
        border: 1px solid #e8edf3;
        border-radius: 16px;
        box-shadow: 0 10px 26px rgba(15, 23, 42, 0.06);
    }
    .students-filter { padding: 18px; margin-bottom: 14px; }
    .input-wrap { position: relative; }
    .input-wrap i {
        position: absolute; left: 12px; top: 50%; transform: translateY(-50%);
        color: #94a3b8; font-size: 12px;
    }
    .input-wrap .form-control, .input-wrap .form-select {
        border-radius: 12px; padding-left: 34px; border-color: #d7dee8;
    }
    .input-wrap .form-control:focus, .input-wrap .form-select:focus {
        border-color: #f4b400; box-shadow: 0 0 0 3px rgba(244,180,0,.2);
    }
    .students-header {
        padding: 12px 16px; border-bottom: 1px solid #edf2f7; background: #f8fafc;
        display: flex; align-items: center; justify-content: space-between;
    }
    .count-pill { border-radius: 999px; font-size: 12px; font-weight: 700; }
    .table-students { margin-bottom: 0; }
    .table-students thead th {
        font-size: 11px; text-transform: uppercase; letter-spacing: .03em;
        color: #64748b; background: #f8fafc; border-bottom: 1px solid #e8edf3;
        padding: 12px 14px; white-space: nowrap;
    }
    .table-students tbody td { padding: 15px 14px; vertical-align: middle; border-color: #eef2f7; }
    .table-students tbody tr { transition: background-color .2s ease; }
    .table-students tbody tr:hover { background: #f8fafc; }
    .avatar-circle {
        width: 40px; height: 40px; border-radius: 999px;
        background: #fff3d1; color: #b7791f;
        display: inline-flex; align-items: center; justify-content: center;
    }
    .badge-soft-pill { border-radius: 999px; font-size: 11px; font-weight: 700; padding: 6px 10px; }
    .action-btn {
        width: 32px; height: 32px; border-radius: 10px;
        display: inline-flex; align-items: center; justify-content: center;
        transition: all .2s ease;
    }
    .action-btn:hover { transform: scale(1.05); }
    .empty-state { padding: 48px 16px; text-align: center; color: #64748b; }
    .empty-icon {
        width: 56px; height: 56px; border-radius: 999px; margin: 0 auto 10px;
        background: #f1f5f9; display: inline-flex; align-items: center; justify-content: center;
    }
</style>

<div class="students-shell">
    <div class="students-card students-filter">
        <form method="GET" action="{{ route('admin.students') }}" class="row g-3 align-items-end">
            <div class="col-lg-3 col-md-4">
                <label for="status" class="form-label small text-muted fw-semibold">Kayıt Durumu</label>
                <div class="input-wrap">
                    <i class="fas fa-filter"></i>
                    <select class="form-select" id="status" name="status">
                        <option value="">Tümü</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Beklemede</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Onaylandı</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Reddedildi</option>
                    </select>
                </div>
            </div>
            <div class="col-lg-6 col-md-8">
                <label for="search" class="form-label small text-muted fw-semibold">Arama</label>
                <div class="input-wrap">
                    <i class="fas fa-search"></i>
                    <input type="text" class="form-control" id="search" name="search" value="{{ request('search') }}" placeholder="Ad, soyad veya T.C. kimlik...">
                </div>
            </div>
            <div class="col-lg-3 col-md-12 d-flex gap-2 justify-content-lg-end">
                <button type="submit" class="btn btn-primary rounded-3 px-3">
                    <i class="fas fa-search me-1"></i>Filtrele
                </button>
                <a href="{{ route('admin.students') }}" class="btn btn-outline-secondary rounded-3 px-3">
                    <i class="fas fa-times me-1"></i>Temizle
                </a>
            </div>
        </form>
    </div>

    <div class="students-card overflow-hidden">
        <div class="students-header">
            <h5 class="mb-0 fw-semibold"><i class="fas fa-user-graduate me-2 text-muted"></i>Öğrenci Listesi</h5>
            <span class="badge text-bg-primary count-pill">{{ $students->total() }} öğrenci</span>
        </div>
        <div class="table-responsive">
            <table class="table table-students">
                <thead>
                    <tr>
                        <th>Öğrenci Bilgileri</th>
                        <th>Veli Bilgileri</th>
                        <th>Acil Durum İletişim</th>
                        <th>Durum</th>
                        <th>Tarih</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $student)
                    <tr>
                        <td data-bs-toggle="modal" data-bs-target="#studentModal{{ $student->id }}" style="cursor:pointer;">
                            <div class="d-flex align-items-center">
                                <div class="avatar-circle me-3">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold text-dark">{{ $student->full_name }}</div>
                                    <div class="small text-muted">
                                        <i class="fas fa-id-card me-1"></i>{{ $student->tc_identity }}
                                    </div>
                                    <div class="small text-muted">
                                        <i class="fas fa-birthday-cake me-1"></i>{{ $student->birth_date->format('d.m.Y') }} ({{ $student->age }} yaş)
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $student->parent_full_name }}</div>
                            <div class="small text-muted">
                                <i class="fas fa-phone me-1"></i>{{ $student->parent_phone }}
                            </div>
                            @if($student->parent_email)
                            <div class="small text-muted">
                                <i class="fas fa-envelope me-1"></i>{{ $student->parent_email }}
                            </div>
                            @endif
                        </td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $student->emergency_contact_name }}</div>
                            <div class="small text-muted">
                                <i class="fas fa-phone me-1"></i>{{ $student->emergency_contact_phone }}
                            </div>
                            @if($student->emergency_contact_relation)
                            <div class="small text-muted">
                                <i class="fas fa-user-friends me-1"></i>{{ $student->emergency_contact_relation }}
                            </div>
                            @endif
                        </td>
                        <td>
                            @if($student->registration_status == 'pending')
                                <span class="badge-soft-pill text-warning-emphasis bg-warning-subtle border border-warning-subtle">
                                    <i class="fas fa-clock me-1"></i>Beklemede
                                </span>
                            @elseif($student->registration_status == 'approved')
                                <span class="badge-soft-pill text-success-emphasis bg-success-subtle border border-success-subtle">
                                    <i class="fas fa-check me-1"></i>Onaylandı
                                </span>
                            @else
                                <span class="badge-soft-pill text-danger-emphasis bg-danger-subtle border border-danger-subtle">
                                    <i class="fas fa-times me-1"></i>Reddedildi
                                </span>
                            @endif
                        </td>
                        <td>
                            <div class="small text-dark">{{ $student->created_at->format('d.m.Y') }}</div>
                            <div class="small text-muted">{{ $student->created_at->format('H:i') }}</div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-primary action-btn"
                                    data-bs-toggle="modal"
                                    data-bs-target="#studentModal{{ $student->id }}"
                                    title="Detayları Görüntüle"
                                >
                                    <i class="fas fa-eye"></i>
                                </button>

                                @if($student->registration_status == 'pending')
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-success action-btn"
                                    onclick="updateStudentStatus({{ $student->id }}, 'approved')"
                                    title="Onayla"
                                >
                                    <i class="fas fa-check"></i>
                                </button>
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-danger action-btn"
                                    onclick="updateStudentStatus({{ $student->id }}, 'rejected')"
                                    title="Reddet"
                                >
                                    <i class="fas fa-times"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="border-0">
                            <div class="empty-state">
                                <div class="empty-icon">
                                    <i class="fas fa-user-graduate"></i>
                                </div>
                                <div class="fw-semibold text-dark">Öğrenci bulunamadı</div>
                                <div class="small mt-1">Filtreleri değiştirerek tekrar deneyin veya yeni öğrenci kaydı bekleyin.</div>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($students->hasPages())
        <div class="px-3 py-3 border-top">
            {{ $students->links() }}
        </div>
        @endif
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
                                                        <td><strong>Secilen Sınıflar:</strong></td>
                                                        <td>
                                                            @foreach($student->enrollments as $enrollment)
                                                                @if($enrollment->workshop)
                                                                    <span class="badge bg-light text-dark border mb-1">{{ $enrollment->workshop->name }}</span>
                                                                @endif
                                                            @endforeach
                                                        </td>
                                                    </tr>
                                                    @else
                                                    <tr><td><strong>Secilen Sınıflar:</strong></td><td><span class="text-muted">Henuz sınıf secilmemis</span></td></tr>
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
