<x-mail.layout>
    <x-slot:title>
        {{ ucfirst($transaction->type) }} Transaction Notification
    </x-slot>

    <x-slot:greeting>
        Hello {{ $user->first_name }},
    </x-slot>

    <p class="text-base">
        A new {{ $transaction->type }} transaction has been processed on your account.
    </p>

    <div class="info-box" style="background-color: #f3f4f6; border-radius: 8px; padding: 16px; margin: 24px 0;">
        <table style="width: 100%;">
            <tr>
                <td style="padding: 8px 0;">
                    <strong>Amount:</strong>
                </td>
                <td style="text-align: right; padding: 8px 0;">
                    <span style="color: {{ $transaction->type === 'credit' ? '#059669' : '#DC2626' }};">
                        {{ $transaction->type === 'credit' ? '+' : '-' }}{{ $currency['symbol'] }}{{ number_format($transaction->amount, 2) }}
                    </span>
                </td>
            </tr>
            <tr>
                <td style="padding: 8px 0;">
                    <strong>Type:</strong>
                </td>
                <td style="text-align: right; padding: 8px 0;">
                    {{ ucfirst($transaction->type) }}
                </td>
            </tr>
            <tr>
                <td style="padding: 8px 0;">
                    <strong>Status:</strong>
                </td>
                <td style="text-align: right; padding: 8px 0;">
                    {{ ucfirst($transaction->status) }}
                </td>
            </tr>
            <tr>
                <td style="padding: 8px 0;">
                    <strong>Reference:</strong>
                </td>
                <td style="text-align: right; padding: 8px 0; font-family: monospace;">
                    {{ $transaction->reference_number }}
                </td>
            </tr>
            <tr>
                <td style="padding: 8px 0;">
                    <strong>Date:</strong>
                </td>
                <td style="text-align: right; padding: 8px 0;">
                    {{ $transaction->created_at->format('M d, Y h:i A') }}
                </td>
            </tr>
        </table>
    </div>

    <p class="text-base">
        Your current wallet balance is <strong>{{ $currency['symbol'] }}{{ number_format($user->wallet_balance, 2) }}</strong>
    </p>

    @if($transaction->description)
        <p class="text-base" style="margin-top: 24px;">
            <strong>Description:</strong><br>
            {{ $transaction->description }}
        </p>
    @endif

    <x-mail.button url="{{ config('app.frontend_url') }}/dashboard/wallet">
        View Transaction
    </x-mail.button>

    <x-slot:closing>
        If you did not authorize this transaction, please contact our support team immediately.
    </x-slot>
</x-mail.layout> 