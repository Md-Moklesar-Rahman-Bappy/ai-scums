<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\ExamMark;
use App\Models\Fee;
use App\Models\Notice;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\View\View;

/**
 * DashboardController.
 *
 * Serves the role-aware dashboard with summary statistics and charts
 * (Chart.js). Counts are automatically tenant-scoped via the global scope.
 */
class DashboardController extends Controller
{
    /**
     * Display the dashboard.
     */
    public function index(): View
    {
        $stats = [
            'students' => Student::count(),
            'teachers' => Teacher::count(),
            'notices' => Notice::where('type', 'announcement')->count(),
        ];

        $attendanceTrend = $this->attendanceTrend();
        $resultDistribution = $this->resultDistribution();
        $feeStatus = $this->feeStatus();

        return view('dashboard', compact('stats', 'attendanceTrend', 'resultDistribution', 'feeStatus'));
    }

    /**
     * Last 7 days attendance counts by status.
     *
     * @return array{labels: array<int,string>, present: array<int,int>, absent: array<int,int>}
     */
    private function attendanceTrend(): array
    {
        $labels = [];
        $present = [];
        $absent = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $labels[] = $date->format('D');
            $present[] = Attendance::whereDate('date', $date)->where('status', 'present')->count();
            $absent[] = Attendance::whereDate('date', $date)->where('status', 'absent')->count();
        }

        return ['labels' => $labels, 'present' => $present, 'absent' => $absent];
    }

    /**
     * Grade distribution for charting.
     *
     * @return array{labels: array<int,string>, values: array<int,int>}
     */
    private function resultDistribution(): array
    {
        $grades = ['A+', 'A', 'B', 'C', 'D', 'F'];
        $values = array_map(
            fn ($g) => ExamMark::where('grade', $g)->count(),
            $grades
        );

        return ['labels' => $grades, 'values' => $values];
    }

    /**
     * Fee status counts.
     *
     * @return array{labels: array<int,string>, values: array<int,int>}
     */
    private function feeStatus(): array
    {
        $statuses = ['paid', 'partial', 'pending', 'overdue'];
        $values = array_map(
            fn ($s) => Fee::where('status', $s)->count(),
            $statuses
        );

        return ['labels' => $statuses, 'values' => $values];
    }
}
