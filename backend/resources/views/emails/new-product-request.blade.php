<x-mail.layout>
    <x-slot:greeting>
        {{ $greeting }}
    </x-slot>

    <p>A new product request has been submitted.</p>

    <div style="margin: 20px 0;">
        <h3 style="color: var(--primary); font-size: 18px; margin-bottom: 15px;">
            Request Details:
        </h3>
        <ul style="list-style: none; padding: 0;">
            <li style="margin-bottom: 10px;">
                <strong>Product:</strong> {{ $productRequest->product_name }}
            </li>
            <li style="margin-bottom: 10px;">
                <strong>Price Range:</strong> ₦{{ number_format($productRequest->min_price) }} - ₦{{ number_format($productRequest->max_price) }}
            </li>
            <li style="margin-bottom: 10px;">
                <strong>Quantity:</strong> {{ $productRequest->quantity_needed }} units
            </li>
            <li style="margin-bottom: 10px;">
                <strong>Expected by:</strong> {{ $productRequest->deadline->format('M d, Y') }}
            </li>
        </ul>
    </div>

    <p>Please review this request as soon as possible.</p>

    <x-slot:closing>
        Best regards,<br>
        The TekiPlanet Team
    </x-slot>
</x-mail.layout> 