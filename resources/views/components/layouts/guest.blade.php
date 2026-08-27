<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name', 'AI-SCUMS') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <script>
        (function () { try { var t = localStorage.getItem('scums-theme'); if (!t) t = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'; document.documentElement.setAttribute('data-bs-theme', t); } catch (e) {} })();
    </script>
    @stack('styles')
</head>
<body>
    <div class="auth-wrap">
        <div class="auth-hero">
            <div>
                <div class="d-flex align-items-center gap-2 mb-4" style="color:#fff">
                    <span class="logo-badge" style="width:44px;height:44px;border-radius:12px;display:grid;place-items:center;background:rgba(255,255,255,.18)"><i class="bi bi-mortarboard-fill fs-4"></i></span>
                    <span class="fw-bold fs-4">{{ config('app.name', 'AI-SCUMS') }}</span>
                </div>
                <h1 class="fw-bold mb-3" style="font-size:2.4rem;line-height:1.1">AI-Powered School, College &amp; University Management</h1>
                <p class="mb-4" style="color:rgba(255,255,255,.85);max-width:42ch">Admissions, attendance, exams, fees, and an intelligent assistant — all in one elegant, secure platform.</p>
                <ul class="list-unstyled mb-0" style="color:rgba(255,255,255,.9)">
                    <li class="mb-2"><i class="bi bi-check-circle me-2"></i> Multi-role RBAC &amp; tenant isolation</li>
                    <li class="mb-2"><i class="bi bi-check-circle me-2"></i> Real-time analytics &amp; insights</li>
                    <li class="mb-2"><i class="bi bi-check-circle me-2"></i> Conversational AI assistant</li>
                </ul>
            </div>
            <div class="small" style="color:rgba(255,255,255,.7)">© {{ date('Y') }} AI-SCUMS. All rights reserved.</div>
        </div>
        <div class="auth-card">
            <div class="auth-form">
                @include('partials.alerts')
                {{ $slot }}
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
