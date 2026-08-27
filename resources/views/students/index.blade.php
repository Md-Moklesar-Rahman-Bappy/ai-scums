<x-layouts.app title="Students">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="fw-bold mb-0">Students</h3>
        @can('students.create')
            <a href="{{ route('students.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Admit</a>
        @endcan
    </div>

    <div class="card card-stat">
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="dataTable">
                <thead><tr><th>Admission No</th><th>Class</th><th>Section</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                    @forelse($students as $student)
                        <tr>
                            <td>{{ $student->admission_no }}</td>
                            <td>{{ $student->schoolClass?->name ?? $student->program?->name ?? '-' }}</td>
                            <td>{{ $student->section?->name ?? '-' }}</td>
                            <td><span class="badge bg-{{ $student->status==='active' ? 'success' : 'secondary' }}">{{ $student->status }}</span></td>
                            <td>
                                <a href="{{ route('students.show', $student) }}" class="btn btn-sm btn-outline-info">View</a>
                                @can('students.edit')
                                    <a href="{{ route('students.edit', $student) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted">No students yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{ $students->links() }}

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/datatables.net@1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/datatables.net-bs5@1.13.8/js/dataTables.bootstrap5.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/datatables.net-bs5@1.13.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <script>$(function(){ $('#dataTable').DataTable(); });</script>
    @endpush
</x-layouts.app>
