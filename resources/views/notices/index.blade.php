<x-layouts.app title="Notices">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="fw-bold mb-0">Notices & Events</h3>
        @can('notices.manage')
            <a href="{{ route('notices.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Publish</a>
        @endcan
    </div>
    <div class="row g-3">
        @forelse($notices as $notice)
            <div class="col-md-6">
                <div class="card card-stat p-3">
                    <div class="d-flex justify-content-between">
                        <span class="badge bg-{{ $notice->type=='event'?'info':($notice->type=='notification'?'warning':'primary') }}">{{ $notice->type }}</span>
                        <span class="badge badge-soft-secondary">{{ $notice->audience }}</span>
                    </div>
                    <h6 class="fw-bold mt-2">{{ $notice->title }}</h6>
                    <p class="small text-muted mb-2">{{ Str::limit($notice->body, 120) }}</p>
                    @can('notices.manage')
                        <div>
                            <a href="{{ route('notices.edit', $notice) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                            <form action="{{ route('notices.destroy', $notice) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete?')">
                                @csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </div>
                    @endcan
                </div>
            </div>
        @empty
            <div class="col-12 text-center text-muted">No notices yet.</div>
        @endforelse
    </div>
    {{ $notices->links() }}
</x-layouts.app>
