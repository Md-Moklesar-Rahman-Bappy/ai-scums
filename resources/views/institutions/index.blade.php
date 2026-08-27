<x-layouts.app title="Institutions">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="fw-bold mb-0">Institutions</h3>
        <a href="{{ route('institutions.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> New</a>
    </div>

    <div class="card card-stat">
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="dataTable">
                <thead>
                    <tr><th>Name</th><th>Type</th><th>Email</th><th>Status</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    @forelse($institutions as $institution)
                        <tr>
                            <td>{{ $institution->name }}</td>
                            <td><span class="badge bg-info">{{ $institution->type }}</span></td>
                            <td>{{ $institution->email }}</td>
                            <td>
                                @if($institution->is_active)<span class="badge bg-success">Active</span>
                                @else <span class="badge bg-secondary">Inactive</span> @endif
                            </td>
                            <td>
                                <a href="{{ route('institutions.edit', $institution) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                <form action="{{ route('institutions.destroy', $institution) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted">No institutions yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{ $institutions->links() }}

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/datatables.net@1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/datatables.net-bs5@1.13.8/js/dataTables.bootstrap5.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/datatables.net-bs5@1.13.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <script>$(function(){ $('#dataTable').DataTable(); });</script>
    @endpush
</x-layouts.app>
