@extends('admin.layouts.app')

@section('content')
@include('admin.components.notification')
<div class="container px-6 mx-auto">
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
            Edit Service
        </h2>
        <a href="{{ route('admin.services.index') }}"
           class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
            Back to Services
        </a>
    </div>

    <div class="mt-6">
        <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
            <form action="{{ route('admin.services.update', $service) }}" method="POST" id="editForm">
                @csrf
                @method('PUT')

                <div class="space-y-6">
                    <div>
                        <label for="category_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Category
                        </label>
                        <select name="category_id" 
                                id="category_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                required>
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" 
                                    {{ old('category_id', $service->category_id) == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Name
                        </label>
                        <input type="text" 
                               name="name" 
                               id="name"
                               value="{{ old('name', $service->name) }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                               required>
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="short_description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Short Description
                        </label>
                        <input type="text" 
                               name="short_description" 
                               id="short_description"
                               value="{{ old('short_description', $service->short_description) }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                               required>
                        @error('short_description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="long_description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Long Description
                        </label>
                        <textarea name="long_description" 
                                  id="long_description"
                                  rows="4"
                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                  required>{{ old('long_description', $service->long_description) }}</textarea>
                        @error('long_description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="starting_price" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Starting Price (₦)
                        </label>
                        <input type="number" 
                               name="starting_price" 
                               id="starting_price"
                               value="{{ old('starting_price', $service->starting_price) }}"
                               step="0.01"
                               min="0"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                               required>
                        @error('starting_price')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="icon_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Icon Name
                        </label>
                        <input type="text" 
                               name="icon_name" 
                               id="icon_name"
                               value="{{ old('icon_name', $service->icon_name) }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                               required>
                        @error('icon_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" 
                               name="is_featured" 
                               id="is_featured"
                               value="1"
                               {{ old('is_featured', $service->is_featured) ? 'checked' : '' }}
                               class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                        <label for="is_featured" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                            Featured Service
                        </label>
                    </div>

                    <!-- Quote Fields Section -->
                    <div class="border-t pt-4">
                        <h3 class="text-lg font-medium text-gray-700 dark:text-gray-300 mb-4">Quote Fields</h3>
                        <div id="quoteFields" class="space-y-4">
                            @foreach($service->quoteFields as $field)
                                <div class="quote-field bg-gray-50 p-4 rounded-lg relative" data-field-id="{{ $loop->index }}">
                                    <button type="button" 
                                            onclick="removeQuoteField({{ $loop->index }})" 
                                            class="absolute right-2 top-2 text-gray-400 hover:text-red-500">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                    <input type="hidden" name="quote_fields[{{ $loop->index }}][id]" value="{{ $field->id }}">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Field Name</label>
                                            <input type="text" 
                                                   name="quote_fields[{{ $loop->index }}][name]" 
                                                   value="{{ $field->name }}"
                                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                                   required>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Label</label>
                                            <input type="text" 
                                                   name="quote_fields[{{ $loop->index }}][label]" 
                                                   value="{{ $field->label }}"
                                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                                   required>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Type</label>
                                            <select name="quote_fields[{{ $loop->index }}][type]" 
                                                    onchange="toggleOptions({{ $loop->index }})"
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                                @foreach([
                                                    'text' => 'Text',
                                                    'textarea' => 'Textarea',
                                                    'number' => 'Number',
                                                    'select' => 'Select',
                                                    'multi-select' => 'Multi Select',
                                                    'radio' => 'Radio',
                                                    'checkbox' => 'Checkbox',
                                                    'date' => 'Date',
                                                    'email' => 'Email',
                                                    'phone' => 'Phone'
                                                ] as $value => $label)
                                                    <option value="{{ $value }}" 
                                                            {{ $field->type === $value ? 'selected' : '' }}>
                                                        {{ $label }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Required</label>
                                            <select name="quote_fields[{{ $loop->index }}][required]" 
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                                <option value="1" {{ $field->required ? 'selected' : '' }}>Yes</option>
                                                <option value="0" {{ !$field->required ? 'selected' : '' }}>No</option>
                                            </select>
                                        </div>
                                        <div class="options-container {{ !in_array($field->type, ['select', 'multi-select', 'checkbox', 'radio']) ? 'hidden' : '' }} col-span-2" 
                                             id="options-{{ $loop->index }}">
                                               <label class="block text-sm font-medium text-gray-700">Options (one per line)</label>
                                               <textarea name="quote_fields[{{ $loop->index }}][options]" 
                                                         rows="3"
                                                         class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">{{ is_array($field->options) ? implode("\n", $field->options) : '' }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <button type="button" 
                                onclick="addQuoteField()"
                                class="mt-4 inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Add Field
                        </button>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit"
                                id="submitButton"
                                class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50">
                            <svg id="loadingIcon" class="hidden w-4 h-4 mr-2 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span id="buttonText">Update Service</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
let fieldCount = {{ $service->quoteFields->count() }};

function addQuoteField() {
    const fieldHtml = `
        <div class="quote-field bg-gray-50 p-4 rounded-lg relative" data-field-id="${fieldCount}">
            <button type="button" 
                    onclick="removeQuoteField(${fieldCount})" 
                    class="absolute right-2 top-2 text-gray-400 hover:text-red-500">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Field Name</label>
                    <input type="text" 
                           name="quote_fields[${fieldCount}][name]" 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                           required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Label</label>
                    <input type="text" 
                           name="quote_fields[${fieldCount}][label]" 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                           required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Type</label>
                    <select name="quote_fields[${fieldCount}][type]" 
                            onchange="toggleOptions(${fieldCount})"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                        <option value="text">Text</option>
                        <option value="textarea">Textarea</option>
                        <option value="number">Number</option>
                        <option value="select">Select</option>
                        <option value="multi-select">Multi Select</option>
                        <option value="radio">Radio</option>
                        <option value="checkbox">Checkbox</option>
                        <option value="date">Date</option>
                        <option value="email">Email</option>
                        <option value="phone">Phone</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Required</label>
                    <select name="quote_fields[${fieldCount}][required]" 
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                        <option value="1">Yes</option>
                        <option value="0">No</option>
                    </select>
                </div>
                <div class="options-container hidden col-span-2" id="options-${fieldCount}">
                    <label class="block text-sm font-medium text-gray-700">Options (one per line)</label>
                    <textarea name="quote_fields[${fieldCount}][options]" 
                              rows="3"
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"></textarea>
                </div>
            </div>
        </div>
    `;
    
    document.getElementById('quoteFields').insertAdjacentHTML('beforeend', fieldHtml);
    fieldCount++;
}

function removeQuoteField(id) {
    const field = document.querySelector(`[data-field-id="${id}"]`);
    field.remove();
}

function toggleOptions(id) {
    const select = document.querySelector(`[name="quote_fields[${id}][type]"]`);
    const optionsContainer = document.getElementById(`options-${id}`);
    
    if (['select', 'multi-select', 'checkbox', 'radio'].includes(select.value)) {
        optionsContainer.classList.remove('hidden');
    } else {
        optionsContainer.classList.add('hidden');
    }
}

document.getElementById('editForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const button = document.getElementById('submitButton');
    const loadingIcon = document.getElementById('loadingIcon');
    const buttonText = document.getElementById('buttonText');
    
    button.disabled = true;
    loadingIcon.classList.remove('hidden');
    buttonText.textContent = 'Updating...';

    try {
        const formData = new FormData(this);
        
        // Process quote fields options
        document.querySelectorAll('[name$="[options]"]').forEach(textarea => {
            if (!textarea.parentElement.classList.contains('hidden')) {
                const options = textarea.value.split('\n').filter(option => option.trim());
                formData.set(textarea.name, JSON.stringify(options));
            }
        });

        const response = await fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });

        const data = await response.json();
        if (data.success) {
            showNotification(data.title, data.message);
            window.location.href = data.redirect;
        } else {
            showNotification('Error', data.message || 'Failed to update service', 'error');
            button.disabled = false;
            loadingIcon.classList.add('hidden');
            buttonText.textContent = 'Update Service';
        }
    } catch (error) {
        showNotification('Error', 'An error occurred while updating the service', 'error');
        button.disabled = false;
        loadingIcon.classList.add('hidden');
        buttonText.textContent = 'Update Service';
    }
});
</script>
@endpush
@endsection 