<x-layouts.app title="Edit Student">
    <h3 class="fw-bold mb-3">Edit Student: {{ $student->admission_no }}</h3>
    <div class="card card-stat"><div class="card-body">
        <form method="POST" action="{{ route('students.update', $student) }}">
            @csrf @method('PUT')
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Class</label>
                    <select name="class_id" class="form-select select2">
                        <option value="">Select</option>
                        @foreach(\App\Models\SchoolClass::all() as $c)<option value="{{ $c->id }}" @selected(old('class_id', $student->class_id)==$c->id)>{{ $c->name }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Section</label>
                    <select name="section_id" class="form-select select2">
                        <option value="">Select</option>
                        @foreach(\App\Models\Section::all() as $s)<option value="{{ $s->id }}" @selected(old('section_id', $student->section_id)==$s->id)>{{ $s->name }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Program</label>
                    <select name="program_id" class="form-select select2">
                        <option value="">Select</option>
                        @foreach(\App\Models\Program::all() as $p)<option value="{{ $p->id }}" @selected(old('program_id', $student->program_id)==$p->id)>{{ $p->name }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Semester</label>
                    <select name="semester_id" class="form-select select2">
                        <option value="">Select</option>
                        @foreach(\App\Models\Semester::all() as $sem)<option value="{{ $sem->id }}" @selected(old('semester_id', $student->semester_id)==$sem->id)>{{ $sem->name }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Gender</label>
                    <input name="gender" class="form-control" value="{{ old('gender', $student->gender) }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Blood Group</label>
                    <input name="blood_group" class="form-control" value="{{ old('blood_group', $student->blood_group) }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Guardian Name</label>
                    <input name="guardian_name" class="form-control" value="{{ old('guardian_name', $student->guardian_name) }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Guardian Phone</label>
                    <input name="guardian_phone" class="form-control" value="{{ old('guardian_phone', $student->guardian_phone) }}">
                </div>
                <div class="col-12 mb-3">
                    <label class="form-label">Address</label>
                    <textarea name="address" class="form-control">{{ old('address', $student->address) }}</textarea>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        @foreach(['active','inactive','graduated','transferred'] as $st)<option value="{{ $st }}" @selected(old('status', $student->status)==$st)>{{ ucfirst($st) }}</option>@endforeach
                    </select>
                </div>
            </div>
            <button class="btn btn-primary">Update</button>
            <a href="{{ route('students.index') }}" class="btn btn-link">Cancel</a>
        </form>

        @can('students.edit')
        <hr>
        <h6 class="fw-semibold">Promote Student</h6>
        <form method="POST" action="{{ route('students.promote', $student) }}" class="row g-2">
            @csrf
            <div class="col-md-5">
                <select name="class_id" class="form-select select2">
                    <option value="">New Class (optional)</option>
                    @foreach(\App\Models\SchoolClass::all() as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-5">
                <select name="semester_id" class="form-select select2">
                    <option value="">New Semester (optional)</option>
                    @foreach(\App\Models\Semester::all() as $sem)<option value="{{ $sem->id }}">{{ $sem->name }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-success w-100">Promote</button>
            </div>
        </form>
        @endcan
    </div></div>

    @push('scripts')
    <script>$(function(){ $('.select2').select2(); });</script>
    @endpush
</x-layouts.app>
