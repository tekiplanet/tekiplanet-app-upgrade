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
                                    <button type="button"
                                            class="text-blue-600 hover:text-blue-800 edit-btn"
                                            data-task='@json($task)'>
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
window.hideAllDynamicFields = function() {
    document.getElementById('product-field').classList.add('hidden');
    document.getElementById('coupon-field').classList.add('hidden');
    document.getElementById('course-field').classList.add('hidden');
    document.getElementById('cash-field').classList.add('hidden');
    document.getElementById('discount-field').classList.add('hidden');
    document.getElementById('referral-target-field').classList.add('hidden');
    document.getElementById('share-target-field').classList.add('hidden');
}
window.showReferralTargetIfNeeded = function() {
    var taskTypeSelect = document.getElementById('task_type_id');
    var selectedText = taskTypeSelect.options[taskTypeSelect.selectedIndex]?.text?.toLowerCase() || '';
    if (selectedText.includes('refer')) {
        document.getElementById('referral-target-field').classList.remove('hidden');
    } else {
        document.getElementById('referral-target-field').classList.add('hidden');
    }
    
    // Show share target for share product tasks
    if (selectedText.includes('share')) {
        document.getElementById('share-target-field').classList.remove('hidden');
    } else {
        document.getElementById('share-target-field').classList.add('hidden');
    }
}
window.openCreateModal = function() {
    document.getElementById('taskForm').reset();
    document.getElementById('modalTitle').textContent = 'Create Task';
    document.getElementById('taskForm').action = '{{ route('admin.conversion-tasks.store') }}';
    document.getElementById('taskForm').querySelector('input[name="_method"]').value = 'POST';
    document.getElementById('taskModal').classList.remove('hidden');
    window.hideAllDynamicFields();
    window.showReferralTargetIfNeeded();
}
window.openEditModal = function(task) {
    const form = document.getElementById('taskForm');
    form.reset();
    form.elements['title'].value = task.title;
    form.elements['description'].value = task.description;
    form.elements['task_type_id'].value = task.task_type_id;
    form.elements['reward_type_id'].value = task.reward_type_id;
    form.elements['min_points'].value = task.min_points;
    form.elements['max_points'].value = task.max_points;
    
    // Populate reward-specific fields
    if (task.product_id) form.elements['product_id'].value = task.product_id;
    if (task.coupon_id) form.elements['coupon_id'].value = task.coupon_id;
    if (task.course_id) form.elements['course_id'].value = task.course_id;
    if (task.cash_amount) form.elements['cash_amount'].value = task.cash_amount;
    if (task.discount_percent) form.elements['discount_percent'].value = task.discount_percent;
    if (task.service_name) form.elements['service_name'].value = task.service_name;
    
    if ('referral_target' in task && document.getElementById('referral_target')) {
        document.getElementById('referral_target').value = task.referral_target ?? '';
    } else if (document.getElementById('referral_target')) {
        document.getElementById('referral_target').value = '';
    }
    
    if ('share_target' in task && document.getElementById('share_target')) {
        document.getElementById('share_target').value = task.share_target ?? '';
    } else if (document.getElementById('share_target')) {
        document.getElementById('share_target').value = '';
    }
    form.action = `/admin/conversion-tasks/${task.id}`;
    form.querySelector('input[name="_method"]').value = 'PUT';
    document.getElementById('modalTitle').textContent = 'Edit Task';
    document.getElementById('taskModal').classList.remove('hidden');
    window.hideAllDynamicFields();
    window.showReferralTargetIfNeeded();
    
    // Show appropriate reward fields based on selected reward type
    setTimeout(function() {
        var rewardTypeSelect = document.getElementById('reward_type_id');
        if (rewardTypeSelect && rewardTypeSelect.value) {
            var selected = rewardTypeSelect.options[rewardTypeSelect.selectedIndex].text.toLowerCase();
            if (selected.includes('product')) {
                document.getElementById('product-field').classList.remove('hidden');
            } else if (selected.includes('coupon')) {
                document.getElementById('coupon-field').classList.remove('hidden');
            } else if (selected.includes('course')) {
                document.getElementById('course-field').classList.remove('hidden');
            } else if (selected.includes('cash')) {
                document.getElementById('cash-field').classList.remove('hidden');
            } else if (selected.includes('discount')) {
                document.getElementById('discount-field').classList.remove('hidden');
            }
        }
        
        // Show task-specific fields based on task type
        var taskTypeSelect = document.getElementById('task_type_id');
        var selectedText = taskTypeSelect.options[taskTypeSelect.selectedIndex]?.text?.toLowerCase() || '';
        if (selectedText.includes('refer')) {
            document.getElementById('referral-target-field').classList.remove('hidden');
        }
        if (selectedText.includes('share')) {
            document.getElementById('share-target-field').classList.remove('hidden');
            // Also show product field for share tasks regardless of reward type
            document.getElementById('product-field').classList.remove('hidden');
        }
    }, 100);
}
window.closeModal = function() {
    document.getElementById('taskModal').classList.add('hidden');
}
document.getElementById('reward_type_id').addEventListener('change', function() {
    window.hideAllDynamicFields();
    var selected = this.options[this.selectedIndex].text.toLowerCase();
    if (selected.includes('product')) {
        document.getElementById('product-field').classList.remove('hidden');
    } else if (selected.includes('coupon')) {
        document.getElementById('coupon-field').classList.remove('hidden');
    } else if (selected.includes('course')) {
        document.getElementById('course-field').classList.remove('hidden');
    } else if (selected.includes('cash')) {
        document.getElementById('cash-field').classList.remove('hidden');
    } else if (selected.includes('discount')) {
        document.getElementById('discount-field').classList.remove('hidden');
    }
    window.showReferralTargetIfNeeded();
});
document.getElementById('task_type_id').addEventListener('change', function() {
    window.showReferralTargetIfNeeded();
});
// Hide all on load
window.hideAllDynamicFields();
window.showReferralTargetIfNeeded();
document.querySelectorAll('.edit-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var task = JSON.parse(this.getAttribute('data-task'));
        window.openEditModal(task);
    });
});
</script>
@endpush
@endsection
