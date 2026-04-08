@extends('admin.layout')

@section('title', 'Dashboard - Keşfet LAB')
@section('page-title', 'Dashboard')

@section('content')
<div class="row">
    <!-- İstatistik Kartları -->
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="kpi-card">
            <div class="kpi-head">
                <div class="kpi-icon"><i class="fas fa-user-graduate"></i></div>
                <span class="kpi-chip">GENEL</span>
            </div>
            <div class="kpi-value">{{ $stats['total_students'] }}</div>
            <div class="kpi-label">Toplam Öğrenci</div>
            <div class="kpi-foot"><i class="fas fa-wave-square"></i> Tüm kayıtlar</div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="kpi-card">
            <div class="kpi-head">
                <div class="kpi-icon"><i class="fas fa-clock"></i></div>
                <span class="kpi-chip">AKTİF</span>
            </div>
            <div class="kpi-value">{{ $stats['pending_students'] }}</div>
            <div class="kpi-label">Bekleyen Kayıt</div>
            <div class="kpi-foot"><i class="fas fa-hourglass-half"></i> Onay sürecinde</div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="kpi-card">
            <div class="kpi-head">
                <div class="kpi-icon"><i class="fas fa-flask"></i></div>
                <span class="kpi-chip">EĞİTİM</span>
            </div>
            <div class="kpi-value">{{ $stats['active_workshops'] }}</div>
            <div class="kpi-label">Aktif Sınıf</div>
            <div class="kpi-foot"><i class="fas fa-bolt"></i> Yayında olan içerik</div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="kpi-card">
            <div class="kpi-head">
                <div class="kpi-icon"><i class="fas fa-users"></i></div>
                <span class="kpi-chip">SINIF</span>
            </div>
            <div class="kpi-value">{{ $stats['active_groups'] }}</div>
            <div class="kpi-label">Aktif Grup</div>
            <div class="kpi-foot"><i class="fas fa-layer-group"></i> Planlanan ders grubu</div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Son Öğrenci Kayıtları -->
    <div class="col-lg-6 mb-4">
        <div class="table-card">
            <div class="table-header">
                <i class="fas fa-user-graduate me-2"></i>
                Son Öğrenci Kayıtları
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Öğrenci</th>
                            <th>Okul</th>
                            <th>T.C. Kimlik</th>
                            <th>Durum</th>
                            <th>Tarih</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentStudents as $student)
                        <tr>
                            <td>
                                <strong>{{ $student->full_name }}</strong>
                                <br>
                                <small class="text-muted">{{ $student->parent_full_name }}</small>
                            </td>
                            <td>
                                @if($student->school_name)
                                    <span>{{ $student->school_name }}</span>
                                @else
                                    <span class="text-muted">Belirtilmemis</span>
                                @endif
                            </td>
                            <td>{{ $student->tc_identity }}</td>
                            <td>
                                @if($student->registration_status == 'pending')
                                    <span class="badge badge-pending">Beklemede</span>
                                @elseif($student->registration_status == 'approved')
                                    <span class="badge badge-approved">Onaylandı</span>
                                @else
                                    <span class="badge badge-rejected">Reddedildi</span>
                                @endif
                            </td>
                            <td>{{ $student->created_at->format('d.m.Y') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">
                                <i class="fas fa-inbox me-2"></i>
                                Henüz öğrenci kaydı bulunmuyor
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Son Kayıtlar -->
    <div class="col-lg-6 mb-4">
        <div class="table-card">
            <div class="table-header">
                <i class="fas fa-clipboard-list me-2"></i>
                Son Kayıtlar
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Öğrenci</th>
                            <th>Sınıf</th>
                            <th>Durum</th>
                            <th>Tarih</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentEnrollments as $enrollment)
                        <tr>
                            <td>
                                <strong>{{ $enrollment->student->full_name }}</strong>
                            </td>
                            <td>
                                {{ $enrollment->workshop->name }}
                                @if($enrollment->group)
                                    <br>
                                    <small class="text-muted">{{ $enrollment->group->name }}</small>
                                @endif
                            </td>
                            <td>
                                @if($enrollment->status == 'pending')
                                    <span class="badge badge-pending">Beklemede</span>
                                @elseif($enrollment->status == 'approved')
                                    <span class="badge badge-approved">Onaylandı</span>
                                @else
                                    <span class="badge badge-rejected">Reddedildi</span>
                                @endif
                            </td>
                            <td>{{ $enrollment->created_at->format('d.m.Y') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">
                                <i class="fas fa-inbox me-2"></i>
                                Henüz kayıt bulunmuyor
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Hızlı İşlemler -->
    <div class="col-12">
        <div class="table-card">
            <div class="table-header">
                <i class="fas fa-bolt me-2"></i>
                Hızlı İşlemler
            </div>
            <div class="p-3">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <a href="{{ route('admin.students') }}" class="btn btn-primary w-100">
                            <i class="fas fa-user-graduate me-2"></i>
                            Öğrenci Yönetimi
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="{{ route('admin.workshops') }}" class="btn btn-primary w-100">
                            <i class="fas fa-flask me-2"></i>
                            Sınıf Yönetimi
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="{{ route('admin.groups') }}" class="btn btn-primary w-100">
                            <i class="fas fa-users me-2"></i>
                            Grup Yönetimi
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="{{ route('admin.enrollments') }}" class="btn btn-primary w-100">
                            <i class="fas fa-clipboard-list me-2"></i>
                            Kayıt Yönetimi
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Sistem Bilgileri -->
<div class="row mt-4">
    <div class="col-12">
        <div class="table-card">
            <div class="table-header">
                <i class="fas fa-info-circle me-2"></i>
                Sistem Bilgileri
            </div>
            <div class="p-3">
                <div class="row">
                    <div class="col-md-6">
                        <h6><i class="fas fa-chart-pie me-2"></i>Genel İstatistikler</h6>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-circle text-primary me-2"></i>Toplam Öğrenci: {{ $stats['total_students'] }}</li>
                            <li><i class="fas fa-circle text-warning me-2"></i>Bekleyen Kayıt: {{ $stats['pending_students'] }}</li>
                            <li><i class="fas fa-circle text-success me-2"></i>Onaylanan Kayıt: {{ $stats['approved_students'] }}</li>
                            <li><i class="fas fa-circle text-info me-2"></i>Toplam Sınıf: {{ $stats['total_workshops'] }}</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6><i class="fas fa-cog me-2"></i>Sistem Durumu</h6>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check-circle text-success me-2"></i>Veritabanı: Aktif</li>
                            <li><i class="fas fa-check-circle text-success me-2"></i>API: Çalışıyor</li>
                            <li><i class="fas fa-check-circle text-success me-2"></i>Online Form: Aktif</li>
                            <li><i class="fas fa-clock text-warning me-2"></i>Son Güncelleme: {{ now()->format('d.m.Y H:i') }}</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
