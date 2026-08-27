<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name', 'IEMS') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet">
    <style>
        body { background:#f5f6fa; }
        .sidebar { min-height:100vh; background:#1e293b; color:#cbd5e1; }
        .sidebar a { color:#cbd5e1; text-decoration:none; display:block; padding:.6rem 1rem; border-radius:.4rem; }
        .sidebar a:hover, .sidebar a.active { background:#334155; color:#fff; }
        .sidebar .brand { color:#fff; font-weight:700; padding:1rem; }
        .card-stat { border:none; border-radius:1rem; box-shadow:0 .25rem .75rem rgba(0,0,0,.05); }
    </style>
    @stack('styles')
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <nav class="col-md-3 col-lg-2 sidebar py-2 d-none d-md-block">
            <div class="brand"><i class="bi bi-mortarboard-fill text-info"></i> {{ config('app.name') }}</div>
            <div class="small px-3 pb-2 text-info">{{ auth()->user()->institution?->name ?? 'Platform Admin' }}</div>

            <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}"><i class="bi bi-grid-1x2 me-2"></i>Dashboard</a>

            @role('super_admin')
                <a href="{{ route('institutions.index') }}"><i class="bi bi-building me-2"></i>Institutions</a>
            @endrole

            @hasanyrole('super_admin|institution_admin')
                <a href="{{ route('students.index') }}"><i class="bi bi-people me-2"></i>Students</a>
                <a href="{{ route('teachers.index') }}"><i class="bi bi-person-video3 me-2"></i>Teachers</a>
                <a href="{{ route('fees.index') }}"><i class="bi bi-cash-coin me-2"></i>Fees</a>
                <a href="{{ route('notices.index') }}"><i class="bi bi-megaphone me-2"></i>Notices</a>
            @endhasanyrole

            @hasanyrole('super_admin|institution_admin|teacher')
                <a href="{{ route('attendances.index') }}"><i class="bi bi-calendar-check me-2"></i>Attendance</a>
                <a href="{{ route('exams.index') }}"><i class="bi bi-file-earmark-text me-2"></i>Examinations</a>
                <a href="{{ route('routines.index') }}"><i class="bi bi-calendar-week me-2"></i>Routines</a>
            @endhasanyrole

            <a href="{{ route('assistant.index') }}"><i class="bi bi-robot me-2"></i>AI Assistant</a>

            <hr class="border-secondary">
            <a href="{{ route('logout') }}" onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                <i class="bi bi-box-arrow-right me-2"></i>Logout
            </a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
        </nav>

        <main class="col-md-9 col-lg-10 px-4 py-3">
            @if(auth()->user()->isSuperAdmin())
                <div class="alert alert-warning small py-2">
                    <strong>Super Admin:</strong> switch institution
                    <select id="tenant-switch" class="form-select form-select-sm d-inline w-auto ms-2">
                        <option value="">All institutions</option>
                        @foreach(\App\Models\Institution::all() as $inst)
                            <option value="{{ $inst->id }}" @selected(session('active_institution_id')==$inst->id)>{{ $inst->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            @include('partials.alerts')
            {{ $slot }}
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/axios@1.7.2/dist/axios.min.js"></script>
<script>
    const CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    axios.defaults.headers.common['X-CSRF-TOKEN'] = CSRF;
    @if(auth()->user()->isSuperAdmin())
    document.getElementById('tenant-switch')?.addEventListener('change', function() {
        axios.post('{{ route('tenant.switch') }}', { institution_id: this.value }).then(() => location.reload());
    });
    @endif
</script>
@stack('scripts')
</body>
</html>
