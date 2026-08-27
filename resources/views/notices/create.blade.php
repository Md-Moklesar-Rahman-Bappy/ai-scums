<x-layouts.app title="Publish Notice">
    <h3 class="fw-bold mb-3">Publish Notice</h3>
    <div class="card card-stat"><div class="card-body">
        <form method="POST" action="{{ route('notices.store') }}">
            @csrf
            <div class="mb-3"><label class="form-label">Title</label><input name="title" class="form-control" required></div>
            <div class="mb-3"><label class="form-label">Body</label><textarea name="body" class="form-control" rows="4"></textarea></div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Type</label>
                    <select name="type" class="form-select">
                        @foreach(['announcement','event','notification'] as $t)<option value="{{ $t }}">{{ ucfirst($t) }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Audience</label>
                    <select name="audience" class="form-select">
                        @foreach(['all','students','teachers','parents','admins'] as $a)<option value="{{ $a }}">{{ ucfirst($a) }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-3"><label class="form-label">Published At</label><input type="datetime-local" name="published_at" class="form-control"></div>
            </div>
            <button class="btn btn-primary">Publish</button>
            <a href="{{ route('notices.index') }}" class="btn btn-link">Cancel</a>
        </form>
    </div></div>
</x-layouts.app>
