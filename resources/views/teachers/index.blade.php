<x-layouts.app title="Teachers">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="fw-bold mb-0">Teachers</h3>
        @can('teachers.create')
            <a href="{{ route('teachers.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add</a>
        @endcan
    </div>
    <div class="card card-stat">
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="dataTable">
                <thead><tr><th>Employee ID</th><th>Department</th><th>Designation</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                    @forelse($teachers as $teacher)
                        <tr>
                            <td>{{ $teacher->employee_id }}</td>
                            <td>{{ $teacher->department?->name ?? '-' }}</td>
                            <td>{{ $teacher->designation ?? '-' }}</td>
                            <td><span class="badge bg-{{ $teacher->status==='active'?'success':'secondary' }}">{{ $teacher->status }}</span></td>
                            <td>
                                <a href="{{ route('teachers.edit', $teacher) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted">No teachers yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    {{ $teachers->links() }}
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/datatables.net@1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/datatables.net-bs5@1.13.8/js/dataTables.bootstrap5.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/datatables.net-bs5@1.13.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <script>$(function(){ $('#dataTable').DataTable(); });</script>
    @endpush
</x-layouts.app>
