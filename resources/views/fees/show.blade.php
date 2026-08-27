<x-layouts.app title="Fee Detail">
    <h3 class="fw-bold mb-3">Fee #{{ $fee->id }}</h3>
    <div class="card card-stat p-3 mb-3">
        <p class="mb-1"><strong>Student:</strong> {{ $fee->student?->admission_no }}</p>
        <p class="mb-1"><strong>Amount:</strong> {{ $fee->amount }} | <strong>Paid:</strong> {{ $fee->paid_amount }}</p>
        <p class="mb-0"><strong>Status:</strong> <span class="badge bg-{{ $fee->status=='paid'?'success':'warning' }}">{{ $fee->status }}</span></p>
    </div>
    <div class="card card-stat p-3" id="pay">
        <h6 class="fw-semibold">Record Payment</h6>
        <form method="POST" action="{{ route('fees.pay', $fee) }}" class="row g-2">
            @csrf
            <div class="col-md-4"><input type="number" step="0.01" name="amount" class="form-control" placeholder="Amount" required></div>
            <div class="col-md-4"><input name="payment_method" class="form-control" placeholder="Method (cash/bank)" value="cash"></div>
            <div class="col-md-4"><button class="btn btn-success">Pay</button></div>
        </form>
        <hr>
        <h6 class="fw-semibold">Payment History</h6>
        <table class="table table-sm mb-0">
            <thead><tr><th>Amount</th><th>Method</th><th>Date</th></tr></thead>
            <tbody>
                @forelse($fee->payments as $p)
                    <tr><td>{{ $p->amount }}</td><td>{{ $p->payment_method }}</td><td>{{ $p->created_at?->format('Y-m-d') }}</td></tr>
                @empty
                    <tr><td colspan="3" class="text-muted">No payments.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-layouts.app>
