<x-layouts.app title="Institution Details">
    <h3 class="fw-bold mb-3">Institution Details</h3>
    <div class="card card-stat">
        <div class="card-body">
            <dl class="row">
                <dt class="col-sm-3">Name</dt>
                <dd class="col-sm-9">{{ $institution->name }}</dd>

                <dt class="col-sm-3">Type</dt>
                <dd class="col-sm-9">{{ ucfirst($institution->type) }}</dd>

                <dt class="col-sm-3">Slug</dt>
                <dd class="col-sm-9">{{ $institution->slug }}</dd>

                <dt class="col-sm-3">Email</dt>
                <dd class="col-sm-9">{{ $institution->email ?? '—' }}</dd>

                <dt class="col-sm-3">Phone</dt>
                <dd class="col-sm-9">{{ $institution->phone ?? '—' }}</dd>

                <dt class="col-sm-3">Website</dt>
                <dd class="col-sm-9">{{ $institution->website ?? '—' }}</dd>

                <dt class="col-sm-3">Active</dt>
                <dd class="col-sm-9">{{ $institution->is_active ? 'Yes' : 'No' }}</dd>
            </dl>

            <a href="{{ route('institutions.edit', $institution) }}" class="btn btn-primary">Edit</a>
            <a href="{{ route('institutions.index') }}" class="btn btn-link">Back</a>
        </div>
    </div>
</x-layouts.app>
