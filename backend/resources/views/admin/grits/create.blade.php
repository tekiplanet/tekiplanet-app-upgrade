@extends('admin.layouts.app')

@section('content')
<div class="container px-6 mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
            Create New Hustle
        </h2>
        <a href="{{ route('admin.hustles.index') }}" 
           class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
            Back to Hustles
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-md dark:bg-gray-800 p-6">
        <form id="createHustleForm" action="{{ route('admin.hustles.store') }}" method="POST" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Title -->
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Title</label>
                    <input type="text" 
                           name="title" 
                           value="{{ old('title') }}"
                           required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('title')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Category -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Category</label>
                    <select name="category_id" 
                            required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Select Category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Budget -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Budget (₦)</label>
                    <input type="number" 
                           name="budget" 
                           value="{{ old('budget') }}"
                           required
                           min="0"
                           step="0.01"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('budget')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Deadline -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Deadline</label>
                    <input type="date" 
                           name="deadline" 
                           value="{{ old('deadline') }}"
                           required
                           min="{{ date('Y-m-d') }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('deadline')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Description -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                <textarea name="description" 
                          rows="4" 
                          required
                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('description') }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Requirements -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Requirements</label>
                <textarea name="requirements" 
                          rows="4" 
                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                          placeholder="Enter each requirement on a new line">{{ old('requirements') }}</textarea>
                @error('requirements')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end">
                <button type="submit" 
                        id="submitButton"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                    <span>Create Hustle</span>
                    <svg id="loadingIcon" class="hidden w-5 h-5 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('createHustleForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const form = e.target;
    const submitButton = document.getElementById('submitButton');
    const loadingIcon = document.getElementById('loadingIcon');
    const buttonText = submitButton.querySelector('span');
    
    try {
        // Disable button and show loading state
        submitButton.disabled = true;
        loadingIcon.classList.remove('hidden');
        buttonText.textContent = 'Creating...';

        // Get form data
        const formData = new FormData(form);

        // Send AJAX request
        const response = await fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.message || 'Something went wrong');
        }

        // Show success message
        await Swal.fire({
            title: 'Success!',
            text: 'Hustle created successfully',
            icon: 'success',
            timer: 2000,
            showConfirmButton: false
        });

        // Redirect to show page
        window.location.href = data.redirect;

    } catch (error) {
        // Reset button state
        submitButton.disabled = false;
        loadingIcon.classList.add('hidden');
        buttonText.textContent = 'Create Hustle';

        // Show error message
        await Swal.fire({
            title: 'Error!',
            text: error.message || 'Failed to create hustle',
            icon: 'error',
            confirmButtonText: 'OK'
        });
    }
});

// Add form validation feedback using SweetAlert2
function showValidationError(message) {
    Swal.fire({
        title: 'Validation Error',
        text: message,
        icon: 'warning',
        confirmButtonText: 'OK'
    });
}

// Add client-side validation
document.getElementById('createHustleForm').addEventListener('invalid', function(e) {
    e.preventDefault();
    showValidationError('Please fill in all required fields correctly');
}, true);
</script>
@endpush
@endsection 