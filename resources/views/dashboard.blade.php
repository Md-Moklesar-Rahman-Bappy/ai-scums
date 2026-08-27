<x-layouts.app title="Dashboard">
    <h3 class="fw-bold mb-4">Dashboard</h3>

    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="card card-stat p-3">
                <div class="text-muted small">Students</div>
                <div class="fs-3 fw-bold">{{ $stats['students'] }}</div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card card-stat p-3">
                <div class="text-muted small">Teachers</div>
                <div class="fs-3 fw-bold">{{ $stats['teachers'] }}</div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card card-stat p-3">
                <div class="text-muted small">Notices</div>
                <div class="fs-3 fw-bold">{{ $stats['notices'] }}</div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12 col-lg-6">
            <div class="card card-stat p-3">
                <h6 class="fw-semibold">Attendance (last 7 days)</h6>
                <canvas id="attendanceChart" height="120"></canvas>
            </div>
        </div>
        <div class="col-12 col-lg-6">
            <div class="card card-stat p-3">
                <h6 class="fw-semibold">Result Distribution</h6>
                <canvas id="resultChart" height="120"></canvas>
            </div>
        </div>
        <div class="col-12">
            <div class="card card-stat p-3">
                <h6 class="fw-semibold">Fee Status</h6>
                <canvas id="feeChart" height="90"></canvas>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        new Chart(document.getElementById('attendanceChart'), {
            type: 'line',
            data: { labels: @json($attendanceTrend['labels']),
                datasets: [
                    { label: 'Present', data: @json($attendanceTrend['present']), borderColor: '#198754', tension: .3 },
                    { label: 'Absent', data: @json($attendanceTrend['absent']), borderColor: '#dc3545', tension: .3 }
                ]}
        });
        new Chart(document.getElementById('resultChart'), {
            type: 'bar',
            data: { labels: @json($resultDistribution['labels']),
                datasets: [{ label: 'Students', data: @json($resultDistribution['values']), backgroundColor: '#0d6efd' }] }
        });
        new Chart(document.getElementById('feeChart'), {
            type: 'doughnut',
            data: { labels: @json($feeStatus['labels']),
                datasets: [{ data: @json($feeStatus['values']), backgroundColor: ['#198754','#ffc107','#0dcaf0','#dc3545'] }] }
        });
    </script>
    @endpush
</x-layouts.app>
