@extends('admin.layout')

@section('title', 'Veliler - Keşfet LAB')

@section('page-title', 'Veliler')

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
        font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .03em;
        color: #64748b;
        border-bottom: 1px solid #e8edf3;
        background: #f8fafc;
        padding: 12px 14px; white-space: nowrap;
    }
    .table-students tbody td { padding: 15px 14px; vertical-align: middle; border-color: #eef2f7; }
    .table-students tbody tr { transition: background-color .2s ease; }
    .table-students tbody tr:hover { background: #f8fafc; }
    .avatar-sm {
        width: 40px; height: 40px; border-radius: 999px;
        background: #fff3d1 !important; color: #b7791f;
        display: inline-flex; align-items: center; justify-content: center;
    }
    .badge-soft-pill { border-radius: 999px; font-size: 11px; font-weight: 700; padding: 6px 10px; }
    .action-btn { width: 32px; height: 32px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; transition: all .2s ease; }
    .action-btn:hover { transform: scale(1.05); }
</style>
<div class="students-shell">
    <!-- Filtreler -->
    <div class="students-card students-filter">
            <form method="GET" action="{{ route('admin.parents') }}" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="status" class="form-label small text-muted fw-semibold">Durum</label>
                    <div class="input-wrap">
                        <i class="fas fa-filter"></i>
                        <select name="status" id="status" class="form-select">
                            <option value="">Tümü</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Pasif</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <label for="search" class="form-label small text-muted fw-semibold">Arama</label>
                    <div class="input-wrap">
                        <i class="fas fa-search"></i>
                        <input type="text" name="search" id="search" class="form-control" placeholder="Ad, telefon veya T.C. kimlik..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3 d-flex gap-2 align-items-end justify-content-md-end">
                    <button type="submit" class="btn btn-primary rounded-3 px-3">
                        <i class="fas fa-search me-1"></i>Filtrele
                    </button>
                    <a href="{{ route('admin.parents') }}" class="btn btn-outline-secondary rounded-3 px-3">
                        <i class="fas fa-times me-1"></i>Temizle
                    </a>
                </div>
            </form>
    </div>

    <!-- Veliler Listesi -->
    <div class="students-card overflow-hidden">
        <div class="students-header">
            <h5 class="mb-0 fw-semibold"><i class="fas fa-user-friends me-2 text-muted"></i>Veli Listesi</h5>
            <span class="badge text-bg-primary count-pill">{{ $parents->total() }} veli</span>
        </div>
            @if($parents->count() > 0)
                <div class="table-responsive">
                    <table class="table table-students">
                        <thead>
                            <tr>
                                <th>Veli Bilgileri</th>
                                <th>İletişim</th>
                                <th>Öğrenci Sayısı</th>
                                <th>Durum</th>
                                <th>Kayıt Tarihi</th>
                                <th>İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($parents as $parent)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm me-3">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">{{ $parent->full_name }}</h6>
                                            <small class="text-muted">T.C: {{ $parent->tc_identity }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <div><i class="fas fa-phone me-1"></i>{{ $parent->phone }}</div>
                                        @if($parent->email)
                                            <div><i class="fas fa-envelope me-1"></i>{{ $parent->email }}</div>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <span class="badge-soft-pill text-info-emphasis bg-info-subtle border border-info-subtle">{{ $parent->students_count }} öğrenci</span>
                                </td>
                                <td>
                                    @if($parent->status == 'active')
                                        <span class="badge-soft-pill text-success-emphasis bg-success-subtle border border-success-subtle">Aktif</span>
                                    @else
                                        <span class="badge-soft-pill text-secondary-emphasis bg-secondary-subtle border border-secondary-subtle">Pasif</span>
                                    @endif
                                </td>
                                <td>
                                    <small>{{ $parent->created_at->format('d.m.Y H:i') }}</small>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <button type="button" class="btn btn-sm btn-outline-primary action-btn" 
                                                onclick="viewParent({{ $parent->id }})" 
                                                title="Detaylar">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-success action-btn" 
                                                onclick="generateCode({{ $parent->id }})" 
                                                title="Kod Üret">
                                            <i class="fas fa-key"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-warning action-btn" 
                                                onclick="toggleStatus({{ $parent->id }}, '{{ $parent->status }}')" 
                                                title="Durum Değiştir">
                                            <i class="fas fa-toggle-on"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($parents->hasPages())
                <div class="px-3 py-3 border-top">
                    {{ $parents->links() }}
                </div>
                @endif
            @else
                <div class="text-center py-5 px-3">
                    <i class="fas fa-user-friends fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Henüz veli kaydı bulunmuyor</h5>
                    <p class="text-muted mb-0">Online form üzerinden öğrenci kayıtları yapıldıktan sonra veliler burada görünecektir.</p>
                </div>
            @endif
    </div>
</div>

<!-- Kod Üretme Modal -->
<div class="modal fade" id="codeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">6 Haneli Kod Üret</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Bu veli için yeni bir 6 haneli kod üretilecek. Eski kod geçersiz olacaktır.</p>
                <div id="generatedCode" class="alert alert-success d-none">
                    <strong>Yeni Kod:</strong> <span id="codeValue"></span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                <button type="button" class="btn btn-primary" onclick="confirmGenerateCode()">Kod Üret</button>
            </div>
        </div>
    </div>
</div>

<script>
let currentParentId = null;

function viewParent(parentId) {
    // URL'yi doğru bir şekilde oluşturmak için url() helper'ını kullanalım
    window.location.href = `{{ url('admin/parents') }}/${parentId}`;
}

function generateCode(parentId) {
    currentParentId = parentId;
    $('#codeModal').modal('show');
}

function confirmGenerateCode() {
    if (!currentParentId) return;
    
    fetch(`{{ route('admin.parents') }}/${currentParentId}/generate-code`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('codeValue').textContent = data.data.temp_code;
            document.getElementById('generatedCode').classList.remove('d-none');
        } else {
            alert('Kod üretilirken bir hata oluştu: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Bir hata oluştu');
    });
}

function toggleStatus(parentId, currentStatus) {
    const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
    const statusText = newStatus === 'active' ? 'aktif' : 'pasif';
    
    if (!confirm(`Bu veliyi ${statusText} yapmak istediğinizden emin misiniz?`)) {
        return;
    }
    
    fetch(`{{ route('admin.parents') }}/${parentId}/status`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ status: newStatus })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Durum güncellenirken bir hata oluştu: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Bir hata oluştu');
    });
}
</script>
@endsection
