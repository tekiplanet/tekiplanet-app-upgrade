@extends('admin.layouts.app')

@section('content')
<div class="container px-6 mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
            Conversion Reward Types
        </h2>
        <button onclick="openCreateModal()" 
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            Create Reward Type
        </button>
    </div>

    <!-- Search Section -->
    <div class="mb-6 bg-white rounded-lg shadow-md dark:bg-gray-800 p-4">
        <form action="{{ route('admin.conversion-reward-types.index') }}" method="GET" class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}"
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500"
                       placeholder="Search reward types...">
            </div>
            <button type="submit" 
                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                Search
            </button>
        </form>
    </div>

    <!-- Reward Types List -->
    <div class="bg-white rounded-lg shadow-md dark:bg-gray-800">
        <div class="overflow-x-auto">
            <table class="w-full whitespace-nowrap">
                <thead>
                    <tr class="text-left font-semibold bg-gray-50 dark:bg-gray-700">
                        <th class="px-6 py-3">Name</th>
                        <th class="px-6 py-3">Description</th>
                        <th class="px-6 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($types as $type)
                        <tr>
                            <td class="px-6 py-4 font-semibold">{{ $type->name }}</td>
                            <td class="px-6 py-4">{{ $type->description }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <button onclick="openEditModal(@json($type))"
                                            class="text-blue-600 hover:text-blue-800">
                                        Edit
                                    </button>
                                    <form action="{{ route('admin.conversion-reward-types.destroy', $type) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800" onclick="return confirm('Delete this reward type?')">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-4 text-center text-gray-500">
                                No reward types found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@include('admin.conversion-reward-types._form_modal')

@push('scripts')
<script>
function openCreateModal() {
    document.getElementById('rewardTypeForm').reset();
    document.getElementById('modalTitle').textContent = 'Create Reward Type';
    document.getElementById('rewardTypeModal').classList.remove('hidden');
}

function openEditModal(type) {
    const form = document.getElementById('rewardTypeForm');
    form.reset();
    form.elements['name'].value = type.name;
    form.elements['description'].value = type.description;
    form.action = `/admin/conversion-reward-types/${type.id}`;
    form.querySelector('input[name="_method"]').value = 'PUT';
    document.getElementById('modalTitle').textContent = 'Edit Reward Type';
    document.getElementById('rewardTypeModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('rewardTypeModal').classList.add('hidden');
}
</script>
@endpush
@endsection
