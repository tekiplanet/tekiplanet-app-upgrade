@php $pro = $professional->user ?? null; @endphp

<x-mail.layout>
    <x-slot:greeting>
        Hello {{ $grit->user->name ?? 'there' }}!
    </x-slot>

    <p>You have a new application for your GRIT:</p>

    <div style="margin: 20px 0; padding: 15px; background-color: #f8fafc; border: 1px solid #e5e7eb; border-radius: 8px;">
        <h3 style="margin-top: 0; color: #111827; font-size: 18px;">{{ $grit->title }}</h3>
        <p><strong>Category:</strong> {{ $grit->category->name ?? '—' }}</p>
        @if($pro)
            <p><strong>Applicant:</strong> {{ trim(($pro->first_name ?? '').' '.($pro->last_name ?? '')) }} ({{ $pro->email }})</p>
        @endif
        <p><strong>Budget:</strong> {{ $grit->owner_currency ?? '₦' }}{{ number_format($grit->owner_budget ?? $grit->budget, 2) }}</p>
        <p><strong>Deadline:</strong> {{ optional($grit->deadline)->format('M d, Y') }}</p>
    </div>

    <p>Click the button below to view details and manage applications.</p>

    <p>
        <a href="{{ config('app.frontend_url') }}/#/dashboard/grits/{{ $grit->id }}"
           style="display:inline-block; background:#0ea5e9; color:#fff; padding:10px 14px; text-decoration:none; border-radius:6px;">
            View GRIT
        </a>
    </p>

    <x-slot:closing>
        <p>Best regards,<br>TekiPlanet Team</p>
    </x-slot>
</x-mail.layout>


