<div id="methodModal" class="fixed inset-0 z-50 overflow-y-auto hidden">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full dark:bg-gray-800">
            <form id="methodForm" onsubmit="handleSubmit(event)" class="p-6">
                <input type="hidden" id="methodId">
                
                <h3 id="modalTitle" class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                    Create Shipping Method
                </h3>

                <div class="space-y-4">
                    <!-- Name -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Method Name
                        </label>
                        <input type="text" 
                               id="methodName" 
                               name="name" 
                               required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Description
                        </label>
                        <textarea id="methodDescription" 
                                  name="description" 
                                  rows="2"
                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                    </div>

                    <!-- Base Cost -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Base Cost (₦)
                        </label>
                        <input type="number" 
                               id="baseCost" 
                               name="base_cost"
                               step="0.01"
                               min="0"
                               required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <!-- Estimated Days -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Min Days
                            </label>
                            <input type="number" 
                                   id="estimatedDaysMin" 
                                   name="estimated_days_min"
                                   min="0"
                                   required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Max Days
                            </label>
                            <input type="number" 
                                   id="estimatedDaysMax" 
                                   name="estimated_days_max"
                                   min="0"
                                   required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>

                    <!-- Priority -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Priority (Lower number = Higher priority)
                        </label>
                        <input type="number" 
                               id="priority" 
                               name="priority"
                               min="0"
                               required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <!-- Is Active -->
                    <div class="flex items-center">
                        <input type="checkbox" 
                               id="isActive" 
                               name="is_active"
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="isActive" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                            Active
                        </label>
                    </div>

                    <!-- Zone Rates -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Zone Rates
                        </label>
                        <div class="space-y-2">
                            @foreach($zones as $zone)
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="col-span-1">
                                        <label class="text-sm text-gray-600 dark:text-gray-400">
                                            {{ $zone->name }} Rate (₦)
                                        </label>
                                        <input type="number" 
                                               name="zone_rates[{{ $zone->id }}][rate]"
                                               step="0.01"
                                               min="0"
                                               placeholder="0.00"
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    </div>
                                    <div class="col-span-1">
                                        <label class="text-sm text-gray-600 dark:text-gray-400">
                                            Estimated Days
                                        </label>
                                        <input type="number" 
                                               name="zone_rates[{{ $zone->id }}][estimated_days]"
                                               min="0"
                                               placeholder="0"
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="mt-5 sm:mt-6 flex justify-end gap-2">
                    <button type="button" 
                            onclick="closeModal()"
                            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center gap-2">
                        <span id="submitButtonText">Create Method</span>
                        <span id="loadingSpinner" class="hidden">
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