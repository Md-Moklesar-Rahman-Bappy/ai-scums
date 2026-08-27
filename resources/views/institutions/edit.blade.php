<x-layouts.app title="Edit Institution">
    <h3 class="fw-bold mb-3">Edit Institution</h3>
    <div class="card card-stat">
        <div class="card-body">
            <form method="POST" action="{{ route('institutions.update', $institution) }}">
                @csrf @method('PUT')
                <div class="mb-3">
                    <label class="form-label">Name</label>
                    <input name="name" class="form-control" value="{{ old('name', $institution->name) }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Type</label>
                    <select name="type" class="form-select" required>
                        @foreach(['school','college','university'] as $t)
                            <option value="{{ $t }}" @selected($institution->type==$t)>{{ ucfirst($t) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input name="email" type="email" class="form-control" value="{{ old('email', $institution->email) }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Phone</label>
                    <input name="phone" class="form-control" value="{{ old('phone', $institution->phone) }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Address</label>
                    <textarea name="address" class="form-control">{{ old('address', $institution->address) }}</textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Website</label>
                    <input name="website" class="form-control" value="{{ old('website', $institution->website) }}">
                </div>
                <div class="form-check mb-3">
                    <input type="checkbox" class="form-check-input" name="is_active" id="is_active" value="1" @checked($institution->is_active)>
                    <label class="form-check-label" for="is_active">Active</label>
                </div>
                <button class="btn btn-primary">Update</button>
                <a href="{{ route('institutions.index') }}" class="btn btn-link">Cancel</a>
            </form>
        </div>
    </div>
</x-layouts.app>
