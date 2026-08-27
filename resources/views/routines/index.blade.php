<x-layouts.app title="Routines">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="fw-bold mb-0">Class & Exam Routines</h3>
    </div>

    @can('routines.manage')
    <div class="card card-stat mb-3"><div class="card-body">
        <form method="POST" action="{{ route('routines.store') }}" class="row g-2">
            @csrf
            <div class="col-md-2">
                <select name="type" class="form-select"><option value="class">Class</option><option value="exam">Exam</option></select>
            </div>
            <div class="col-md-2">
                <select name="subject_id" class="form-select select2">
                    <option value="">Subject</option>
                    @foreach(\App\Models\Subject::all() as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="day_of_week" class="form-select">
                    @foreach([1=>'Mon',2=>'Tue',3=>'Wed',4=>'Thu',5=>'Fri',6=>'Sat',7=>'Sun'] as $d=>$n)
                        <option value="{{ $d }}">{{ $n }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2"><input type="time" name="start_time" class="form-control" required></div>
            <div class="col-md-2"><input type="time" name="end_time" class="form-control" required></div>
            <div class="col-md-2"><button class="btn btn-primary w-100">Add</button></div>
        </form>
    </div></div>
    @endcan

    <div class="card card-stat p-3">
        <div id="calendar"></div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
                initialView: 'timeGridWeek',
                headerToolbar: { left: 'prev,next today', center: 'title', right: 'timeGridWeek,timeGridDay' },
                events: '{{ route('routines.events') }}'
            });
            calendar.render();
            $('.select2').select2();
        });
    </script>
    @endpush
</x-layouts.app>
