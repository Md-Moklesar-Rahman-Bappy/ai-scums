<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Exam\ExamMarkRequest;
use App\Http\Requests\Exam\ExamRequest;
use App\Models\Exam;
use App\Models\Student;
use App\Policies\ExamPolicy;
use App\Services\ExamService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * ExamController.
 *
 * Examination scheduling, mark entry and result processing. Delegates to
 * {@see ExamService}. Authorization via {@see ExamPolicy}.
 */
class ExamController extends Controller
{
    public function __construct(private readonly ExamService $service)
    {
        $this->authorizeResource(Exam::class);
    }

    public function index(): View
    {
        $exams = $this->service->list();

        return view('exams.index', compact('exams'));
    }

    public function create(): View
    {
        return view('exams.create');
    }

    public function store(ExamRequest $request): RedirectResponse
    {
        $this->service->create($request->validated());

        return redirect()->route('exams.index')->with('success', 'Exam created.');
    }

    public function show(Exam $exam): View
    {
        $exam->load('marks.student');
        $summary = $this->service->resultSummary($exam);

        return view('exams.show', compact('exam', 'summary'));
    }

    /**
     * Show the mark entry form for an exam.
     */
    public function marks(Exam $exam): View
    {
        $this->authorize('enterMarks', $exam);
        $students = Student::query()
            ->when($exam->section_id, fn ($q) => $q->where('section_id', $exam->section_id))
            ->get();

        return view('exams.marks', compact('exam', 'students'));
    }

    /**
     * Persist entered marks.
     */
    public function storeMarks(ExamMarkRequest $request, Exam $exam): RedirectResponse
    {
        $this->authorize('enterMarks', $exam);
        $this->service->enterMarks($exam, $request->input('marks'));

        return redirect()->route('exams.show', $exam)->with('success', 'Marks saved.');
    }

    public function destroy(Exam $exam): RedirectResponse
    {
        $exam->delete();

        return redirect()->route('exams.index')->with('success', 'Exam removed.');
    }
}
