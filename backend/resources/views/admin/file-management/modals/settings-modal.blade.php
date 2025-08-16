<!-- Settings Modal -->
<div id="settingsModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="settingsModalLabel" aria-hidden="true">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

        <!-- Modal panel -->
        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
            <!-- Header -->
            <div class="bg-white dark:bg-gray-800 px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white" id="settingsModalLabel">File System Settings</h3>
                    <button type="button" onclick="closeSettingsModal()" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="px-6 py-4">
                <div id="settings-container">
                    <!-- Loading State -->
                    <div class="text-center py-12">
                        <svg class="animate-spin mx-auto h-8 w-8 text-primary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Loading settings...</p>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="bg-gray-50 dark:bg-gray-700 px-6 py-4 border-t border-gray-200 dark:border-gray-600 flex justify-between">
                <div class="flex space-x-3">
                    <button type="button" onclick="resetSettingsToDefaults()" class="inline-flex items-center px-4 py-2 border border-yellow-300 dark:border-yellow-600 shadow-sm text-sm font-medium rounded-md text-yellow-700 dark:text-yellow-200 bg-yellow-50 dark:bg-yellow-900 hover:bg-yellow-100 dark:hover:bg-yellow-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Reset to Defaults
                    </button>
                    <button type="button" onclick="exportSettings()" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Export
                    </button>
                    <button type="button" onclick="importSettings()" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                        </svg>
                        Import
                    </button>
                </div>
                <div class="flex space-x-3">
                    <button type="button" onclick="closeSettingsModal()" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                        Cancel
                    </button>
                    <button type="button" onclick="saveAllSettings()" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Save All Changes
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Settings modal functions
function openSettingsModal() {
    const modal = document.getElementById('settingsModal');
    modal.classList.remove('hidden');
    
    // Load settings if FileManagementSystem is available
    if (window.FileManagementSystem && window.FileManagementSystem.loadSettings) {
        window.FileManagementSystem.loadSettings();
    }
}

function closeSettingsModal() {
    const modal = document.getElementById('settingsModal');
    modal.classList.add('hidden');
}

// Settings management functions
function resetSettingsToDefaults() {
    if (confirm('Are you sure you want to reset all settings to their default values? This action cannot be undone.')) {
        fetch('/admin/file-settings/reset-defaults', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (typeof toastr !== 'undefined') {
                    toastr.success('Settings reset to defaults successfully');
                } else {
                    alert('Settings reset to defaults successfully');
                }
                // Reload settings
                if (window.FileManagementSystem && window.FileManagementSystem.loadSettings) {
                    window.FileManagementSystem.loadSettings();
                }
            } else {
                if (typeof toastr !== 'undefined') {
                    toastr.error(data.message || 'Failed to reset settings');
                } else {
                    alert(data.message || 'Failed to reset settings');
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (typeof toastr !== 'undefined') {
                toastr.error('An error occurred while resetting settings');
            } else {
                alert('An error occurred while resetting settings');
            }
        });
    }
}

function saveAllSettings() {
    const settings = [];
    const inputs = document.querySelectorAll('#settings-container input, #settings-container select, #settings-container textarea');
    
    inputs.forEach(input => {
        if (input.id && input.id.startsWith('setting-')) {
            const settingKey = input.id.replace('setting-', '');
            let value = input.value;
            
            // Handle checkbox values
            if (input.type === 'checkbox') {
                value = input.checked;
            }
            
            // Handle number inputs
            if (input.type === 'number') {
                value = parseInt(value) || 0;
            }
            
            settings.push({
                key: settingKey,
                value: value
            });
        }
    });
    
    if (settings.length === 0) {
        if (typeof toastr !== 'undefined') {
            toastr.warning('No settings to save');
        } else {
            alert('No settings to save');
        }
        return;
    }
    
    // Save settings
    fetch('/admin/file-settings/update', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ settings: settings })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (typeof toastr !== 'undefined') {
                toastr.success('Settings saved successfully');
            } else {
                alert('Settings saved successfully');
            }
        } else {
            if (typeof toastr !== 'undefined') {
                toastr.error(data.message || 'Failed to save settings');
            } else {
                alert(data.message || 'Failed to save settings');
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (typeof toastr !== 'undefined') {
            toastr.error('An error occurred while saving settings');
        } else {
            alert('An error occurred while saving settings');
        }
    });
}

function exportSettings() {
    // Implementation for exporting settings
    console.log('Exporting settings...');
    
    // Create a download link for settings export
    const dataStr = "data:text/json;charset=utf-8," + encodeURIComponent(JSON.stringify({
        export_date: new Date().toISOString(),
        settings: "Settings data will be exported here"
    }, null, 2));
    
    const downloadAnchorNode = document.createElement('a');
    downloadAnchorNode.setAttribute("href", dataStr);
    downloadAnchorNode.setAttribute("download", "file-system-settings.json");
    document.body.appendChild(downloadAnchorNode);
    downloadAnchorNode.click();
    downloadAnchorNode.remove();
}

function importSettings() {
    // Create file input for importing settings
    const input = document.createElement('input');
    input.type = 'file';
    input.accept = '.json';
    input.onchange = function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                try {
                    const settings = JSON.parse(e.target.result);
                    console.log('Importing settings:', settings);
                    
                    // Here you would implement the actual import logic
                    if (typeof toastr !== 'undefined') {
                        toastr.success('Settings imported successfully');
                    } else {
                        alert('Settings imported successfully');
                    }
                } catch (error) {
                    console.error('Error parsing settings file:', error);
                    if (typeof toastr !== 'undefined') {
                        toastr.error('Invalid settings file format');
                    } else {
                        alert('Invalid settings file format');
                    }
                }
            };
            reader.readAsText(file);
        }
    };
    input.click();
}

// Close modal when clicking outside
document.getElementById('settingsModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeSettingsModal();
    }
});
</script>
