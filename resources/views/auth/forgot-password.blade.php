<x-layouts.guest>
    <div class="card shadow-sm">
        <div class="card-body p-4">
            <h4 class="card-title mb-3">Forgot Password</h4>
            <form method="POST" action="{{ route('password.email') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                </div>
                <button class="btn btn-primary w-100">Send Reset Link</button>
                <div class="text-center mt-3 small">
                    <a href="{{ route('login') }}">Back to login</a>
                </div>
            </form>
        </div>
    </div>
</x-layouts.guest>
