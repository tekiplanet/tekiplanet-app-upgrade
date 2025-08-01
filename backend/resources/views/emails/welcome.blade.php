<x-mail.layout>
    <x-slot:title>Welcome to {{ config('app.name') }}</x-slot>
    
    <x-slot:greeting>
        Hello {{ $user->first_name }},
    </x-slot>

    <p class="text-base">
        Welcome to {{ config('app.name') }}! We're excited to have you on board.
    </p>

    <p class="text-base">
        To get started, you can:
    </p>

    <ul style="padding-left: 20px; margin: 16px 0;">
        <li>Complete your profile</li>
        <li>Browse available courses</li>
        <li>Connect with other learners</li>
    </ul>

    <x-mail.button url="{{ route('dashboard') }}">
        Get Started
    </x-mail.button>

    <x-slot:closing>
        If you have any questions, feel free to reply to this email.
    </x-slot>

    <x-slot:unsubscribe>
        {{ route('unsubscribe', ['token' => $user->unsubscribe_token]) }}
    </x-slot>
</x-mail.layout> 