<x-mail.layout>
    <x-slot:greeting>
        {{ $greeting }}
    </x-slot>

    <p>There's a new tracking update for your order.</p>
    
    <p>Order ID: <strong>{{ $order->id }}</strong></p>
    <p>Status: <strong>{{ ucfirst($status) }}</strong></p>
    <p>Location: <strong>{{ $location }}</strong></p>
    
    @if($description)
        <p>Update Details: {{ $description }}</p>
    @endif

    <p>Track your order in real-time by clicking the button below:</p>
    
    <a href="{{ config('app.frontend_url') }}/orders/{{ $order->id }}/tracking" class="button">
        Track Order
    </a>

    <x-slot:closing>
        {{ $closing }}
    </x-slot>
</x-mail.layout> 