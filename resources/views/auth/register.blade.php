<x-layouts.guest>
    <div class="auth-brand"><span class="logo-badge"><i class="bi bi-mortarboard-fill"></i></span> {{ config('app.name', 'AI-SCUMS') }}</div>
    <h4 class="fw-bold mb-1">Create your Institution</h4>
    <p class="text-muted mb-4">Onboarding provisions a new tenant and its first administrator.</p>

    <form method="POST" action="{{ route('register') }}">
        @csrf
        <div class="form-floating mb-3">
            <input type="text" name="institution_name" class="form-control" id="institution_name" placeholder="Institution name" value="{{ old('institution_name') }}" required>
            <label for="institution_name">Institution Name</label>
        </div>
        <div class="form-floating mb-3">
            <select name="institution_type" class="form-select" id="institution_type" required>
                <option value="" selected disabled>Select type</option>
                <option value="school" @selected(old('institution_type')=='school')>School</option>
                <option value="college" @selected(old('institution_type')=='college')>College</option>
                <option value="university" @selected(old('institution_type')=='university')>University</option>
            </select>
            <label for="institution_type">Institution Type</label>
        </div>
        <div class="form-floating mb-3">
            <input type="text" name="admin_name" class="form-control" id="admin_name" placeholder="Admin name" value="{{ old('admin_name') }}" required>
            <label for="admin_name">Admin Name</label>
        </div>
        <div class="form-floating mb-3">
            <input type="email" name="email" class="form-control" id="email" placeholder="name@example.com" value="{{ old('email') }}" required>
            <label for="email">Email</label>
        </div>
        <div class="form-floating mb-3">
            <input type="text" name="phone" class="form-control" id="phone" placeholder="Phone" value="{{ old('phone') }}">
            <label for="phone">Phone</label>
        </div>
        <div class="form-floating mb-3">
            <input type="password" name="password" class="form-control" id="password" placeholder="Password" required>
            <label for="password">Password</label>
        </div>
        <div class="form-floating mb-4">
            <input type="password" name="password_confirmation" class="form-control" id="password_confirmation" placeholder="Confirm" required>
            <label for="password_confirmation">Confirm Password</label>
        </div>
        <button class="btn btn-primary w-100 btn-lg">Create Institution</button>
        <div class="text-center mt-3 small text-muted">
            Already registered? <a href="{{ route('login') }}">Sign in</a>
        </div>
    </form>
</x-layouts.guest>
