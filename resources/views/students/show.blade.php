<x-layouts.app title="Student Profile">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="fw-bold mb-0">Student: {{ $student->admission_no }}</h3>
        @can('students.edit')
            <a href="{{ route('students.edit', $student) }}" class="btn btn-outline-secondary btn-sm">Edit</a>
        @endcan
    </div>

    <div class="row g-3">
        <div class="col-md-4">
            <div class="card card-stat p-3">
                <h6 class="fw-semibold">Profile</h6>
                <p class="mb-1"><strong>Class:</strong> {{ $student->schoolClass?->name ?? $student->program?->name ?? '-' }}</p>
                <p class="mb-1"><strong>Section:</strong> {{ $student->section?->name ?? '-' }}</p>
                <p class="mb-1"><strong>Semester:</strong> {{ $student->semester?->name ?? '-' }}</p>
                <p class="mb-1"><strong>Guardian:</strong> {{ $student->guardian_name }}</p>
                <p class="mb-0"><strong>Status:</strong> {{ $student->status }}</p>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card card-stat p-3 mb-3">
                <h6 class="fw-semibold">Attendance ({{ $student->attendances->count() }} records)</h6>
                @php $pct = \App\Models\Attendance::percentageFor($student->attendances); @endphp
                <div class="progress mb-2" style="height:20px;">
                    <div class="progress-bar bg-{{ $pct >= 75 ? 'success' : 'danger' }}" style="width:{{ $pct }}%">{{ $pct }}%</div>
                </div>
            </div>
            <div class="card card-stat p-3">
                <h6 class="fw-semibold">Results</h6>
                <table class="table table-sm mb-0">
                    <thead><tr><th>Exam</th><th>Subject</th><th>Marks</th><th>Grade</th></tr></thead>
                    <tbody>
                        @forelse($student->examMarks as $mark)
                            <tr>
                                <td>{{ $mark->exam->name }}</td>
                                <td>{{ $mark->exam->subject?->name }}</td>
                                <td>{{ $mark->marks_obtained }}/{{ $mark->total_marks }}</td>
                                <td><span class="badge bg-info">{{ $mark->grade }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-muted">No results.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.app>
