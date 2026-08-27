<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Fee\FeePaymentRequest;
use App\Http\Requests\Fee\FeeRequest;
use App\Models\Fee;
use App\Policies\FeePolicy;
use App\Services\FeeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * FeeController.
 *
 * Fee assignment, payment tracking and due reports. Delegates to
 * {@see FeeService}. Authorization via {@see FeePolicy}.
 */
class FeeController extends Controller
{
    public function __construct(private readonly FeeService $service)
    {
        $this->authorizeResource(Fee::class);
    }

    public function index(): View
    {
        $fees = $this->service->list();
        $due = $this->service->dueReport();

        return view('fees.index', compact('fees', 'due'));
    }

    public function create(): View
    {
        return view('fees.create');
    }

    public function store(FeeRequest $request): RedirectResponse
    {
        $this->service->create($request->validated());

        return redirect()->route('fees.index')->with('success', 'Fee assigned.');
    }

    public function show(Fee $fee): View
    {
        $fee->load('payments');

        return view('fees.show', compact('fee'));
    }

    /**
     * Record a payment against a fee.
     */
    public function pay(FeePaymentRequest $request, Fee $fee): RedirectResponse
    {
        $this->authorize('pay', $fee);
        $this->service->recordPayment($fee, $request->validated());

        return redirect()->route('fees.show', $fee)->with('success', 'Payment recorded.');
    }

    public function destroy(Fee $fee): RedirectResponse
    {
        $fee->delete();

        return redirect()->route('fees.index')->with('success', 'Fee removed.');
    }
}
