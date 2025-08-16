<!-- File Details Modal -->
<div class="modal fade" id="fileDetailsModal" tabindex="-1" aria-labelledby="fileDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="fileDetailsModalLabel">File Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="file-details-container">
                    <!-- File details will be loaded here -->
                    <div class="text-center">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading file details...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-danger" onclick="deleteCurrentFile()" id="delete-file-btn" style="display: none;">Delete File</button>
                <button type="button" class="btn btn-primary" onclick="downloadCurrentFile()" id="download-file-btn" style="display: none;">Download</button>
            </div>
        </div>
    </div>
</div>

<script>
let currentFileId = null;

// View file details
function viewFile(fileId) {
    currentFileId = fileId;
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('fileDetailsModal'));
    modal.show();
    
    // Load file details
    loadFileDetails(fileId);
}

// Load file details
function loadFileDetails(fileId) {
    const container = document.getElementById('file-details-container');
    
    // Show loading state
    container.innerHTML = `
        <div class="text-center">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading file details...</p>
        </div>
    `;
    
    fetch(`/admin/file-management/${fileId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderFileDetails(data.data);
            } else {
                container.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        Failed to load file details: ${data.message || 'Unknown error'}
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            container.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    An error occurred while loading file details
                </div>
            `;
        });
}

// Render file details
function renderFileDetails(file) {
    const container = document.getElementById('file-details-container');
    
    // Show/hide action buttons based on file status
    const deleteBtn = document.getElementById('delete-file-btn');
    const downloadBtn = document.getElementById('download-file-btn');
    
    if (file.status === 'active') {
        downloadBtn.style.display = 'inline-block';
        deleteBtn.style.display = 'inline-block';
    } else {
        downloadBtn.style.display = 'none';
        deleteBtn.style.display = 'inline-block';
    }
    
    // Format file size
    const fileSize = formatBytes(file.file_size || 0);
    
    // Format dates
    const createdAt = new Date(file.created_at).toLocaleString();
    const updatedAt = new Date(file.updated_at).toLocaleString();
    const expiresAt = file.expires_at ? new Date(file.expires_at).toLocaleString() : 'Never';
    
    // Get file type icon
    const fileTypeIcon = getFileTypeIcon(file.file_extension);
    
    container.innerHTML = `
        <div class="row">
            <div class="col-md-4 text-center">
                <div class="file-preview mb-3">
                    ${fileTypeIcon}
                    <h6 class="mt-2">${file.original_name}</h6>
                    <small class="text-muted">${file.file_extension?.toUpperCase() || 'Unknown'}</small>
                </div>
            </div>
            <div class="col-md-8">
                <div class="row">
                    <div class="col-md-6">
                        <h6>File Information</h6>
                        <table class="table table-sm">
                            <tr>
                                <td><strong>Size:</strong></td>
                                <td>${fileSize}</td>
                            </tr>
                            <tr>
                                <td><strong>Type:</strong></td>
                                <td>${file.mime_type || 'Unknown'}</td>
                            </tr>
                            <tr>
                                <td><strong>Status:</strong></td>
                                <td>
                                    <span class="badge bg-${file.status === 'active' ? 'success' : file.status === 'expired' ? 'warning' : 'danger'}">
                                        ${file.status}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Downloads:</strong></td>
                                <td>${file.download_count || 0}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6>Timestamps</h6>
                        <table class="table table-sm">
                            <tr>
                                <td><strong>Created:</strong></td>
                                <td>${createdAt}</td>
                            </tr>
                            <tr>
                                <td><strong>Updated:</strong></td>
                                <td>${updatedAt}</td>
                            </tr>
                            <tr>
                                <td><strong>Expires:</strong></td>
                                <td>${expiresAt}</td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <div class="row mt-3">
                    <div class="col-12">
                        <h6>User Information</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Sender:</strong><br>
                                <small class="text-muted">
                                    ${file.sender?.name || 'Unknown'} (${file.sender?.email || 'No email'})
                                </small>
                            </div>
                            <div class="col-md-6">
                                <strong>Receiver:</strong><br>
                                <small class="text-muted">
                                    ${file.receiver?.name || 'Unknown'} (${file.receiver?.email || 'No email'})
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
                
                ${file.description ? `
                <div class="row mt-3">
                    <div class="col-12">
                        <h6>Description</h6>
                        <p class="text-muted">${file.description}</p>
                    </div>
                </div>
                ` : ''}
                
                ${file.category ? `
                <div class="row mt-3">
                    <div class="col-12">
                        <h6>Category</h6>
                        <span class="badge bg-info">${file.category.name}</span>
                        ${file.category.description ? `<br><small class="text-muted">${file.category.description}</small>` : ''}
                    </div>
                </div>
                ` : ''}
            </div>
        </div>
    `;
}

// Download current file
function downloadCurrentFile() {
    if (!currentFileId) return;
    
    // Create a temporary download link
    const link = document.createElement('a');
    link.href = `/admin/file-management/${currentFileId}/download`;
    link.download = '';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// Delete current file
function deleteCurrentFile() {
    if (!currentFileId) return;
    
    if (confirm('Are you sure you want to delete this file? This action cannot be undone.')) {
        fetch(`/admin/file-management/files/${currentFileId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (typeof toastr !== 'undefined') {
                    toastr.success('File deleted successfully');
                } else {
                    alert('File deleted successfully');
                }
                
                // Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('fileDetailsModal'));
                if (modal) {
                    modal.hide();
                }
                
                // Reload files if on files tab
                if (window.FileManagementSystem && window.FileManagementSystem.loadFiles) {
                    window.FileManagementSystem.loadFiles();
                }
            } else {
                if (typeof toastr !== 'undefined') {
                    toastr.error(data.message || 'Failed to delete file');
                } else {
                    alert(data.message || 'Failed to delete file');
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (typeof toastr !== 'undefined') {
                toastr.error('An error occurred while deleting file');
            } else {
                alert('An error occurred while deleting file');
            }
        });
    }
}

// Helper function to format bytes
function formatBytes(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// Helper function to get file type icon
function getFileTypeIcon(extension) {
    const ext = extension?.toLowerCase();
    
    if (['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'].includes(ext)) {
        return '<i class="fas fa-image fa-3x text-primary"></i>';
    } else if (['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm'].includes(ext)) {
        return '<i class="fas fa-video fa-3x text-danger"></i>';
    } else if (['pdf'].includes(ext)) {
        return '<i class="fas fa-file-pdf fa-3x text-danger"></i>';
    } else if (['doc', 'docx'].includes(ext)) {
        return '<i class="fas fa-file-word fa-3x text-primary"></i>';
    } else if (['xls', 'xlsx'].includes(ext)) {
        return '<i class="fas fa-file-excel fa-3x text-success"></i>';
    } else if (['ppt', 'pptx'].includes(ext)) {
        return '<i class="fas fa-file-powerpoint fa-3x text-warning"></i>';
    } else if (['zip', 'rar', '7z'].includes(ext)) {
        return '<i class="fas fa-file-archive fa-3x text-secondary"></i>';
    } else {
        return '<i class="fas fa-file fa-3x text-muted"></i>';
    }
}

// Make functions globally available
window.viewFile = viewFile;
window.downloadCurrentFile = downloadCurrentFile;
window.deleteCurrentFile = deleteCurrentFile;
</script>
