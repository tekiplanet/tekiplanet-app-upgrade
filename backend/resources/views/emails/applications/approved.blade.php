<x-mail.layout>
    <x-slot:greeting>
        Hello {{ $professional->user->name }}!
    </x-slot>

    <p>Congratulations! Your application for the hustle "{{ $hustle->title }}" has been approved.</p>
    
    <h3 style="margin-top: 20px; font-weight: 600;">Hustle Details:</h3>
    <p><strong>Title:</strong> {{ $hustle->title }}</p>
    <p><strong>Budget:</strong> ₦{{ number_format($hustle->budget, 2) }}</p>
    <p><strong>Deadline:</strong> {{ $hustle->deadline->format('M d, Y') }}</p>

    <x-slot:closing>
        <p>Please log in to your account to view the full details and get started.</p>
        <p>Best regards,<br>TekiPlanet Team</p>
    </x-slot>
</x-mail.layout> 