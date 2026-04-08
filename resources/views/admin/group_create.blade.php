@extends('admin.layout')

@section('title', 'Yeni Grup Oluştur')
@section('page-title', 'Yeni Grup Oluştur')

@section('content')
<div class="row justify-content-center">
    <div class="col-xl-10">
        <div class="table-card">
            <div class="table-header d-flex justify-content-between align-items-center">
                <div><i class="fas fa-plus me-2"></i>Yeni Grup Oluştur</div>
                <a href="{{ route('admin.groups') }}" class="btn btn-sm btn-light">Gruplara Dön</a>
            </div>
            <div class="p-4">
                <div id="conflictAlert" class="alert alert-warning d-none"></div>
                <form id="groupCreateForm" action="{{ route('admin.groups.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="conflict_override" id="conflict_override" value="0">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Grup Adı</label>
                            <input type="text" class="form-control" name="name" value="{{ old('name') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Sınıf</label>
                            <select class="form-select" name="workshop_id" required>
                                <option value="">Seçin</option>
                                @foreach($workshops as $workshop)
                                    <option value="{{ $workshop->id }}" {{ (int) old('workshop_id') === $workshop->id ? 'selected' : '' }}>{{ $workshop->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Eğitmen</label>
                            <select class="form-select" name="instructor_id" required>
                                <option value="">Seçin</option>
                                @foreach($instructors as $instructor)
                                    <option value="{{ $instructor->id }}" {{ (int) old('instructor_id') === $instructor->id ? 'selected' : '' }}>{{ $instructor->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kontenjan</label>
                            <input type="number" class="form-control" name="capacity" min="1" value="{{ old('capacity', 20) }}" required>
                        </div>
                    </div>

                    @php($dayMap = ['monday'=>'Pazartesi','tuesday'=>'Salı','wednesday'=>'Çarşamba','thursday'=>'Perşembe','friday'=>'Cuma','saturday'=>'Cumartesi','sunday'=>'Pazar'])
                    @php($selectedDays = old('day_of_weeks', []))

                    <hr>
                    <h6 class="mb-3">Ders Günleri ve Saatleri</h6>
                    <div class="mb-3">
                        @foreach($dayMap as $dayKey => $dayLabel)
                            <div class="form-check form-check-inline">
                                <input class="form-check-input day-check" type="checkbox" id="day_{{ $dayKey }}" name="day_of_weeks[]" value="{{ $dayKey }}" {{ in_array($dayKey, $selectedDays, true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="day_{{ $dayKey }}">{{ $dayLabel }}</label>
                            </div>
                        @endforeach
                    </div>

                    <div class="row g-3">
                        @foreach($dayMap as $dayKey => $dayLabel)
                            @php($slot = old("day_schedules.$dayKey", []))
                            <div class="col-md-6 day-schedule-block" data-day="{{ $dayKey }}" style="{{ in_array($dayKey, $selectedDays, true) ? '' : 'display:none;' }}">
                                <div class="border rounded p-3">
                                    <div class="fw-semibold mb-2">{{ $dayLabel }}</div>
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <label class="form-label">Başlangıç</label>
                                            <input type="time" class="form-control" name="day_schedules[{{ $dayKey }}][start]" value="{{ $slot['start'] ?? '' }}">
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label">Bitiş</label>
                                            <input type="time" class="form-control" name="day_schedules[{{ $dayKey }}][end]" value="{{ $slot['end'] ?? '' }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="row g-3 mt-2">
                        <div class="col-md-6">
                            <label class="form-label">Grup Başlangıç Tarihi</label>
                            <input type="date" class="form-control" name="group_start_date" value="{{ old('group_start_date') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Grup Bitiş Tarihi</label>
                            <input type="date" class="form-control" name="group_end_date" value="{{ old('group_end_date') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Durum</label>
                            <select class="form-select" name="status">
                                <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Aktif</option>
                                <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Pasif</option>
                                <option value="full" {{ old('status') === 'full' ? 'selected' : '' }}>Dolu</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Açıklama</label>
                            <textarea class="form-control" name="description" rows="3">{{ old('description') }}</textarea>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="{{ route('admin.groups') }}" class="btn btn-outline-secondary">Vazgeç</a>
                        <button type="submit" class="btn btn-primary">Kaydet</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
const formEl = document.getElementById('groupCreateForm');
const conflictAlertEl = document.getElementById('conflictAlert');

function toggleDayBlock(day, show) {
    const block = document.querySelector(`.day-schedule-block[data-day="${day}"]`);
    if (block) block.style.display = show ? '' : 'none';
}

document.querySelectorAll('.day-check').forEach((checkbox) => {
    checkbox.addEventListener('change', function () {
        toggleDayBlock(this.value, this.checked);
        runConflictCheck();
    });
});

formEl.addEventListener('change', (e) => {
    if (e.target.matches('select,input[type="time"],input[type="date"]')) {
        runConflictCheck();
    }
});

let submitConfirmed = false;
formEl.addEventListener('submit', async function(e) {
    if (submitConfirmed) return;
    e.preventDefault();

    const result = await runConflictCheck(true);
    if (result.has_conflict) {
        const ok = confirm("Çakışma tespit edildi:\\n\\n" + (result.conflicts || []).map(c => "- " + c).join("\\n") + "\\n\\nYine de kaydetmek istiyor musunuz?");
        if (!ok) return;
        document.getElementById('conflict_override').value = '1';
    }

    submitConfirmed = true;
    formEl.submit();
});

async function runConflictCheck(returnData = false) {
    const fd = new FormData(formEl);
    const dayOfWeeks = fd.getAll('day_of_weeks[]');
    if (!fd.get('workshop_id') || !fd.get('instructor_id') || dayOfWeeks.length === 0) {
        conflictAlertEl.classList.add('d-none');
        return { has_conflict: false, conflicts: [] };
    }

    const payload = {
        workshop_id: fd.get('workshop_id'),
        instructor_id: fd.get('instructor_id'),
        day_of_weeks: dayOfWeeks,
        group_start_date: fd.get('group_start_date') || null,
        group_end_date: fd.get('group_end_date') || null,
        day_schedules: {}
    };

    dayOfWeeks.forEach((day) => {
        payload.day_schedules[day] = {
            start: fd.get(`day_schedules[${day}][start]`) || null,
            end: fd.get(`day_schedules[${day}][end]`) || null
        };
    });

    const baseUrl = window.appBaseUrl || '';
    const res = await fetch(`${baseUrl}/admin/groups/conflicts`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(payload)
    });
    const data = await res.json().catch(() => ({ has_conflict: false, conflicts: [] }));

    if (data.has_conflict) {
        conflictAlertEl.classList.remove('d-none');
        conflictAlertEl.innerHTML = `<strong>Çakışma Uyarısı:</strong><br>${(data.conflicts || []).map(c => `- ${c}`).join('<br>')}`;
    } else {
        conflictAlertEl.classList.add('d-none');
    }
    return returnData ? data : data;
}
</script>
@endsection

