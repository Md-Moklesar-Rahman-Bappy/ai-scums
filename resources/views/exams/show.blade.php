<x-layouts.app title="Exam Results">
    <h3 class="fw-bold mb-3">{{ $exam->name }} - Results</h3>
    <div class="row g-2 mb-3">
        <div class="col"><div class="card card-stat p-2 text-center"><div class="fs-4 fw-bold">{{ $summary['average'] }}</div><small>Average</small></div></div>
        <div class="col"><div class="card card-stat p-2 text-center"><div class="fs-4 fw-bold">{{ $summary['highest'] }}</div><small>Highest</small></div></div>
        <div class="col"><div class="card card-stat p-2 text-center"><div class="fs-4 fw-bold">{{ $summary['pass_rate'] }}%</div><small>Pass Rate</small></div></div>
    </div>
    <div class="card card-stat">
        <div class="table-responsive">
            <table class="table table-sm mb-0">
                <thead><tr><th>Student</th><th>Marks</th><th>Grade</th></tr></thead>
                <tbody>
                    @forelse($exam->marks as $mark)
                        <tr><td>{{ $mark->student?->admission_no }}</td><td>{{ $mark->marks_obtained }}/{{ $mark->total_marks }}</td><td><span class="badge bg-info">{{ $mark->grade }}</span></td></tr>
                    @empty
                        <tr><td colspan="3" class="text-muted text-center">No marks entered.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.app>
