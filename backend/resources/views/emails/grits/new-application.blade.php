@php
    $pro = $professional->user ?? null;
@endphp

<div style="font-family: Arial, sans-serif; line-height: 1.5;">
    <h2 style="margin-bottom: 8px;">New GRIT Application</h2>
    <p style="margin: 0 0 12px 0;">A professional just applied to your GRIT:</p>
    <p style="margin: 0 0 12px 0;"><strong>{{ $grit->title }}</strong></p>

    @if($pro)
        <p style="margin: 0 0 8px 0;">
            Applicant: {{ $pro->first_name }} {{ $pro->last_name }} ({{ $pro->email }})
        </p>
    @endif

    <p style="margin: 0 0 16px 0;">Category: {{ $grit->category->name ?? '—' }}</p>
    <p style="margin: 0 0 24px 0;">
        <a href="{{ config('app.frontend_url') }}/#/dashboard/grits/{{ $grit->id }}" 
           style="display:inline-block; background:#3b82f6; color:#fff; padding:10px 14px; text-decoration:none; border-radius:6px;">
            View GRIT
        </a>
    </p>
    <p style="color: #6b7280; font-size: 12px;">
        This is an automated message. Please do not reply.
    </p>
</div>


