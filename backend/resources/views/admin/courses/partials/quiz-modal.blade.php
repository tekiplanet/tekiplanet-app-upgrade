<div id="quizModal" class="fixed inset-0 z-50 overflow-y-auto hidden">
    <!-- Modal Backdrop -->
    <div class="fixed inset-0 bg-black bg-opacity-50 z-40"
         onclick="closeQuizModal()"></div>

    <!-- Modal Content -->
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg max-w-4xl w-full max-h-[90vh] overflow-y-auto">
            <!-- Header -->
            <div class="flex justify-between items-center p-4 border-b dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    <span id="quizModalTitle">Manage Quiz Questions</span>
                </h3>
                <button onclick="closeQuizModal()" class="text-gray-400 hover:text-gray-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Content -->
            <div class="p-4">
                <!-- Add Question Button -->
                <div class="mb-4">
                    <button onclick="openAddQuestionModal()" 
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        Add New Question
                    </button>
                </div>

                <!-- Questions List -->
                <div id="questionsList" class="space-y-4">
                    <!-- Questions will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Question Modal -->
<div id="questionModal" class="fixed inset-0 z-[99999] overflow-y-auto hidden" style="z-index: 99999;">
    <!-- Modal Backdrop -->
    <div class="fixed inset-0 bg-black bg-opacity-75 z-[99998]"
         onclick="closeQuestionModal()" style="z-index: 99998;"></div>

    <!-- Modal Content -->
    <div class="fixed inset-0 z-[99999] flex items-center justify-center p-4" style="z-index: 99999;">
        <div class="bg-white dark:bg-gray-800 rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <!-- Header -->
            <div class="flex justify-between items-center p-4 border-b dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    <span id="questionModalTitle">Add Question</span>
                </h3>
                <button onclick="closeQuestionModal()" class="text-gray-400 hover:text-gray-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Form -->
            <form id="questionForm" onsubmit="event.preventDefault(); handleQuestionSubmit(event);" class="p-4 space-y-4">
                <input type="hidden" name="lesson_id" id="questionLessonId">
                <input type="hidden" name="question_id" id="questionId">
                
                <!-- Question Text -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Question</label>
                    <textarea name="question" rows="3" required
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                </div>

                <!-- Question Type -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Question Type</label>
                    <select name="question_type" required onchange="handleQuestionTypeChange()"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="multiple_choice">Multiple Choice</option>
                        <option value="true_false">True/False</option>
                        <option value="short_answer">Short Answer</option>
                    </select>
                </div>

                <!-- Points -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Points</label>
                    <input type="number" name="points" min="1" value="1" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <!-- Answers Section -->
                <div id="answersSection">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Answers</label>
                    <div id="answersList" class="space-y-2">
                        <!-- Answers will be added here -->
                    </div>
                    <button type="button" onclick="addAnswer()" 
                            class="mt-2 px-3 py-1 text-sm bg-gray-100 text-gray-700 rounded hover:bg-gray-200">
                        + Add Answer
                    </button>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end gap-2 pt-4">
                    <button type="button" onclick="closeQuestionModal()"
                            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                        <span id="questionModalAction">Add Question</span>
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