@extends('admin.layout')

@section('title', 'Takvim - Keşfet LAB')
@section('page-title', 'Takvim')

@section('content')
<style>
    .calendar-toolbar-card {
        border: 1px solid #eceff3;
        border-radius: 14px;
        background: #fff;
        box-shadow: 0 8px 20px rgba(47,49,56,.06);
    }
    .calendar-shell {
        border: 1px solid rgba(255,122,0,.15);
        border-radius: 16px;
        background: linear-gradient(160deg, #ffffff 0%, #fffaf2 100%);
        box-shadow: 0 14px 28px rgba(47,49,56,.10);
    }
    .legend-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 6px;
    }
    .fc .fc-toolbar-title {
        font-weight: 700;
        color: #2f3138;
    }
    .fc .fc-button-primary {
        background: #2f3138;
        border-color: #2f3138;
    }
    .fc .fc-button-primary:hover {
        background: #1f2530;
        border-color: #1f2530;
    }
    .fc .fc-event {
        border-radius: 8px;
        border: 0;
        padding: 1px 4px;
        font-size: .78rem;
    }
</style>
<div class="row mb-3">
    <div class="col-12">
        <div class="calendar-toolbar-card p-3">
            <form method="GET" action="{{ route('admin.calendar') }}" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label for="workshop_id" class="form-label">Sınıf Filtresi</label>
                    <select id="workshop_id" name="workshop_id" class="form-select">
                        <option value="">Tüm Sınıflar</option>
                        @foreach($workshops as $workshop)
                            <option value="{{ $workshop->id }}" {{ (string)$selectedWorkshopId === (string)$workshop->id ? 'selected' : '' }}>
                                {{ $workshop->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-8">
                    <button class="btn btn-primary" type="submit">
                        <i class="fas fa-filter me-1"></i>Filtrele
                    </button>
                    <a href="{{ route('admin.calendar') }}" class="btn btn-outline-secondary ms-2">Temizle</a>
                </div>
                <div class="col-md-8 text-md-end">
                    <span class="small text-muted me-3"><span class="legend-dot" style="background:#1f6feb;"></span>Normal Plan</span>
                    <span class="small text-muted"><span class="legend-dot" style="background:#d73a49;"></span>Çakışma</span>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="calendar-shell p-3">
    <div id="admin-calendar"></div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('admin-calendar');
    const workshopId = @json($selectedWorkshopId);
    const baseUrl = window.appBaseUrl || '';
    const eventsUrl = `${baseUrl}/admin/calendar/events`;

    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'tr',
        height: 720,
        buttonText: {
            today: 'Bugün',
            month: 'Ay',
            week: 'Hafta',
            day: 'Gün'
        },
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        eventTimeFormat: { hour: '2-digit', minute: '2-digit', hour12: false },
        events: function(fetchInfo, successCallback, failureCallback) {
            const params = new URLSearchParams({
                start: fetchInfo.startStr,
                end: fetchInfo.endStr
            });
            if (workshopId) params.append('workshop_id', workshopId);

            fetch(`${eventsUrl}?${params.toString()}`, {
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin'
            })
            .then((res) => res.json())
            .then((data) => successCallback(data))
            .catch((err) => failureCallback(err));
        },
        eventDidMount: function(info) {
            const p = info.event.extendedProps || {};
            const conflictText = p.conflict ? '\n⚠ ÇAKIŞMA VAR' : '';
            info.el.title = `${p.workshop_name || ''}\n${p.group_name || ''}\nEğitmen: ${p.instructor_name || '-'}${conflictText}`;
            if (p.conflict) {
                info.el.style.boxShadow = '0 0 0 2px rgba(215,58,73,.28)';
            }
        }
    });

    calendar.render();
});
</script>
@endsection

