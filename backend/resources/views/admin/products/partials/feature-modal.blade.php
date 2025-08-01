<div id="featureModal" class="fixed inset-0 z-50 overflow-y-auto hidden">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full dark:bg-gray-800">
            <form id="featureForm" onsubmit="handleFeatureSubmit(event)" class="p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                    Add Feature
                </h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Feature Description
                        </label>
                        <input type="text" 
                               name="feature" 
                               required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>

                <div class="mt-5 sm:mt-6 flex justify-end gap-2">
                    <button type="button" 
                            onclick="closeFeatureModal()"
                            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center gap-2">
                        <span id="featureSubmitButtonText">Add Feature</span>
                        <span id="featureLoadingSpinner" class="hidden">
                            <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
const featureModal = document.getElementById('featureModal');
const featureForm = document.getElementById('featureForm');
const featureSubmitButton = featureForm.querySelector('button[type="submit"]');
const featureLoadingSpinner = document.getElementById('featureLoadingSpinner');
const featureSubmitButtonText = document.getElementById('featureSubmitButtonText');

function openFeatureModal() {
    featureForm.reset();
    featureModal.classList.remove('hidden');
}

function closeFeatureModal() {
    featureModal.classList.add('hidden');
}

async function handleFeatureSubmit(e) {
    e.preventDefault();
    
    featureSubmitButton.disabled = true;
    featureLoadingSpinner.classList.remove('hidden');
    featureSubmitButtonText.textContent = 'Adding...';
    
    const formData = new FormData(featureForm);
    
    try {
        const response = await fetch('{{ route('admin.products.features.store', $product) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify(Object.fromEntries(formData))
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.message || `HTTP error! status: ${response.status}`);
        }

        if (data.success) {
            showNotification('Success', data.message);
            window.location.reload();
        } else {
            throw new Error(data.message || 'Something went wrong');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Error', error.message, 'error');
    } finally {
        featureSubmitButton.disabled = false;
        featureLoadingSpinner.classList.add('hidden');
        featureSubmitButtonText.textContent = 'Add Feature';
    }
}

async function deleteFeature(featureId) {
    if (!confirm('Are you sure you want to delete this feature?')) return;

    try {
        const response = await fetch(`{{ url('admin/products/features') }}/${featureId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.message || `HTTP error! status: ${response.status}`);
        }

        if (data.success) {
            showNotification('Success', data.message);
            window.location.reload();
        } else {
            throw new Error(data.message || 'Something went wrong');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Error', error.message, 'error');
    }
}

// Close modal when clicking outside
featureModal.addEventListener('click', function(e) {
    if (e.target === featureModal) {
        closeFeatureModal();
    }
});
</script>
@endpush 