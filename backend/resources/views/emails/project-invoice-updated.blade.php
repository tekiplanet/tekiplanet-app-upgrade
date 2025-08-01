<x-mail.layout>
    <x-slot:greeting>
        {{ $greeting }}
    </x-slot>

    <div>
        <p>The invoice status for your project has been updated:</p>
        
        <div class="mt-4 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
            <p><strong>Project:</strong> {{ $project->name }}</p>
            <p><strong>Invoice Number:</strong> {{ $invoice->invoice_number }}</p>
            <p><strong>Amount:</strong> ₦{{ number_format($invoice->amount, 2) }}</p>
            <p><strong>Status:</strong> {{ ucfirst($invoice->status) }}</p>
            <p><strong>Due Date:</strong> {{ $invoice->due_date->format('M d, Y') }}</p>
        </div>
    </div>

    <x-slot:closing>
        {{ $closing }}
    </x-slot>
</x-mail.layout> 