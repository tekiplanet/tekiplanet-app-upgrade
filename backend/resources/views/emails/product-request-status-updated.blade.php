<x-mail.layout>
    <x-slot:greeting>
        Hello {{ $user->first_name }},
    </x-slot>

    <p>The status of your product request has been updated.</p>

    <div style="margin: 20px 0;">
        <h3 style="color: var(--primary); font-size: 18px; margin-bottom: 15px;">
            Request Details:
        </h3>
        <ul style="list-style: none; padding: 0;">
            <li style="margin-bottom: 10px;">
                <strong>Product:</strong> {{ $productRequest->product_name }}
            </li>
            <li style="margin-bottom: 10px;">
                <strong>Previous Status:</strong> {{ ucfirst($oldStatus) }}
            </li>
            <li style="margin-bottom: 10px;">
                <strong>New Status:</strong> {{ ucfirst($productRequest->status) }}
            </li>
        </ul>
    </div>

    <p>You can view your request details by clicking the button below.</p>

    <x-slot:action>
        <x-mail.button :url="url('/product-requests/' . $productRequest->id)">
            View Request
        </x-mail.button>
    </x-slot>

    <x-slot:closing>
        Best regards,<br>
        The TekiPlanet Team
    </x-slot>
</x-mail.layout> 