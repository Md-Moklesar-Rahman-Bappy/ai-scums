<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Student\StudentRequest;
use App\Models\Student;
use App\Policies\StudentPolicy;
use App\Services\StudentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * StudentController.
 *
 * Resource controller for student admission, enrollment, profile and promotion.
 * Delegates persistence to {@see StudentService}; authorization via
 * {@see StudentPolicy}.
 */
class StudentController extends Controller
{
    public function __construct(private readonly StudentService $service)
    {
        $this->authorizeResource(Student::class);
    }

    public function index(): View
    {
        $students = $this->service->list();

        return view('students.index', compact('students'));
    }

    public function create(): View
    {
        return view('students.create');
    }

    public function store(StudentRequest $request): RedirectResponse
    {
        $this->service->admit($request->validated());

        return redirect()->route('students.index')->with('success', 'Student admitted.');
    }

    public function show(Student $student): View
    {
        $student->load(['attendances', 'examMarks.exam.subject', 'fees', 'schoolClass', 'section']);

        return view('students.show', compact('student'));
    }

    public function edit(Student $student): View
    {
        return view('students.edit', compact('student'));
    }

    public function update(StudentRequest $request, Student $student): RedirectResponse
    {
        $this->service->update($student, $request->validated());

        return redirect()->route('students.index')->with('success', 'Student updated.');
    }

    /**
     * Promote a student (change academic placement).
     */
    public function promote(StudentRequest $request, Student $student): RedirectResponse
    {
        $this->authorize('promote', $student);
        $this->service->promote($student, $request->validated());

        return redirect()->route('students.show', $student)->with('success', 'Student promoted.');
    }

    public function destroy(Student $student): RedirectResponse
    {
        $this->service->delete($student);

        return redirect()->route('students.index')->with('success', 'Student removed.');
    }
}
