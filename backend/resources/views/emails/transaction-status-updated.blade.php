<x-mail.layout>
    <x-slot name="greeting">
        Hello {{ $user->first_name }},
    </x-slot>

    @php
        $statusMessage = match ($transaction->status) {
            'failed' => "We regret to inform you that your transaction has failed.",
            'cancelled' => "Your transaction has been cancelled.",
            'completed' => "Great news! Your transaction has been completed successfully.",
            default => "Your transaction status has been updated."
        };
    @endphp

    <p>{{ $statusMessage }}</p>

    <p>Transaction Details:</p>
    <ul>
        <li>Reference Number: <strong>{{ $transaction->reference_number }}</strong></li>
        <li>Amount: <strong>{{ number_format($transaction->amount, 2) }}</strong></li>
        <li>Type: <strong>{{ ucfirst($transaction->type) }}</strong></li>
        @if(isset($transaction->notes['status_update']['note']))
            <li>Note: {{ $transaction->notes['status_update']['note'] }}</li>
        @endif
        @if(isset($transaction->notes['wallet_update']))
            <li class="text-green-600">{{ $transaction->notes['wallet_update'] }}</li>
        @endif
    </ul>

    <p>Current Wallet Balance: <strong>{{ number_format($user->wallet_balance, 2) }}</strong></p>

    @if($transaction->status === 'failed')
        <p>If you believe this is an error, please contact our support team for assistance.</p>
    @elseif($transaction->status === 'cancelled')
        <p>If you didn't request this cancellation, please contact our support team immediately.</p>
    @elseif($transaction->status === 'completed')
        <p>Thank you for using our services!</p>
    @endif

    <p>You can view the complete transaction details in your account.</p>

    <x-slot name="closing">
        Best regards,<br>
        The TekiPlanet Team
    </x-slot>
</x-mail.layout> 