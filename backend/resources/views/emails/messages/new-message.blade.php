<x-mail.layout>
    <x-slot:greeting>
        Hello {{ $recipient->name }}!
    </x-slot>

    <p>You have received a new message regarding the hustle "{{ $hustle->title }}".</p>
    
    <h3 style="margin-top: 20px; font-weight: 600;">Message:</h3>
    <p style="background-color: #f3f4f6; padding: 15px; border-radius: 8px;">{{ $messageContent }}</p>

    <x-slot:closing>
        <p>Please log in to your account to view and respond to this message.</p>
        <p>Best regards,<br>TekiPlanet Team</p>
    </x-slot>
</x-mail.layout> 