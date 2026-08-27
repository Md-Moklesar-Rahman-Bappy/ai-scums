<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Notice\NoticeRequest;
use App\Models\Notice;
use App\Services\NoticeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * NoticeController.
 *
 * Announcements, events and notifications. Delegates to {@see NoticeService}.
 */
class NoticeController extends Controller
{
    public function __construct(private readonly NoticeService $service)
    {
        $this->authorizeResource(Notice::class);
    }

    public function index(): View
    {
        $notices = $this->service->list();

        return view('notices.index', compact('notices'));
    }

    public function create(): View
    {
        return view('notices.create');
    }

    public function store(NoticeRequest $request): RedirectResponse
    {
        $this->service->create($request->validated());

        return redirect()->route('notices.index')->with('success', 'Notice published.');
    }

    public function edit(Notice $notice): View
    {
        return view('notices.edit', compact('notice'));
    }

    public function update(NoticeRequest $request, Notice $notice): RedirectResponse
    {
        $this->service->update($notice, $request->validated());

        return redirect()->route('notices.index')->with('success', 'Notice updated.');
    }

    public function destroy(Notice $notice): RedirectResponse
    {
        $this->service->delete($notice);

        return redirect()->route('notices.index')->with('success', 'Notice removed.');
    }
}
