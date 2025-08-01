@extends('admin.layouts.app')

@section('content')
<div class="container px-6 mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
            Quote Details
        </h2>
        <a href="{{ route('admin.quotes.index') }}" 
           class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
            Back to Quotes
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Quote Information -->
        <div class="md:col-span-2 bg-white rounded-lg shadow-md dark:bg-gray-800 p-6">
            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4">
                Quote Information
            </h3>
            <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Service</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                        {{ $quote->service->name }}
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Customer</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                        <div class="space-y-2">
                            <div class="font-medium">
                                {{ $quote->user->first_name }} {{ $quote->user->last_name }}
                            </div>
                            <div class="text-gray-600">
                                <div>Email: {{ $quote->user->email }}</div>
                                @if($quote->user->phone)
                                    <div>Phone: {{ $quote->user->phone }}</div>
                                @endif
                            </div>
                            <div>
                                <a href="{{ route('admin.users.show', $quote->user->id) }}" 
                                   class="inline-flex items-center text-sm text-blue-600 hover:text-blue-700">
                                    <span>View Full Profile</span>
                                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                              d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Industry</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                        {{ $quote->industry }}
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Budget Range</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                        {{ $quote->budget_range }}
                    </dd>
                </div>
                <div class="col-span-2">
                    <dt class="text-sm font-medium text-gray-500">Project Description</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                        {{ $quote->project_description }}
                    </dd>
                </div>
                <!-- Debug Info -->
                <!-- @php
                    \Log::info('Quote Fields:', ['fields' => $quote->quote_fields]);
                    \Log::info('Service Quote Fields:', ['fields' => $quote->service->quoteFields->toArray()]);
                @endphp -->

                @if($quote->quote_fields)
                    <div class="col-span-2">
                        <dt class="text-sm font-medium text-gray-500 mb-2">Additional Information</dt>
                        <dd class="mt-1">
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                <dl class="grid grid-cols-1 gap-3">
                                    @foreach($quote->service->quoteFields->sortBy('order') as $field)
                                        @if(isset($quote->quote_fields[$field->id]))
                                            <div>
                                                <dt class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                                    {{ $field->label }}
                                                </dt>
                                                <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                                    @if(is_array($quote->quote_fields[$field->id]))
                                                        <ul class="list-disc list-inside">
                                                            @foreach($quote->quote_fields[$field->id] as $item)
                                                                <li>{{ $item }}</li>
                                                            @endforeach
                                                        </ul>
                                                    @else
                                                        {{ $quote->quote_fields[$field->id] }}
                                                    @endif
                                                </dd>
                                            </div>
                                        @endif
                                    @endforeach
                                </dl>
                            </div>
                        </dd>
                    </div>
                @endif
            </dl>

            <!-- Status and Assignment Section -->
            <div class="mt-6 border-t pt-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                        <select id="status" 
                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                            <option value="">Select Status</option>
                            <option value="pending" {{ $quote->status === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="reviewed" {{ $quote->status === 'reviewed' ? 'selected' : '' }}>Reviewed</option>
                            <option value="accepted" {{ $quote->status === 'accepted' ? 'selected' : '' }}>Accepted</option>
                            <option value="rejected" {{ $quote->status === 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Assign To</label>
                        <select id="assignedTo" 
                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                            <option value="">Select Assignee</option>
                            @foreach($admins as $admin)
                                <option value="{{ $admin->id }}" 
                                        {{ $quote->assigned_to === $admin->id ? 'selected' : '' }}>
                                    {{ $admin->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Messages Section -->
        <div class="bg-white rounded-lg shadow-md dark:bg-gray-800 p-6">
            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4">
                Messages
            </h3>
            <div class="flex flex-col space-y-4 max-h-96 overflow-y-auto mb-4" id="messages">
                @foreach($quote->messages->sortBy('created_at') as $message)
                    <div class="flex gap-4 {{ $message->sender_type === 'user' ? 'flex-row-reverse' : '' }}">
                        <div class="flex-shrink-0">
                            <div class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center">
                                @if($message->sender_type === 'admin')
                                    {{ substr($message->user->name, 0, 1) }}
                                @else
                                    {{ substr($message->user->first_name, 0, 1) }}
                                @endif
                            </div>
                        </div>
                        <div class="flex-1 {{ $message->sender_type === 'user' ? 'bg-blue-100' : 'bg-gray-100' }} rounded-lg p-4">
                            <div class="text-sm text-gray-600">
                                @if($message->sender_type === 'admin')
                                    {{ $message->user->name }}
                                @else
                                    {{ $message->user->first_name }} {{ $message->user->last_name }}
                                @endif
                            </div>
                            <div class="mt-1">
                                {{ $message->message }}
                            </div>
                            <div class="text-xs text-gray-500 mt-1">
                                {{ $message->created_at->diffForHumans() }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <form id="messageForm" class="mt-4">
                @csrf
                <textarea id="message" 
                          rows="3"
                          class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                          placeholder="Type your message..."></textarea>
                <button type="submit" 
                        class="mt-2 w-full inline-flex justify-center items-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50">
                    <svg id="loadingIcon" class="hidden w-4 h-4 mr-2 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span id="buttonText">Send Message</span>
                </button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
let isLoadingMessages = false;
let lastMessageId = null;

// Message template function
function createMessageElement(message) {
    const isAdmin = message.sender_type === 'admin';
    const senderName = message.sender_name || (isAdmin ? 'Admin' : 'User');
    const initial = senderName.charAt(0);

    return `
        <div class="flex gap-4 ${!isAdmin ? 'flex-row-reverse' : ''}">
            <div class="flex-shrink-0">
                <div class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center">
                    ${initial}
                </div>
            </div>
            <div class="flex-1 ${!isAdmin ? 'bg-blue-100' : 'bg-gray-100'} rounded-lg p-4">
                <div class="text-sm text-gray-600">
                    ${senderName}
                </div>
                <div class="mt-1">${message.message}</div>
                <div class="text-xs text-gray-500 mt-1">${message.created_at}</div>
            </div>
        </div>
    `;
}

// Load messages
async function loadMessages() {
    if (isLoadingMessages) return;
    isLoadingMessages = true;

    try {
        const response = await fetch('{{ route('admin.quotes.messages', $quote) }}');
        const messages = await response.json();
        
        const container = document.getElementById('messages');
        container.innerHTML = messages.map(message => createMessageElement(message)).join('');
        
        // Scroll to bottom
        container.scrollTop = container.scrollHeight;
    } catch (error) {
        console.error('Failed to load messages:', error);
    } finally {
        isLoadingMessages = false;
    }
}

// Handle message form submission
document.getElementById('messageForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const messageInput = document.getElementById('message');
    const message = messageInput.value.trim();
    
    if (!message) return;

    // Disable submit button while sending
    const submitButton = this.querySelector('button[type="submit"]');
    const loadingIcon = document.getElementById('loadingIcon');
    const buttonText = document.getElementById('buttonText');
    
    submitButton.disabled = true;
    loadingIcon.classList.remove('hidden');
    buttonText.textContent = 'Sending...';

    try {
        const response = await fetch(`{{ route('admin.quotes.messages.send', $quote) }}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ message })
        });

        const data = await response.json();
        if (data.success) {
            messageInput.value = '';
            // Reload messages after sending
            await loadMessages();
        }
    } catch (error) {
        showNotification('Error', 'Failed to send message', 'error');
    } finally {
        submitButton.disabled = false;
        loadingIcon.classList.add('hidden');
        buttonText.textContent = 'Send Message';
    }
});

// Load messages on page load
loadMessages();

// Poll for new messages every 5 seconds
setInterval(loadMessages, 5000);
</script>
@endpush
@endsection 