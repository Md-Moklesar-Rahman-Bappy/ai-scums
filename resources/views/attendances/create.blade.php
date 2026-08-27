<x-layouts.app title="Mark Attendance">
    <h3 class="fw-bold mb-3">Mark Attendance</h3>
    <div class="card card-stat"><div class="card-body">
        <form method="POST" action="{{ route('attendances.store') }}" id="attForm">
            @csrf
            <div class="row g-2 mb-3">
                <div class="col-md-4">
                    <label class="form-label">Date</label>
                    <input type="date" name="date" class="form-control" value="{{ now()->toDateString() }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Section</label>
                    <select name="section_id" class="form-select select2">
                        <option value="">Select</option>
                        @foreach(\App\Models\Section::all() as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Subject (optional)</label>
                    <select name="subject_id" class="form-select select2">
                        <option value="">None</option>
                        @foreach(\App\Models\Subject::all() as $sub)<option value="{{ $sub->id }}">{{ $sub->name }}</option>@endforeach
                    </select>
                </div>
            </div>
            <table class="table table-sm" id="dataTable">
                <thead><tr><th>Student</th><th>Status</th></tr></thead>
                <tbody>
                    @foreach($students as $student)
                        <tr>
                            <td>{{ $student->admission_no }}</td>
                            <td>
                                <select name="records[{{ $loop->index }}][student_id]" class="d-none">
                                    <option value="{{ $student->id }}" selected></option>
                                </select>
                                <select name="records[{{ $loop->index }}][status]" class="form-select form-select-sm">
                                    <option value="present">Present</option>
                                    <option value="absent">Absent</option>
                                    <option value="late">Late</option>
                                    <option value="half_day">Half Day</option>
                                </select>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <button class="btn btn-primary">Save Attendance</button>
        </form>
    </div></div>
    @push('scripts')<script>$(function(){ $('.select2').select2(); });</script>@endpush
</x-layouts.app>
