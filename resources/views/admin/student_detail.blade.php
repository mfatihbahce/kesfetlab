@extends('admin.layout')

@section('title', $student->full_name . ' - Öğrenci Detayı')
@section('page-title', 'Öğrenci Detayı')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">{{ $student->full_name }}</h5>
    <a href="{{ route('admin.enrollments') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i> Öğrenciler
    </a>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="table-card p-3">
            <div class="text-muted small">T.C. Kimlik</div>
            <div class="fw-bold">{{ $student->tc_identity }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="table-card p-3">
            <div class="text-muted small">Veli</div>
            <div class="fw-bold">{{ $student->parent_full_name }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="table-card p-3">
            <div class="text-muted small">Veli Telefon</div>
            <div class="fw-bold">{{ $student->parent_phone }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="table-card p-3">
            <div class="text-muted small">Durum</div>
            <div class="fw-bold">{{ $student->registration_status_text }}</div>
        </div>
    </div>
</div>

<div class="table-card mb-4">
    <div class="table-header">
        <i class="fas fa-book-open me-2"></i> Aktif / Devam Eden Kayıtlar
    </div>
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>Sınıf</th>
                    <th>Grup</th>
                    <th>Program</th>
                    <th>Kayıt Durumu</th>
                    <th>Ödeme</th>
                    <th>İşlem</th>
                </tr>
            </thead>
            <tbody>
            @forelse($activeEnrollments as $enrollment)
                <tr>
                    <td>{{ $enrollment->workshop->name ?? '-' }}</td>
                    <td>{{ $enrollment->group->name ?? 'Grup atanmamış' }}</td>
                    <td>{{ $enrollment->group->schedule ?? '-' }}</td>
                    <td>{{ $enrollment->status_text }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <input type="number" step="0.01" min="0" class="form-control form-control-sm" id="amount_{{ $enrollment->id }}" value="{{ $enrollment->amount ?? 0 }}" style="max-width: 120px;">
                            <span class="small text-muted">{{ $enrollment->payment_status_text }}</span>
                        </div>
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-primary" onclick="savePayment({{ $enrollment->id }})">Ödeme Ekle/Güncelle</button>
                            @if($enrollment->payment_status !== 'paid')
                                <button class="btn btn-outline-success" onclick="markAsPaid({{ $enrollment->id }})">Ödendi</button>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-4">Aktif kayıt bulunmuyor.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="table-card">
    <div class="table-header">
        <i class="fas fa-graduation-cap me-2"></i> Mezun Olduğu Gruplar / Dersler
    </div>
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>Sınıf</th>
                    <th>Grup</th>
                    <th>Program</th>
                    <th>Mezuniyet Tarihi</th>
                </tr>
            </thead>
            <tbody>
            @forelse($graduatedEnrollments as $enrollment)
                <tr>
                    <td>{{ $enrollment->workshop->name ?? '-' }}</td>
                    <td>{{ $enrollment->group->name ?? '-' }}</td>
                    <td>{{ $enrollment->group->schedule ?? '-' }}</td>
                    <td>{{ optional($enrollment->end_date)->format('d.m.Y') ?? '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center text-muted py-4">Mezun kaydı bulunmuyor.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
function savePayment(enrollmentId) {
    const amount = document.getElementById(`amount_${enrollmentId}`).value || 0;
    const baseUrl = window.appBaseUrl || '';
    fetch(`${baseUrl}/admin/enrollments/${enrollmentId}/payment`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name=\"csrf-token\"]').getAttribute('content')
        },
        body: JSON.stringify({
            amount: amount,
            payment_status: 'partial',
            payment_notes: `Öğrenci bazlı tutar: ${amount} TL`
        })
    }).then(() => location.reload());
}

function markAsPaid(enrollmentId) {
    const amount = document.getElementById(`amount_${enrollmentId}`).value || 0;
    const baseUrl = window.appBaseUrl || '';
    fetch(`${baseUrl}/admin/enrollments/${enrollmentId}/payment`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name=\"csrf-token\"]').getAttribute('content')
        },
        body: JSON.stringify({
            amount: amount,
            payment_status: 'paid',
            payment_date: new Date().toISOString().split('T')[0],
            payment_notes: `Öğrenci bazlı tutar: ${amount} TL`
        })
    }).then(() => location.reload());
}
</script>
@endsection

