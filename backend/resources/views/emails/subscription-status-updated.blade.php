@component('components.mail.layout')
    @slot('greeting')
        Hello {{ $user->first_name }},
    @endslot

    <p>Your workstation subscription status has been updated to <strong class="highlight">{{ $status }}</strong>.</p>

    <p>Subscription Details:</p>
    <ul style="list-style: none; padding: 0; margin: 20px 0;">
        <li style="margin-bottom: 10px;">
            <strong>Plan:</strong> {{ $subscription->plan->name }}
        </li>
        <li style="margin-bottom: 10px;">
            <strong>Duration:</strong> {{ $subscription->start_date->format('M d, Y') }} to {{ $subscription->end_date->format('M d, Y') }}
        </li>
        <li style="margin-bottom: 10px;">
            <strong>Tracking Code:</strong> {{ $subscription->tracking_code }}
        </li>
    </ul>

    <a href="{{ config('app.url') }}/subscriptions/{{ $subscription->id }}" class="button">
        View Subscription Details
    </a>

    <p>If you have any questions about this update, please don't hesitate to contact our support team.</p>

    @slot('closing')
        Best regards,<br>
        The TekiPlanet Team
    @endslot
@endcomponent 