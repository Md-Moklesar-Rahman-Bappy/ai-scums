<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Teacher\TeacherRequest;
use App\Models\Teacher;
use App\Services\TeacherService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * TeacherController.
 *
 * Resource controller for teacher assignment, department linkage and subject
 * allocation. Delegates to {@see TeacherService}.
 */
class TeacherController extends Controller
{
    public function __construct(private readonly TeacherService $service)
    {
        $this->authorizeResource(Teacher::class);
    }

    public function index(): View
    {
        $teachers = $this->service->list();

        return view('teachers.index', compact('teachers'));
    }

    public function create(): View
    {
        return view('teachers.create');
    }

    public function store(TeacherRequest $request): RedirectResponse
    {
        $this->service->create($request->validated());

        return redirect()->route('teachers.index')->with('success', 'Teacher added.');
    }

    public function edit(Teacher $teacher): View
    {
        $teacher->load('subjects');

        return view('teachers.edit', compact('teacher'));
    }

    public function update(TeacherRequest $request, Teacher $teacher): RedirectResponse
    {
        $this->service->update($teacher, $request->validated());

        return redirect()->route('teachers.index')->with('success', 'Teacher updated.');
    }

    public function destroy(Teacher $teacher): RedirectResponse
    {
        $this->service->delete($teacher);

        return redirect()->route('teachers.index')->with('success', 'Teacher removed.');
    }
}
