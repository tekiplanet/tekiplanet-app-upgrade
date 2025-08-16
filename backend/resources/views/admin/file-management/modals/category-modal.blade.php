<!-- Category Modal -->
<div id="categoryModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="categoryModalLabel" aria-hidden="true">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

        <!-- Modal panel -->
        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
            <!-- Header -->
            <div class="bg-white dark:bg-gray-800 px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white" id="categoryModalLabel">Add Category</h3>
                    <button type="button" onclick="closeCategoryModal()" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Form -->
            <form id="categoryForm" class="space-y-6">
                <div class="px-6 py-4">
                    <input type="hidden" id="category-id" name="id">
                    
                    <!-- Basic Information -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="category-name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Category Name *</label>
                            <input type="text" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm" id="category-name" name="name" required>
                        </div>
                        <div>
                            <label for="category-resource-type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Resource Type *</label>
                            <select class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm" id="category-resource-type" name="resource_type" required>
                                <option value="">Select Type</option>
                                <option value="image">Image</option>
                                <option value="video">Video</option>
                                <option value="raw">Document/Archive</option>
                            </select>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="mt-6">
                        <label for="category-description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                        <textarea class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm" id="category-description" name="description" rows="3" placeholder="Enter category description..."></textarea>
                    </div>

                    <!-- File Limits -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                        <div>
                            <label for="category-max-size" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Max File Size (MB) *</label>
                            <input type="number" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm" id="category-max-size" name="max_file_size" required min="1" step="0.1">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Maximum file size in megabytes</p>
                        </div>
                        <div>
                            <label for="category-sort-order" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Sort Order</label>
                            <input type="number" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm" id="category-sort-order" name="sort_order" value="0" min="0">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Lower numbers appear first</p>
                        </div>
                    </div>

                    <!-- Allowed Extensions -->
                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Allowed Extensions *</label>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <!-- Image Extensions -->
                            <div class="space-y-2">
                                <h4 class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Images</h4>
                                <div class="space-y-2">
                                    <label class="flex items-center">
                                        <input type="checkbox" value="jpg" id="ext-jpg" class="rounded border-gray-300 text-primary focus:ring-primary">
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">JPG</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox" value="jpeg" id="ext-jpeg" class="rounded border-gray-300 text-primary focus:ring-primary">
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">JPEG</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox" value="png" id="ext-png" class="rounded border-gray-300 text-primary focus:ring-primary">
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">PNG</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox" value="gif" id="ext-gif" class="rounded border-gray-300 text-primary focus:ring-primary">
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">GIF</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox" value="webp" id="ext-webp" class="rounded border-gray-300 text-primary focus:ring-primary">
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">WebP</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox" value="svg" id="ext-svg" class="rounded border-gray-300 text-primary focus:ring-primary">
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">SVG</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Video Extensions -->
                            <div class="space-y-2">
                                <h4 class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Videos</h4>
                                <div class="space-y-2">
                                    <label class="flex items-center">
                                        <input type="checkbox" value="mp4" id="ext-mp4" class="rounded border-gray-300 text-primary focus:ring-primary">
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">MP4</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox" value="avi" id="ext-avi" class="rounded border-gray-300 text-primary focus:ring-primary">
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">AVI</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox" value="mov" id="ext-mov" class="rounded border-gray-300 text-primary focus:ring-primary">
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">MOV</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox" value="wmv" id="ext-wmv" class="rounded border-gray-300 text-primary focus:ring-primary">
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">WMV</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox" value="flv" id="ext-flv" class="rounded border-gray-300 text-primary focus:ring-primary">
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">FLV</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox" value="webm" id="ext-webm" class="rounded border-gray-300 text-primary focus:ring-primary">
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">WebM</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Document Extensions -->
                            <div class="space-y-2">
                                <h4 class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Documents</h4>
                                <div class="space-y-2">
                                    <label class="flex items-center">
                                        <input type="checkbox" value="pdf" id="ext-pdf" class="rounded border-gray-300 text-primary focus:ring-primary">
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">PDF</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox" value="doc" id="ext-doc" class="rounded border-gray-300 text-primary focus:ring-primary">
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">DOC</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox" value="docx" id="ext-docx" class="rounded border-gray-300 text-primary focus:ring-primary">
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">DOCX</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox" value="xls" id="ext-xls" class="rounded border-gray-300 text-primary focus:ring-primary">
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">XLS</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox" value="xlsx" id="ext-xlsx" class="rounded border-gray-300 text-primary focus:ring-primary">
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">XLSX</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox" value="ppt" id="ext-ppt" class="rounded border-gray-300 text-primary focus:ring-primary">
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">PPT</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox" value="pptx" id="ext-pptx" class="rounded border-gray-300 text-primary focus:ring-primary">
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">PPTX</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Archive Extensions -->
                            <div class="space-y-2">
                                <h4 class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Archives</h4>
                                <div class="space-y-2">
                                    <label class="flex items-center">
                                        <input type="checkbox" value="zip" id="ext-zip" class="rounded border-gray-300 text-primary focus:ring-primary">
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">ZIP</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox" value="rar" id="ext-rar" class="rounded border-gray-300 text-primary focus:ring-primary">
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">RAR</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox" value="7z" id="ext-7z" class="rounded border-gray-300 text-primary focus:ring-primary">
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">7Z</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox" value="tar" id="ext-tar" class="rounded border-gray-300 text-primary focus:ring-primary">
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">TAR</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox" value="gz" id="ext-gz" class="rounded border-gray-300 text-primary focus:ring-primary">
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">GZ</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="mt-6">
                        <label class="flex items-center">
                            <input type="checkbox" id="category-is-active" name="is_active" class="rounded border-gray-300 text-primary focus:ring-primary" checked>
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Active Category</span>
                        </label>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Inactive categories cannot receive new files</p>
                    </div>
                </div>

                <!-- Footer -->
                <div class="bg-gray-50 dark:bg-gray-700 px-6 py-4 border-t border-gray-200 dark:border-gray-600 flex justify-end space-x-3">
                    <button type="button" onclick="closeCategoryModal()" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                        Cancel
                    </button>
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Save Category
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Category modal functions
function closeCategoryModal() {
    const modal = document.getElementById('categoryModal');
    modal.classList.add('hidden');
}

function setDefaultExtensions() {
    const resourceType = document.getElementById('category-resource-type').value;
    const checkboxes = document.querySelectorAll('input[type="checkbox"]');
    
    // Uncheck all first
    checkboxes.forEach(cb => cb.checked = false);
    
    // Set defaults based on resource type
    switch(resourceType) {
        case 'image':
            ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'].forEach(ext => {
                const checkbox = document.getElementById(`ext-${ext}`);
                if (checkbox) checkbox.checked = true;
            });
            break;
        case 'video':
            ['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm'].forEach(ext => {
                const checkbox = document.getElementById(`ext-${ext}`);
                if (checkbox) checkbox.checked = true;
            });
            break;
        case 'raw':
            ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'zip', 'rar', '7z'].forEach(ext => {
                const checkbox = document.getElementById(`ext-${ext}`);
                if (checkbox) checkbox.checked = true;
            });
            break;
    }
}

// Form submission
document.getElementById('categoryForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const extensions = [];
    
    // Collect checked extensions
    document.querySelectorAll('input[type="checkbox"]:checked').forEach(cb => {
        extensions.push(cb.value);
    });
    
    formData.append('extensions', JSON.stringify(extensions));
    
    // Submit form data
    console.log('Submitting category form:', Object.fromEntries(formData));
    
    // Close modal after successful submission
    closeCategoryModal();
});

// Close modal when clicking outside
document.getElementById('categoryModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeCategoryModal();
    }
});

// Add event listener for resource type change
document.addEventListener('DOMContentLoaded', function() {
    const resourceTypeSelect = document.getElementById('category-resource-type');
    if (resourceTypeSelect) {
        resourceTypeSelect.addEventListener('change', setDefaultExtensions);
    }
});
</script>
