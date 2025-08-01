<x-mail.layout>
    <x-slot name="greeting">
        Hello {{ $booking->user->first_name }},
    </x-slot>

    <p>This is a reminder about your upcoming consulting session scheduled for {{ $booking->selected_date->format('M d, Y') }} at {{ $booking->selected_time->format('h:i A') }} (in {{ $timeUntil }}).</p>

    @if($note)
        <p style="margin-top: 20px;">Additional Note:</p>
        <p style="padding: 15px; background-color: #f3f4f6; border-radius: 6px;">{{ $note }}</p>
    @endif

    <p style="margin-top: 20px;">Session Details:</p>
    <ul style="list-style: none; padding: 0;">
        <li>Duration: {{ $booking->hours }} hours</li>
        @if($booking->expert)
            <li>Expert: {{ $booking->expert->user->first_name }} {{ $booking->expert->user->last_name }}</li>
        @endif
    </ul>

    <x-slot name="closing">
        Best regards,<br>
        The TekiPlanet Team
    </x-slot>
</x-mail.layout> 