@extends('admin.layout')

@section('title', 'Grup Yönetimi - Keşfet LAB')
@section('page-title', 'Grup Yönetimi')

@section('content')
<style>
    .groups-page {
        display: grid;
        gap: 16px;
    }
    .summary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 12px;
    }
    .summary-card {
        background: #fff;
        border: 1px solid #eceff3;
        border-radius: 14px;
        padding: 12px 14px;
        box-shadow: 0 8px 18px rgba(47,49,56,.06);
    }
    .summary-label {
        font-size: .78rem;
        color: #7b8190;
        font-weight: 600;
    }
    .summary-value {
        margin-top: 4px;
        font-size: 1.5rem;
        font-weight: 700;
        color: #2f3138;
        line-height: 1.1;
    }
    .groups-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 16px;
    }
    .group-card {
        border-radius: 18px;
        background: linear-gradient(160deg, #ffffff 0%, #fff9ef 100%);
        border: 1px solid rgba(255,122,0,.16);
        box-shadow: 0 12px 28px rgba(47,49,56,.10);
        overflow: hidden;
        transition: transform .2s ease, box-shadow .2s ease;
    }
    .group-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 18px 34px rgba(47,49,56,.14);
    }
    .group-head {
        padding: 12px 14px;
        color: #fff;
        background: linear-gradient(120deg, #2f3138, #464a55);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
    }
    .group-name {
        font-weight: 700;
        letter-spacing: .2px;
    }
    .group-body {
        padding: 14px;
    }
    .group-meta {
        display: grid;
        gap: 4px;
        margin-bottom: 10px;
        color: #4b5563;
    }
    .group-meta b {
        color: #2f3138;
    }
    .group-stats {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 8px;
        margin-bottom: 10px;
    }
    .mini-stat {
        text-align: center;
        border: 1px solid #edf0f3;
        border-radius: 11px;
        padding: 8px 6px;
        background: #fff;
    }
    .mini-stat .n {
        font-size: 1.1rem;
        font-weight: 700;
        line-height: 1;
    }
    .mini-stat .t {
        margin-top: 3px;
        font-size: .72rem;
        color: #8a909b;
        font-weight: 600;
    }
    .program-box {
        border-radius: 11px;
        border: 1px solid rgba(255,122,0,.22);
        background: rgba(255,122,0,.08);
        color: #6b7280;
        padding: 8px 10px;
        font-size: .85rem;
        margin-bottom: 10px;
    }
    .group-actions {
        display: grid;
        grid-template-columns: repeat(2, 1fr) auto;
        gap: 8px;
        margin-top: 10px;
    }
    .empty-card {
        grid-column: 1 / -1;
        background: #fff;
        border: 1px dashed #d5dbe3;
        border-radius: 16px;
        padding: 42px 18px;
        text-align: center;
        color: #7b8190;
    }
</style>

@php
    $totalGroups = $groups->count();
    $activeGroups = $groups->where('status', 'active')->count();
    $fullGroups = $groups->where('status', 'full')->count();
    $totalStudents = $groups->sum('enrollments_count');
@endphp

<div class="groups-page">
    <div class="d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-users me-2"></i>Gruplar</h5>
        <a href="{{ route('admin.groups.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Yeni Grup Oluştur
        </a>
    </div>

    <div class="summary-grid">
        <div class="summary-card">
            <div class="summary-label">Toplam Grup</div>
            <div class="summary-value">{{ $totalGroups }}</div>
        </div>
        <div class="summary-card">
            <div class="summary-label">Aktif Grup</div>
            <div class="summary-value text-success">{{ $activeGroups }}</div>
        </div>
        <div class="summary-card">
            <div class="summary-label">Dolu Grup</div>
            <div class="summary-value text-warning">{{ $fullGroups }}</div>
        </div>
        <div class="summary-card">
            <div class="summary-label">Toplam Öğrenci</div>
            <div class="summary-value text-primary">{{ $totalStudents }}</div>
        </div>
    </div>

    <div class="groups-grid">
        @forelse($groups as $group)
            <div class="group-card">
                <div class="group-head">
                    <div class="group-name"><i class="fas fa-layer-group me-2"></i>{{ $group->name }}</div>
                    @if($group->status == 'active')
                        <span class="badge bg-success">Aktif</span>
                    @elseif($group->status == 'inactive')
                        <span class="badge bg-secondary">Pasif</span>
                    @else
                        <span class="badge bg-warning text-dark">Dolu</span>
                    @endif
                </div>
                <div class="group-body">
                    <div class="group-meta">
                        <div><b>Sınıf:</b> {{ $group->workshop->name }}</div>
                        <div><b>Eğitmen:</b> {{ $group->instructor->name ?? 'Atanmamış' }}</div>
                    </div>

                    <div class="group-stats">
                        <div class="mini-stat">
                            <div class="n text-primary">{{ $group->capacity }}</div>
                            <div class="t">Kontenjan</div>
                        </div>
                        <div class="mini-stat">
                            <div class="n text-success">{{ $group->enrollments_count }}</div>
                            <div class="t">Kayıt</div>
                        </div>
                        <div class="mini-stat">
                            <div class="n text-info">{{ max($group->capacity - $group->enrollments_count, 0) }}</div>
                            <div class="t">Boş</div>
                        </div>
                    </div>

                    <div class="program-box"><b>Program:</b> {{ $group->schedule }}</div>

                    @if($group->description)
                        <div class="small text-muted">{{ $group->description }}</div>
                    @endif

                    <div class="group-actions">
                        <a href="{{ route('admin.groups.detail', $group->id) }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-circle-info me-1"></i>Detay
                        </a>
                        <a href="{{ route('admin.groups.edit', $group->id) }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-pen me-1"></i>Düzenle
                        </a>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteGroup({{ $group->id }})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="empty-card">
                <i class="fas fa-users fa-2x mb-2"></i>
                <div class="fw-semibold">Henüz grup bulunmuyor</div>
                <div class="small">İlk grubu oluşturmak için sağ üstteki butonu kullan.</div>
            </div>
        @endforelse
    </div>
</div>

<div class="modal fade" id="addGroupModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus me-2"></i>Yeni Grup Oluştur</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.groups.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Grup Adı</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Sınıf</label>
                            <select class="form-select" name="workshop_id" required>
                                <option value="">Sınıf Seçin</option>
                                @foreach($workshops as $workshop)
                                    <option value="{{ $workshop->id }}">{{ $workshop->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Eğitmen</label>
                            <select class="form-select" name="instructor_id" required>
                                <option value="">Eğitmen Seçin</option>
                                @foreach($instructors as $instructor)
                                    <option value="{{ $instructor->id }}">{{ $instructor->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kontenjan</label>
                            <input type="number" class="form-control" name="capacity" value="20" min="1" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label d-block">Günler</label>
                            @foreach(['monday' => 'Pazartesi','tuesday' => 'Salı','wednesday' => 'Çarşamba','thursday' => 'Perşembe','friday' => 'Cuma','saturday' => 'Cumartesi','sunday' => 'Pazar'] as $dayKey => $dayLabel)
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input new-day-check" type="checkbox" id="new_day_{{ $dayKey }}" name="day_of_weeks[]" value="{{ $dayKey }}">
                                    <label class="form-check-label" for="new_day_{{ $dayKey }}">{{ $dayLabel }}</label>
                                </div>
                            @endforeach
                        </div>

                        <div class="col-12">
                            <div class="row g-2">
                                @foreach(['monday' => 'Pazartesi','tuesday' => 'Salı','wednesday' => 'Çarşamba','thursday' => 'Perşembe','friday' => 'Cuma','saturday' => 'Cumartesi','sunday' => 'Pazar'] as $dayKey => $dayLabel)
                                    <div class="col-md-6 new-day-block" data-day="{{ $dayKey }}" style="display:none;">
                                        <div class="border rounded p-2">
                                            <div class="fw-semibold small mb-1">{{ $dayLabel }}</div>
                                            <div class="row g-2">
                                                <div class="col-6">
                                                    <input type="time" class="form-control form-control-sm" name="day_schedules[{{ $dayKey }}][start]">
                                                </div>
                                                <div class="col-6">
                                                    <input type="time" class="form-control form-control-sm" name="day_schedules[{{ $dayKey }}][end]">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Grup Başlangıç Tarihi</label>
                            <input type="date" class="form-control" name="group_start_date">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Grup Bitiş Tarihi</label>
                            <input type="date" class="form-control" name="group_end_date">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Durum</label>
                            <select class="form-select" name="status">
                                <option value="active">Aktif</option>
                                <option value="inactive">Pasif</option>
                                <option value="full">Dolu</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Açıklama</label>
                            <textarea class="form-control" name="description" rows="2"></textarea>
                        </div>
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
    if (!confirm('Bu grubu silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.')) return;
    fetch(`/admin/groups/${groupId}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) location.reload();
        else alert('Hata: ' + (data.message || 'İşlem başarısız'));
    })
    .catch(() => alert('Bir hata oluştu'));
}

document.querySelectorAll('.new-day-check').forEach((checkbox) => {
    checkbox.addEventListener('change', function () {
        const day = this.value;
        const block = document.querySelector(`.new-day-block[data-day="${day}"]`);
        if (!block) return;
        block.style.display = this.checked ? '' : 'none';
    });
});
</script>
@endsection

