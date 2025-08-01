<x-mail.layout>
    <x-slot:greeting>
        Hello {{ $professional->user->name }}!
    </x-slot>

    <p>The {{ $payment->payment_type }} payment for your hustle "{{ $hustle->title }}" has been marked as {{ $payment->status }}.</p>
    
    <h3 style="margin-top: 20px; font-weight: 600;">Payment Details:</h3>
    <p><strong>Amount:</strong> ₦{{ number_format($payment->amount, 2) }}</p>
    <p><strong>Type:</strong> {{ ucfirst($payment->payment_type) }} Payment</p>

    <x-slot:closing>
        <p>Thank you for using our platform.</p>
        <p>Best regards,<br>TekiPlanet Team</p>
    </x-slot>
</x-mail.layout> 