<x-layouts.guest>
    <div class="card shadow-sm">
        <div class="card-body p-4">
            <h4 class="card-title mb-3">Sign In</h4>
            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" name="remember" id="remember">
                    <label class="form-check-label" for="remember">Remember me</label>
                </div>
                <button class="btn btn-primary w-100">Login</button>
                <div class="d-flex justify-content-between mt-3 small">
                    <a href="{{ route('password.request') }}">Forgot password?</a>
                    <a href="{{ route('register') }}">Create institution</a>
                </div>
            </form>
        </div>
    </div>
</x-layouts.guest>
