<!-- File Details Modal -->
<div id="fileDetailsModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="fileDetailsModalLabel" aria-hidden="true">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

        <!-- Modal panel -->
        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
            <!-- Header -->
            <div class="bg-white dark:bg-gray-800 px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white" id="fileDetailsModalLabel">File Details</h3>
                    <button type="button" onclick="closeFileDetailsModal()" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="px-6 py-4">
                <div id="file-details-container">
                    <!-- Loading State -->
                    <div class="text-center py-12">
                        <svg class="animate-spin mx-auto h-8 w-8 text-primary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Loading file details...</p>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="bg-gray-50 dark:bg-gray-700 px-6 py-4 border-t border-gray-200 dark:border-gray-600 flex justify-between">
                <div class="flex space-x-3">
                    <button type="button" onclick="downloadFile()" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Download
                    </button>
                    <button type="button" onclick="shareFile()" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z" />
                        </svg>
                        Share
                    </button>
                </div>
                <div class="flex space-x-3">
                    <button type="button" onclick="closeFileDetailsModal()" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                        Close
                    </button>
                    <button type="button" onclick="deleteFile()" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Delete
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// File details modal functions
function openFileDetailsModal(fileId) {
    const modal = document.getElementById('fileDetailsModal');
    modal.classList.remove('hidden');
    
    // Load file details
    loadFileDetails(fileId);
}

function closeFileDetailsModal() {
    const modal = document.getElementById('fileDetailsModal');
    modal.classList.add('hidden');
}

function loadFileDetails(fileId) {
    const container = document.getElementById('file-details-container');
    
    // Show loading state
    container.innerHTML = `
        <div class="text-center py-12">
            <svg class="animate-spin mx-auto h-8 w-8 text-primary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Loading file details...</p>
        </div>
    `;
    
    // Fetch file details
    fetch(`/admin/file-management/${fileId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderFileDetails(data.data);
            } else {
                container.innerHTML = `
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Error loading file</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">${data.message || 'Failed to load file details'}</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error loading file details:', error);
            container.innerHTML = `
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Error loading file</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">An error occurred while loading file details</p>
                </div>
            `;
        });
}

function renderFileDetails(file) {
    const container = document.getElementById('file-details-container');
    
    container.innerHTML = `
        <div class="space-y-6">
            <!-- File Preview -->
            <div class="text-center">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-lg bg-gray-100 dark:bg-gray-700">
                    ${getFileIcon(file.extension, file.mime_type)}
                </div>
                <h4 class="mt-3 text-lg font-medium text-gray-900 dark:text-white">${file.original_name}</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">${file.file_name}</p>
            </div>

            <!-- File Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h5 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">File Information</h5>
                    <dl class="space-y-2">
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500 dark:text-gray-400">Size:</dt>
                            <dd class="text-sm text-gray-900 dark:text-white">${formatFileSize(file.file_size)}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500 dark:text-gray-400">Type:</dt>
                            <dd class="text-sm text-gray-900 dark:text-white">${file.mime_type}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500 dark:text-gray-400">Extension:</dt>
                            <dd class="text-sm text-gray-900 dark:text-white">${file.extension}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500 dark:text-gray-400">Category:</dt>
                            <dd class="text-sm text-gray-900 dark:text-white">${file.category?.name || 'Unknown'}</dd>
                        </div>
                    </dl>
                </div>

                <div>
                    <h5 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Status & Usage</h5>
                    <dl class="space-y-2">
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500 dark:text-gray-400">Status:</dt>
                            <dd>
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full ${getStatusColor(file.status)}">
                                    ${file.status}
                                </span>
                            </dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500 dark:text-gray-400">Downloads:</dt>
                            <dd class="text-sm text-gray-900 dark:text-white">${file.download_count}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500 dark:text-gray-400">Created:</dt>
                            <dd class="text-sm text-gray-900 dark:text-white">${new Date(file.created_at).toLocaleDateString()}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500 dark:text-gray-400">Expires:</dt>
                            <dd class="text-sm text-gray-900 dark:text-white">${file.expires_at ? new Date(file.expires_at).toLocaleDateString() : 'Never'}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- User Information -->
            <div>
                <h5 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">User Information</h5>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h6 class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Sender</h6>
                        <div class="mt-1 flex items-center">
                            <div class="flex-shrink-0 h-8 w-8">
                                <div class="h-8 w-8 rounded-full bg-gray-300 dark:bg-gray-600 flex items-center justify-center">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                        ${getInitials(file.sender?.name || 'Unknown')}
                                    </span>
                                </div>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">${file.sender?.name || 'Unknown'}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">${file.sender?.email || 'No email'}</p>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h6 class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Receiver</h6>
                        <div class="mt-1 flex items-center">
                            <div class="flex-shrink-0 h-8 w-8">
                                <div class="h-8 w-8 rounded-full bg-gray-300 dark:bg-gray-600 flex items-center justify-center">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                        ${getInitials(file.receiver?.name || 'Unknown')}
                                    </span>
                                </div>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">${file.receiver?.name || 'Unknown'}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">${file.receiver?.email || 'No email'}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cloudinary Information -->
            ${file.public_id ? `
            <div>
                <h5 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Cloud Storage</h5>
                <dl class="space-y-2">
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500 dark:text-gray-400">Public ID:</dt>
                        <dd class="text-sm text-gray-900 dark:text-white font-mono">${file.public_id}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500 dark:text-gray-400">Resource Type:</dt>
                        <dd class="text-sm text-gray-900 dark:text-white">${file.resource_type}</dd>
                    </div>
                </dl>
            </div>
            ` : ''}
        </div>
    `;
}

function getFileIcon(extension, mimeType) {
    if (mimeType && mimeType.startsWith('image/')) {
        return `<svg class="h-10 w-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>`;
    } else if (mimeType && mimeType.startsWith('video/')) {
        return `<svg class="h-10 w-10 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
        </svg>`;
    } else if (mimeType && mimeType.startsWith('application/pdf')) {
        return `<svg class="h-10 w-10 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>`;
    } else {
        return `<svg class="h-10 w-10 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
        </svg>`;
    }
}

function getStatusColor(status) {
    switch (status) {
        case 'active':
            return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200';
        case 'expired':
            return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200';
        case 'deleted':
            return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200';
        default:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200';
    }
}

function getInitials(name) {
    return name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function downloadFile() {
    // Implementation for downloading file
    console.log('Downloading file...');
}

function shareFile() {
    // Implementation for sharing file
    console.log('Sharing file...');
}

function deleteFile() {
    if (confirm('Are you sure you want to delete this file? This action cannot be undone.')) {
        // Implementation for deleting file
        console.log('Deleting file...');
        closeFileDetailsModal();
    }
}

// Close modal when clicking outside
document.getElementById('fileDetailsModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeFileDetailsModal();
    }
});
</script>
