<x-layouts.guest>
    <div class="card shadow-sm">
        <div class="card-body p-4 text-center">
            @if ($verified)
                <h4 class="card-title mb-3">Email Verified</h4>
                <p class="text-muted">Your email address has already been verified. You can continue to the dashboard.</p>
                <a href="{{ route('dashboard') }}" class="btn btn-primary w-100">Go to Dashboard</a>
            @else
                <h4 class="card-title mb-3">Verify Your Email</h4>
                <p class="text-muted">
                    Thanks for signing up! Before you can access the management modules, please
                    verify your email address by clicking the link we just emailed you.
                </p>
                <p class="text-muted">If you didn't receive the email, we can send another.</p>

                @if (session('success'))
                    <div class="alert alert-success py-2">{{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger py-2">{{ session('error') }}</div>
                @endif

                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <button type="submit" class="btn btn-primary w-100">Resend Verification Email</button>
                </form>

                <form method="POST" action="{{ route('logout') }}" class="mt-2">
                    @csrf
                    <button type="submit" class="btn btn-link btn-sm text-muted">Log Out</button>
                </form>
            @endif
        </div>
    </div>
</x-layouts.guest>
