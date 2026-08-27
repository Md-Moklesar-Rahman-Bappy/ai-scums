<x-layouts.guest>
    <div class="auth-brand"><span class="logo-badge"><i class="bi bi-mortarboard-fill"></i></span> {{ config('app.name', 'AI-SCUMS') }}</div>
    <h4 class="fw-bold mb-1">Welcome back</h4>
    <p class="text-muted mb-4">Sign in to your institution workspace.</p>

    <form method="POST" action="{{ route('login') }}">
        @csrf
        <div class="form-floating mb-3">
            <input type="email" name="email" class="form-control" id="email" placeholder="name@example.com" value="{{ old('email') }}" required autofocus>
            <label for="email">Email address</label>
        </div>
        <div class="form-floating mb-3">
            <input type="password" name="password" class="form-control" id="password" placeholder="Password" required>
            <label for="password">Password</label>
        </div>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="form-check">
                <input type="checkbox" class="form-check-input" name="remember" id="remember">
                <label class="form-check-label" for="remember">Remember me</label>
            </div>
            <a href="{{ route('password.request') }}" class="small text-decoration-none">Forgot password?</a>
        </div>
        <button class="btn btn-primary w-100 btn-lg">Sign In</button>
        <div class="text-center mt-3 small text-muted">
            New here? <a href="{{ route('register') }}">Create an institution</a>
        </div>
    </form>
</x-layouts.guest>
