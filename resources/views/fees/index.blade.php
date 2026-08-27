<x-layouts.app title="Fees">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="fw-bold mb-0">Fee Management</h3>
        @can('fees.manage')
            <a href="{{ route('fees.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Assign Fee</a>
        @endcan
    </div>
    <div class="row g-2 mb-3">
        <div class="col-md-4"><div class="card card-stat p-2 text-center"><div class="fs-4 fw-bold text-danger">{{ number_format($due['total'], 2) }}</div><small>Outstanding</small></div></div>
        <div class="col-md-4"><div class="card card-stat p-2 text-center"><div class="fs-4 fw-bold">{{ $due['count'] }}</div><small>Due Count</small></div></div>
    </div>
    <div class="card card-stat">
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="dataTable">
                <thead><tr><th>Student</th><th>Amount</th><th>Paid</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                    @forelse($fees as $fee)
                        <tr>
                            <td>{{ $fee->student?->admission_no }}</td>
                            <td>{{ $fee->amount }}</td>
                            <td>{{ $fee->paid_amount }}</td>
                            <td><span class="badge bg-{{ $fee->status=='paid'?'success':($fee->status=='overdue'?'danger':'warning') }}">{{ $fee->status }}</span></td>
                            <td>
                                <a href="{{ route('fees.show', $fee) }}" class="btn btn-sm btn-outline-info">View</a>
                                @can('fees.manage')
                                    <a href="{{ route('fees.show', $fee) }}#pay" class="btn btn-sm btn-outline-success">Pay</a>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted">No fees yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    {{ $fees->links() }}
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/datatables.net@1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/datatables.net-bs5@1.13.8/js/dataTables.bootstrap5.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/datatables.net-bs5@1.13.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <script>$(function(){ $('#dataTable').DataTable(); });</script>
    @endpush
</x-layouts.app>
