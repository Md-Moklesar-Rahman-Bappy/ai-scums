<x-layouts.app title="Dashboard">
    <div class="page-header">
        <div>
            <h1>Dashboard</h1>
            <p class="subtitle">Welcome back, {{ auth()->user()->name }} — here's what's happening across your institution.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('assistant.index') }}" class="btn btn-outline-primary"><i class="bi bi-robot me-1"></i> Ask AI</a>
            @hasanyrole('super_admin|institution_admin')
                <a href="{{ route('students.create') }}" class="btn btn-primary"><i class="bi bi-person-plus me-1"></i> New Student</a>
            @endhasanyrole
        </div>
    </div>

    {{-- KPI cards --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card-stat">
                <div class="stat-icon bg-brand"><i class="bi bi-people"></i></div>
                <div class="stat-label">Students</div>
                <div class="stat-value">{{ number_format($stats['students']) }}</div>
                <div class="stat-trend up"><i class="bi bi-arrow-up-right"></i> Active enrollment</div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card-stat">
                <div class="stat-icon bg-violet"><i class="bi bi-person-video3"></i></div>
                <div class="stat-label">Teachers</div>
                <div class="stat-value">{{ number_format($stats['teachers']) }}</div>
                <div class="stat-trend up"><i class="bi bi-arrow-up-right"></i> Faculty</div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card-stat">
                <div class="stat-icon bg-warning"><i class="bi bi-cash-coin"></i></div>
                <div class="stat-label">Pending / Overdue Fees</div>
                <div class="stat-value">{{ number_format($feeStatus['values'][2] + $feeStatus['values'][3]) }}</div>
                <div class="stat-trend down"><i class="bi bi-exclamation-circle"></i> Needs attention</div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card-stat">
                <div class="stat-icon bg-accent"><i class="bi bi-megaphone"></i></div>
                <div class="stat-label">Notices</div>
                <div class="stat-value">{{ number_format($stats['notices']) }}</div>
                <div class="stat-trend up"><i class="bi bi-arrow-up-right"></i> Published</div>
            </div>
        </div>
    </div>

    {{-- Charts --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-lg-8">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="fw-semibold mb-0">Attendance — last 7 days</h6>
                        <span class="badge badge-soft-success">Live</span>
                    </div>
                    <canvas id="attendanceChart" height="120"></canvas>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-4">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="fw-semibold mb-2">Result Distribution</h6>
                    <canvas id="resultChart" height="220"></canvas>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-6">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="fw-semibold mb-2">Fee Status</h6>
                    <canvas id="feeChart" height="140"></canvas>
                </div>
            </div>
        </div>

        {{-- AI Insights --}}
        <div class="col-12 col-lg-6">
            <div class="ai-insight h-100">
                <div class="ai-head"><i class="bi bi-stars"></i> AI Insights</div>
                <p class="mb-3">{{ $aiInsight }}</p>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('assistant.index') }}" class="chat-suggestion"><i class="bi bi-robot"></i> Ask the assistant</a>
                    <a href="{{ route('attendances.analytics') }}" class="chat-suggestion"><i class="bi bi-graph-up"></i> Attendance report</a>
                </div>
            </div>
        </div>
    </div>

    {{-- Activity + Quick actions --}}
    <div class="row g-3">
        <div class="col-12 col-lg-8">
            <div class="widget">
                <h6 class="mb-3">Recent Activity</h6>
                <ul class="activity-feed">
                    @forelse($recentNotices as $n)
                        <li>
                            <span class="dot bg-accent"><i class="bi bi-megaphone"></i></span>
                            <span class="meta">
                                New notice: <strong>{{ $n->title }}</strong>
                                <span class="time">{{ $n->created_at->diffForHumans() }}</span>
                            </span>
                        </li>
                    @empty
                        <li><span class="meta text-muted">No recent notices.</span></li>
                    @endforelse
                    @forelse($recentStudents as $s)
                        <li>
                            <span class="dot bg-brand"><i class="bi bi-person-plus"></i></span>
                            <span class="meta">
                                Student admitted: <strong>{{ $s->name }}</strong>@if($s->schoolClass) · {{ $s->schoolClass->name }}@endif
                                <span class="time">{{ $s->created_at->diffForHumans() }}</span>
                            </span>
                        </li>
                    @empty
                        <li><span class="meta text-muted">No recent admissions.</span></li>
                    @endforelse
                </ul>
            </div>
        </div>
        <div class="col-12 col-lg-4">
            <div class="widget">
                <h6 class="mb-3">Quick Actions</h6>
                <div class="quick-action-grid">
                    <a class="quick-action" href="{{ route('students.create') }}"><i class="bi bi-person-plus"></i>Admit</a>
                    <a class="quick-action" href="{{ route('attendances.create') }}"><i class="bi bi-calendar-check"></i>Attendance</a>
                    <a class="quick-action" href="{{ route('exams.index') }}"><i class="bi bi-file-earmark-text"></i>Exam</a>
                    <a class="quick-action" href="{{ route('fees.create') }}"><i class="bi bi-cash-coin"></i>Fee</a>
                    <a class="quick-action" href="{{ route('notices.create') }}"><i class="bi bi-megaphone"></i>Notice</a>
                    <a class="quick-action" href="{{ route('assistant.index') }}"><i class="bi bi-robot"></i>AI</a>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        (function () {
            const css = getComputedStyle(document.documentElement);
            const grid = (css.getPropertyValue('--surface-border') || '#E2E8F0').trim();
            const textMuted = (css.getPropertyValue('--text-muted') || '#64748B').trim();

            function make() {
                const at = @json($attendanceTrend);
                const rd = @json($resultDistribution);
                const fs = @json($feeStatus);

                const attendance = new Chart(document.getElementById('attendanceChart'), {
                    type: 'line',
                    data: { labels: at.labels, datasets: [
                        { label: 'Present', data: at.present, borderColor: '#4F46E5', backgroundColor: 'rgba(79,70,229,0.12)', fill: true, tension: 0.4, pointRadius: 3 },
                        { label: 'Absent', data: at.absent, borderColor: '#EF4444', backgroundColor: 'rgba(239,68,68,0.10)', fill: true, tension: 0.4, pointRadius: 3 }
                    ]},
                    options: { responsive: true, plugins: { legend: { labels: { color: textMuted } } }, scales: {
                        x: { grid: { color: grid }, ticks: { color: textMuted } },
                        y: { grid: { color: grid }, ticks: { color: textMuted }, beginAtZero: true }
                    } }
                });

                const result = new Chart(document.getElementById('resultChart'), {
                    type: 'doughnut',
                    data: { labels: rd.labels, datasets: [{ data: rd.values, backgroundColor: ['#4F46E5','#7C3AED','#06B6D4','#10B981','#F59E0B','#EF4444'], borderWidth: 0 }] },
                    options: { responsive: true, plugins: { legend: { position: 'bottom', labels: { color: textMuted } } }, cutout: '62%' }
                });

                const fee = new Chart(document.getElementById('feeChart'), {
                    type: 'bar',
                    data: { labels: fs.labels, datasets: [{ data: fs.values, backgroundColor: ['#10B981','#F59E0B','#EF4444','#7C3AED'], borderRadius: 6 }] },
                    options: { responsive: true, plugins: { legend: { display: false } }, scales: {
                        x: { grid: { display: false }, ticks: { color: textMuted } },
                        y: { grid: { color: grid }, ticks: { color: textMuted }, beginAtZero: true }
                    } }
                });

                return [attendance, result, fee];
            }
            window.ScumsCharts = { _c: null, refresh() { if (this._c) this._c.forEach(c => c.destroy()); this._c = make(); } };
            window.ScumsCharts.refresh();
        })();
    </script>
    @endpush
</x-layouts.app>
