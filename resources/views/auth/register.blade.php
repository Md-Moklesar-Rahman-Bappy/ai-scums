<x-layouts.guest>
    <div class="card shadow-sm">
        <div class="card-body p-4">
            <h4 class="card-title mb-3">Create your Institution</h4>
            <p class="text-muted small">Onboarding creates a new tenant and its first administrator.</p>
            <form method="POST" action="{{ route('register') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Institution Name</label>
                    <input type="text" name="institution_name" class="form-control" value="{{ old('institution_name') }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Institution Type</label>
                    <select name="institution_type" class="form-select" required>
                        <option value="">Select type</option>
                        <option value="school" @selected(old('institution_type')=='school')>School</option>
                        <option value="college" @selected(old('institution_type')=='college')>College</option>
                        <option value="university" @selected(old('institution_type')=='university')>University</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Admin Name</label>
                    <input type="text" name="admin_name" class="form-control" value="{{ old('admin_name') }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" name="password_confirmation" class="form-control" required>
                </div>
                <button class="btn btn-primary w-100">Create Institution</button>
                <div class="text-center mt-3 small">
                    <a href="{{ route('login') }}">Already registered? Sign in</a>
                </div>
            </form>
        </div>
    </div>
</x-layouts.guest>
