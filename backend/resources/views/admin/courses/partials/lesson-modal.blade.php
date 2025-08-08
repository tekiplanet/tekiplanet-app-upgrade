<div id="lessonModal" class="fixed inset-0 z-50 overflow-y-auto hidden">
    <!-- Modal Backdrop -->
    <div class="fixed inset-0 bg-black bg-opacity-50 z-40"
         onclick="closeLessonModal()"></div>

    <!-- Modal Content -->
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg max-w-4xl w-full max-h-[90vh] overflow-y-auto">
            <!-- Header -->
            <div class="flex justify-between items-center p-4 border-b dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    <span id="lessonModalTitle">Add New Lesson</span>
                </h3>
                <button onclick="closeLessonModal()" class="text-gray-400 hover:text-gray-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Form -->
            <form id="lessonForm" onsubmit="event.preventDefault(); handleLessonSubmit(event);" class="p-4 space-y-4">
                <input type="hidden" name="module_id" id="lessonModuleId">
                
                <!-- Title -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Lesson Title</label>
                    <input type="text" name="title" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                    <textarea name="description" rows="2" required
                              placeholder="Brief description of what this lesson covers"
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                    <p class="mt-1 text-xs text-gray-500">Brief description of what this lesson covers</p>
                </div>

                <!-- Content Type -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Content Type</label>
                    <select name="content_type" required onchange="handleContentTypeChange()"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="video">Video</option>
                        <option value="text">Text</option>
                        <option value="quiz">Quiz</option>
                        <option value="assignment">Assignment</option>
                        <option value="pdf">PDF</option>
                    </select>
                </div>

                <!-- Content Field (for text lessons) -->
                <div id="contentField" class="hidden">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Lesson Content</label>
                    <textarea id="lessonContent" name="content" rows="15"
                              placeholder="Enter the full lesson content here. Use the rich text editor above for formatting."
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                    <p class="mt-1 text-xs text-gray-500">Use the rich text editor above for formatting. Supports headings, lists, links, and more.</p>
                </div>

                <!-- Duration -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Duration (minutes)</label>
                    <input type="number" name="duration_minutes" min="1" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <!-- Learn Rewards -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Learn Rewards</label>
                    <input type="number" name="learn_rewards" min="0" step="1"
                           placeholder="0"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <p class="mt-1 text-xs text-gray-500">Reward points user will earn for completing this lesson.</p>
                </div>

                <!-- Order -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Order</label>
                    <input type="number" name="order" min="1" required
                           placeholder="1, 2, 3..."
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <p class="mt-1 text-xs text-gray-500">Set the order in which this lesson appears (1 = first, 2 = second, etc.)</p>
                </div>

                <!-- Resource URL -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Resource URL</label>
                    <input type="url" name="resource_url"
                           placeholder="https://example.com/resource"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <!-- Is Preview -->
                <div class="flex items-center">
                    <input type="checkbox" name="is_preview" id="is_preview"
                           class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <label for="is_preview" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                        Make this lesson available for preview
                    </label>
                </div>

                <!-- Quiz Management Button (only for quiz lessons) -->
                <div id="quizManagementSection" class="hidden">
                    <div class="border-t pt-4">
                        <p class="text-sm text-gray-600 mb-2">Quiz Content Management</p>
                        <button type="button" onclick="openQuizModal()" 
                                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm">
                            Manage Quiz Questions
                        </button>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end gap-2 pt-4">
                    <button type="button" onclick="closeLessonModal()"
                            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                        <span id="lessonModalAction">Create Lesson</span>
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