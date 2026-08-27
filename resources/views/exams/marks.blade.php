<x-layouts.app title="Enter Marks">
    <h3 class="fw-bold mb-3">Enter Marks: {{ $exam->name }}</h3>
    <div class="card card-stat"><div class="card-body">
        <form method="POST" action="{{ route('exams.storeMarks', $exam) }}">
            @csrf
            <table class="table table-sm">
                <thead><tr><th>Student</th><th>Marks (out of {{ $exam->total_marks }})</th></tr></thead>
                <tbody>
                    @foreach($students as $student)
                        <tr>
                            <td>{{ $student->admission_no }}</td>
                            <td>
                                <input type="hidden" name="marks[{{ $loop->index }}][student_id]" value="{{ $student->id }}">
                                <input type="number" step="0.01" name="marks[{{ $loop->index }}][marks_obtained]" class="form-control form-control-sm" placeholder="0">
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <button class="btn btn-primary">Save Marks</button>
        </form>
    </div></div>
</x-layouts.app>
