<x-mail.layout>
    <x-slot name="greeting">
        Hello {{ $transaction->user->first_name }},
    </x-slot>

    <p>Please find attached your transaction receipt for reference number <strong>{{ $transaction->reference_number }}</strong>.</p>

    <p>Transaction Details:</p>
    <ul>
        <li>Amount: <strong>{{ number_format($transaction->amount, 2) }}</strong></li>
        <li>Type: <strong>{{ ucfirst($transaction->type) }}</strong></li>
        <li>Status: <strong>{{ ucfirst($transaction->status) }}</strong></li>
        <li>Date: <strong>{{ $transaction->created_at->format('M d, Y H:i:s') }}</strong></li>
    </ul>

    <x-slot name="closing">
        Best regards,<br>
        The TekiPlanet Team
    </x-slot>
</x-mail.layout> 