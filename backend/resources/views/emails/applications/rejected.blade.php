<x-mail.layout>
    <x-slot:greeting>
        Hello {{ $professional->user->name }}!
    </x-slot>

    <p>We regret to inform you that your application for the hustle "{{ $hustle->title }}" was not selected.</p>
    
    <h3 style="margin-top: 20px; font-weight: 600;">Hustle Details:</h3>
    <p><strong>Title:</strong> {{ $hustle->title }}</p>

    <x-slot:closing>
        <p>Don't worry! Keep checking for new hustles that match your skills.</p>
        <p>Best regards,<br>TekiPlanet Team</p>
    </x-slot>
</x-mail.layout> 