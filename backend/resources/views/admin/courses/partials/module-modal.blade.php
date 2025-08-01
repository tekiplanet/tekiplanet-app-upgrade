<div id="moduleModal" class="fixed inset-0 z-50 overflow-y-auto hidden">
    <!-- Modal Backdrop -->
    <div 
        class="fixed inset-0 bg-black bg-opacity-50 z-40"
        onclick="closeModuleModal()"></div>

    <!-- Modal Content -->
    <div
        class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg max-w-lg w-full max-h-[90vh] overflow-y-auto" 
             >
            
            <!-- Header -->
            <div class="flex justify-between items-center p-4 border-b dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    <span id="modalTitle">Add New Module</span>
                </h3>
                <button onclick="closeModuleModal()"
                        class="text-gray-400 hover:text-gray-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Form -->
            <form id="moduleForm" onsubmit="event.preventDefault(); handleModuleSubmit(event);"
                  class="p-4 space-y-4">
                <!-- Title -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Module Title</label>
                    <input type="text" 
                           name="title"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                           required>
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                    <textarea name="description"
                              rows="3"
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                              required></textarea>
                </div>

                <!-- Duration -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Duration (hours)</label>
                    <input type="number" 
                           name="duration_hours"
                           min="1"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                           required>
                </div>

                <!-- Order -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Order</label>
                    <input type="number" 
                           name="order"
                           min="1"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                           required>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end gap-2 pt-4">
                    <button type="button" 
                            @click="open = false"
                            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                        <span id="modalAction">Create Module</span>
                        <span class="hidden loading-spinner">
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
let currentModuleId = null;
let isEditMode = false;

function openModuleModal(moduleId = null) {
    const modal = document.getElementById('moduleModal');
    const form = document.getElementById('moduleForm');
    const modalTitle = document.getElementById('modalTitle');
    
    currentModuleId = moduleId;
    isEditMode = !!moduleId;
    
    modalTitle.textContent = isEditMode ? 'Edit Module' : 'Add New Module';
    form.reset();
    
    if (moduleId) {
        loadModule(moduleId);
    }
    
    modal.classList.remove('hidden');
}

function closeModuleModal() {
    const modal = document.getElementById('moduleModal');
    modal.classList.add('hidden');
}

function loadModule(moduleId) {
    fetch(`/admin/courses/{{ $course->id }}/modules/${moduleId}/edit`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const form = document.getElementById('moduleForm');
                form.title.value = data.module.title;
                form.description.value = data.module.description;
                form.duration_hours.value = data.module.duration_hours;
                form.order.value = data.module.order;
            }
        })
        .catch(error => {
            console.error('Error loading module:', error);
            showNotification('Error', 'Failed to load module data', 'error');
        });
}

function handleModuleSubmit(event) {
    const submitButton = event.target.querySelector('button[type="submit"]');
    const loadingSpinner = submitButton.querySelector('.loading-spinner');
    
    submitButton.disabled = true;
    loadingSpinner.classList.remove('hidden');

    const formData = new FormData(event.target);
    const data = {
        course_id: '{{ $course->id }}',
        title: formData.get('title'),
        description: formData.get('description'),
        duration_hours: formData.get('duration_hours'),
        order: formData.get('order')
    };

    const url = isEditMode 
        ? `/admin/courses/{{ $course->id }}/modules/${currentModuleId}`
        : '/admin/courses/{{ $course->id }}/modules';

    const method = isEditMode ? 'PUT' : 'POST';

    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showNotification('Success', `Module ${isEditMode ? 'updated' : 'created'} successfully`);
            closeModuleModal();
            window.location.reload();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error', `Failed to ${isEditMode ? 'update' : 'create'} module`, 'error');
    })
    .finally(() => {
        submitButton.disabled = false;
        loadingSpinner.classList.add('hidden');
    });
}
</script>
@endpush 