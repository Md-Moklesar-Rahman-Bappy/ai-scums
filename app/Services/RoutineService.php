<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Routine;
use App\Repositories\RoutineRepository;
use Illuminate\Database\Eloquent\Collection;

/**
 * RoutineService.
 *
 * Builds FullCalendar-compatible event payloads from weekly routines (class &
 * exam). A weekly routine is expanded into recurring weekday events.
 */
class RoutineService
{
    public function __construct(private readonly RoutineRepository $repository) {}

    /**
     * All routines for the current tenant.
     *
     * @return Collection<int, Routine>
     */
    public function all()
    {
        return $this->repository->all();
    }

    /**
     * Build FullCalendar events from weekly routines.
     *
     * Each routine becomes a recurring event on its weekday with the given
     * start/end times.
     *
     * @return array<int, array{title: string, daysOfWeek: array<int,int>, startTime: string, endTime: string, color: string}>
     */
    public function calendarEvents(): array
    {
        return $this->repository->all()->map(function (Routine $routine): array {
            return [
                'title' => ($routine->type === 'exam' ? 'Exam: ' : '').$routine->subject?->name.' ('.$routine->room.')',
                'daysOfWeek' => [$routine->day_of_week],
                'startTime' => $routine->start_time,
                'endTime' => $routine->end_time,
                'color' => $routine->type === 'exam' ? '#dc3545' : '#0d6efd',
            ];
        })->all();
    }

    /**
     * Create a routine slot.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Routine
    {
        return $this->repository->create($data);
    }
}
