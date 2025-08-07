@extends('admin.layouts.app')

@section('content')
<div class="container px-6 mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
            Conversion Tasks
        </h2>
        <button onclick="openCreateModal()" 
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            Create Task
        </button>
    </div>

    <!-- Search Section -->
    <div class="mb-6 bg-white rounded-lg shadow-md dark:bg-gray-800 p-4">
        <form action="{{ route('admin.conversion-tasks.index') }}" method="GET" class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}"
                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500"
                       placeholder="Search tasks...">
            </div>
            <button type="submit" 
                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                Search
            </button>
        </form>
    </div>

    <!-- Tasks List -->
    <div class="bg-white rounded-lg shadow-md dark:bg-gray-800">
        <div class="overflow-x-auto">
            <table class="w-full whitespace-nowrap">
                <thead>
                    <tr class="text-left font-semibold bg-gray-50 dark:bg-gray-700">
                        <th class="px-6 py-3">Title</th>
                        <th class="px-6 py-3">Task Type</th>
                        <th class="px-6 py-3">Reward Type</th>
                        <th class="px-6 py-3">Points Range</th>
                        <th class="px-6 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($tasks as $task)
                        <tr>
                            <td class="px-6 py-4 font-semibold">{{ $task->title }}</td>
                            <td class="px-6 py-4">{{ $task->type->name ?? '-' }}</td>
                            <td class="px-6 py-4">{{ $task->rewardType->name ?? '-' }}</td>
                            <td class="px-6 py-4">{{ $task->min_points }} - {{ $task->max_points }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <button onclick="openEditModal(@json($task))"
                                            class="text-blue-600 hover:text-blue-800">
                                        Edit
                                    </button>
                                    <form action="{{ route('admin.conversion-tasks.destroy', $task) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800" onclick="return confirm('Delete this task?')">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                No tasks found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@include('admin.conversion-tasks._form_modal')

@push('scripts')
<script>
function openCreateModal() {
    document.getElementById('taskForm').reset();
    document.getElementById('modalTitle').textContent = 'Create Task';
    document.getElementById('taskForm').action = '{{ route('admin.conversion-tasks.store') }}';
    document.getElementById('taskForm').querySelector('input[name="_method"]').value = 'POST';
    document.getElementById('taskModal').classList.remove('hidden');
}

function openEditModal(task) {
    const form = document.getElementById('taskForm');
    form.reset();
    form.elements['title'].value = task.title;
    form.elements['description'].value = task.description;
    form.elements['task_type_id'].value = task.task_type_id;
    form.elements['reward_type_id'].value = task.reward_type_id;
    form.elements['min_points'].value = task.min_points;
    form.elements['max_points'].value = task.max_points;
    form.action = `/admin/conversion-tasks/${task.id}`;
    form.querySelector('input[name="_method"]').value = 'PUT';
    document.getElementById('modalTitle').textContent = 'Edit Task';
    document.getElementById('taskModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('taskModal').classList.add('hidden');
}
</script>
@endpush
@endsection
