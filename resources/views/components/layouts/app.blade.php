<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="color-scheme" content="light dark">
    <title>{{ $title ?? config('app.name', 'AI-SCUMS') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    <script>
        (function () {
            try {
                var t = localStorage.getItem('scums-theme');
                if (!t) t = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
                document.documentElement.setAttribute('data-bs-theme', t);
            } catch (e) {}
        })();
    </script>
    @stack('styles')
</head>
<body>
<div class="app-shell">

    <aside class="app-sidebar" id="appSidebar" aria-label="Primary navigation">
        <div class="app-sidebar__brand">
            <span class="logo-badge"><i class="bi bi-mortarboard-fill"></i></span>
            <span class="label">{{ config('app.name', 'AI-SCUMS') }}</span>
        </div>
        <nav class="app-sidebar__nav">
            <a class="app-sidebar__link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                <i class="bi bi-grid-1x2"></i><span class="label">Dashboard</span>
            </a>

            @role('super_admin')
            <div class="app-sidebar__section">Administration</div>
            <a class="app-sidebar__link {{ request()->routeIs('institutions.*') ? 'active' : '' }}" href="{{ route('institutions.index') }}">
                <i class="bi bi-building"></i><span class="label">Institutions</span>
            </a>
            @endrole

            @hasanyrole('super_admin|institution_admin')
            <div class="app-sidebar__section">Academic</div>
            <a class="app-sidebar__link {{ request()->routeIs('students.*') ? 'active' : '' }}" href="{{ route('students.index') }}">
                <i class="bi bi-people"></i><span class="label">Students</span>
            </a>
            <a class="app-sidebar__link {{ request()->routeIs('teachers.*') ? 'active' : '' }}" href="{{ route('teachers.index') }}">
                <i class="bi bi-person-video3"></i><span class="label">Teachers</span>
            </a>
            <a class="app-sidebar__link {{ request()->routeIs('fees.*') ? 'active' : '' }}" href="{{ route('fees.index') }}">
                <i class="bi bi-cash-coin"></i><span class="label">Fees</span>
            </a>
            <a class="app-sidebar__link {{ request()->routeIs('notices.*') ? 'active' : '' }}" href="{{ route('notices.index') }}">
                <i class="bi bi-megaphone"></i><span class="label">Notices</span>
            </a>
            @endhasanyrole

            @hasanyrole('super_admin|institution_admin|teacher')
            <div class="app-sidebar__section">Operations</div>
            <a class="app-sidebar__link {{ request()->routeIs('attendances.*') ? 'active' : '' }}" href="{{ route('attendances.index') }}">
                <i class="bi bi-calendar-check"></i><span class="label">Attendance</span>
            </a>
            <a class="app-sidebar__link {{ request()->routeIs('exams.*') ? 'active' : '' }}" href="{{ route('exams.index') }}">
                <i class="bi bi-file-earmark-text"></i><span class="label">Examinations</span>
            </a>
            <a class="app-sidebar__link {{ request()->routeIs('routines.*') ? 'active' : '' }}" href="{{ route('routines.index') }}">
                <i class="bi bi-calendar-week"></i><span class="label">Routines</span>
            </a>
            @endhasanyrole

            <div class="app-sidebar__section">Tools</div>
            <a class="app-sidebar__link {{ request()->routeIs('assistant.*') ? 'active' : '' }}" href="{{ route('assistant.index') }}">
                <i class="bi bi-robot"></i><span class="label">AI Assistant</span>
            </a>

            <hr class="border-secondary opacity-25 my-2">
            <a class="app-sidebar__link" href="{{ route('logout') }}" onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                <i class="bi bi-box-arrow-right"></i><span class="label">Logout</span>
            </a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
        </nav>
    </aside>
    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

    <div class="app-content">
        <header class="app-topbar">
            <button class="icon-btn mobile-only" id="mobileMenuBtn" aria-label="Open menu"><i class="bi bi-list"></i></button>
            <button class="icon-btn d-none d-lg-inline-flex" id="sidebarToggle" aria-label="Toggle sidebar"><i class="bi bi-sidebar"></i></button>

            <div class="d-none d-md-flex search-trigger" id="searchTrigger" role="button" tabindex="0" aria-label="Search (Ctrl+K)">
                <i class="bi bi-search"></i><span>Search…</span><kbd>⌘K</kbd>
            </div>

            <div class="ms-auto d-flex align-items-center gap-2">
                @if(auth()->user()->isSuperAdmin())
                    <select id="tenantSwitch" class="form-select form-select-sm" style="width:auto" aria-label="Switch institution">
                        <option value="">All institutions</option>
                        @foreach(\App\Models\Institution::orderBy('name')->limit(200)->get() as $inst)
                            <option value="{{ $inst->id }}" @selected(session('active_institution_id') == $inst->id)>{{ $inst->name }}</option>
                        @endforeach
                    </select>
                @endif

                <button class="icon-btn" id="themeToggle" aria-label="Toggle theme"><i class="bi bi-moon-stars"></i></button>

                <div class="dropdown">
                    <button class="btn p-0 border-0 d-flex align-items-center gap-2" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                        <span class="d-none d-sm-inline text-start lh-1">
                            <small class="d-block text-strong fw-semibold">{{ auth()->user()->name }}</small>
                            <small class="text-muted">{{ auth()->user()->roles->first()?->name ?? 'User' }}</small>
                        </span>
                        <i class="bi bi-chevron-down text-muted small"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                        <li class="dropdown-item-text small text-muted px-3">{{ auth()->user()->email }}</li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="{{ route('assistant.index') }}"><i class="bi bi-robot me-2"></i>AI Assistant</a></li>
                        <li>
                            <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                                <i class="bi bi-box-arrow-right me-2"></i>Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <main class="app-main">
            @if(isset($breadcrumbs) && count($breadcrumbs))
                <nav class="app-breadcrumb" aria-label="Breadcrumb">
                    @foreach($breadcrumbs as $bc)
                        @if(!$loop->last && isset($bc['url']))
                            <a href="{{ $bc['url'] }}">{{ $bc['label'] }}</a><span class="sep">/</span>
                        @else
                            <span>{{ $bc['label'] }}</span>
                        @endif
                    @endforeach
                </nav>
            @endif

            @include('partials.alerts')

            {{ $slot }}
        </main>
    </div>
</div>

<div class="cmdk-backdrop" id="cmdkBackdrop"></div>
<div class="cmdk" id="cmdk" role="dialog" aria-label="Command palette">
    <input type="text" id="cmdkInput" placeholder="Search pages, actions…" aria-label="Command search">
    <div class="cmdk-results" id="cmdkResults"></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/axios@1.7.2/dist/axios.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script src="{{ asset('js/app.js') }}"></script>
<script>
    window.SCUMS_TENANT_SWITCH_URL = '{{ route('tenant.switch') }}';
    window.SCUMS_COMMANDS = [
        { label: 'Dashboard', url: '{{ route('dashboard') }}', icon: 'bi bi-grid-1x2', keywords: 'home overview' },
        { label: 'Students', url: '{{ route('students.index') }}', icon: 'bi bi-people', keywords: 'admission enroll' },
        { label: 'Teachers', url: '{{ route('teachers.index') }}', icon: 'bi bi-person-video3', keywords: 'staff faculty' },
        { label: 'Attendance', url: '{{ route('attendances.index') }}', icon: 'bi bi-calendar-check', keywords: 'present absent' },
        { label: 'Examinations', url: '{{ route('exams.index') }}', icon: 'bi bi-file-earmark-text', keywords: 'exam marks result' },
        { label: 'Routines', url: '{{ route('routines.index') }}', icon: 'bi bi-calendar-week', keywords: 'timetable schedule' },
        { label: 'Fees', url: '{{ route('fees.index') }}', icon: 'bi bi-cash-coin', keywords: 'payment invoice' },
        { label: 'Notices', url: '{{ route('notices.index') }}', icon: 'bi bi-megaphone', keywords: 'announcement message' },
        { label: 'Institutions', url: '{{ route('institutions.index') }}', icon: 'bi bi-building', keywords: 'tenant school college' },
        { label: 'AI Assistant', url: '{{ route('assistant.index') }}', icon: 'bi bi-robot', keywords: 'chat gpt help' },
        { label: 'New Student', url: '{{ route('students.create') }}', icon: 'bi bi-person-plus', keywords: 'admit add' },
        { label: 'New Teacher', url: '{{ route('teachers.create') }}', icon: 'bi bi-person-plus', keywords: 'add staff' },
        { label: 'New Notice', url: '{{ route('notices.create') }}', icon: 'bi bi-megaphone', keywords: 'post announcement' },
        { label: 'Mark Attendance', url: '{{ route('attendances.create') }}', icon: 'bi bi-calendar-plus', keywords: 'take attendance' },
        { label: 'Attendance Analytics', url: '{{ route('attendances.analytics') }}', icon: 'bi bi-graph-up', keywords: 'report chart' }
    ];
</script>
@stack('scripts')
</body>
</html>
