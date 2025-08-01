<x-mail.layout>
    <x-slot:greeting>
        Hello {{ $professional->user->name }}!
    </x-slot>

    <p>A new payment has been created for your hustle "{{ $hustle->title }}".</p>
    
    <h3 style="margin-top: 20px; font-weight: 600;">Payment Details:</h3>
    <p><strong>Amount:</strong> ₦{{ number_format($payment->amount, 2) }}</p>
    <p><strong>Type:</strong> {{ ucfirst($payment->payment_type) }} Payment ({{ $payment->payment_type === 'initial' ? '40%' : '60%' }})</p>
    <p><strong>Status:</strong> {{ ucfirst($payment->status) }}</p>

    <x-slot:closing>
        <p>We will notify you once the payment is processed.</p>
        <p>Best regards,<br>TekiPlanet Team</p>
    </x-slot>
</x-mail.layout> 