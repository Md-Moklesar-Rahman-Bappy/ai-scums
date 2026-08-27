<x-layouts.app title="Attendance">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="fw-bold mb-0">Attendance</h3>
        @can('attendance.manage')
            <a href="{{ route('attendances.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Mark</a>
        @endcan
    </div>

    <form method="GET" class="row g-2 mb-3">
        <div class="col-md-4">
            <input type="date" name="date" value="{{ $date }}" class="form-control">
        </div>
        <div class="col-md-3">
            <select name="section_id" class="form-select select2">
                <option value="">All sections</option>
                @foreach(\App\Models\Section::all() as $s)<option value="{{ $s->id }}" @selected(request('section_id')==$s->id)>{{ $s->name }}</option>@endforeach
            </select>
        </div>
        <div class="col-auto"><button class="btn btn-outline-primary">Filter</button></div>
        <div class="col-auto"><a href="{{ route('attendances.analytics') }}" class="btn btn-outline-info">Analytics</a></div>
    </form>

    <div class="row g-2 mb-3">
        <div class="col"><div class="card card-stat p-2 text-center"><div class="fs-4 fw-bold">{{ $summary['present'] }}</div><small class="text-success">Present</small></div></div>
        <div class="col"><div class="card card-stat p-2 text-center"><div class="fs-4 fw-bold">{{ $summary['absent'] }}</div><small class="text-danger">Absent</small></div></div>
        <div class="col"><div class="card card-stat p-2 text-center"><div class="fs-4 fw-bold">{{ $summary['late'] }}</div><small class="text-warning">Late</small></div></div>
        <div class="col"><div class="card card-stat p-2 text-center"><div class="fs-4 fw-bold">{{ $summary['total'] }}</div><small class="text-muted">Total</small></div></div>
    </div>

    <div class="card card-stat">
        <div class="table-responsive">
            <table class="table table-sm mb-0">
                <thead><tr><th>Student</th><th>Status</th><th>Marked</th></tr></thead>
                <tbody>
                    @forelse($records as $rec)
                        <tr>
                            <td>{{ $rec->student?->admission_no ?? 'N/A' }}</td>
                            <td><span class="badge bg-{{ $rec->status=='present'?'success':($rec->status=='late'?'warning':'danger') }}">{{ $rec->status }}</span></td>
                            <td>{{ $rec->created_at?->format('H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-center text-muted">No records for this date.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @push('scripts')<script>$(function(){ $('.select2').select2(); });</script>@endpush
</x-layouts.app>
