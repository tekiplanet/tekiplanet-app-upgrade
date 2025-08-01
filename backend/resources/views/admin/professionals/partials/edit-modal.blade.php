<!-- Edit Modal -->
<div id="editModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen p-4">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black opacity-50"></div>

        <!-- Modal Content -->
        <div class="relative bg-white dark:bg-gray-800 rounded-lg w-full max-w-lg">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                    Edit Professional Details
                </h3>

                <form id="editForm" class="space-y-4">
                    <!-- Title -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Title</label>
                        <input type="text" name="title" value="{{ $professional->title }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <!-- Category -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Category</label>
                        <select name="category_id" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ $professional->category_id == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Specialization -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Specialization</label>
                        <input type="text" name="specialization" value="{{ $professional->specialization }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <!-- Expertise Areas -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Expertise Areas</label>
                        <input type="text" name="expertise_areas" value="{{ is_array($professional->expertise_areas) ? implode(', ', $professional->expertise_areas) : $professional->expertise_areas }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <p class="mt-1 text-sm text-gray-500">Separate multiple areas with commas</p>
                    </div>

                    <!-- Years of Experience -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Years of Experience</label>
                        <input type="number" name="years_of_experience" value="{{ $professional->years_of_experience }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <!-- Hourly Rate -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Hourly Rate (₦)</label>
                        <input type="number" step="0.01" name="hourly_rate" value="{{ $professional->hourly_rate }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <!-- Availability Status -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Availability Status</label>
                        <select name="availability_status" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="available" {{ $professional->availability_status === 'available' ? 'selected' : '' }}>Available</option>
                            <option value="unavailable" {{ $professional->availability_status === 'unavailable' ? 'selected' : '' }}>Unavailable</option>
                        </select>
                    </div>

                    <!-- Bio -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Bio</label>
                        <textarea name="bio" rows="3"
                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ $professional->bio }}</textarea>
                    </div>

                    <!-- Certifications -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Certifications</label>
                        <input type="text" name="certifications" value="{{ is_array($professional->certifications) ? implode(', ', $professional->certifications) : $professional->certifications }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <p class="mt-1 text-sm text-gray-500">Separate multiple certifications with commas</p>
                    </div>

                    <!-- Social Links -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">LinkedIn URL</label>
                        <input type="url" name="linkedin_url" value="{{ $professional->linkedin_url }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">GitHub URL</label>
                        <input type="url" name="github_url" value="{{ $professional->github_url }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Portfolio URL</label>
                        <input type="url" name="portfolio_url" value="{{ $professional->portfolio_url }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <!-- Languages -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Languages</label>
                        <input type="text" name="languages" value="{{ is_array($professional->languages) ? implode(', ', $professional->languages) : $professional->languages }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <p class="mt-1 text-sm text-gray-500">Separate multiple languages with commas</p>
                    </div>

                    <!-- Timezone -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Timezone</label>
                        <input type="text" name="timezone" value="{{ $professional->timezone }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <!-- Preferred Contact Method -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Preferred Contact Method</label>
                        <select name="preferred_contact_method" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="email" {{ $professional->preferred_contact_method === 'email' ? 'selected' : '' }}>Email</option>
                            <option value="phone" {{ $professional->preferred_contact_method === 'phone' ? 'selected' : '' }}>Phone</option>
                        </select>
                    </div>

                    <!-- Buttons -->
                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" onclick="closeEditModal()"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit"
                                class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg id="saveSpinner" class="w-4 h-4 mr-2 animate-spin hidden" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span id="saveButtonText">Save Changes</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let isProcessing = false;

function openEditModal() {
    document.getElementById('editModal').classList.remove('hidden');
}

function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
}

document.getElementById('editForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    if (isProcessing) return;

    try {
        isProcessing = true;
        const saveSpinner = document.getElementById('saveSpinner');
        const saveButtonText = document.getElementById('saveButtonText');
        saveSpinner.classList.remove('hidden');
        saveButtonText.textContent = 'Saving...';

        const response = await fetch('{{ route("admin.professionals.update", $professional) }}', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(Object.fromEntries(new FormData(this)))
        });

        const data = await response.json();

        if (response.ok) {
            showNotification('Success', data.message);
            closeEditModal();
            setTimeout(() => location.reload(), 2000);
        } else {
            throw new Error(data.message || 'Failed to update professional details');
        }
    } catch (error) {
        showNotification('Error', error.message, 'error');
    } finally {
        isProcessing = false;
        saveSpinner.classList.add('hidden');
        saveButtonText.textContent = 'Save Changes';
    }
});
</script>
@endpush 