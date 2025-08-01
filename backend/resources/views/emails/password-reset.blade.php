<x-mail.layout>
    <x-slot name="greeting">
        Reset Your Password
    </x-slot>

    <p>You have requested to reset your password. Here is your recovery code:</p>
    
    <div style="text-align: center; margin: 30px 0;">
        <div style="
            font-family: monospace;
            font-size: 24px;
            letter-spacing: 0.5em;
            background: #f3f4f6;
            padding: 20px;
            border-radius: 8px;
            display: inline-block;
        ">
            {{ $code }}
        </div>
    </div>

    <p>This code will expire in 30 minutes. If you did not request a password reset, please ignore this email.</p>

    <x-slot name="closing">
        Best regards,<br>
        The TekiPlanet Team
    </x-slot>
</x-mail.layout> 