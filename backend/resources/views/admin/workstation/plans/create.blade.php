<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Create Workstation Plan') }}
            </h2>
            <a href="{{ route('admin.workstation.plans.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Back to Plans
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form action="{{ route('admin.workstation.plans.store') }}" method="POST" x-data="planForm()" @submit.prevent="submitForm">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Basic Information -->
                            <div class="space-y-4">
                                <h3 class="text-lg font-semibold">Basic Information</h3>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Name</label>
                                    <input type="text" name="name" x-model="form.name" required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Price (₦)</label>
                                    <input type="number" name="price" x-model="form.price" step="0.01" required min="0"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Duration (Days)</label>
                                    <input type="number" name="duration_days" x-model="form.duration_days" required min="1"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Print Pages Limit</label>
                                    <input type="number" name="print_pages_limit" x-model="form.print_pages_limit" required min="0"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Meeting Room Hours (-1 for unlimited)</label>
                                    <input type="number" name="meeting_room_hours" x-model="form.meeting_room_hours" required min="-1"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                </div>
                            </div>

                            <!-- Additional Features -->
                            <div class="space-y-4">
                                <h3 class="text-lg font-semibold">Additional Features</h3>

                                <div>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="has_locker" x-model="form.has_locker"
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        <span class="ml-2">Has Locker Access</span>
                                    </label>
                                </div>

                                <div>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="has_dedicated_support" x-model="form.has_dedicated_support"
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        <span class="ml-2">Has Dedicated Support</span>
                                    </label>
                                </div>

                                <div>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="allows_installments" x-model="form.allows_installments"
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        <span class="ml-2">Allows Installments</span>
                                    </label>
                                </div>

                                <div x-show="form.allows_installments" class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Installment Months</label>
                                        <input type="number" name="installment_months" x-model="form.installment_months" min="1"
                                            :required="form.allows_installments"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Installment Amount (₦)</label>
                                        <input type="number" name="installment_amount" x-model="form.installment_amount" step="0.01" min="0"
                                            :required="form.allows_installments"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Features (one per line)</label>
                                    <textarea name="features" x-model="form.features" required rows="5"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                        placeholder="Enter features, one per line"></textarea>
                                </div>

                                <div>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="is_active" x-model="form.is_active"
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        <span class="ml-2">Is Active</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <button type="submit" 
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded inline-flex items-center"
                                :class="{ 'opacity-50 cursor-not-allowed': loading }"
                                :disabled="loading">
                                <span x-show="loading" class="mr-2">
                                    <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </span>
                                <span x-text="loading ? 'Creating...' : 'Create Plan'"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function planForm() {
            return {
                loading: false,
                form: {
                    name: '',
                    price: '',
                    duration_days: '',
                    print_pages_limit: '',
                    meeting_room_hours: '',
                    has_locker: false,
                    has_dedicated_support: false,
                    allows_installments: false,
                    installment_months: '',
                    installment_amount: '',
                    features: '',
                    is_active: true
                },
                async submitForm() {
                    this.loading = true;
                    
                    try {
                        // Convert features textarea to array
                        const formData = new FormData();
                        Object.keys(this.form).forEach(key => {
                            if (key === 'features') {
                                const featuresArray = this.form[key]
                                    .split('\n')
                                    .map(f => f.trim())
                                    .filter(f => f);
                                featuresArray.forEach((feature, index) => {
                                    formData.append(`features[${index}]`, feature);
                                });
                            } else {
                                formData.append(key, this.form[key]);
                            }
                        });

                        const response = await fetch(this.$el.action, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        });

                        if (!response.ok) throw new Error('Network response was not ok');

                        window.location.href = '{{ route("admin.workstation.plans.index") }}';
                    } catch (error) {
                        console.error('Error:', error);
                        alert('An error occurred while creating the plan. Please try again.');
                    } finally {
                        this.loading = false;
                    }
                }
            }
        }
    </script>
    @endpush
</x-app-layout> 