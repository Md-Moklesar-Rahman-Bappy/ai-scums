<x-layouts.app title="Examinations">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="fw-bold mb-0">Examinations</h3>
        @can('exams.manage')
            <a href="{{ route('exams.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> New Exam</a>
        @endcan
    </div>
    <div class="card card-stat">
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="dataTable">
                <thead><tr><th>Name</th><th>Subject</th><th>Date</th><th>Total</th><th>Actions</th></tr></thead>
                <tbody>
                    @forelse($exams as $exam)
                        <tr>
                            <td>{{ $exam->name }}</td>
                            <td>{{ $exam->subject?->name ?? '-' }}</td>
                            <td>{{ $exam->exam_date?->format('Y-m-d') ?? '-' }}</td>
                            <td>{{ $exam->total_marks }}</td>
                            <td>
                                <a href="{{ route('exams.show', $exam) }}" class="btn btn-sm btn-outline-info">Results</a>
                                @can('marks.manage')
                                    <a href="{{ route('exams.marks', $exam) }}" class="btn btn-sm btn-outline-secondary">Enter Marks</a>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted">No exams yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    {{ $exams->links() }}
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/datatables.net@1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/datatables.net-bs5@1.13.8/js/dataTables.bootstrap5.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/datatables.net-bs5@1.13.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <script>$(function(){ $('#dataTable').DataTable(); });</script>
    @endpush
</x-layouts.app>
