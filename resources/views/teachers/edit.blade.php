<x-layouts.app title="Edit Teacher">
    <h3 class="fw-bold mb-3">Edit Teacher: {{ $teacher->employee_id }}</h3>
    <div class="card card-stat"><div class="card-body">
        <form method="POST" action="{{ route('teachers.update', $teacher) }}">
            @csrf @method('PUT')
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Employee ID</label>
                    <input name="employee_id" class="form-control" value="{{ old('employee_id', $teacher->employee_id) }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Department</label>
                    <select name="department_id" class="form-select select2">
                        <option value="">Select</option>
                        @foreach(\App\Models\Department::all() as $d)<option value="{{ $d->id }}" @selected(old('department_id', $teacher->department_id)==$d->id)>{{ $d->name }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Designation</label>
                    <input name="designation" class="form-control" value="{{ old('designation', $teacher->designation) }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Qualification</label>
                    <input name="qualification" class="form-control" value="{{ old('qualification', $teacher->qualification) }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        @foreach(['active','inactive'] as $st)<option value="{{ $st }}" @selected(old('status', $teacher->status)==$st)>{{ ucfirst($st) }}</option>@endforeach
                    </select>
                </div>
                <div class="col-12 mb-3">
                    <label class="form-label">Subject Allocation</label>
                    <select name="subject_ids[]" class="form-select select2" multiple>
                        @foreach(\App\Models\Subject::all() as $s)<option value="{{ $s->id }}" @selected(in_array($s->id, old('subject_ids', $teacher->subjects->pluck('id')->toArray())))>{{ $s->name }}</option>@endforeach
                    </select>
                </div>
            </div>
            <button class="btn btn-primary">Update</button>
            <a href="{{ route('teachers.index') }}" class="btn btn-link">Cancel</a>
        </form>
    </div></div>
    @push('scripts')<script>$(function(){ $('.select2').select2(); });</script>@endpush
</x-layouts.app>
