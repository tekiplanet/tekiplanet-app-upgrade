<div class="space-y-6">
    <!-- Settings Header -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">System Settings</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Configure file system behavior and limits</p>
            </div>
            <div class="flex space-x-3">
                <button type="button" onclick="resetSettingsToDefaults()" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Reset to Defaults
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

    <!-- Settings Container -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h4 class="text-md font-medium text-gray-900 dark:text-white">Configuration Options</h4>
        </div>
        <div class="p-6">
            <div id="settings-container" class="space-y-6">
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
    </div>

    <!-- Settings Groups -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- File Limits -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h4 class="text-md font-medium text-gray-900 dark:text-white mb-4">File Limits</h4>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Maximum File Size (MB)</label>
                    <input type="number" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm" placeholder="100">
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Global maximum file size limit</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Maximum Files per User</label>
                    <input type="number" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm" placeholder="1000">
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Maximum number of files a user can have</p>
                </div>
            </div>
        </div>

        <!-- Security Settings -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h4 class="text-md font-medium text-gray-900 dark:text-white mb-4">Security</h4>
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Virus Scanning</label>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Scan uploaded files for viruses</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" class="sr-only peer" checked>
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary dark:peer-focus:ring-primary rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary"></div>
                    </label>
                </div>
                <div class="flex items-center justify-between">
                    <div>
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">File Expiration</label>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Automatically expire old files</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary dark:peer-focus:ring-primary rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary"></div>
                    </label>
                </div>
            </div>
        </div>
    </div>

    <!-- Cloudinary Configuration -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
        <h4 class="text-md font-medium text-gray-900 dark:text-white mb-4">Cloudinary Configuration</h4>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Cloud Name</label>
                <input type="text" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm" placeholder="your-cloud-name">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">API Key</label>
                <input type="password" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm" placeholder="••••••••">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">API Secret</label>
                <input type="password" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm" placeholder="••••••••">
            </div>
        </div>
        <div class="mt-4">
            <button type="button" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                Test Connection
            </button>
        </div>
    </div>
</div>
