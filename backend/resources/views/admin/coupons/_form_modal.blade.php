<div id="couponModal" class="fixed inset-0 z-50 overflow-y-auto hidden" role="dialog">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full dark:bg-gray-800">
            <form id="couponForm" class="px-4 pt-5 pb-4 sm:p-6">
                <input type="hidden" name="id" id="couponId">
                
                <h3 id="modalTitle" class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                    Create Coupon
                </h3>

                <div class="space-y-4">
                    <!-- Code -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Coupon Code
                        </label>
                        <input type="text" 
                               name="code" 
                               required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <!-- Value Type and Value -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Value Type
                            </label>
                            <select name="value_type" 
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="fixed">Fixed Amount</option>
                                <option value="percentage">Percentage</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Value
                            </label>
                            <input type="number" 
                                   name="value" 
                                   required
                                   min="0"
                                   step="0.01"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>

                    <!-- Min Order Amount and Max Discount -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Min Order Amount
                            </label>
                            <input type="number" 
                                   name="min_order_amount" 
                                   required
                                   min="0"
                                   step="0.01"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Maximum Discount
                            </label>
                            <input type="number" 
                                   name="max_discount" 
                                   step="0.01"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <p class="mt-1 text-sm text-gray-500">Required for percentage discounts</p>
                        </div>
                    </div>

                    <!-- Valid Period -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Start Date
                            </label>
                            <input type="date" 
                                   name="starts_at" 
                                   required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                End Date
                            </label>
                            <input type="date" 
                                   name="expires_at" 
                                   required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>

                    <!-- Usage Limit Per User -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Usage Limit Per User
                        </label>
                        <input type="number" 
                               name="usage_limit_per_user" 
                               min="1"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <p class="mt-1 text-sm text-gray-500">Leave empty for unlimited uses per user</p>
                    </div>

                    <!-- Status -->
                    <div class="flex items-center">
                        <input type="checkbox" 
                               name="is_active" 
                               class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <label class="ml-2 block text-sm text-gray-900 dark:text-gray-100">
                            Active
                        </label>
                    </div>
                </div>

                <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                    <button type="submit"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:col-start-2 sm:text-sm">
                        Save
                    </button>
                    <button type="button"
                            onclick="closeModal()"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:col-start-1 sm:text-sm">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('couponForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const isEdit = formData.get('id');
    const formObject = Object.fromEntries(formData);
    
    // Properly handle checkbox - if it's not in formData, it means it's unchecked
    formObject.is_active = formData.has('is_active');
    
    try {
        const response = await fetch(`/admin/coupons${isEdit ? '/' + isEdit : ''}`, {
            method: isEdit ? 'PATCH' : 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(formObject)
        });

        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.message || 'Failed to save coupon');
        }

        const data = await response.json();

        if (data.success) {
            showNotification('Success', data.message);
            closeModal();
            setTimeout(() => window.location.reload(), 1000);
        } else {
            throw new Error(data.message);
        }
    } catch (error) {
        showNotification('Error', error.message, 'error');
    }
});

function openEditModal(coupon) {
    document.getElementById('modalTitle').textContent = 'Edit Coupon';
    const form = document.getElementById('couponForm');
    
    // Set the coupon ID
    document.getElementById('couponId').value = coupon.id;
    
    // Fill in the form fields
    form.querySelector('[name="code"]').value = coupon.code;
    form.querySelector('[name="value_type"]').value = coupon.value_type;
    form.querySelector('[name="value"]').value = coupon.value;
    form.querySelector('[name="min_order_amount"]').value = coupon.min_order_amount;
    form.querySelector('[name="max_discount"]').value = coupon.max_discount || '';
    form.querySelector('[name="starts_at"]').value = coupon.starts_at.split(' ')[0];
    form.querySelector('[name="expires_at"]').value = coupon.expires_at.split(' ')[0];
    form.querySelector('[name="usage_limit_per_user"]').value = coupon.usage_limit_per_user || '';
    form.querySelector('[name="is_active"]').checked = coupon.is_active;

    document.getElementById('couponModal').classList.remove('hidden');
}
</script>
@endpush 