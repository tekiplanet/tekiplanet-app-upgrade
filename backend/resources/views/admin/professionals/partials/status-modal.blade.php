<!-- Status Modal -->
<div id="confirmStatusModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen p-4">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black opacity-50"></div>

        <!-- Modal Content -->
        <div class="relative bg-white dark:bg-gray-800 rounded-lg w-full max-w-md">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4" id="confirmStatusMessage">
                    Are you sure you want to change the status?
                </h3>

                <!-- Reason field (shown only for deactivation) -->
                <div id="deactivationReasonContainer" class="mb-4 hidden">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Reason for Deactivation
                    </label>
                    <textarea id="deactivationReason" rows="3"
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                </div>

                <!-- Buttons -->
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="closeConfirmStatusModal()"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50">
                        Cancel
                    </button>
                    <button onclick="confirmToggleStatus()" id="confirmStatusButton"
                            class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white rounded-md shadow-sm">
                        <svg id="statusSpinner" class="w-4 h-4 mr-2 animate-spin hidden" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span id="confirmStatusButtonText">Confirm</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function toggleStatus() {
    const isCurrentlyActive = '{{ $professional->status }}' === 'active';
    const confirmStatusModal = document.getElementById('confirmStatusModal');
    const confirmStatusMessage = document.getElementById('confirmStatusMessage');
    const confirmStatusButton = document.getElementById('confirmStatusButton');
    const deactivationReasonContainer = document.getElementById('deactivationReasonContainer');

    confirmStatusMessage.textContent = `Are you sure you want to ${isCurrentlyActive ? 'deactivate' : 'activate'} this professional?`;
    confirmStatusButton.className = `px-4 py-2 text-white rounded-lg ${isCurrentlyActive ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700'}`;
    
    if (isCurrentlyActive) {
        deactivationReasonContainer.classList.remove('hidden');
    } else {
        deactivationReasonContainer.classList.add('hidden');
    }
    
    confirmStatusModal.classList.remove('hidden');
}

function closeConfirmStatusModal() {
    document.getElementById('confirmStatusModal').classList.add('hidden');
}

async function confirmToggleStatus() {
    if (isProcessing) return;

    const isCurrentlyActive = '{{ $professional->status }}' === 'active';
    const deactivationReason = document.getElementById('deactivationReason').value;

    if (isCurrentlyActive && !deactivationReason.trim()) {
        showNotification('Error', 'Please provide a reason for deactivation', 'error');
        return;
    }

    try {
        isProcessing = true;
        const statusSpinner = document.getElementById('statusSpinner');
        const confirmStatusButtonText = document.getElementById('confirmStatusButtonText');
        statusSpinner.classList.remove('hidden');
        confirmStatusButtonText.textContent = 'Processing...';

        const response = await fetch('{{ route("admin.professionals.toggle-status", $professional) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ reason: deactivationReason })
        });

        const data = await response.json();

        if (response.ok) {
            showNotification('Success', data.message);
            closeConfirmStatusModal();
            location.reload();
        } else {
            throw new Error(data.message || 'Something went wrong');
        }
    } catch (error) {
        showNotification('Error', error.message, 'error');
    } finally {
        isProcessing = false;
        statusSpinner.classList.add('hidden');
        confirmStatusButtonText.textContent = 'Confirm';
    }
}
</script>
@endpush 