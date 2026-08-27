<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Institution\InstitutionRequest;
use App\Models\Institution;
use App\Policies\InstitutionPolicy;
use App\Services\InstitutionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * InstitutionController.
 *
 * Resource controller for tenant (institution) administration. Thin: delegates
 * to {@see InstitutionService}. Guarded by {@see InstitutionPolicy}.
 */
class InstitutionController extends Controller
{
    public function __construct(private readonly InstitutionService $service)
    {
        $this->authorizeResource(Institution::class);
    }

    /**
     * List institutions.
     */
    public function index(): View
    {
        $institutions = $this->service->list();

        return view('institutions.index', compact('institutions'));
    }

    /**
     * Show the create form.
     */
    public function create(): View
    {
        return view('institutions.create');
    }

    /**
     * Store a new institution.
     */
    public function store(InstitutionRequest $request): RedirectResponse
    {
        $this->service->create($request->validated());

        return redirect()->route('institutions.index')->with('success', 'Institution created.');
    }

    /**
     * Show the edit form.
     */
    public function edit(Institution $institution): View
    {
        return view('institutions.edit', compact('institution'));
    }

    /**
     * Update an institution.
     */
    public function update(InstitutionRequest $request, Institution $institution): RedirectResponse
    {
        $this->service->update($institution, $request->validated());

        return redirect()->route('institutions.index')->with('success', 'Institution updated.');
    }

    /**
     * Delete an institution.
     */
    public function destroy(Institution $institution): RedirectResponse
    {
        $this->service->delete($institution);

        return redirect()->route('institutions.index')->with('success', 'Institution deleted.');
    }
}
