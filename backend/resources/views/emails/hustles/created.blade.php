<x-mail.layout>
    <x-slot:greeting>
        Hello {{ $professional->user->name }}!
    </x-slot>

    <p>A new hustle has been posted in your category ({{ $hustle->category->name }}).</p>

    <h3 style="margin-top: 20px; font-weight: 600;">{{ $hustle->title }}</h3>
    
    <p><strong>Budget:</strong> ₦{{ number_format($hustle->budget, 2) }}</p>
    <p><strong>Deadline:</strong> {{ $hustle->deadline->format('M d, Y') }}</p>
    
    <p style="margin-top: 20px;">{{ Str::limit($hustle->description, 200) }}</p>

    <x-slot:closing>
        <p>Don't miss this opportunity! Log in to your account to apply for this hustle.</p>
        <p>Best regards,<br>TekiPlanet Team</p>
    </x-slot>
</x-mail.layout> 