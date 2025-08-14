<x-mail.layout>
    <x-slot:greeting>
        Hello {{ $professional->user->name }}!
    </x-slot>

    <p>A new GRIT has been posted in your category ({{ $grit->category->name }}).</p>

    <div style="margin: 20px 0; padding: 15px; background-color: #f0f9ff; border: 1px solid #0ea5e9; border-radius: 6px;">
        <h3 style="margin-top: 0; color: #0c4a6e; font-size: 18px;">{{ $grit->title }}</h3>
        <p><strong>Budget:</strong> {{ $grit->owner_currency ?? '₦' }}{{ number_format($grit->owner_budget ?? $grit->budget, 2) }}</p>
        <p><strong>Deadline:</strong> {{ optional($grit->deadline)->format('M d, Y') }}</p>
        <p><strong>Requirements:</strong> {{ Str::limit($grit->requirements, 100) }}</p>
    </div>
    
    <p style="margin-top: 20px;">{{ Str::limit($grit->description, 200) }}</p>

    <x-slot:closing>
        <p>Don't miss this opportunity! Log in to your account to apply for this GRIT.</p>
        <p>Best regards,<br>TekiPlanet Team</p>
    </x-slot>
</x-mail.layout>
