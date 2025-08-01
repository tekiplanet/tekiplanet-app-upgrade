<x-mail.layout>
    <x-slot name="greeting">
        Hello {{ $booking->user->first_name }},
    </x-slot>

    <p>
        @if($isReassignment)
            A new expert has been assigned to your consulting session.
        @else
            An expert has been assigned to your consulting session.
        @endif
    </p>

    <p style="margin-top: 20px;">Expert Details:</p>
    <ul style="list-style: none; padding: 0;">
        <li>Name: {{ $booking->expert->user->first_name }} {{ $booking->expert->user->last_name }}</li>
    </ul>

    <p style="margin-top: 20px;">Session Details:</p>
    <ul style="list-style: none; padding: 0;">
        <li>Date: {{ $booking->selected_date->format('M d, Y') }}</li>
        <li>Time: {{ $booking->selected_time->format('h:i A') }}</li>
        <li>Duration: {{ $booking->hours }} hours</li>
    </ul>

    <x-slot name="closing">
        Best regards,<br>
        The TekiPlanet Team
    </x-slot>
</x-mail.layout> 