@extends('admin.layout')

@section('title', 'Veliler - Keşfet LAB')

@section('page-title', 'Veliler')

@section('content')
<div class="container-fluid">
    <!-- Filtreler -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.parents') }}" class="row g-3">
                <div class="col-md-3">
                    <label for="status" class="form-label">Durum</label>
                    <select name="status" id="status" class="form-select">
                        <option value="">Tümü</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Pasif</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="search" class="form-label">Arama</label>
                    <input type="text" name="search" id="search" class="form-control" 
                           placeholder="Ad, telefon veya T.C. kimlik..." 
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search me-1"></i>Filtrele
                    </button>
                    <a href="{{ route('admin.parents') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i>Temizle
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Veliler Listesi -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-user-friends me-2"></i>Veli Listesi
            </h5>
            <span class="badge bg-primary fs-6">{{ $parents->total() }} veli</span>
        </div>
        <div class="card-body">
            @if($parents->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
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
                                        <div class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center me-3">
                                            <i class="fas fa-user text-white"></i>
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
                                    <span class="badge bg-info">{{ $parent->students_count }} öğrenci</span>
                                </td>
                                <td>
                                    @if($parent->status == 'active')
                                        <span class="badge bg-success">Aktif</span>
                                    @else
                                        <span class="badge bg-secondary">Pasif</span>
                                    @endif
                                </td>
                                <td>
                                    <small>{{ $parent->created_at->format('d.m.Y H:i') }}</small>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                                onclick="viewParent({{ $parent->id }})" 
                                                title="Detaylar">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-success" 
                                                onclick="generateCode({{ $parent->id }})" 
                                                title="Kod Üret">
                                            <i class="fas fa-key"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-warning" 
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

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $parents->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-user-friends fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Henüz veli kaydı bulunmuyor</h5>
                    <p class="text-muted">Online form üzerinden öğrenci kayıtları yapıldıktan sonra veliler burada görünecektir.</p>
                </div>
            @endif
        </div>
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
