@extends('admin.layout')

@section('title', 'Dashboard - Keşfet LAB')
@section('page-title', 'Dashboard')

@section('content')
<style>
    .dash-shell { display: grid; gap: 16px; }
    .hero-card {
        border-radius: 16px;
        padding: 18px 20px;
        background: linear-gradient(120deg, #2f3138, #464a55);
        color: #fff;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
    }
    .hero-title {
        margin: 0;
        font-size: 1.2rem;
        font-weight: 700;
    }
    .hero-sub {
        margin: 6px 0 0;
        color: rgba(255,255,255,.78);
        font-size: .9rem;
    }
    .hero-badge {
        border-radius: 999px;
        padding: 8px 12px;
        background: rgba(245,209,0,.2);
        color: #f5d100;
        font-size: .8rem;
        font-weight: 700;
        white-space: nowrap;
    }
    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
    }
    .metric-card {
        background: #fff;
        border: 1px solid #eceff3;
        border-radius: 12px;
        padding: 12px;
        box-shadow: 0 6px 16px rgba(47,49,56,.05);
    }
    .metric-top {
        display: flex;
        justify-content: flex-start;
        align-items: center;
        gap: 8px;
        margin-bottom: 4px;
    }
    .metric-name { font-size: .78rem; color: #6b7280; font-weight: 600; }
    .metric-ico {
        width: 28px; height: 28px; border-radius: 8px;
        display: inline-flex; align-items: center; justify-content: center;
        background: rgba(255,122,0,.12); color: #c56d00; font-size: .8rem;
    }
    .metric-value {
        font-size: 1.35rem;
        font-weight: 800;
        line-height: 1;
        color: #111827;
        margin-left: auto;
        margin-right: 2px;
    }
    .metric-foot { margin-top: 4px; font-size: .75rem; color: #9ca3af; }
    .dash-panels {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px;
    }
    .panel-card {
        background: #fff;
        border: 1px solid #eceff3;
        border-radius: 14px;
        overflow: hidden;
    }
    .panel-head {
        background: #f8fafc;
        padding: 11px 14px;
        border-bottom: 1px solid #eceff3;
        font-weight: 700;
        color: #2f3138;
    }
    .quick-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 10px;
    }
    .quick-btn {
        border-radius: 12px;
        padding: 11px 10px;
        text-align: center;
        font-weight: 700;
    }
    @media (max-width: 992px) {
        .kpi-grid, .quick-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .dash-panels { grid-template-columns: 1fr; }
    }
</style>

<div class="dash-shell">
    <div class="hero-card">
        <div>
            <h3 class="hero-title">Yönetim Özeti</h3>
            <p class="hero-sub">Ön kayıtları, sınıf ve grup planlarını tek ekrandan takip edin.</p>
        </div>
        <div class="hero-badge"><i class="fas fa-circle-check me-1"></i>Sistem Aktif</div>
    </div>

    <div class="kpi-grid">
        <div class="metric-card">
            <div class="metric-top">
                <div class="metric-name">Toplam Öğrenci</div>
                <div class="metric-value">{{ $stats['total_students'] }}</div>
                <span class="metric-ico"><i class="fas fa-user-graduate"></i></span>
            </div>
            <div class="metric-foot">Sisteme kayıtlı tüm öğrenciler</div>
        </div>
        <div class="metric-card">
            <div class="metric-top">
                <div class="metric-name">Bekleyen Kayıt</div>
                <div class="metric-value">{{ $stats['pending_students'] }}</div>
                <span class="metric-ico"><i class="fas fa-hourglass-half"></i></span>
            </div>
            <div class="metric-foot">Onay bekleyen başvurular</div>
        </div>
        <div class="metric-card">
            <div class="metric-top">
                <div class="metric-name">Aktif Sınıf</div>
                <div class="metric-value">{{ $stats['active_workshops'] }}</div>
                <span class="metric-ico"><i class="fas fa-flask"></i></span>
            </div>
            <div class="metric-foot">Devam eden sınıf içerikleri</div>
        </div>
        <div class="metric-card">
            <div class="metric-top">
                <div class="metric-name">Aktif Grup</div>
                <div class="metric-value">{{ $stats['active_groups'] }}</div>
                <span class="metric-ico"><i class="fas fa-users"></i></span>
            </div>
            <div class="metric-foot">Planı açık ders grupları</div>
        </div>
    </div>

    <div class="dash-panels">
        <div class="panel-card">
            <div class="panel-head">
                <i class="fas fa-user-graduate me-2"></i>
                Son Öğrenci Kayıtları
            </div>
            <div class="table-responsive p-2">
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

        <div class="panel-card">
            <div class="panel-head">
                <i class="fas fa-clipboard-list me-2"></i>
                Son Kayıtlar
            </div>
            <div class="table-responsive p-2">
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

    <div class="panel-card">
        <div class="panel-head">
            <i class="fas fa-bolt me-2"></i>
            Hızlı İşlemler
        </div>
        <div class="p-3">
            <div class="quick-grid">
                <a href="{{ route('admin.students') }}" class="btn btn-primary quick-btn">
                    <i class="fas fa-user-graduate me-2"></i>Öğrenci Yönetimi
                </a>
                <a href="{{ route('admin.workshops') }}" class="btn btn-primary quick-btn">
                    <i class="fas fa-flask me-2"></i>Sınıf Yönetimi
                </a>
                <a href="{{ route('admin.groups') }}" class="btn btn-primary quick-btn">
                    <i class="fas fa-users me-2"></i>Grup Yönetimi
                </a>
                <a href="{{ route('admin.enrollments') }}" class="btn btn-primary quick-btn">
                    <i class="fas fa-clipboard-list me-2"></i>Kayıt Yönetimi
                </a>
            </div>
        </div>
    </div>

    <div class="panel-card">
        <div class="panel-head">
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
@endsection
