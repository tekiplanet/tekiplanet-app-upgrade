@extends('admin.layouts.app')

@section('title', 'Edit Plan')

@section('content')
    <div class="flex justify-between items-center mb-4">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Workstation Plan') }}: {{ $plan->name }}
        </h2>
        <a href="{{ route('admin.workstation.plans.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
            Back to Plans
        </a>
    </div>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form action="{{ route('admin.workstation.plans.update', $plan) }}" method="POST" 
                        id="planForm">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Basic Information -->
                            <div class="space-y-4">
                                <h3 class="text-lg font-semibold">Basic Information</h3>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Name</label>
                                    <input type="text" name="name" value="{{ old('name', $plan->name) }}" required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Price (₦)</label>
                                    <input type="number" name="price" value="{{ old('price', $plan->price) }}" required min="0" step="0.01"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Duration (Days)</label>
                                    <input type="number" name="duration_days" value="{{ old('duration_days', $plan->duration_days) }}" required min="1"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Print Pages Limit</label>
                                    <input type="number" name="print_pages_limit" value="{{ old('print_pages_limit', $plan->print_pages_limit) }}" required min="0"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Meeting Room Hours (-1 for unlimited)</label>
                                    <input type="number" name="meeting_room_hours" value="{{ old('meeting_room_hours', $plan->meeting_room_hours) }}" required min="-1"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                </div>
                            </div>

                            <!-- Additional Features -->
                            <div class="space-y-4">
                                <h3 class="text-lg font-semibold">Additional Features</h3>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Features (one per line)</label>
                                    <textarea name="features" rows="5" required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('features', implode("\n", $plan->features)) }}</textarea>
                                </div>

                                <div class="flex items-center gap-4">
                                    <div class="flex items-center">
                                        <input type="checkbox" name="has_locker" value="1" {{ old('has_locker', $plan->has_locker) ? 'checked' : '' }}
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        <label class="ml-2 text-sm text-gray-600">Has Locker</label>
                                    </div>

                                    <div class="flex items-center">
                                        <input type="checkbox" name="has_dedicated_support" value="1" {{ old('has_dedicated_support', $plan->has_dedicated_support) ? 'checked' : '' }}
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        <label class="ml-2 text-sm text-gray-600">Dedicated Support</label>
                                    </div>

                                    <div class="flex items-center">
                                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $plan->is_active) ? 'checked' : '' }}
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        <label class="ml-2 text-sm text-gray-600">Active</label>
                                    </div>
                                </div>

                                <!-- Installment Options -->
                                <div class="space-y-4">
                                    <div class="flex items-center">
                                        <input type="checkbox" name="allows_installments" value="1" 
                                            {{ old('allows_installments', $plan->allows_installments) ? 'checked' : '' }}
                                            id="allowsInstallments"
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        <label class="ml-2 text-sm text-gray-600">Allow Installments</label>
                                    </div>

                                    <div id="installmentFields" class="space-y-4 {{ old('allows_installments', $plan->allows_installments) ? '' : 'hidden' }}">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Number of Months</label>
                                            <input type="number" name="installment_months" 
                                                value="{{ old('installment_months', $plan->installment_months) }}" 
                                                min="1"
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Monthly Amount (₦)</label>
                                            <input type="number" name="installment_amount" 
                                                value="{{ old('installment_amount', $plan->installment_amount) }}" 
                                                min="0" step="0.01"
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <button type="submit" id="submitButton" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                <span id="submitLoader" class="hidden">
                                    <svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Processing...
                                </span>
                                <span id="submitText">Update Plan</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Toggle installment fields visibility
        const allowsInstallmentsCheckbox = document.getElementById('allowsInstallments');
        const installmentFields = document.getElementById('installmentFields');
        
        allowsInstallmentsCheckbox.addEventListener('change', function() {
            installmentFields.classList.toggle('hidden', !this.checked);
        });

        // Form submission with loading state
        const form = document.getElementById('planForm');
        const submitButton = document.getElementById('submitButton');
        const submitLoader = document.getElementById('submitLoader');
        const submitText = document.getElementById('submitText');

        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            // Show loading state
            submitButton.disabled = true;
            submitLoader.classList.remove('hidden');
            submitText.classList.add('hidden');

            try {
                const formData = new FormData(this);
                
                // Handle checkbox values
                ['has_locker', 'has_dedicated_support', 'allows_installments', 'is_active'].forEach(field => {
                    if (!formData.has(field)) {
                        formData.append(field, '0');
                    }
                });

                // Handle features array
                const features = formData.get('features')
                    .split('\n')
                    .map(f => f.trim())
                    .filter(f => f);
                
                formData.delete('features');
                features.forEach((feature, index) => {
                    formData.append(`features[${index}]`, feature);
                });

                const response = await fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'application/json'
                    }
                });

                if (!response.ok) {
                    const data = await response.json();
                    throw new Error(data.message || 'Something went wrong');
                }

                // Show success message
                await Swal.fire({
                    title: 'Success!',
                    text: 'Plan updated successfully',
                    icon: 'success',
                    confirmButtonText: 'OK',
                    timer: 2000,
                    timerProgressBar: true
                });

                // Redirect to show page
                window.location.href = "{{ route('admin.workstation.plans.show', $plan) }}";
            } catch (error) {
                console.error('Error:', error);
                
                // Show error message
                await Swal.fire({
                    title: 'Error!',
                    text: error.message || 'Failed to update plan',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            } finally {
                // Reset loading state
                submitButton.disabled = false;
                submitLoader.classList.add('hidden');
                submitText.classList.remove('hidden');
            }
        });
    </script>
    @endpush
@endsection 