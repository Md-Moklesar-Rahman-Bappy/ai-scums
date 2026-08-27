<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Attendance\AttendanceMarkRequest;
use App\Models\Attendance;
use App\Models\Student;
use App\Services\AttendanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * AttendanceController.
 *
 * Daily attendance marking, listing with a date filter, and analytics. Uses
 * {@see AttendanceService} for persistence and reporting.
 */
class AttendanceController extends Controller
{
    public function __construct(private readonly AttendanceService $service) {}

    /**
     * List attendance for a date (default today).
     */
    public function index(): View
    {
        $this->authorize('viewAny', Attendance::class);

        $date = request('date', now()->toDateString());
        $records = Attendance::whereDate('date', $date)->with('student')->get();
        $summary = $this->service->sectionSummary(request('section_id'), $date);

        return view('attendances.index', compact('records', 'date', 'summary'));
    }

    /**
     * Show the bulk marking form for a section.
     */
    public function create(): View
    {
        $this->authorize('manage', Attendance::class);

        $students = Student::query()
            ->when(request('section_id'), fn ($q) => $q->where('section_id', request('section_id')))
            ->get();

        return view('attendances.create', compact('students'));
    }

    /**
     * Persist attendance marks.
     */
    public function store(AttendanceMarkRequest $request): RedirectResponse
    {
        $this->authorize('manage', Attendance::class);
        $this->service->mark(
            $request->input('date'),
            $request->input('records'),
            $request->input('subject_id'),
            $request->input('section_id')
        );

        return redirect()->route('attendances.index', ['date' => $request->input('date')])
            ->with('success', 'Attendance saved.');
    }

    /**
     * Analytics view (Chart.js) for attendance. Read-only.
     */
    public function analytics(): View
    {
        $this->authorize('viewAny', Attendance::class);

        $students = Student::with('attendances')->get();
        $labels = $students->pluck('admission_no')->all();
        $values = $students->map(fn ($s) => Attendance::percentageFor($s->attendances))->all();

        return view('attendances.analytics', compact('labels', 'values'));
    }
}
