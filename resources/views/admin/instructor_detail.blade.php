@extends('admin.layout')

@section('title', $instructor->full_name . ' - Eğitmen Detayı')
@section('page-title', $instructor->full_name . ' - Eğitmen Detayı')

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
                <h5 class="mb-0"><i class="fas fa-users me-2"></i>Yönettiği Gruplar</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Grup</th>
                                <th>Sınıf</th>
                                <th>Program</th>
                                <th>Öğrenci</th>
                                <th>Durum</th>
                                <th>İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($instructor->groups as $group)
                            <tr>
                                <td>
                                    <div class="fw-bold">{{ $group->name }}</div>
                                    <div class="text-muted small">{{ $group->capacity }} kontenjan</div>
                                </td>
                                <td>{{ $group->workshop->name }}</td>
                                <td>{{ $group->schedule }}</td>
                                <td>
                                    <span class="badge bg-primary">{{ $group->enrollments->count() }}</span>
                                </td>
                                <td>
                                    @if($group->status == 'active')
                                        <span class="badge bg-success">Aktif</span>
                                    @elseif($group->status == 'inactive')
                                        <span class="badge bg-secondary">Pasif</span>
                                    @else
                                        <span class="badge bg-warning">Dolu</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.groups.detail', $group->id) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">Bu eğitmenin henüz grubu bulunmuyor.</td>
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
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Eğitmen Bilgileri</h5>
            </div>
            <div class="card-body">
                <div class="mb-2"><strong>Ad Soyad:</strong> {{ $instructor->full_name }}</div>
                <div class="mb-2"><strong>E-posta:</strong> {{ $instructor->email }}</div>
                <div class="mb-2"><strong>Telefon:</strong> {{ $instructor->phone }}</div>
                @if($instructor->profession)
                <div class="mb-2"><strong>Meslek:</strong> {{ $instructor->profession }}</div>
                @endif
                @if($instructor->address)
                <div class="mb-2"><strong>Adres:</strong> {{ $instructor->address }}</div>
                @endif
                <div class="mb-2">
                    <strong>Durum:</strong> 
                    @if($instructor->is_active)
                        <span class="badge bg-success">Aktif</span>
                    @else
                        <span class="badge bg-secondary">Pasif</span>
                    @endif
                </div>
                <div class="mb-2"><strong>Kayıt Tarihi:</strong> {{ $instructor->created_at->format('d.m.Y') }}</div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>İstatistikler</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <div class="h4 text-primary mb-0">{{ $instructor->groups->count() }}</div>
                        <small class="text-muted">Grup</small>
                    </div>
                    <div class="col-6">
                        <div class="h4 text-success mb-0">
                            {{ $instructor->groups->sum(function($group) { return $group->enrollments->count(); }) }}
                        </div>
                        <small class="text-muted">Öğrenci</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-users me-2"></i>Öğrenci Listesi</h5>
            </div>
            <div class="list-group list-group-flush">
                @php
                    $allStudents = collect();
                    foreach($instructor->groups as $group) {
                        $allStudents = $allStudents->merge($group->enrollments->pluck('student'));
                    }
                    $allStudents = $allStudents->unique('id');
                @endphp
                
                @forelse($allStudents as $student)
                    <div class="list-group-item">
                        <div class="fw-bold">{{ $student->full_name }}</div>
                        <div class="small text-muted">{{ $student->parent_full_name }}</div>
                        <div class="small text-muted">{{ $student->parent_phone }}</div>
                    </div>
                @empty
                    <div class="list-group-item text-muted">Henüz öğrenci bulunmuyor.</div>
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
        },
        eventClick: function(info) {
            if (info.event.extendedProps.groupId) {
                window.open('/admin/groups/' + info.event.extendedProps.groupId, '_blank');
            }
        }
    });
    calendar.render();
});
</script>
@endsection
