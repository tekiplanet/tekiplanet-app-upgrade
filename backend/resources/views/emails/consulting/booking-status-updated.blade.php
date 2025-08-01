<x-mail.layout>
    <x-slot name="greeting">
        Hello {{ $booking->user->first_name }},
    </x-slot>

    @php
        $statusMessage = match($booking->status) {
            'confirmed' => 'Your booking has been confirmed.',
            'ongoing' => 'Your consulting session is now in progress.',
            'completed' => 'Your consulting session has been marked as completed.',
            default => "Your booking status has been updated from " . ucfirst($oldStatus) . " to " . ucfirst($booking->status) . "."
        };
    @endphp

    <p>{{ $statusMessage }}</p>

    <p style="margin-top: 20px;">Booking Details:</p>
    <ul style="list-style: none; padding: 0;">
        <li>Date: {{ $booking->selected_date->format('M d, Y') }}</li>
        <li>Time: {{ $booking->selected_time->format('h:i A') }}</li>
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