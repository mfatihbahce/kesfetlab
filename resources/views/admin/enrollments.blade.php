@extends('admin.layout')

@section('title', 'Öğrenci Yönetimi - Keşfet LAB')
@section('page-title', 'Öğrenci Yönetimi')

@section('content')
<div class="row">
    <div class="col-12">
        <!-- Filtreler -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.enrollments') }}" class="row g-3">
                    <div class="col-md-3">
                        <label for="status" class="form-label">Kayıt Durumu</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">Tümü</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Beklemede</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Onaylandı</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Reddedildi</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>İptal Edildi</option>
                            <option value="graduated" {{ request('status') == 'graduated' ? 'selected' : '' }}>Mezun</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="payment_status" class="form-label">Ödeme Durumu</label>
                        <select class="form-select" id="payment_status" name="payment_status">
                            <option value="">Tümü</option>
                            <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>Beklemede</option>
                            <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Ödendi</option>
                            <option value="partial" {{ request('payment_status') == 'partial' ? 'selected' : '' }}>Kısmi</option>
                            <option value="refunded" {{ request('payment_status') == 'refunded' ? 'selected' : '' }}>İade</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-2"></i>Filtrele
                            </button>
                            <a href="{{ route('admin.enrollments') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>Temizle
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Kayıt Tablosu -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-clipboard-list me-2"></i>
                    Öğrenci Kayıt Listesi
                </h5>
                <span class="badge bg-primary fs-6">{{ $enrollments->total() }} kayıt</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
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
                                        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-3" 
                                             style="width: 40px; height: 40px;">
                                            <i class="fas fa-user text-white"></i>
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
                                        <span class="badge bg-warning text-dark">
                                            <i class="fas fa-clock me-1"></i>Beklemede
                                        </span>
                                    @elseif($enrollment->status == 'approved')
                                        <span class="badge bg-success">
                                            <i class="fas fa-check me-1"></i>Onaylandı
                                        </span>
                                    @elseif($enrollment->status == 'rejected')
                                        <span class="badge bg-danger">
                                            <i class="fas fa-times me-1"></i>Reddedildi
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">
                                            <i class="fas fa-ban me-1"></i>İptal
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($enrollment->payment_status == 'paid')
                                        <span class="badge bg-success">
                                            <i class="fas fa-check me-1"></i>Ödendi
                                        </span>
                                    @elseif($enrollment->payment_status == 'partial')
                                        <span class="badge bg-warning text-dark">
                                            <i class="fas fa-clock me-1"></i>Kısmi
                                        </span>
                                    @elseif($enrollment->payment_status == 'refunded')
                                        <span class="badge bg-info">
                                            <i class="fas fa-undo me-1"></i>İade
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">
                                            <i class="fas fa-clock me-1"></i>Beklemede
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div>{{ $enrollment->enrollment_date->format('d.m.Y') }}</div>
                                    <div class="text-muted small">{{ $enrollment->created_at->format('H:i') }}</div>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.students.detail', $enrollment->student->id) }}" class="btn btn-sm btn-outline-primary" title="Öğrenci Detayı">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        @if($enrollment->status == 'pending')
                                        <button type="button" class="btn btn-sm btn-outline-info" 
                                                data-bs-toggle="modal" data-bs-target="#assignGroupModal{{ $enrollment->id }}"
                                                title="Grup Ata">
                                            <i class="fas fa-users"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-success" 
                                                onclick="updateEnrollmentStatus({{ $enrollment->id }}, 'approved')"
                                                title="Onayla">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                                onclick="updateEnrollmentStatus({{ $enrollment->id }}, 'rejected')"
                                                title="Reddet">
                                            <i class="fas fa-times"></i>
                                        </button>
                                        @endif
                                        
                                        @if($enrollment->payment_status != 'paid')
                                        <button type="button" class="btn btn-sm btn-outline-info" 
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
                                <td colspan="6" class="text-center py-5">
                                    <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                                    <div class="text-muted">Henüz kayıt bulunmuyor</div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            
            @if($enrollments->hasPages())
            <div class="card-footer">
                {{ $enrollments->links() }}
            </div>
            @endif
        </div>
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
