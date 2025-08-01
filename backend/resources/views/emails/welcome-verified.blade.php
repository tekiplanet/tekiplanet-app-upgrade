<x-mail.layout>
    <x-slot:greeting>
        Hello {{ $user->first_name ?? $user->username }}!
    </x-slot>

    <p>Thank you for verifying your email. Your journey into our tech ecosystem starts now!</p>

    <div style="text-align: center; margin: 30px 0;">
        <h2 style="color: var(--primary); font-size: 24px; margin-bottom: 20px;">
            🚀 Here's what you can explore:
        </h2>
        
        <ul style="list-style: none; padding: 0; margin: 0; text-align: left;">
            <li style="margin-bottom: 12px;">
                📚 <strong>Teki Academy:</strong> Access cutting-edge courses in software development, cybersecurity, and more
            </li>
            <li style="margin-bottom: 12px;">
                🛍️ <strong>Teki Store:</strong> Shop for quality tech products with secure payments
            </li>
            <li style="margin-bottom: 12px;">
                💼 <strong>IT Services:</strong> Professional consulting, software engineering, and cybersecurity solutions
            </li>
            <li style="margin-bottom: 12px;">
                💡 <strong>Teki Hustles:</strong> Find opportunities in tech projects and gigs
            </li>
            <li style="margin-bottom: 12px;">
                🖥️ <strong>Virtual Workstation:</strong> Access cloud development environments
            </li>
        </ul>
    </div>

    <div style="margin: 25px 0;">
        <h3 style="color: var(--primary); font-size: 20px; margin-bottom: 15px;">
            🎯 Quick Start Guide:
        </h3>
        <ol style="padding-left: 20px;">
            <li style="margin-bottom: 10px;">Complete your profile to unlock personalized recommendations</li>
            <li style="margin-bottom: 10px;">Explore our Academy courses and start learning</li>
            <li style="margin-bottom: 10px;">Check out available tech hustles in your area</li>
            <li style="margin-bottom: 10px;">Browse our tech store for essential tools</li>
        </ol>
    </div>

    <p>Need help? Our support team is always here to assist you.</p>

    <x-slot:closing>
        Best regards,<br>
        The TekiPlanet Team
    </x-slot>
</x-mail.layout> 