<x-layouts.app title="Create Institution">
    <h3 class="fw-bold mb-3">New Institution</h3>
    <div class="card card-stat">
        <div class="card-body">
            <form method="POST" action="{{ route('institutions.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Name</label>
                    <input name="name" class="form-control" value="{{ old('name') }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Type</label>
                    <select name="type" class="form-select" required>
                        <option value="school">School</option>
                        <option value="college">College</option>
                        <option value="university">University</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input name="email" type="email" class="form-control" value="{{ old('email') }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Phone</label>
                    <input name="phone" class="form-control" value="{{ old('phone') }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Address</label>
                    <textarea name="address" class="form-control">{{ old('address') }}</textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Website</label>
                    <input name="website" class="form-control" value="{{ old('website') }}">
                </div>
                <div class="form-check mb-3">
                    <input type="checkbox" class="form-check-input" name="is_active" id="is_active" value="1" checked>
                    <label class="form-check-label" for="is_active">Active</label>
                </div>
                <button class="btn btn-primary">Save</button>
                <a href="{{ route('institutions.index') }}" class="btn btn-link">Cancel</a>
            </form>
        </div>
    </div>
</x-layouts.app>
