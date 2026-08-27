<x-layouts.app title="Admit Student">
    <h3 class="fw-bold mb-3">Admit Student</h3>
    <div class="card card-stat"><div class="card-body">
        <form method="POST" action="{{ route('students.store') }}">
            @csrf
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Admission No (auto if blank)</label>
                    <input name="admission_no" class="form-control" value="{{ old('admission_no') }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Roll No</label>
                    <input name="roll_no" class="form-control" value="{{ old('roll_no') }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Class</label>
                    <select name="class_id" class="form-select select2">
                        <option value="">Select</option>
                        @foreach(\App\Models\SchoolClass::all() as $c)<option value="{{ $c->id }}" @selected(old('class_id')==$c->id)>{{ $c->name }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Section</label>
                    <select name="section_id" class="form-select select2">
                        <option value="">Select</option>
                        @foreach(\App\Models\Section::all() as $s)<option value="{{ $s->id }}" @selected(old('section_id')==$s->id)>{{ $s->name }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Program (College/Uni)</label>
                    <select name="program_id" class="form-select select2">
                        <option value="">Select</option>
                        @foreach(\App\Models\Program::all() as $p)<option value="{{ $p->id }}" @selected(old('program_id')==$p->id)>{{ $p->name }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Semester (University)</label>
                    <select name="semester_id" class="form-select select2">
                        <option value="">Select</option>
                        @foreach(\App\Models\Semester::all() as $sem)<option value="{{ $sem->id }}" @selected(old('semester_id')==$sem->id)>{{ $sem->name }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Gender</label>
                    <input name="gender" class="form-control" value="{{ old('gender') }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Date of Birth</label>
                    <input type="date" name="date_of_birth" class="form-control" value="{{ old('date_of_birth') }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Blood Group</label>
                    <input name="blood_group" class="form-control" value="{{ old('blood_group') }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Guardian Name</label>
                    <input name="guardian_name" class="form-control" value="{{ old('guardian_name') }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Guardian Phone</label>
                    <input name="guardian_phone" class="form-control" value="{{ old('guardian_phone') }}">
                </div>
                <div class="col-12 mb-3">
                    <label class="form-label">Address</label>
                    <textarea name="address" class="form-control">{{ old('address') }}</textarea>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        @foreach(['active','inactive','graduated','transferred'] as $st)<option value="{{ $st }}" @selected(old('status','active')==$st)>{{ ucfirst($st) }}</option>@endforeach
                    </select>
                </div>
            </div>
            <button class="btn btn-primary">Admit</button>
            <a href="{{ route('students.index') }}" class="btn btn-link">Cancel</a>
        </form>
    </div></div>

    @push('scripts')
    <script>$(function(){ $('.select2').select2(); });</script>
    @endpush
</x-layouts.app>
