<div class="space-y-6">
    <!-- Files Header -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">File Management</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">View and manage all files in the system</p>
            </div>
            <div class="flex space-x-3">
                <button type="button" onclick="exportFiles()" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Export
                </button>
                <button type="button" onclick="bulkDelete()" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Bulk Delete
                </button>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <label for="status-filter" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                <select id="status-filter" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="expired">Expired</option>
                    <option value="deleted">Deleted</option>
                </select>
            </div>
            <div>
                <label for="category-filter" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Category</label>
                <select id="category-filter" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                    <option value="">All Categories</option>
                    <option value="images">Images</option>
                    <option value="videos">Videos</option>
                    <option value="documents">Documents</option>
                    <option value="archives">Archives</option>
                </select>
            </div>
            <div>
                <label for="date-from" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Date From</label>
                <input type="date" id="date-from" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
            </div>
            <div>
                <label for="date-to" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Date To</label>
                <input type="date" id="date-to" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
            </div>
        </div>
        <div class="mt-4 flex justify-between items-center">
            <div class="flex space-x-3">
                <button type="button" onclick="applyFilters()" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.207A1 1 0 013 6.5V4z" />
                    </svg>
                    Apply Filters
                </button>
                <button type="button" onclick="clearFilters()" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                    Clear
                </button>
            </div>
            <div class="text-sm text-gray-500 dark:text-gray-400">
                <span id="files-count">0</span> files found
            </div>
        </div>
    </div>

    <!-- Files Table -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <h4 class="text-md font-medium text-gray-900 dark:text-white">Files</h4>
                <div class="flex items-center space-x-2">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" id="select-all" class="sr-only peer">
                        <div class="w-4 h-4 text-primary bg-gray-100 border-gray-300 rounded focus:ring-primary dark:focus:ring-primary dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"></div>
                        <span class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">Select All</span>
                    </label>
                </div>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            <input type="checkbox" id="select-all-header" class="rounded border-gray-300 text-primary focus:ring-primary">
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">File</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Sender</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Receiver</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Category</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Size</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Downloads</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody id="files-table-body" class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <tr>
                        <td colspan="10" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                            <div class="flex items-center justify-center">
                                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-primary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Loading files...
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div id="files-pagination" class="flex items-center justify-center">
        <!-- Pagination will be rendered here -->
    </div>

    <!-- Empty State (hidden by default) -->
    <div id="files-empty-state" class="hidden text-center py-12">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No files found</h3>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Try adjusting your filters or upload some files.</p>
    </div>
</div>

<script>
// Filter functions
function applyFilters() {
    const filters = {
        status: document.getElementById('status-filter').value,
        category: document.getElementById('category-filter').value,
        date_from: document.getElementById('date-from').value,
        date_to: document.getElementById('date-to').value
    };
    
    if (window.FileManagementSystem) {
        window.FileManagementSystem.loadFiles(filters);
    }
}

function clearFilters() {
    document.getElementById('status-filter').value = '';
    document.getElementById('category-filter').value = '';
    document.getElementById('date-from').value = '';
    document.getElementById('date-to').value = '';
    
    if (window.FileManagementSystem) {
        window.FileManagementSystem.loadFiles();
    }
}

function exportFiles() {
    // Implementation for exporting files
    console.log('Exporting files...');
}

function bulkDelete() {
    const selectedFiles = document.querySelectorAll('input[name="file-select"]:checked');
    if (selectedFiles.length === 0) {
        alert('Please select files to delete');
        return;
    }
    
    if (confirm(`Are you sure you want to delete ${selectedFiles.length} selected files?`)) {
        // Implementation for bulk delete
        console.log('Bulk deleting files...');
    }
}

// Select all functionality
document.addEventListener('DOMContentLoaded', function() {
    const selectAllHeader = document.getElementById('select-all-header');
    const selectAllLabel = document.getElementById('select-all');
    
    if (selectAllHeader && selectAllLabel) {
        selectAllHeader.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('input[name="file-select"]');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            selectAllLabel.checked = this.checked;
        });
        
        selectAllLabel.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('input[name="file-select"]');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            selectAllHeader.checked = this.checked;
        });
    }
});
</script>
