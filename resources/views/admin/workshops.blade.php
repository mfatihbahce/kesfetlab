@extends('admin.layout')

@section('title', 'Sınıf Yönetimi - Keşfet LAB')
@section('page-title', 'Sınıf Yönetimi')

@section('content')
<!-- Yeni Sınıf Ekleme Butonu -->
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-flask me-2"></i>
                Sınıflar
            </h5>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addWorkshopModal">
                <i class="fas fa-plus me-2"></i>
                Yeni Sınıf Ekle
            </button>
        </div>
    </div>
</div>

<!-- Sınıf Listesi -->
<div class="row">
    @forelse($workshops as $workshop)
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="table-card h-100">
            <div class="table-header d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-flask me-2"></i>
                    {{ $workshop->name }}
                </div>
                <div>
                    @if($workshop->status == 'active')
                        <span class="badge bg-success">Aktif</span>
                    @else
                        <span class="badge bg-secondary">Pasif</span>
                    @endif
                </div>
            </div>
            
            <div class="p-3">
                <div class="mb-3">
                    <p class="text-muted mb-2">{{ $workshop->description ?: 'Açıklama bulunmuyor' }}</p>
                </div>
                
                <div class="row text-center mb-3">
                    <div class="col-4">
                        <div class="border-end">
                            <div class="h5 mb-0 text-primary">{{ $workshop->capacity }}</div>
                            <small class="text-muted">Kontenjan</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="border-end">
                            <div class="h5 mb-0 text-success">{{ $workshop->groups->count() }}</div>
                            <small class="text-muted">Grup</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="h5 mb-0 text-info">{{ $workshop->enrollments->count() }}</div>
                        <small class="text-muted">Kayıt</small>
                    </div>
                </div>
                
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-outline-primary flex-fill" 
                            data-bs-toggle="modal" data-bs-target="#editWorkshopModal{{ $workshop->id }}">
                        <i class="fas fa-edit me-1"></i>
                        Düzenle
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger" 
                            onclick="deleteWorkshop({{ $workshop->id }})">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Düzenleme Modal -->
    <div class="modal fade" id="editWorkshopModal{{ $workshop->id }}" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-edit me-2"></i>
                        {{ $workshop->name }} - Düzenle
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('admin.workshops.update', $workshop->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="name{{ $workshop->id }}" class="form-label">Sınıf Adı</label>
                            <input type="text" class="form-control" id="name{{ $workshop->id }}" 
                                   name="name" value="{{ $workshop->name }}" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description{{ $workshop->id }}" class="form-label">Açıklama</label>
                            <textarea class="form-control" id="description{{ $workshop->id }}" 
                                      name="description" rows="3">{{ $workshop->description }}</textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="capacity{{ $workshop->id }}" class="form-label">Kontenjan</label>
                            <input type="number" class="form-control" id="capacity{{ $workshop->id }}" 
                                   name="capacity" value="{{ $workshop->capacity }}" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="status{{ $workshop->id }}" class="form-label">Durum</label>
                            <select class="form-select" id="status{{ $workshop->id }}" name="status">
                                <option value="active" {{ $workshop->status == 'active' ? 'selected' : '' }}>Aktif</option>
                                <option value="inactive" {{ $workshop->status == 'inactive' ? 'selected' : '' }}>Pasif</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                        <button type="submit" class="btn btn-primary">Güncelle</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="table-card">
            <div class="text-center text-muted py-5">
                <i class="fas fa-flask fa-3x mb-3"></i>
                <h5>Henüz sınıf bulunmuyor</h5>
                <p>İlk sınıfı eklemek için yukarıdaki butonu kullanın.</p>
            </div>
        </div>
    </div>
    @endforelse
</div>

<!-- Yeni Sınıf Ekleme Modal -->
<div class="modal fade" id="addWorkshopModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus me-2"></i>
                    Yeni Sınıf Ekle
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.workshops.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Sınıf Adı</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Açıklama</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="capacity" class="form-label">Kontenjan</label>
                        <input type="number" class="form-control" id="capacity" name="capacity" value="20" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="status" class="form-label">Durum</label>
                        <select class="form-select" id="status" name="status">
                            <option value="active">Aktif</option>
                            <option value="inactive">Pasif</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                    <button type="submit" class="btn btn-primary">Ekle</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function deleteWorkshop(workshopId) {
    if (confirm('Bu sınıfı silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.')) {
        fetch(`/admin/workshops/${workshopId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Hata: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Bir hata oluştu');
        });
    }
}
</script>
@endsection
