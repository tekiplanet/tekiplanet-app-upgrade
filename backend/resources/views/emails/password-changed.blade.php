<x-mail.layout>
    <x-slot name="greeting">
        Password Changed
    </x-slot>

    <p>Your password was recently changed on {{ $datetime }}.</p>
    
    <div style="margin: 20px 0; padding: 15px; background: #f8f9fa; border-radius: 5px;">
        <p style="margin: 0;"><strong>IP Address:</strong> {{ $ipAddress }}</p>
        <p style="margin: 10px 0 0 0;">If you did not make this change, please contact our support team immediately.</p>
    </div>

    <p>For security reasons, we recommend:</p>
    <ul style="margin: 15px 0;">
        <li>Change your password immediately if you didn't authorize this change</li>
        <li>Enable two-factor authentication if you haven't already</li>
        <li>Review your recent account activity</li>
    </ul>

    <x-slot name="closing">
        Best regards,<br>
        The TekiPlanet Team
    </x-slot>
</x-mail.layout> 