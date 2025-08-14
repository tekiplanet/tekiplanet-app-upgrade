@extends('admin.layouts.app')

@section('content')
@include('admin.components.notification')

@if(session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            showNotification('{{ session('success') }}');
        });
    </script>
@endif

@if(session('error'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            showNotification('{{ session('error') }}', 'error');
        });
    </script>
@endif
<div class="container px-6 mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
            Edit GRIT
        </h2>
        <a href="{{ route('admin.grits.show', $grit) }}" 
           class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
            Back to Details
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-md dark:bg-gray-800 p-6">
        <form action="{{ route('admin.grits.update', $grit) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Title -->
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Title</label>
                    <input type="text" 
                           name="title" 
                           value="{{ old('title', $grit->title) }}"
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
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" 
                                {{ old('category_id', $grit->category_id) == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Owner Budget -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Owner Budget</label>
                    <input type="number" 
                           name="owner_budget" 
                           value="{{ old('owner_budget', $grit->owner_budget ?? $grit->budget) }}"
                           required
                           min="0"
                           step="0.01"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('owner_budget')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Owner Currency -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Owner Currency</label>
                    <select name="owner_currency" 
                            required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Select Currency</option>
                        @foreach($currencies as $currency)
                            <option value="{{ $currency->code }}" {{ old('owner_currency', $grit->owner_currency ?? 'NGN') == $currency->code ? 'selected' : '' }}>
                                {{ $currency->symbol }} {{ $currency->code }} - {{ $currency->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('owner_currency')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Deadline -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Deadline</label>
                    <input type="date" 
                           name="deadline" 
                           value="{{ old('deadline', $grit->deadline->format('Y-m-d')) }}"
                           required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('deadline')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                    <select name="status" 
                            required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="open" {{ old('status', $grit->status) === 'open' ? 'selected' : '' }}>Open</option>
                        <option value="approved" {{ old('status', $grit->status) === 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="in_progress" {{ old('status', $grit->status) === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="completed" {{ old('status', $grit->status) === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ old('status', $grit->status) === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                    @error('status')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Admin Approval Status -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Admin Approval Status</label>
                    <select name="admin_approval_status" 
                            required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="pending" {{ old('admin_approval_status', $grit->admin_approval_status) === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ old('admin_approval_status', $grit->admin_approval_status) === 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ old('admin_approval_status', $grit->admin_approval_status) === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                    @error('admin_approval_status')
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
                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('description', $grit->description) }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Requirements -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Requirements & Skills</label>
                <textarea name="requirements" 
                          rows="4" 
                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                          placeholder="Enter skills, requirements, or any other project specifications (e.g., PHP, Laravel, MySQL, Must have 3+ years experience, Portfolio required)">{{ old('requirements', $grit->requirements) }}</textarea>
                <p class="mt-1 text-sm text-gray-500">Enter skills, requirements, or any other project specifications. You can separate items with commas or put each on a new line.</p>
                @error('requirements')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end">
                <button type="submit" 
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    Update GRIT
                </button>
            </div>
        </form>
    </div>
</div>
@endsection 