<x-mail.layout>
    <x-slot name="greeting">
        Hello {{ $booking->user->first_name }},
    </x-slot>

    <p>Your consulting session scheduled for {{ $booking->selected_date->format('M d, Y') }} at {{ $booking->selected_time->format('h:i A') }} has been cancelled.</p>

    <p style="margin-top: 20px;">Cancellation Reason:</p>
    <p style="padding: 15px; background-color: #f3f4f6; border-radius: 6px;">{{ $booking->cancellation_reason }}</p>

    <p style="margin-top: 20px;">Booking Details:</p>
    <ul style="list-style: none; padding: 0;">
        <li>Duration: {{ $booking->hours }} hours</li>
        <li>Cancelled at: {{ $booking->cancelled_at->format('M d, Y h:i A') }}</li>
    </ul>

    <x-slot name="closing">
        Best regards,<br>
        The TekiPlanet Team
    </x-slot>
</x-mail.layout> 