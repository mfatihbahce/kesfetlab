@extends('admin.layout')

@section('title', 'Eğitmen Yönetimi - Keşfet LAB')
@section('page-title', 'Eğitmen Yönetimi')

@section('content')
<!-- Yeni Eğitmen Ekleme Butonu -->
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-chalkboard-teacher me-2"></i>
                Eğitmenler
            </h5>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addInstructorModal">
                <i class="fas fa-plus me-2"></i>
                Yeni Eğitmen Ekle
            </button>
        </div>
    </div>
</div>

<!-- Eğitmen Listesi -->
<div class="row">
    @forelse($instructors as $instructor)
    <div class="col-lg-6 col-xl-4 mb-4">
        <div class="table-card h-100">
            <div class="table-header d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-chalkboard-teacher me-2"></i>
                    {{ $instructor->full_name }}
                </div>
                <div>
                    @if($instructor->is_active)
                        <span class="badge bg-success">Aktif</span>
                    @else
                        <span class="badge bg-secondary">Pasif</span>
                    @endif
                </div>
            </div>
            
            <div class="p-3">
                <div class="mb-3">
                    <strong>E-posta:</strong> {{ $instructor->email }}
                </div>
                
                <div class="mb-3">
                    <strong>Telefon:</strong> {{ $instructor->phone }}
                </div>
                
                @if($instructor->profession)
                <div class="mb-3">
                    <strong>Meslek:</strong> {{ $instructor->profession }}
                </div>
                @endif
                
                <div class="row text-center mb-3">
                    <div class="col-6">
                        <div class="border-end">
                            <div class="h5 mb-0 text-primary">{{ $instructor->groups_count }}</div>
                            <small class="text-muted">Grup</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="h5 mb-0 text-success">
                            {{ $instructor->groups->sum(function($group) { return $group->enrollments->count(); }) }}
                        </div>
                        <small class="text-muted">Öğrenci</small>
                    </div>
                </div>
                
                <div class="d-flex gap-2 mb-3">
                    <a href="{{ route('admin.instructors.detail', $instructor->id) }}" class="btn btn-sm btn-primary flex-fill">
                        <i class="fas fa-info-circle me-1"></i>
                        Detay
                    </a>
                    <button type="button" class="btn btn-sm btn-outline-primary" 
                            data-bs-toggle="modal" data-bs-target="#editInstructorModal{{ $instructor->id }}">
                        <i class="fas fa-edit me-1"></i>
                        Düzenle
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger" 
                            onclick="deleteInstructor({{ $instructor->id }})">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                
                @if($instructor->groups->count() > 0)
                <div class="mt-3">
                    <small class="text-muted">
                        <i class="fas fa-users me-1"></i>
                        {{ $instructor->groups->count() }} grup yönetiyor
                    </small>
                </div>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Düzenleme Modal -->
    <div class="modal fade" id="editInstructorModal{{ $instructor->id }}" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-edit me-2"></i>
                        {{ $instructor->full_name }} - Düzenle
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('admin.instructors.update', $instructor->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="first_name{{ $instructor->id }}" class="form-label">Ad</label>
                                    <input type="text" class="form-control" id="first_name{{ $instructor->id }}" 
                                           name="first_name" value="{{ $instructor->first_name }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="last_name{{ $instructor->id }}" class="form-label">Soyad</label>
                                    <input type="text" class="form-control" id="last_name{{ $instructor->id }}" 
                                           name="last_name" value="{{ $instructor->last_name }}" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email{{ $instructor->id }}" class="form-label">E-posta</label>
                                    <input type="email" class="form-control" id="email{{ $instructor->id }}" 
                                           name="email" value="{{ $instructor->email }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="phone{{ $instructor->id }}" class="form-label">Telefon</label>
                                    <input type="text" class="form-control" id="phone{{ $instructor->id }}" 
                                           name="phone" value="{{ $instructor->phone }}" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="profession{{ $instructor->id }}" class="form-label">Meslek</label>
                            <input type="text" class="form-control" id="profession{{ $instructor->id }}" 
                                   name="profession" value="{{ $instructor->profession }}">
                        </div>
                        
                        <div class="mb-3">
                            <label for="address{{ $instructor->id }}" class="form-label">Adres</label>
                            <textarea class="form-control" id="address{{ $instructor->id }}" 
                                      name="address" rows="2">{{ $instructor->address }}</textarea>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_active{{ $instructor->id }}" 
                                       name="is_active" value="1" {{ $instructor->is_active ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active{{ $instructor->id }}">
                                    Aktif
                                </label>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password{{ $instructor->id }}" class="form-label">Yeni Şifre</label>
                            <input type="text" class="form-control" id="password{{ $instructor->id }}" 
                                   name="password" placeholder="Şifreyi değiştirmek için doldurun">
                            <div class="form-text">Boş bırakırsanız şifre değişmez.</div>
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
                <i class="fas fa-chalkboard-teacher fa-3x mb-3"></i>
                <h5>Henüz eğitmen bulunmuyor</h5>
                <p>İlk eğitmeni eklemek için yukarıdaki butonu kullanın.</p>
            </div>
        </div>
    </div>
    @endforelse
</div>

<!-- Yeni Eğitmen Ekleme Modal -->
<div class="modal fade" id="addInstructorModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus me-2"></i>
                    Yeni Eğitmen Ekle
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.instructors.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="first_name" class="form-label">Ad</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="last_name" class="form-label">Soyad</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="email" class="form-label">E-posta</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="phone" class="form-label">Telefon</label>
                                <input type="text" class="form-control" id="phone" name="phone" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="profession" class="form-label">Meslek</label>
                        <input type="text" class="form-control" id="profession" name="profession">
                    </div>
                    
                    <div class="mb-3">
                        <label for="address" class="form-label">Adres</label>
                        <textarea class="form-control" id="address" name="address" rows="2"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Şifre</label>
                        <input type="text" class="form-control" id="password" name="password" 
                               placeholder="Eğitmen giriş şifresi" required>
                        <div class="form-text">Eğitmen telefon numarası ve bu şifre ile giriş yapacak.</div>
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
function deleteInstructor(instructorId) {
    if (confirm('Bu eğitmeni silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.')) {
        fetch(`/admin/instructors/${instructorId}`, {
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
