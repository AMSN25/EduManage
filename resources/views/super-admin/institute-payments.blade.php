@extends('layouts.app')

@section('content')
    <div class="mb-6">
        <a href="{{ route('super-admin.institute.detail', $institute) }}" class="text-sm text-indigo-600 hover:text-indigo-800">&larr; Back to Institute</a>
    </div>

    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">Payment History</h1>
        <p class="mt-1 text-sm text-gray-600">{{ $institute->name }}</p>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Method</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Reference</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Recorded By</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Notes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($payments as $payment)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-gray-900">{{ $payment->paid_at->format('M d, Y H:i') }}</td>
                            <td class="px-6 py-4 text-green-600 font-semibold">৳{{ number_format($payment->amount, 2) }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ $payment->transaction_ref ?? '—' }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ $payment->recordedBy?->name ?? 'Unknown' }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ Str::limit($payment->notes, 50) ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500">No payments recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-3 border-t border-gray-200">
            {{ $payments->links() }}
        </div>
    </div>
@endsection
