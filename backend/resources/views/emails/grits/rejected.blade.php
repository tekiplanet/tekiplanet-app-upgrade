<x-mail.layout>
    <x-slot:greeting>
        Hello {{ $grit->user->name }},
    </x-slot>

    <p>We regret to inform you that your GRIT has been reviewed and could not be approved at this time.</p>

    <div style="margin: 20px 0; padding: 15px; background-color: #fef2f2; border: 1px solid #ef4444; border-radius: 6px;">
        <h3 style="margin-top: 0; color: #991b1b; font-size: 18px;">{{ $grit->title }}</h3>
        <p><strong>Category:</strong> {{ $grit->category->name }}</p>
        <p><strong>Budget:</strong> {{ $grit->owner_currency ?? '₦' }}{{ number_format($grit->owner_budget ?? $grit->budget, 2) }}</p>
        <p><strong>Deadline:</strong> {{ optional($grit->deadline)->format('M d, Y') }}</p>
        <p><strong>Status:</strong> <span style="color: #dc2626; font-weight: 600;">Rejected</span></p>
    </div>

    @if(isset($reason) && $reason)
    <div style="margin: 20px 0; padding: 15px; background-color: #fffbeb; border: 1px solid #f59e0b; border-radius: 6px;">
        <h4 style="margin-top: 0; color: #92400e; font-size: 16px;">Reason for Rejection:</h4>
        <p style="color: #92400e; margin: 0;">{{ $reason }}</p>
    </div>
    @endif

    <p>You can review the feedback above and make necessary adjustments to your GRIT. Once updated, you can resubmit it for approval.</p>

    <p>If you have any questions about this decision, please don't hesitate to contact our support team.</p>

    <x-slot:closing>
        <p>Best regards,<br>TekiPlanet Team</p>
    </x-slot>
</x-mail.layout>
