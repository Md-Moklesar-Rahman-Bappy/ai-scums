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
            'fees_outstanding' => Fee::whereIn('status', ['pending', 'overdue', 'partial'])->sum('balance') ?: Fee::whereIn('status', ['pending', 'overdue'])->count(),
            'notices' => Notice::where('type', 'announcement')->count(),
        ];

        $attendanceTrend = $this->attendanceTrend();
        $resultDistribution = $this->resultDistribution();
        $feeStatus = $this->feeStatus();

        $recentNotices = Notice::latest()->limit(5)->get();
        $recentStudents = Student::with('schoolClass')->latest()->limit(5)->get();

        $aiInsight = $this->aiInsight($stats, $attendanceTrend, $feeStatus);

        return view('dashboard', compact(
            'stats', 'attendanceTrend', 'resultDistribution', 'feeStatus',
            'recentNotices', 'recentStudents', 'aiInsight'
        ));
    }

    /**
     * Last 7 days attendance counts by status (single grouped query per status).
     *
     * @return array{labels: array<int,string>, present: array<int,int>, absent: array<int,int>}
     */
    private function attendanceTrend(): array
    {
        $labels = [];
        $dates = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $labels[] = $date->format('D');
            $dates[] = $date->toDateString();
        }

        $rows = Attendance::query()
            ->whereIn('date', $dates)
            ->whereIn('status', ['present', 'absent'])
            ->selectRaw('date, status, count(*) as total')
            ->groupBy('date', 'status')
            ->get();

        $presentRows = [];
        $absentRows = [];
        foreach ($rows as $r) {
            if ($r->status === 'present') {
                $presentRows[$r->date] = (int) ($r['total'] ?? 0);
            } else {
                $absentRows[$r->date] = (int) ($r['total'] ?? 0);
            }
        }

        $present = [];
        $absent = [];
        foreach ($dates as $date) {
            $present[] = $presentRows[$date] ?? 0;
            $absent[] = $absentRows[$date] ?? 0;
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
        $counts = ExamMark::query()
            ->whereIn('grade', $grades)
            ->groupBy('grade')
            ->selectRaw('grade, count(*) as total')
            ->pluck('total', 'grade')
            ->toArray();

        $values = array_map(fn ($g) => (int) ($counts[$g] ?? 0), $grades);

        return ['labels' => $grades, 'values' => $values];
    }

    /**
     * Fee status counts (single grouped query).
     *
     * @return array{labels: array<int,string>, values: array<int,int>}
     */
    private function feeStatus(): array
    {
        $statuses = ['paid', 'partial', 'pending', 'overdue'];
        $counts = Fee::query()
            ->whereIn('status', $statuses)
            ->groupBy('status')
            ->selectRaw('status, count(*) as total')
            ->pluck('total', 'status')
            ->toArray();

        $values = array_map(fn ($s) => (int) ($counts[$s] ?? 0), $statuses);

        return ['labels' => $statuses, 'values' => $values];
    }

    /**
     * Generate a friendly natural-language insight for the AI widget.
     */
    private function aiInsight(array $stats, array $attendanceTrend, array $feeStatus): string
    {
        $totalAtt = array_sum($attendanceTrend['present']) + array_sum($attendanceTrend['absent']);
        $rate = $totalAtt > 0 ? round(array_sum($attendanceTrend['present']) / $totalAtt * 100) : 0;
        $pendingFees = $feeStatus['values'][2] + $feeStatus['values'][3];

        return "Attendance is at {$rate}% over the last 7 days across {$stats['students']} students. "
            ."{$pendingFees} fee record(s) are pending or overdue — consider sending reminders. "
            .'Ask me to draft a notice or summarize performance for any class.';
    }
}
