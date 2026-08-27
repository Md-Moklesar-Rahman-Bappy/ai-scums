<x-layouts.app title="Edit Notice">
    <h3 class="fw-bold mb-3">Edit Notice</h3>
    <div class="card card-stat"><div class="card-body">
        <form method="POST" action="{{ route('notices.update', $notice) }}">
            @csrf @method('PUT')
            <div class="mb-3"><label class="form-label">Title</label><input name="title" class="form-control" value="{{ old('title', $notice->title) }}" required></div>
            <div class="mb-3"><label class="form-label">Body</label><textarea name="body" class="form-control" rows="4">{{ old('body', $notice->body) }}</textarea></div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Type</label>
                    <select name="type" class="form-select">
                        @foreach(['announcement','event','notification'] as $t)<option value="{{ $t }}" @selected(old('type', $notice->type)==$t)>{{ ucfirst($t) }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Audience</label>
                    <select name="audience" class="form-select">
                        @foreach(['all','students','teachers','parents','admins'] as $a)<option value="{{ $a }}" @selected(old('audience', $notice->audience)==$a)>{{ ucfirst($a) }}</option>@endforeach
                    </select>
                </div>
            </div>
            <button class="btn btn-primary">Update</button>
            <a href="{{ route('notices.index') }}" class="btn btn-link">Cancel</a>
        </form>
    </div></div>
</x-layouts.app>
