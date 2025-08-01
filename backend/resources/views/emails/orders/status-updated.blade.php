<x-mail.layout>
    <x-slot:greeting>
        {{ $greeting }}
    </x-slot>

    <p>Your order status has been updated.</p>
    
    <p>Order ID: <strong>{{ $order->id }}</strong></p>
    <p>New Status: <strong>{{ ucfirst($status) }}</strong></p>
    
    @if($notes)
        <p>Additional Information: {{ $notes }}</p>
    @endif

    <p>You can track your order by clicking the button below:</p>
    
    <a href="{{ config('app.frontend_url') }}/orders/{{ $order->id }}/tracking" class="button">
        Track Order
    </a>

    <x-slot:closing>
        {{ $closing }}
    </x-slot>
</x-mail.layout> 