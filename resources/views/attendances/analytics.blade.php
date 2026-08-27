<x-layouts.app title="Attendance Analytics">
    <h3 class="fw-bold mb-3">Attendance Analytics</h3>
    <div class="card card-stat p-3">
        <canvas id="attChart" height="120"></canvas>
    </div>
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        new Chart(document.getElementById('attChart'), {
            type: 'bar',
            data: { labels: @json($labels),
                datasets: [{ label: 'Attendance %', data: @json($values), backgroundColor: '#0d6efd' }] }
        });
    </script>
    @endpush
</x-layouts.app>
