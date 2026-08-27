<x-layouts.app title="Assign Fee">
    <h3 class="fw-bold mb-3">Assign Fee</h3>
    <div class="card card-stat"><div class="card-body">
        <form method="POST" action="{{ route('fees.store') }}">
            @csrf
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Student</label>
                    <select name="student_id" class="form-select select2" required>
                        <option value="">Select</option>
                        @foreach(\App\Models\Student::all() as $s)<option value="{{ $s->id }}">{{ $s->admission_no }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Fee Type</label>
                    <select name="fee_type_id" class="form-select select2">
                        <option value="">None</option>
                        @foreach(\App\Models\FeeType::all() as $ft)<option value="{{ $ft->id }}">{{ $ft->name }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3"><label class="form-label">Amount</label><input type="number" step="0.01" name="amount" class="form-control" required></div>
                <div class="col-md-6 mb-3"><label class="form-label">Due Date</label><input type="date" name="due_date" class="form-control"></div>
            </div>
            <button class="btn btn-primary">Assign</button>
            <a href="{{ route('fees.index') }}" class="btn btn-link">Cancel</a>
        </form>
    </div></div>
    @push('scripts')<script>$(function(){ $('.select2').select2(); });</script>@endpush
</x-layouts.app>
