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
    .students-header {
        padding: 12px 16px; border-bottom: 1px solid #edf2f7; background: #f8fafc;
        display: flex; align-items: center; justify-content: space-between;
    }
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
    .enroll-avatar {
        width: 40px; height: 40px; border-radius: 999px;
        background: #fff3d1; color: #b7791f; display: inline-flex; align-items: center; justify-content: center;
    }
    .badge-soft-pill {
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
        padding: 6px 10px;
        white-space: nowrap;
        display: inline-flex;
        align-items: center;
    }
    .action-btn { width: 32px; height: 32px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; transition: all .2s ease; }
    .action-btn:hover { transform: scale(1.05); }
    .empty-state { padding: 48px 16px; text-align: center; color: #64748b; }
    .empty-icon { width: 56px; height: 56px; border-radius: 999px; margin: 0 auto 10px; background: #f1f5f9; display: inline-flex; align-items: center; justify-content: center; }
</style>
<div class="students-shell">
        <!-- Filtreler -->
        <div class="students-card students-filter">
                <form method="GET" action="{{ route('admin.enrollments') }}" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label for="status" class="form-label small text-muted fw-semibold">Kayıt Durumu</label>
                        <div class="input-wrap">
                            <i class="fas fa-filter"></i>
                            <select class="form-select" id="status" name="status">
                                <option value="">Tümü</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Beklemede</option>
                                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Onaylandı</option>
                                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Reddedildi</option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>İptal Edildi</option>
                                <option value="graduated" {{ request('status') == 'graduated' ? 'selected' : '' }}>Mezun</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label for="payment_status" class="form-label small text-muted fw-semibold">Ödeme Durumu</label>
                        <div class="input-wrap">
                            <i class="fas fa-credit-card"></i>
                            <select class="form-select" id="payment_status" name="payment_status">
                                <option value="">Tümü</option>
                                <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>Beklemede</option>
                                <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Ödendi</option>
                                <option value="partial" {{ request('payment_status') == 'partial' ? 'selected' : '' }}>Kısmi</option>
                                <option value="refunded" {{ request('payment_status') == 'refunded' ? 'selected' : '' }}>İade</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <div class="d-flex gap-2 w-100 justify-content-md-end">
                            <button type="submit" class="btn btn-primary rounded-3 px-3">
                                <i class="fas fa-search me-1"></i>Filtrele
                            </button>
                            <a href="{{ route('admin.enrollments') }}" class="btn btn-outline-secondary rounded-3 px-3">
                                <i class="fas fa-times me-1"></i>Temizle
                            </a>
                        </div>
                    </div>
                </form>
        </div>

        <!-- Kayıt Tablosu -->
        <div class="students-card overflow-hidden">
            <div class="students-header">
                <h5 class="mb-0 fw-semibold"><i class="fas fa-clipboard-list me-2 text-muted"></i>Öğrenci Kayıt Listesi</h5>
                <span class="badge text-bg-primary count-pill">{{ $enrollments->total() }} kayıt</span>
            </div>
                <div class="table-responsive">
                    <table class="table table-students">
                        <thead>
                            <tr>
                                <th style="width: 25%;">Öğrenci</th>
                                <th style="width: 25%;">Sınıf & Grup</th>
                                <th style="width: 10%;">Kayıt Durumu</th>
                                <th style="width: 10%;">Ödeme Durumu</th>
                                <th style="width: 10%;">Tarih</th>
                                <th style="width: 10%;">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($enrollments as $enrollment)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="enroll-avatar me-3">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold">{{ $enrollment->student->full_name }}</div>
                                            <div class="text-muted small">
                                                <i class="fas fa-id-card me-1"></i>{{ $enrollment->student->tc_identity }}
                                            </div>
                                            <div class="text-muted small">
                                                <i class="fas fa-phone me-1"></i>{{ $enrollment->student->parent_phone }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-bold">{{ $enrollment->workshop->name }}</div>
                                    @if($enrollment->group)
                                    <div class="text-muted small">
                                        <i class="fas fa-users me-1"></i>{{ $enrollment->group->name }}
                                    </div>
                                    <div class="text-muted small">
                                        <i class="fas fa-clock me-1"></i>{{ $enrollment->group->schedule }}
                                    </div>
                                    @else
                                    <div class="text-warning small">
                                        <i class="fas fa-exclamation-triangle me-1"></i>Grup atanmamış
                                    </div>
                                    @endif
                                </td>
                                <td>
                                    @if($enrollment->status == 'pending')
                                        <span class="badge-soft-pill text-warning-emphasis bg-warning-subtle border border-warning-subtle">
                                            <i class="fas fa-clock me-1"></i>Beklemede
                                        </span>
                                    @elseif($enrollment->status == 'approved')
                                        <span class="badge-soft-pill text-success-emphasis bg-success-subtle border border-success-subtle">
                                            <i class="fas fa-check me-1"></i>Onaylandı
                                        </span>
                                    @elseif($enrollment->status == 'rejected')
                                        <span class="badge-soft-pill text-danger-emphasis bg-danger-subtle border border-danger-subtle">
                                            <i class="fas fa-times me-1"></i>Reddedildi
                                        </span>
                                    @else
                                        <span class="badge-soft-pill text-secondary-emphasis bg-secondary-subtle border border-secondary-subtle">
                                            <i class="fas fa-ban me-1"></i>İptal
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($enrollment->payment_status == 'paid')
                                        <span class="badge-soft-pill text-success-emphasis bg-success-subtle border border-success-subtle">
                                            <i class="fas fa-check me-1"></i>Ödendi
                                        </span>
                                    @elseif($enrollment->payment_status == 'partial')
                                        <span class="badge-soft-pill text-warning-emphasis bg-warning-subtle border border-warning-subtle">
                                            <i class="fas fa-clock me-1"></i>Kısmi
                                        </span>
                                    @elseif($enrollment->payment_status == 'refunded')
                                        <span class="badge-soft-pill text-info-emphasis bg-info-subtle border border-info-subtle">
                                            <i class="fas fa-undo me-1"></i>İade
                                        </span>
                                    @else
                                        <span class="badge-soft-pill text-secondary-emphasis bg-secondary-subtle border border-secondary-subtle">
                                            <i class="fas fa-clock me-1"></i>Beklemede
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div>{{ $enrollment->enrollment_date->format('d.m.Y') }}</div>
                                    <div class="text-muted small">{{ $enrollment->created_at->format('H:i') }}</div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <a href="{{ route('admin.students.detail', $enrollment->student->id) }}" class="btn btn-sm btn-outline-primary action-btn" title="Öğrenci Detayı">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        @if($enrollment->status == 'pending')
                                        <button type="button" class="btn btn-sm btn-outline-info action-btn" 
                                                data-bs-toggle="modal" data-bs-target="#assignGroupModal{{ $enrollment->id }}"
                                                title="Grup Ata">
                                            <i class="fas fa-users"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-success action-btn" 
                                                onclick="updateEnrollmentStatus({{ $enrollment->id }}, 'approved')"
                                                title="Onayla">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger action-btn" 
                                                onclick="updateEnrollmentStatus({{ $enrollment->id }}, 'rejected')"
                                                title="Reddet">
                                            <i class="fas fa-times"></i>
                                        </button>
                                        @endif
                                        
                                        @if($enrollment->payment_status != 'paid')
                                        <button type="button" class="btn btn-sm btn-outline-info action-btn" 
                                                onclick="markAsPaid({{ $enrollment->id }})"
                                                title="Ödendi Olarak İşaretle">
                                            <i class="fas fa-money-bill"></i>
                                        </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6">
                                    <div class="empty-state">
                                        <div class="empty-icon"><i class="fas fa-clipboard-list"></i></div>
                                        <div class="fw-semibold text-dark">Henüz kayıt bulunmuyor</div>
                                        <div class="small mt-1">Filtreleri değiştirin veya yeni başvuru bekleyin.</div>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @if($enrollments->hasPages())
            <div class="px-3 py-3 border-top">
                {{ $enrollments->links() }}
            </div>
            @endif
        </div>
</div>

<!-- Kayıt Detay Modalları -->
@foreach($enrollments as $enrollment)

<!-- Grup Atama Modal -->
<div class="modal fade" id="assignGroupModal{{ $enrollment->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-users me-2"></i>
                    Grup Atama - {{ $enrollment->student->full_name }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <strong>Sınıf:</strong> {{ $enrollment->workshop->name }}
                </div>
                
                <form id="assignGroupForm{{ $enrollment->id }}">
                    <div class="mb-3">
                        <label for="group_id{{ $enrollment->id }}" class="form-label">Grup Seçin</label>
                        <select class="form-select" id="group_id{{ $enrollment->id }}" name="group_id" required>
                            <option value="">Grup seçin...</option>
                            @foreach($groups as $group)
                                @if($group->workshop_id == $enrollment->workshop_id)
                                <option value="{{ $group->id }}" 
                                        data-capacity="{{ $group->capacity }}"
                                        data-current="{{ $group->enrollments->count() }}">
                                    {{ $group->name }} 
                                    ({{ $group->enrollments->count() }}/{{ $group->capacity }})
                                    - {{ $group->schedule }}
                                </option>
                                @endif
                            @endforeach
                        </select>
                        <div class="form-text">Sadece aynı sınıftaki gruplar gösterilmektedir.</div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                <button type="button" class="btn btn-primary" onclick="assignGroup({{ $enrollment->id }})">
                    <i class="fas fa-users me-1"></i>Grup Ata
                </button>
            </div>
        </div>
    </div>
</div>
@endforeach

<script>
function updateEnrollmentStatus(enrollmentId, status) {
    if (!confirm('Kayıt durumunu güncellemek istediğinizden emin misiniz?')) return;

    const baseUrl = window.appBaseUrl || '';
    const url = `${baseUrl}/admin/enrollments/${enrollmentId}/status`;

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

function markAsPaid(enrollmentId) {
    if (!confirm('Bu kaydı ödenmiş olarak işaretlemek istediğinizden emin misiniz?')) return;

    const baseUrl = window.appBaseUrl || '';
    const url = `${baseUrl}/admin/enrollments/${enrollmentId}/payment`;

    fetch(url, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        credentials: 'same-origin',
        body: JSON.stringify({
            payment_status: 'paid',
            payment_date: new Date().toISOString().split('T')[0]
        })
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
        console.error('Payment update error:', error);
        alert('Bir hata oluştu');
    });
}

function assignGroup(enrollmentId) {
    const groupSelect = document.getElementById(`group_id${enrollmentId}`);
    const groupId = groupSelect.value;
    
    if (!groupId) {
        alert('Lütfen bir grup seçin.');
        return;
    }
    
    const selectedOption = groupSelect.options[groupSelect.selectedIndex];
    const capacity = parseInt(selectedOption.dataset.capacity);
    const current = parseInt(selectedOption.dataset.current);
    
    if (current >= capacity) {
        alert('Bu grup dolu. Başka bir grup seçin.');
        return;
    }

    const baseUrl = window.appBaseUrl || '';
    const url = `${baseUrl}/admin/enrollments/${enrollmentId}/assign-group`;

    fetch(url, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        credentials: 'same-origin',
        body: JSON.stringify({ group_id: groupId })
    })
    .then(async (response) => {
        const data = await response.json().catch(() => ({ success: false, message: 'Sunucudan geçersiz yanıt' }));
        if (response.ok && data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert(data.message || 'Bir hata oluştu');
        }
    })
    .catch((error) => {
        console.error('Group assignment error:', error);
        alert('Bir hata oluştu');
    });
}
</script>
@endsection
