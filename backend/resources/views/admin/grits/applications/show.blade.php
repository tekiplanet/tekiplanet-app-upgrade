@extends('admin.layouts.app')

@section('content')
<div class="container px-6 mx-auto">
    <div class="flex justify-between items-center mb-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.hustles.applications.index', $hustle) }}" 
               class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
                    Application Details
                </h2>
                <p class="text-sm text-gray-500">{{ $hustle->title }}</p>
            </div>
        </div>
        @if($application->status === 'pending')
            <div class="flex gap-2">
                <button onclick="updateApplicationStatus(
                    '{{ route('admin.hustles.applications.update-status', [$hustle, $application]) }}',
                    'approved',
                    'Approve'
                )" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                    Approve Application
                </button>
                <button onclick="updateApplicationStatus(
                    '{{ route('admin.hustles.applications.update-status', [$hustle, $application]) }}',
                    'rejected',
                    'Reject'
                )" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                    Reject Application
                </button>
            </div>
        @endif
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Application Information -->
        <div class="bg-white rounded-lg shadow-md dark:bg-gray-800 p-6">
            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4">
                Application Information
            </h3>
            <div class="space-y-4">
                <div>
                    <label class="text-sm text-gray-500 dark:text-gray-400">Status</label>
                    <span class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                        {{ $application->status === 'approved' ? 'bg-green-100 text-green-800' : 
                           ($application->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                           ($application->status === 'rejected' ? 'bg-red-100 text-red-800' : 
                            'bg-gray-100 text-gray-800')) }}">
                        {{ ucfirst($application->status) }}
                    </span>
                </div>
                <div>
                    <label class="text-sm text-gray-500 dark:text-gray-400">Applied At</label>
                    <p class="text-gray-900 dark:text-gray-100">
                        {{ $application->created_at->format('M d, Y H:i') }}
                    </p>
                </div>
                @if($application->status !== 'pending')
                    <div>
                        <label class="text-sm text-gray-500 dark:text-gray-400">Status Updated At</label>
                        <p class="text-gray-900 dark:text-gray-100">
                            {{ $application->updated_at->format('M d, Y H:i') }}
                        </p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Professional Information -->
        <div class="bg-white rounded-lg shadow-md dark:bg-gray-800 p-6">
            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4">
                Professional Information
            </h3>
            <div class="space-y-4">
                <div class="flex items-center gap-4">
                    <img class="h-16 w-16 rounded-full" 
                         src="{{ $application->professional->user->avatar ?? 'https://ui-avatars.com/api/?name='.urlencode($application->professional->user->name) }}" 
                         alt="{{ $application->professional->user->name }}">
                    <div>
                        <h4 class="font-medium text-gray-900 dark:text-gray-100">
                            {{ $application->professional->user->name }}
                        </h4>
                        <p class="text-sm text-gray-500">
                            {{ $application->professional->user->email }}
                        </p>
                    </div>
                </div>
                <div>
                    <label class="text-sm text-gray-500 dark:text-gray-400">Category</label>
                    <p class="text-gray-900 dark:text-gray-100">
                        {{ $application->professional->category->name }}
                    </p>
                </div>
                <div>
                    <label class="text-sm text-gray-500 dark:text-gray-400">Phone</label>
                    <p class="text-gray-900 dark:text-gray-100">
                        {{ $application->professional->user->phone }}
                    </p>
                </div>
                <div>
                    <a href="{{ route('admin.professionals.show', $application->professional) }}" 
                       class="text-blue-600 hover:text-blue-900">
                        View Professional Profile
                    </a>
                </div>
            </div>
        </div>

        @if($application->status === 'approved')
            <!-- Hustle Status Management -->
            <div class="bg-white rounded-lg shadow-md dark:bg-gray-800 p-6">
                <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4">
                    Hustle Status
                </h3>
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Current Status:</span>
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            {{ $hustle->status === 'approved' ? 'bg-yellow-100 text-yellow-800' : 
                               ($hustle->status === 'in_progress' ? 'bg-blue-100 text-blue-800' : 
                               ($hustle->status === 'completed' ? 'bg-green-100 text-green-800' : 
                                'bg-gray-100 text-gray-800')) }}">
                            {{ ucfirst($hustle->status) }}
                        </span>
                    </div>

                    @if($hustle->status === 'approved')
                        <button onclick="updateHustleStatus('{{ route('admin.hustles.update-status', $hustle) }}', 'in_progress')" 
                                class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            Mark as In Progress
                        </button>
                    @endif
                </div>
            </div>

            <!-- Payments Section -->
            @if($hustle->status === 'in_progress')
                <div class="bg-white rounded-lg shadow-md dark:bg-gray-800 p-6">
                    <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4">
                        Payments
                    </h3>
                    <div class="space-y-6">
                        @foreach($hustle->payments as $payment)
                            <div class="border-b pb-4 last:border-0 last:pb-0">
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <h4 class="font-medium text-gray-900 dark:text-gray-100">
                                            {{ ucfirst($payment->payment_type) }} Payment 
                                            ({{ $payment->payment_type === 'initial' ? '40%' : '60%' }})
                                        </h4>
                                        <p class="text-sm text-gray-500">
                                            Amount: ₦{{ number_format($payment->amount, 2) }}
                                        </p>
                                    </div>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        {{ $payment->status === 'completed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                        {{ ucfirst($payment->status) }}
                                    </span>
                                </div>

                                @if($payment->status === 'pending')
                                    <button onclick="updatePaymentStatus(
                                        '{{ route('admin.hustles.payments.update-status', [$hustle, $payment]) }}',
                                        'completed'
                                    )" class="text-sm text-green-600 hover:text-green-900">
                                        Mark as Completed
                                    </button>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @endif
    </div>

    @if($application->status === 'approved')
        <!-- Payment Information -->
        <div class="mt-6 bg-white rounded-lg shadow-md dark:bg-gray-800 p-6">
            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4">
                Payment Information
            </h3>
            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="text-sm text-gray-500 dark:text-gray-400">Total Budget</label>
                        <p class="text-gray-900 dark:text-gray-100">
                            ₦{{ number_format($hustle->budget, 2) }}
                        </p>
                    </div>
                    <div>
                        <label class="text-sm text-gray-500 dark:text-gray-400">Initial Payment</label>
                        <p class="text-gray-900 dark:text-gray-100">
                            {{ $hustle->initial_payment_released ? 'Released' : 'Pending' }}
                        </p>
                    </div>
                    <div>
                        <label class="text-sm text-gray-500 dark:text-gray-400">Final Payment</label>
                        <p class="text-gray-900 dark:text-gray-100">
                            {{ $hustle->final_payment_released ? 'Released' : 'Pending' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chat Section -->
        <div class="mt-6 bg-white rounded-lg shadow-md dark:bg-gray-800 p-6">
            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4">
                Messages
            </h3>
            
            <!-- Messages Container -->
            <div id="messages-container" class="space-y-4 h-96 overflow-y-auto mb-4 p-4 bg-gray-50 dark:bg-gray-900 rounded-lg">
                <!-- Messages will be loaded here -->
            </div>

            <!-- Message Input -->
            <div class="flex gap-2">
                <input type="text" 
                       id="message-input"
                       class="flex-1 rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500"
                       placeholder="Type your message...">
                <button onclick="sendMessage()"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
                        id="send-message-btn">
                    Send
                </button>
            </div>
        </div>

        @push('scripts')
        <script>
        let isLoadingMessages = false;
        let lastMessageId = null;

        // Message template function
        function createMessageElement(message) {
            const isAdmin = message.sender_type === 'admin';
            return `
                <div class="flex ${isAdmin ? 'justify-end' : 'justify-start'}">
                    <div class="flex items-start gap-2 max-w-[80%] ${isAdmin ? 'flex-row-reverse' : ''}">
                        <img class="w-8 h-8 rounded-full" 
                             src="${message.sender_avatar || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(message.sender_name)}" 
                             alt="${message.sender_name}">
                        <div>
                            <div class="flex items-center gap-2 ${isAdmin ? 'flex-row-reverse' : ''}">
                                <span class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                    ${message.sender_name}
                                </span>
                                <span class="text-xs text-gray-500">
                                    ${message.created_at}
                                </span>
                            </div>
                            <div class="mt-1 p-3 rounded-lg ${isAdmin ? 'bg-blue-100 dark:bg-blue-900' : 'bg-gray-100 dark:bg-gray-800'}">
                                ${message.message}
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        // Load messages
        async function loadMessages() {
            if (isLoadingMessages) return;
            isLoadingMessages = true;

            try {
                const response = await fetch('{{ route('admin.hustles.messages', $hustle) }}');
                const messages = await response.json();
                
                const container = document.getElementById('messages-container');
                container.innerHTML = messages.map(message => createMessageElement(message)).join('');
                
                // Scroll to bottom
                container.scrollTop = container.scrollHeight;
            } catch (error) {
                console.error('Failed to load messages:', error);
            } finally {
                isLoadingMessages = false;
            }
        }

        // Send message
        async function sendMessage() {
            const input = document.getElementById('message-input');
            const button = document.getElementById('send-message-btn');
            const message = input.value.trim();

            if (!message) return;

            button.disabled = true;
            input.disabled = true;

            try {
                const response = await fetch('{{ route('admin.hustles.messages.send', $hustle) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ message })
                });

                const data = await response.json();
                
                if (data.success) {
                    // Add new message to container
                    const container = document.getElementById('messages-container');
                    container.insertAdjacentHTML('beforeend', createMessageElement(data.message));
                    
                    // Clear input
                    input.value = '';
                    
                    // Scroll to bottom
                    container.scrollTop = container.scrollHeight;
                } else {
                    throw new Error(data.message);
                }
            } catch (error) {
                console.error('Failed to send message:', error);
                Swal.fire({
                    title: 'Error',
                    text: 'Failed to send message. Please try again.',
                    icon: 'error'
                });
            } finally {
                button.disabled = false;
                input.disabled = false;
                input.focus();
            }
        }

        // Handle enter key
        document.getElementById('message-input').addEventListener('keypress', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });

        // Load messages on page load
        loadMessages();

        // Poll for new messages every 5 seconds
        setInterval(loadMessages, 5000);
        </script>
        @endpush
    @endif
</div>
@endsection

@push('scripts')
    @include('admin.hustles.applications._status-update-script')
@endpush 