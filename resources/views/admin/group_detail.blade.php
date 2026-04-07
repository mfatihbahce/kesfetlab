@extends('admin.layout')

@section('title', $group->name . ' - Grup Detayı')
@section('page-title', $group->name . ' - Grup Detayı')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Ders Takvimi</h5>
            </div>
            <div class="card-body">
                <div id="calendar"></div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-users me-2"></i>Öğrenciler</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Öğrenci</th>
                                <th>Veli</th>
                                <th>Kayıt Tarihi</th>
                                <th>Durum</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($group->enrollments as $enrollment)
                            <tr>
                                <td>{{ $enrollment->student->full_name }}</td>
                                <td>{{ $enrollment->student->parent_full_name }} - {{ $enrollment->student->parent_phone }}</td>
                                <td>{{ optional($enrollment->created_at)->format('d.m.Y') }}</td>
                                <td>
                                    @if($enrollment->status == 'approved')
                                        <span class="badge bg-success">Onaylandı</span>
                                    @elseif($enrollment->status == 'pending')
                                        <span class="badge bg-warning text-dark">Beklemede</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $enrollment->status }}</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">Bu grupta öğrenci bulunmuyor.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Grup Bilgileri</h5>
            </div>
            <div class="card-body">
                <div class="mb-2"><strong>Atölye:</strong> {{ $group->workshop->name }}</div>
                <div class="mb-2"><strong>Eğitmen:</strong> {{ optional($group->instructor)->name ?? 'Atanmamış' }}</div>
                <div class="mb-2"><strong>Program:</strong> {{ $group->schedule }}</div>
                <div class="mb-2"><strong>Kontenjan:</strong> {{ $group->capacity }}</div>
                <div class="mb-2"><strong>Kayıt:</strong> {{ $group->enrollments->count() }}</div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-bullhorn me-2"></i>Gruba Duyuru Paylaş</h5>
            </div>
            <form action="{{ route('admin.groups.announcements.store', $group->id) }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Başlık</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mesaj</label>
                        <textarea name="message" rows="4" class="form-control" required></textarea>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane me-1"></i> Gönder</button>
                </div>
            </form>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-bell me-2"></i>Son Duyurular</h5>
            </div>
            <div class="list-group list-group-flush">
                @forelse($announcements as $a)
                    <div class="list-group-item">
                        <div class="fw-bold">{{ $a->title }}</div>
                        <div class="small text-muted">{{ $a->created_at->format('d.m.Y H:i') }}</div>
                        <div class="mt-1">{{ $a->message }}</div>
                    </div>
                @empty
                    <div class="list-group-item text-muted">Henüz duyuru yok.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');
    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'tr',
        height: 600,
        events: @json($events),
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        }
    });
    calendar.render();
});
</script>
@endsection



