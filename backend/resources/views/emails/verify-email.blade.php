<x-mail.layout>
    <x-slot:greeting>
        Hello {{ $user->first_name ?? $user->username }}!
    </x-slot>

    <p>Welcome to TekiPlanet! Please verify your email address by entering the following code:</p>

    <div style="text-align: center; margin: 30px 0;">
        <div style="font-size: 32px; letter-spacing: 8px; font-weight: bold; color: var(--primary);">
            {{ $verificationCode }}
        </div>
    </div>

    <p>This verification code will expire in {{ $expiresIn }} hours.</p>
    
    <p>If you did not create an account, no further action is required.</p>

    <x-slot:closing>
        Best regards,<br>
        The TekiPlanet Team
    </x-slot>
</x-mail.layout> 