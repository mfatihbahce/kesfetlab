@extends('admin.layout')

@section('title', 'Grup Yönetimi - Keşfet LAB')
@section('page-title', 'Grup Yönetimi')

@section('content')
<!-- Yeni Grup Ekleme Butonu -->
<div class="row mb-4">
	<div class="col-12">
		<div class="d-flex justify-content-between align-items-center">
			<h5 class="mb-0">
				<i class="fas fa-users me-2"></i>
				Gruplar
			</h5>
			<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addGroupModal">
				<i class="fas fa-plus me-2"></i>
				Yeni Grup Oluştur
			</button>
		</div>
	</div>
</div>

<!-- Grup Listesi -->
<div class="row">
	@forelse($groups as $group)
	<div class="col-lg-6 col-xl-4 mb-4">
		<div class="table-card h-100">
			<div class="table-header d-flex justify-content-between align-items-center">
				<div>
					<i class="fas fa-users me-2"></i>
					{{ $group->name }}
				</div>
				<div>
					@if($group->status == 'active')
						<span class="badge bg-success">Aktif</span>
					@elseif($group->status == 'inactive')
						<span class="badge bg-secondary">Pasif</span>
					@else
						<span class="badge bg-warning">Dolu</span>
					@endif
				</div>
			</div>
			
			<div class="p-3">
				<div class="mb-3">
					<strong>Sınıf:</strong> {{ $group->workshop->name }}
				</div>
				
				<div class="mb-3">
					<strong>Eğitmen:</strong> 
					@if($group->instructor)
						{{ $group->instructor->name }}
					@else
						<span class="text-muted">Atanmamış</span>
					@endif
				</div>
				
				<div class="row text-center mb-3">
					<div class="col-4">
						<div class="border-end">
							<div class="h5 mb-0 text-primary">{{ $group->capacity }}</div>
							<small class="text-muted">Kontenjan</small>
						</div>
					</div>
					<div class="col-4">
						<div class="border-end">
							<div class="h5 mb-0 text-success">{{ $group->enrollments_count }}</div>
							<small class="text-muted">Kayıt</small>
						</div>
					</div>
					<div class="col-4">
						<div class="h5 mb-0 text-info">{{ max($group->capacity - $group->enrollments_count, 0) }}</div>
						<small class="text-muted">Boş</small>
					</div>
				</div>
				
				<div class="mb-3">
					<strong>Program:</strong> {{ $group->schedule }}
				</div>
				
				@if($group->description)
				<div class="mb-3">
					<small class="text-muted">{{ $group->description }}</small>
				</div>
				@endif
				
				<div class="d-flex gap-2 mb-3">
					<a href="{{ route('admin.groups.detail', $group->id) }}" class="btn btn-sm btn-primary flex-fill">
						<i class="fas fa-info-circle me-1"></i>
						Detay
					</a>
					<button type="button" class="btn btn-sm btn-outline-primary flex-fill" 
							data-bs-toggle="modal" data-bs-target="#editGroupModal{{ $group->id }}">
						<i class="fas fa-edit me-1"></i>
						Düzenle
					</button>
					<button type="button" class="btn btn-sm btn-outline-info" 
							data-bs-toggle="modal" data-bs-target="#groupStudentsModal{{ $group->id }}">
						<i class="fas fa-users me-1"></i>
						Öğrenciler
					</button>
					<button type="button" class="btn btn-sm btn-outline-danger" 
							onclick="deleteGroup({{ $group->id }})">
						<i class="fas fa-trash"></i>
					</button>
				</div>
				
				@if($group->enrollments_count > 0)
				<div class="mt-3">
					<small class="text-muted">
						<i class="fas fa-user-graduate me-1"></i>
						{{ $group->enrollments_count }} öğrenci kayıtlı
					</small>
				</div>
				@endif
			</div>
		</div>
	</div>
	
	<!-- Düzenleme Modal -->
	<div class="modal fade" id="editGroupModal{{ $group->id }}" tabindex="-1">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">
						<i class="fas fa-edit me-2"></i>
						{{ $group->name }} - Düzenle
					</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
				</div>
				<form action="{{ route('admin.groups.update', $group->id) }}" method="POST">
					@csrf
					@method('PUT')
					<div class="modal-body">
						<div class="row">
							<div class="col-md-6">
								<div class="mb-3">
									<label for="name{{ $group->id }}" class="form-label">Grup Adı</label>
									<input type="text" class="form-control" id="name{{ $group->id }}" 
										name="name" value="{{ $group->name }}" required>
								</div>
							</div>
							<div class="col-md-6">
								<div class="mb-3">
									<label for="workshop_id{{ $group->id }}" class="form-label">Sınıf</label>
									<select class="form-select" id="workshop_id{{ $group->id }}" name="workshop_id" required>
										@foreach($workshops as $workshop)
											<option value="{{ $workshop->id }}" {{ $group->workshop_id == $workshop->id ? 'selected' : '' }}>
											{{ $workshop->name }}
											</option>
										@endforeach
									</select>
								</div>
							</div>
						</div>
						
						<div class="row">
							<div class="col-md-6">
								<div class="mb-3">
									<label for="instructor_id{{ $group->id }}" class="form-label">Eğitmen</label>
									<select class="form-select" id="instructor_id{{ $group->id }}" name="instructor_id" required>
										<option value="">Eğitmen Seçin</option>
										@foreach($instructors as $instructor)
											<option value="{{ $instructor->id }}" {{ $group->instructor_id == $instructor->id ? 'selected' : '' }}>
											{{ $instructor->name }}
											</option>
										@endforeach
									</select>
								</div>
							</div>
							<div class="col-md-6">
								<div class="mb-3">
									<label for="capacity{{ $group->id }}" class="form-label">Kontenjan</label>
									<input type="number" class="form-control" id="capacity{{ $group->id }}" 
										name="capacity" value="{{ $group->capacity }}" required>
								</div>
							</div>
						</div>
						
						<div class="row">
							<div class="col-md-4">
								<div class="mb-3">
									<label for="day_of_week{{ $group->id }}" class="form-label">Gün</label>
									<select class="form-select" id="day_of_week{{ $group->id }}" name="day_of_week" required>
										<option value="monday" {{ $group->day_of_week == 'monday' ? 'selected' : '' }}>Pazartesi</option>
										<option value="tuesday" {{ $group->day_of_week == 'tuesday' ? 'selected' : '' }}>Salı</option>
										<option value="wednesday" {{ $group->day_of_week == 'wednesday' ? 'selected' : '' }}>Çarşamba</option>
										<option value="thursday" {{ $group->day_of_week == 'thursday' ? 'selected' : '' }}>Perşembe</option>
										<option value="friday" {{ $group->day_of_week == 'friday' ? 'selected' : '' }}>Cuma</option>
										<option value="saturday" {{ $group->day_of_week == 'saturday' ? 'selected' : '' }}>Cumartesi</option>
										<option value="sunday" {{ $group->day_of_week == 'sunday' ? 'selected' : '' }}>Pazar</option>
									</select>
								</div>
							</div>
							<div class="col-md-4">
								<div class="mb-3">
									<label for="start_time{{ $group->id }}" class="form-label">Başlangıç</label>
									<input type="time" class="form-control" id="start_time{{ $group->id }}" 
										name="start_time" value="{{ $group->start_time?->format('H:i') }}" required>
								</div>
							</div>
							<div class="col-md-4">
								<div class="mb-3">
									<label for="end_time{{ $group->id }}" class="form-label">Bitiş</label>
									<input type="time" class="form-control" id="end_time{{ $group->id }}" 
										name="end_time" value="{{ $group->end_time?->format('H:i') }}" required>
								</div>
							</div>
						</div>
						
						<div class="mb-3">
							<label for="status{{ $group->id }}" class="form-label">Durum</label>
							<select class="form-select" id="status{{ $group->id }}" name="status">
								<option value="active" {{ $group->status == 'active' ? 'selected' : '' }}>Aktif</option>
								<option value="inactive" {{ $group->status == 'inactive' ? 'selected' : '' }}>Pasif</option>
								<option value="full" {{ $group->status == 'full' ? 'selected' : '' }}>Dolu</option>
							</select>
						</div>
						
						<div class="mb-3">
							<label for="description{{ $group->id }}" class="form-label">Açıklama</label>
							<textarea class="form-control" id="description{{ $group->id }}" 
									  name="description" rows="2">{{ $group->description }}</textarea>
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
	
	<!-- Öğrenci Listesi Modal -->
	<div class="modal fade" id="groupStudentsModal{{ $group->id }}" tabindex="-1">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">
						<i class="fas fa-users me-2"></i>
						{{ $group->name }} - Öğrenci Listesi
					</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
				</div>
				<div class="modal-body">
					@if($group->enrollments->count() > 0)
						<div class="table-responsive">
							<table class="table table-sm">
								<thead>
									<tr>
										<th>Öğrenci</th>
										<th>T.C. Kimlik</th>
										<th>Veli</th>
										<th>Kayıt Tarihi</th>
										<th>Durum</th>
									</tr>
								</thead>
								<tbody>
									@foreach($group->enrollments as $enrollment)
									<tr>
										<td>{{ $enrollment->student->full_name }}</td>
										<td>{{ $enrollment->student->tc_identity }}</td>
										<td>{{ $enrollment->student->parent_full_name }}</td>
										<td>
											@if($enrollment->created_at)
												{{ $enrollment->created_at->format('d.m.Y') }}
											@else
												<span class="text-muted">-</span>
											@endif
										</td>
										<td>
											@if($enrollment->status == 'approved')
												<span class="badge bg-success">Onaylandı</span>
											@else
												<span class="badge bg-warning">Beklemede</span>
											@endif
										</td>
									</tr>
									@endforeach
								</tbody>
							</table>
						</div>
					@else
						<div class="text-center text-muted py-4">
							<i class="fas fa-users fa-2x mb-3"></i>
							<p>Bu gruba henüz öğrenci kaydı bulunmuyor.</p>
						</div>
					@endif
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
				</div>
			</div>
		</div>
	</div>
	@empty
	<div class="col-12">
		<div class="table-card">
			<div class="text-center text-muted py-5">
				<i class="fas fa-users fa-3x mb-3"></i>
				<h5>Henüz grup bulunmuyor</h5>
				<p>İlk grubu oluşturmak için yukarıdaki butonu kullanın.</p>
			</div>
		</div>
	</div>
	@endforelse
</div>

<!-- Yeni Grup Ekleme Modal -->
<div class="modal fade" id="addGroupModal" tabindex="-1">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">
					<i class="fas fa-plus me-2"></i>
					Yeni Grup Oluştur
				</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
			</div>
			<form action="{{ route('admin.groups.store') }}" method="POST">
				@csrf
				<div class="modal-body">
					<div class="row">
						<div class="col-md-6">
							<div class="mb-3">
								<label for="name" class="form-label">Grup Adı</label>
								<input type="text" class="form-control" id="name" name="name" required>
							</div>
						</div>
						<div class="col-md-6">
							<div class="mb-3">
								<label for="workshop_id" class="form-label">Sınıf</label>
								<select class="form-select" id="workshop_id" name="workshop_id" required>
									<option value="">Sınıf Seçin</option>
									@foreach($workshops as $workshop)
										<option value="{{ $workshop->id }}">{{ $workshop->name }}</option>
									@endforeach
								</select>
							</div>
						</div>
					</div>
					
					<div class="row">
						<div class="col-md-6">
							<div class="mb-3">
								<label for="instructor_id" class="form-label">Eğitmen</label>
								<select class="form-select" id="instructor_id" name="instructor_id" required>
									<option value="">Eğitmen Seçin</option>
									@foreach($instructors as $instructor)
										<option value="{{ $instructor->id }}">{{ $instructor->name }}</option>
									@endforeach
								</select>
							</div>
						</div>
						<div class="col-md-6">
							<div class="mb-3">
								<label for="capacity" class="form-label">Kontenjan</label>
								<input type="number" class="form-control" id="capacity" name="capacity" value="20" required>
							</div>
						</div>
					</div>
					
					<div class="row">
						<div class="col-md-4">
							<div class="mb-3">
								<label for="day_of_week" class="form-label">Gün</label>
								<select class="form-select" id="day_of_week" name="day_of_week" required>
									<option value="monday">Pazartesi</option>
									<option value="tuesday">Salı</option>
									<option value="wednesday">Çarşamba</option>
									<option value="thursday">Perşembe</option>
									<option value="friday">Cuma</option>
									<option value="saturday">Cumartesi</option>
									<option value="sunday">Pazar</option>
								</select>
							</div>
						</div>
						<div class="col-md-4">
							<div class="mb-3">
								<label for="start_time" class="form-label">Başlangıç</label>
								<input type="time" class="form-control" id="start_time" name="start_time" required>
							</div>
						</div>
						<div class="col-md-4">
							<div class="mb-3">
								<label for="end_time" class="form-label">Bitiş</label>
								<input type="time" class="form-control" id="end_time" name="end_time" required>
							</div>
						</div>
					</div>
					
					<div class="mb-3">
						<label for="status" class="form-label">Durum</label>
						<select class="form-select" id="status" name="status">
							<option value="active">Aktif</option>
							<option value="inactive">Pasif</option>
						</select>
					</div>
					
					<div class="mb-3">
						<label for="description" class="form-label">Açıklama</label>
						<textarea class="form-control" id="description" name="description" rows="2"></textarea>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
					<button type="submit" class="btn btn-primary">Oluştur</button>
				</div>
			</form>
		</div>
	</div>
</div>

<script>
function deleteGroup(groupId) {
	if (confirm('Bu grubu silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.')) {
		fetch(`/admin/groups/${groupId}`, {
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
