@extends('admin.layouts.app')

@section('title', 'File Management System')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">File Management System</h1>
            <p class="mt-2 text-sm text-gray-700 dark:text-gray-300">
                Manage file categories, system settings, and monitor file usage across the platform
            </p>
        </div>
        <div class="mt-4 sm:mt-0">
            <button type="button" onclick="FileManagementSystem.openCategoryModal()" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Add Category
            </button>
        </div>
    </div>

    <!-- Stats Overview -->
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Total Files -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden rounded-lg shadow">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Total Files</dt>
                            <dd class="flex items-baseline">
                                <div class="text-2xl font-semibold text-gray-900 dark:text-white" id="total-files">0</div>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Files -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden rounded-lg shadow">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Active Files</dt>
                            <dd class="flex items-baseline">
                                <div class="text-2xl font-semibold text-gray-900 dark:text-white" id="active-files">0</div>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Storage -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden rounded-lg shadow">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Storage Used</dt>
                            <dd class="flex items-baseline">
                                <div class="text-2xl font-semibold text-gray-900 dark:text-white" id="total-storage">0 MB</div>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Downloads -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden rounded-lg shadow">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Total Downloads</dt>
                            <dd class="flex items-baseline">
                                <div class="text-2xl font-semibold text-gray-900 dark:text-white" id="total-downloads">0</div>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Tabs -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
        <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
                <button onclick="switchTab('overview')" id="overview-tab" class="tab-button whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm border-primary text-primary">
                    <svg class="h-5 w-5 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    Overview
                </button>
                <button onclick="switchTab('categories')" id="categories-tab" class="tab-button whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300">
                    <svg class="h-5 w-5 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z" />
                    </svg>
                    Categories
                </button>
                <button onclick="switchTab('settings')" id="settings-tab" class="tab-button whitespace-nowrap py-4 px-4 py-4 px-1 border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300">
                    <svg class="h-5 w-5 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Settings
                </button>
                <button onclick="switchTab('files')" id="files-tab" class="tab-button whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300">
                    <svg class="h-5 w-5 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                    Files
                </button>
            </nav>
        </div>

        <div class="p-6">
            <!-- Overview Tab -->
            <div id="overview-content" class="tab-content">
                @include('admin.file-management.partials.overview')
            </div>

            <!-- Categories Tab -->
            <div id="categories-content" class="tab-content hidden">
                @include('admin.file-management.partials.categories')
            </div>

            <!-- Settings Tab -->
            <div id="settings-content" class="tab-content hidden">
                @include('admin.file-management.partials.settings')
            </div>

            <!-- Files Tab -->
            <div id="files-content" class="tab-content hidden">
                @include('admin.file-management.partials.files')
            </div>
        </div>
    </div>
</div>

<!-- Category Modal -->
@include('admin.file-management.modals.category-modal')

<!-- Settings Modal -->
@include('admin.file-management.modals.settings-modal')

<!-- File Details Modal -->
@include('admin.file-management.modals.file-details-modal')
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize file management system
    window.FileManagementSystem = {
        // Load overview data
        loadOverview: function() {
            fetch('/admin/file-management/statistics')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Wait for charts to be initialized before updating
                        this.waitForChartsAndUpdate(data.data);
                    }
                })
                .catch(error => console.error('Error loading overview:', error));
        },

        // Wait for charts to be ready before updating
        waitForChartsAndUpdate: function(data) {
            const maxAttempts = 10;
            let attempts = 0;
            
            const checkCharts = () => {
                if (window.filesByCategoryChart && window.storageUsageChart) {
                    this.updateOverviewCharts(data);
                } else if (attempts < maxAttempts) {
                    attempts++;
                    setTimeout(checkCharts, 100);
                } else {
                    console.warn('Charts not ready after maximum attempts, updating without charts');
                    this.updateOverviewCharts(data);
                }
            };
            
            checkCharts();
        },

        // Load categories
        loadCategories: function() {
            console.log('Loading categories from /admin/file-categories/list');
            fetch('/admin/file-categories/list')
                .then(response => {
                    console.log('Response status:', response.status);
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        this.renderCategories(data.data);
                    } else {
                        console.error('Categories API returned success: false:', data);
                    }
                })
                .catch(error => {
                    console.error('Error loading categories:', error);
                    // Show error message to user
                    const tbody = document.getElementById('categories-table-body');
                    if (tbody) {
                        tbody.innerHTML = `
                            <tr>
                                <td colspan="7" class="px-6 py-4 text-center text-sm text-red-500">
                                    Error loading categories: ${error.message}
                                </td>
                            </tr>
                        `;
                    }
                });
        },

        // Load settings
        loadSettings: function() {
            fetch('/admin/file-settings/list')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        this.renderSettings(data.data);
                    }
                })
                .catch(error => console.error('Error loading settings:', error));
        },

        // Load files
        loadFiles: function(filters = {}) {
            const queryString = new URLSearchParams(filters).toString();
            fetch(`/admin/file-management/files?${queryString}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        this.renderFiles(data.data);
                    }
                })
                .catch(error => console.error('Error loading files:', error));
        },

        // Update overview charts
        updateOverviewCharts: function(data) {
            // Update statistics cards
            const totalFilesEl = document.getElementById('total-files');
            const activeFilesEl = document.getElementById('active-files');
            const totalStorageEl = document.getElementById('total-storage');
            const totalDownloadsEl = document.getElementById('total-downloads');
            
            if (totalFilesEl) totalFilesEl.textContent = data.total_files || 0;
            if (activeFilesEl) activeFilesEl.textContent = data.active_files || 0;
            if (totalStorageEl) totalStorageEl.textContent = this.formatBytes(data.total_storage_used || 0);
            if (totalDownloadsEl) totalDownloadsEl.textContent = data.total_downloads || 0;

            // Update charts if they exist and are properly initialized
            if (window.filesByCategoryChart && window.filesByCategoryChart.data && window.filesByCategoryChart.data.datasets) {
                try {
                    window.filesByCategoryChart.data.labels = data.files_by_category?.map(item => item.category?.name || 'Unknown') || [];
                    window.filesByCategoryChart.data.datasets[0].data = data.files_by_category?.map(item => item.count) || [];
                    window.filesByCategoryChart.update();
                } catch (error) {
                    console.warn('Error updating files by category chart:', error);
                }
            }
            
            if (window.storageUsageChart && window.storageUsageChart.data && window.storageUsageChart.data.datasets) {
                try {
                    window.storageUsageChart.data.labels = data.files_by_category?.map(item => item.category?.name || 'Unknown') || [];
                    window.storageUsageChart.data.datasets[0].data = data.files_by_category?.map(item => item.storage_used || 0) || [];
                    window.storageUsageChart.update();
                } catch (error) {
                    console.warn('Error updating storage usage chart:', error);
                }
            }
        },

        // Render categories
        renderCategories: function(categories) {
            const tbody = document.getElementById('categories-table-body');
            if (!tbody) return;

            // Debug: Log the categories data to see what we're receiving
            console.log('Categories data received:', categories);

            if (!categories || categories.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                            <div class="flex items-center justify-center">
                                <svg class="h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z" />
                                </svg>
                            </div>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No categories found</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by creating a new file category.</p>
                        </td>
                    </tr>
                `;
                return;
            }

            tbody.innerHTML = categories.map(category => {

                
                // Handle extensions display - check for allowed_extensions field from database
                let extensionsDisplay = '-';
                if (category.allowed_extensions) {
                    if (Array.isArray(category.allowed_extensions)) {
                        extensionsDisplay = category.allowed_extensions.join(', ');
                    } else if (typeof category.allowed_extensions === 'string') {
                        // Handle case where it might be a JSON string
                        try {
                            const parsed = JSON.parse(category.allowed_extensions);
                            if (Array.isArray(parsed)) {
                                extensionsDisplay = parsed.join(', ');
                            }
                        } catch (e) {
                            extensionsDisplay = category.allowed_extensions;
                        }
                    }
                }
                
                // Handle max size display - convert bytes to MB
                let maxSizeDisplay = '-';
                if (category.max_file_size) {
                    const sizeInMB = (category.max_file_size / (1024 * 1024)).toFixed(1);
                    maxSizeDisplay = sizeInMB + ' MB';
                }
                
                return `
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">${category.name || '-'}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">${category.description || '-'}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">${extensionsDisplay}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">${maxSizeDisplay}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full ${category.resource_type === 'image' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : category.resource_type === 'video' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200'}">
                                ${category.resource_type || 'raw'}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" class="sr-only peer" ${this.isCategoryActive(category.is_active) ? 'checked' : ''} onchange="FileManagementSystem.toggleCategoryStatus('${category.id}', this.checked)">
                                <div class="w-11 h-6 rounded-full transition-all duration-200 ease-in-out ${this.isCategoryActive(category.is_active) ? 'bg-primary' : 'bg-gray-200'} peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary dark:peer-focus:ring-primary peer dark:bg-gray-700">
                                    <div class="w-5 h-5 bg-white border border-gray-300 rounded-full transition-all duration-200 ease-in-out transform ${this.isCategoryActive(category.is_active) ? 'translate-x-5' : 'translate-x-0'}"></div>
                                </div>
                            </label>

                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <button onclick="FileManagementSystem.editCategory('${category.id}')" class="text-primary hover:text-primary-dark mr-3">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </button>
                            <button onclick="FileManagementSystem.deleteCategory('${category.id}')" class="text-red-600 hover:text-red-900">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </td>
                    </tr>
                `;
            }).join('');
        },

        // Render settings
        renderSettings: function(settings) {
            const container = document.getElementById('settings-container');
            if (!container) return;

            container.innerHTML = settings.map(setting => `
                <div class="border-b border-gray-200 dark:border-gray-700 py-4 last:border-b-0">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 items-center">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">${setting.setting_key.replace(/_/g, ' ').toUpperCase()}</label>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">${setting.description}</p>
                        </div>
                        <div class="lg:col-span-2">
                            ${this.renderSettingInput(setting)}
                        </div>
                    </div>
                </div>
            `).join('');
        },

        // Render setting input based on type
        renderSettingInput: function(setting) {
            switch (setting.setting_type) {
                case 'boolean':
                    return `
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" class="sr-only peer" id="setting-${setting.setting_key}" ${setting.typed_value ? 'checked' : ''}>
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary dark:peer-focus:ring-primary rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary"></div>
                        </label>
                    `;
                case 'integer':
                    return `<input type="number" class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm" id="setting-${setting.setting_key}" value="${setting.typed_value}" ${!setting.is_editable ? 'disabled' : ''}>`;
                default:
                    return `<input type="text" class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm" id="setting-${setting.setting_key}" value="${setting.typed_value}" ${!setting.is_editable ? 'disabled' : ''}>`;
            }
        },

        // Render files
        renderFiles: function(files) {
            const tbody = document.getElementById('files-table-body');
            if (!tbody) return;

            tbody.innerHTML = files.data.map(file => `
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10">
                                ${file.file_type_icon}
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">${file.original_name}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">${file.file_name}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">${file.sender?.name || 'Unknown'}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">${file.receiver?.name || 'Unknown'}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">${file.category?.name || 'Unknown'}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">${file.formatted_size}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full ${file.status === 'active' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : file.status === 'expired' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'}">
                            ${file.status}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">${file.download_count}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">${new Date(file.created_at).toLocaleDateString()}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <button onclick="FileManagementSystem.viewFile('${file.id}')" class="text-primary hover:text-primary-dark mr-3">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                        <button onclick="FileManagementSystem.deleteFile('${file.id}')" class="text-red-600 hover:text-red-900">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </td>
                </tr>
            `).join('');

            // Update pagination
            this.renderPagination(files);
        },

        // Render pagination
        renderPagination: function(files) {
            const pagination = document.getElementById('files-pagination');
            if (!pagination) return;

            // Simple pagination rendering
            let paginationHtml = '<nav class="flex items-center justify-between border-t border-gray-200 dark:border-gray-700 px-4 py-3 sm:px-6">';
            
            if (files.prev_page_url) {
                paginationHtml += `<a href="#" onclick="FileManagementSystem.loadFiles({page: ${files.current_page - 1}})" class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-400 dark:hover:bg-gray-700">Previous</a>`;
            }
            
            paginationHtml += `<span class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md dark:bg-gray-800 dark:border-gray-600 dark:text-gray-400">${files.current_page} of ${files.last_page}</span>`;
            
            if (files.next_page_url) {
                paginationHtml += `<a href="#" onclick="FileManagementSystem.loadFiles({page: ${files.current_page + 1}})" class="relative inline-flex items-center ml-3 inline-flex items-center px-4 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-400 dark:hover:bg-gray-700">Next</a>`;
            }
            
            paginationHtml += '</nav>';
            pagination.innerHTML = paginationHtml;
        },

        // Format bytes
        formatBytes: function(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        },

        // Check if category is active (handles different data types)
        isCategoryActive: function(isActive) {
            // Handle null/undefined
            if (isActive === null || isActive === undefined) {
                return false;
            }
            
            // Handle boolean true
            if (isActive === true) {
                return true;
            }
            
            // Handle numeric 1
            if (isActive === 1 || isActive === 1.0) {
                return true;
            }
            
            // Handle string '1'
            if (isActive === '1') {
                return true;
            }
            
            // Handle string 'true'
            if (isActive === 'true') {
                return true;
            }
            
            // Handle string 'on' (sometimes checkbox values)
            if (isActive === 'on') {
                return true;
            }
            
            return false;
        },

        // Toggle category status
        toggleCategoryStatus: function(id, status) {
            // Show loading state
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Updating...',
                    text: 'Please wait while we update the category status.',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
            }
            
            fetch(`/admin/file-categories/${id}/toggle-status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ is_active: status })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (typeof Swal !== 'undefined') {
                        // Close loading dialog first
                        Swal.close();
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: 'Category status updated successfully',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else if (typeof toastr !== 'undefined') {
                        toastr.success('Category status updated successfully');
                    } else {
                        alert('Category status updated successfully');
                    }
                } else {
                    if (typeof Swal !== 'undefined') {
                        // Close loading dialog first
                        Swal.close();
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Failed to update category status'
                        });
                    } else if (typeof toastr !== 'undefined') {
                        toastr.error('Failed to update category status');
                    } else {
                        alert('Failed to update category status');
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                if (typeof Swal !== 'undefined') {
                    // Close loading dialog first
                    Swal.close();
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'An error occurred while updating status'
                    });
                } else if (typeof toastr !== 'undefined') {
                    toastr.error('An error occurred');
                } else {
                    alert('An error occurred');
                }
            });
        },

        // Edit category
        editCategory: function(categoryId) {
            console.log('Editing category:', categoryId);
            // Open the category modal in edit mode
            this.openCategoryModal(categoryId);
        },

        // Delete category
        deleteCategory: function(categoryId) {
            console.log('Deleting category:', categoryId);
            
            // Show confirmation dialog using SweetAlert if available
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Are you sure?',
                    text: 'Are you sure you want to delete this category? This action cannot be undone.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        this.performCategoryDeletion(categoryId);
                    }
                });
            } else {
                // Fallback to regular confirm
                if (confirm('Are you sure you want to delete this category? This action cannot be undone.')) {
                    this.performCategoryDeletion(categoryId);
                }
            }
        },

        // Perform the actual category deletion
        performCategoryDeletion: function(categoryId) {
            fetch(`/admin/file-categories/${categoryId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: 'Category deleted successfully',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else if (typeof toastr !== 'undefined') {
                        toastr.success('Category deleted successfully');
                    } else {
                        alert('Category deleted successfully');
                    }
                    // Reload categories
                    this.loadCategories();
                } else {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: data.message || 'Failed to delete category'
                        });
                    } else if (typeof toastr !== 'undefined') {
                        toastr.error(data.message || 'Failed to delete category');
                    } else {
                        alert(data.message || 'Failed to delete category');
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'An error occurred while deleting the category'
                    });
                } else if (typeof toastr !== 'undefined') {
                    toastr.error('An error occurred while deleting the category');
                } else {
                    alert('An error occurred while deleting the category');
                }
            });
        },

        // View file details
        viewFile: function(fileId) {
            console.log('Viewing file:', fileId);
            // Implementation for viewing file details
            alert('View file functionality not yet implemented');
        },

        // Delete file
        deleteFile: function(fileId) {
            console.log('Deleting file:', fileId);
            
            // Show confirmation dialog using SweetAlert if available
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Are you sure?',
                    text: 'Are you sure you want to delete this file? This action cannot be undone.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        this.performFileDeletion(fileId);
                    }
                });
            } else {
                // Fallback to regular confirm
                if (confirm('Are you sure you want to delete this file? This action cannot be undone.')) {
                    this.performFileDeletion(fileId);
                }
            }
        },

        // Perform the actual file deletion
        performFileDeletion: function(fileId) {
            fetch(`/admin/file-management/files/${fileId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: 'File deleted successfully',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else if (typeof toastr !== 'undefined') {
                        toastr.success('File deleted successfully');
                    } else {
                        alert('File deleted successfully');
                    }
                    // Reload files
                    this.loadCategories();
                } else {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: data.message || 'Failed to delete file'
                        });
                    } else if (typeof toastr !== 'undefined') {
                        toastr.error(data.message || 'Failed to delete file');
                    } else {
                        alert(data.message || 'Failed to delete file');
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'An error occurred while deleting the file'
                    });
                } else if (typeof toastr !== 'undefined') {
                    toastr.error('An error occurred while deleting the file');
                } else {
                    alert('An error occurred while deleting the file');
                }
            });
        },

        // Open category modal
        openCategoryModal: function(categoryId = null) {
            const modal = document.getElementById('categoryModal');
            const modalLabel = document.getElementById('categoryModalLabel');
            const form = document.getElementById('categoryForm');
            
            if (categoryId) {
                // Edit mode
                modalLabel.textContent = 'Edit Category';
                this.loadCategoryData(categoryId);
            } else {
                // Add mode
                modalLabel.textContent = 'Add Category';
                form.reset();
                document.getElementById('category-id').value = '';
            }
            
            modal.classList.remove('hidden');
        },

        // Load category data for editing
        loadCategoryData: function(categoryId) {
            fetch(`/admin/file-categories/${categoryId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const category = data.data;
                        document.getElementById('category-id').value = category.id;
                        document.getElementById('category-name').value = category.name || '';
                        document.getElementById('category-description').value = category.description || '';
                        document.getElementById('category-resource-type').value = category.resource_type || 'raw';
                                                 // Convert bytes to MB for display in the form
                         const maxSizeInMB = category.max_file_size ? (category.max_file_size / (1024 * 1024)).toFixed(1) : '';
                         document.getElementById('category-max-size').value = maxSizeInMB;
                        document.getElementById('category-sort-order').value = category.sort_order || 0;
                        document.getElementById('category-is-active').checked = category.is_active || false;
                        
                        // Set extensions if available
                        if (category.allowed_extensions) {
                            this.setCategoryExtensions(category.allowed_extensions);
                        }
                    } else {
                        console.error('Failed to load category data:', data.message);
                        alert('Failed to load category data');
                    }
                })
                .catch(error => {
                    console.error('Error loading category data:', error);
                    alert('Error loading category data');
                });
        },

        // Set category extensions in the form
        setCategoryExtensions: function(extensions) {
            // Reset all checkboxes first
            document.querySelectorAll('input[type="checkbox"]').forEach(cb => {
                cb.checked = false;
            });
            
            // Check the ones that match the category extensions
            if (Array.isArray(extensions)) {
                extensions.forEach(ext => {
                    const checkbox = document.getElementById(`ext-${ext.toLowerCase()}`);
                    if (checkbox) checkbox.checked = true;
                });
            } else if (typeof extensions === 'string') {
                // Handle case where extensions might be a JSON string
                try {
                    const parsed = JSON.parse(extensions);
                    if (Array.isArray(parsed)) {
                        parsed.forEach(ext => {
                            const checkbox = document.getElementById(`ext-${ext.toLowerCase()}`);
                            if (checkbox) checkbox.checked = true;
                        });
                    }
                } catch (e) {
                    console.warn('Could not parse extensions:', extensions);
                }
            }
        },

        // Initialize the system
        init: function() {
            // Don't load overview immediately - wait for tab to be shown
            // this.loadOverview();
            
            // Load data when tabs are clicked
            document.getElementById('categories-tab').addEventListener('click', () => this.loadCategories());
            document.getElementById('settings-tab').addEventListener('click', () => this.loadSettings());
            document.getElementById('files-tab').addEventListener('click', () => this.loadFiles());
            
            // Set up form submission
            this.setupFormSubmission();
        },

        // Set up form submission
        setupFormSubmission: function() {
            const form = document.getElementById('categoryForm');
            if (form) {
                form.addEventListener('submit', (e) => {
                    e.preventDefault();
                    this.submitCategoryForm();
                });
            }
            
            // Set up resource type change handler
            this.setupResourceTypeHandler();
            
            // Set up modal click outside to close
            this.setupModalClickOutside();
        },

        // Set up resource type change handler
        setupResourceTypeHandler: function() {
            const resourceTypeSelect = document.getElementById('category-resource-type');
            if (resourceTypeSelect) {
                resourceTypeSelect.addEventListener('change', (e) => {
                    this.setDefaultExtensions(e.target.value);
                });
            }
        },

        // Set default extensions based on resource type
        setDefaultExtensions: function(resourceType) {
            // Uncheck all first
            document.querySelectorAll('input[type="checkbox"]').forEach(cb => {
                cb.checked = false;
            });
            
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
        },

        // Set up modal click outside to close
        setupModalClickOutside: function() {
            const modal = document.getElementById('categoryModal');
            if (modal) {
                modal.addEventListener('click', (e) => {
                    if (e.target === modal) {
                        this.closeCategoryModal();
                    }
                });
            }
        },

        // Submit category form
        submitCategoryForm: function() {
            const form = document.getElementById('categoryForm');
            const formData = new FormData(form);
            
            // Get selected extensions
            const selectedExtensions = [];
            document.querySelectorAll('input[type="checkbox"]:checked').forEach(cb => {
                selectedExtensions.push(cb.value);
            });
            
            // Prepare data
            const data = {
                id: formData.get('id') || null,
                name: formData.get('name'),
                description: formData.get('description'),
                resource_type: formData.get('resource_type'),
                max_file_size: Math.round(parseFloat(formData.get('max_file_size')) * 1024 * 1024), // Convert MB to bytes and ensure integer
                sort_order: parseInt(formData.get('sort_order')) || 0,
                is_active: formData.get('is_active') === 'on',
                allowed_extensions: selectedExtensions
            };
            
            // Validate required fields
            if (!data.name || !data.resource_type || !data.max_file_size || selectedExtensions.length === 0) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        text: 'Please fill in all required fields and select at least one file extension.'
                    });
                } else {
                    alert('Please fill in all required fields and select at least one file extension.');
                }
                return;
            }
            
            // Show loading state
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Saving...',
                    text: 'Please wait while we save the category.',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
            }
            
            // Determine if this is a create or update
            const url = data.id ? `/admin/file-categories/${data.id}` : '/admin/file-categories';
            const method = data.id ? 'PUT' : 'POST';
            
            fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(data)
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(errorData => {
                        throw new Error(JSON.stringify(errorData));
                    });
                }
                return response.json();
            })
            .then(result => {
                if (typeof Swal !== 'undefined') {
                    Swal.close();
                }
                
                if (result.success) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: data.id ? 'Category updated successfully' : 'Category created successfully',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        alert(data.id ? 'Category updated successfully' : 'Category created successfully');
                    }
                    
                    // Close modal and reload categories
                    this.closeCategoryModal();
                    this.loadCategories();
                } else {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: result.message || 'Failed to save category'
                        });
                    } else {
                        alert(result.message || 'Failed to save category');
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                if (typeof Swal !== 'undefined') {
                    Swal.close();
                    
                    let errorMessage = 'An error occurred while saving the category';
                    
                    try {
                        const errorData = JSON.parse(error.message);
                        if (errorData.errors) {
                            // Show validation errors
                            const errorDetails = Object.entries(errorData.errors)
                                .map(([field, messages]) => `${field}: ${messages.join(', ')}`)
                                .join('\n');
                            errorMessage = `Validation failed:\n${errorDetails}`;
                        } else if (errorData.message) {
                            errorMessage = errorData.message;
                        }
                    } catch (e) {
                        // If parsing fails, use the original error message
                        if (error.message && error.message !== 'An error occurred while saving the category') {
                            errorMessage = error.message;
                        }
                    }
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: errorMessage,
                        confirmButtonText: 'OK'
                    });
                } else {
                    alert('An error occurred while saving the category');
                }
            });
        },

        // Close category modal
        closeCategoryModal: function() {
            const modal = document.getElementById('categoryModal');
            modal.classList.add('hidden');
        }
    };

    // Initialize the system
    window.FileManagementSystem.init();
});

// Global functions for modal interactions
function closeCategoryModal() {
    if (window.FileManagementSystem) {
        window.FileManagementSystem.closeCategoryModal();
    }
}

// Tab switching functionality
function switchTab(tabName) {
    // Hide all tab contents
    const tabContents = document.querySelectorAll('.tab-content');
    tabContents.forEach(content => content.classList.add('hidden'));
    
    // Remove active state from all tabs
    const tabButtons = document.querySelectorAll('.tab-button');
    tabButtons.forEach(button => {
        button.classList.remove('border-primary', 'text-primary');
        button.classList.add('border-transparent', 'text-gray-500', 'dark:text-gray-400');
    });
    
    // Show selected tab content
    document.getElementById(`${tabName}-content`).classList.remove('hidden');
    
    // Activate selected tab button
    const activeTab = document.getElementById(`${tabName}-tab`);
    activeTab.classList.remove('border-transparent', 'text-gray-500', 'dark:text-gray-400');
    activeTab.classList.add('border-primary', 'text-primary');
    
    // Load data for the selected tab
    if (window.FileManagementSystem) {
        switch(tabName) {
            case 'overview':
                window.FileManagementSystem.loadOverview();
                break;
            case 'categories':
                window.FileManagementSystem.loadCategories();
                break;
            case 'settings':
                window.FileManagementSystem.loadSettings();
                break;
            case 'files':
                window.FileManagementSystem.loadFiles();
                break;
        }
    }
}


</script>
@endpush
