<div id="taskModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50 hidden">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-lg p-6 relative">
        <h3 id="modalTitle" class="text-lg font-semibold mb-4">Create Task</h3>
        <form id="taskForm" method="POST" action="{{ route('admin.conversion-tasks.store') }}">
            @csrf
            <input type="hidden" name="_method" value="POST">
            <div class="mb-4">
                <label for="title" class="block text-gray-700">Title</label>
                <input type="text" name="title" id="title" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500" required>
            </div>
            <div class="mb-4">
                <label for="description" class="block text-gray-700">Description</label>
                <textarea name="description" id="description" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500"></textarea>
            </div>
            <div class="mb-4">
                <label for="task_type_id" class="block text-gray-700">Task Type</label>
                <select name="task_type_id" id="task_type_id" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500" required>
                    <option value="">Select Type</option>
                    @foreach($taskTypes as $type)
                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-4">
                <label for="reward_type_id" class="block text-gray-700">Reward Type</label>
                <select name="reward_type_id" id="reward_type_id" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500" required>
                    <option value="">Select Reward</option>
                    @foreach($rewardTypes as $type)
                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-4 flex gap-4">
                <div class="flex-1">
                    <label for="min_points" class="block text-gray-700">Min Points</label>
                    <input type="number" name="min_points" id="min_points" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500" min="0" required>
                </div>
                <div class="flex-1">
                    <label for="max_points" class="block text-gray-700">Max Points</label>
                    <input type="number" name="max_points" id="max_points" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500" min="0" required>
                </div>
            </div>
            <div id="dynamic-fields">
                <div class="mb-4 hidden" id="product-field">
                    <label for="product_id" class="block text-gray-700">Select Product</label>
                    <select name="product_id" id="product_id" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                        <option value="">Select Product</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-4 hidden" id="coupon-field">
                    <label for="coupon_id" class="block text-gray-700">Select Coupon</label>
                    <select name="coupon_id" id="coupon_id" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                        <option value="">Select Coupon</option>
                        @foreach($coupons as $coupon)
                            <option value="{{ $coupon->id }}">{{ $coupon->code }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-4 hidden" id="course-field">
                    <label for="course_id" class="block text-gray-700">Select Course</label>
                    <select name="course_id" id="course_id" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                        <option value="">Select Course</option>
                        @foreach($courses as $course)
                            <option value="{{ $course->id }}">{{ $course->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-4 hidden" id="cash-field">
                    <label for="cash_amount" class="block text-gray-700">Cash Amount</label>
                    <input type="number" step="0.01" name="cash_amount" id="cash_amount" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                </div>
                <div class="mb-4 hidden" id="discount-field">
                    <label for="discount_percent" class="block text-gray-700">Discount Percent</label>
                    <input type="number" name="discount_percent" id="discount_percent" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500" min="1" max="100">
                    <label for="service_name" class="block text-gray-700 mt-2">Service Name</label>
                    <input type="text" name="service_name" id="service_name" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                </div>
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" onclick="closeModal()" class="px-4 py-2 bg-gray-300 rounded-lg">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Save</button>
            </div>
        </form>
        <button onclick="closeModal()" class="absolute top-2 right-2 text-gray-500 hover:text-gray-700">&times;</button>
    </div>
</div>

@push('scripts')
<script>
    function hideAllDynamicFields() {
        document.getElementById('product-field').classList.add('hidden');
        document.getElementById('coupon-field').classList.add('hidden');
        document.getElementById('course-field').classList.add('hidden');
        document.getElementById('cash-field').classList.add('hidden');
        document.getElementById('discount-field').classList.add('hidden');
    }
    document.getElementById('reward_type_id').addEventListener('change', function() {
        hideAllDynamicFields();
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
    });
    // Hide all on load
    hideAllDynamicFields();
</script>
@endpush
