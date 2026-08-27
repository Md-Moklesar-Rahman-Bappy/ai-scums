<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Routine\RoutineRequest;
use App\Models\Routine;
use App\Services\RoutineService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * RoutineController.
 *
 * Class & exam routine management rendered on a FullCalendar. Exposes a JSON
 * endpoint consumed by the calendar. Delegates to {@see RoutineService}.
 */
class RoutineController extends Controller
{
    public function __construct(private readonly RoutineService $service)
    {
        $this->authorizeResource(Routine::class);
    }

    /**
     * Show the calendar.
     */
    public function index(): View
    {
        return view('routines.index');
    }

    /**
     * FullCalendar event feed (JSON).
     */
    public function events(): JsonResponse
    {
        return response()->json($this->service->calendarEvents());
    }

    /**
     * Persist a routine slot.
     */
    public function store(RoutineRequest $request): RedirectResponse
    {
        $this->service->create($request->validated());

        return redirect()->route('routines.index')->with('success', 'Routine added.');
    }

    public function destroy(Routine $routine): RedirectResponse
    {
        $routine->delete();

        return redirect()->route('routines.index')->with('success', 'Routine removed.');
    }
}
