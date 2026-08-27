<x-layouts.app title="New Exam">
    <h3 class="fw-bold mb-3">New Exam</h3>
    <div class="card card-stat"><div class="card-body">
        <form method="POST" action="{{ route('exams.store') }}">
            @csrf
            <div class="row">
                <div class="col-md-6 mb-3"><label class="form-label">Name</label><input name="name" class="form-control" required></div>
                <div class="col-md-6 mb-3"><label class="form-label">Type</label><input name="exam_type" class="form-control"></div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Subject</label>
                    <select name="subject_id" class="form-select select2"><option value="">None</option>@foreach(\App\Models\Subject::all() as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach</select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Section</label>
                    <select name="section_id" class="form-select select2"><option value="">None</option>@foreach(\App\Models\Section::all() as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach</select>
                </div>
                <div class="col-md-4 mb-3"><label class="form-label">Exam Date</label><input type="date" name="exam_date" class="form-control"></div>
                <div class="col-md-6 mb-3"><label class="form-label">Total Marks</label><input type="number" name="total_marks" class="form-control" value="100"></div>
                <div class="col-md-6 mb-3"><label class="form-label">Pass Marks</label><input type="number" name="pass_marks" class="form-control" value="33"></div>
            </div>
            <button class="btn btn-primary">Create</button>
            <a href="{{ route('exams.index') }}" class="btn btn-link">Cancel</a>
        </form>
    </div></div>
    @push('scripts')<script>$(function(){ $('.select2').select2(); });</script>@endpush
</x-layouts.app>
