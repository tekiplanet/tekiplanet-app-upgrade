<div id="imageModal" class="fixed inset-0 z-50 overflow-y-auto hidden">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full dark:bg-gray-800">
            <form id="imageForm" onsubmit="handleImageSubmit(event)" class="p-6" data-mode="add">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                    <span id="modalTitle">Add Image</span>
                </h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Image URL
                        </label>
                        <input type="url" 
                               name="image_url" 
                               required
                               placeholder="https://example.com/image.jpg"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <input type="hidden" name="image_id" id="imageId">
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" 
                               name="is_primary" 
                               id="is_primary"
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="is_primary" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                            Set as primary image
                        </label>
                    </div>
                </div>

                <div class="mt-5 sm:mt-6 flex justify-end gap-2">
                    <button type="button" 
                            onclick="closeImageModal()"
                            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center gap-2">
                        <span id="imageSubmitButtonText">Save Image</span>
                        <span id="imageLoadingSpinner" class="hidden">
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
const imageModal = document.getElementById('imageModal');
const imageForm = document.getElementById('imageForm');
const imageSubmitButton = imageForm.querySelector('button[type="submit"]');
const imageLoadingSpinner = document.getElementById('imageLoadingSpinner');
const imageSubmitButtonText = document.getElementById('imageSubmitButtonText');

function openImageModal(image = null) {
    imageForm.reset();
    if (image) {
        imageForm.dataset.mode = 'edit';
        document.getElementById('modalTitle').textContent = 'Edit Image';
        document.getElementById('imageId').value = image.id;
        imageForm.elements.image_url.value = image.image_url;
        imageForm.elements.is_primary.checked = image.is_primary;
    } else {
        imageForm.dataset.mode = 'add';
        document.getElementById('modalTitle').textContent = 'Add Image';
        document.getElementById('imageId').value = '';
    }
    imageModal.classList.remove('hidden');
}

function closeImageModal() {
    imageModal.classList.add('hidden');
}

async function handleImageSubmit(e) {
    e.preventDefault();
    
    imageSubmitButton.disabled = true;
    imageLoadingSpinner.classList.remove('hidden');
    imageSubmitButtonText.textContent = imageForm.dataset.mode === 'edit' ? 'Updating...' : 'Adding...';
    
    const formData = new FormData(imageForm);
    const isEdit = imageForm.dataset.mode === 'edit';
    
    try {
        const url = isEdit 
            ? `{{ url('admin/products/images') }}/${formData.get('image_id')}`
            : '{{ route('admin.products.images.store', $product) }}';

        const response = await fetch(url, {
            method: isEdit ? 'PUT' : 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                image_url: formData.get('image_url'),
                is_primary: formData.get('is_primary') === 'on'
            })
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
        imageSubmitButton.disabled = false;
        imageLoadingSpinner.classList.add('hidden');
        imageSubmitButtonText.textContent = 'Add Image';
    }
}

async function setPrimaryImage(imageId) {
    try {
        const response = await fetch(`{{ url('admin/products/images') }}/${imageId}/set-primary`, {
            method: 'POST',
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

async function deleteImage(imageId) {
    if (!confirm('Are you sure you want to delete this image?')) return;

    try {
        const response = await fetch(`{{ url('admin/products/images') }}/${imageId}`, {
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
imageModal.addEventListener('click', function(e) {
    if (e.target === imageModal) {
        closeImageModal();
    }
});

// Add edit button click handler in show view
function editImage(imageId) {
    const image = {
        id: imageId,
        image_url: document.querySelector(`[data-image-id="${imageId}"]`).getAttribute('src'),
        is_primary: document.querySelector(`[data-image-id="${imageId}"]`).closest('.relative').querySelector('.bg-green-500') !== null
    };
    openImageModal(image);
}
</script>
@endpush 