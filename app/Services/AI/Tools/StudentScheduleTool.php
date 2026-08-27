<?php

declare(strict_types=1);

namespace App\Services\AI\Tools;

use App\Contracts\AI\AIDataToolInterface;
use App\Models\Routine;
use App\Models\User;
use App\Services\AI\Intent;

/**
 * StudentScheduleTool.
 *
 * Returns the acting student's weekly class routine. Read-only. Satisfies the
 * "Show my schedule" student query.
 */
class StudentScheduleTool extends BaseDataTool implements AIDataToolInterface
{
    public function intent(): string
    {
        return Intent::STUDENT_SCHEDULE;
    }

    public function name(): string
    {
        return 'student_schedule';
    }

    public function execute(User $user): array
    {
        $student = $this->resolveStudentFor($user);

        if (! $student) {
            return ['summary' => 'No linked student record found.', 'data' => []];
        }

        $routines = Routine::where('type', 'class')
            ->where('section_id', $student->section_id)
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->with(['subject', 'teacher'])
            ->get();

        $entries = $routines->map(fn ($r) => [
            'day' => $r->day_of_week,
            'start' => $r->start_time,
            'end' => $r->end_time,
            'subject' => $r->subject?->name,
            'room' => $r->room,
        ])->all();

        return [
            'summary' => count($entries).' scheduled classes found.',
            'data' => $entries,
        ];
    }
}
