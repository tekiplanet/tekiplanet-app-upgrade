@extends('admin.layouts.app')

@section('content')
<div class="container px-6 mx-auto">
    <!-- Header Section -->
    <div class="bg-white rounded-lg shadow-md dark:bg-gray-800 p-6 mb-6">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">
                    {{ $professional->user->first_name }} {{ $professional->user->last_name }}
                </h2>
                <p class="text-gray-600 dark:text-gray-400">
                    {{ $professional->title }}
                </p>
            </div>
            <div class="flex flex-wrap gap-3">
                <button onclick="openEditModal()" 
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    Edit Details
                </button>
                <button onclick="toggleStatus()" 
                        class="px-4 py-2 {{ $professional->status === 'active' ? 'bg-red-600' : 'bg-green-600' }} text-white rounded-lg hover:{{ $professional->status === 'active' ? 'bg-red-700' : 'bg-green-700' }}">
                    {{ $professional->status === 'active' ? 'Deactivate' : 'Activate' }}
                </button>
            </div>
        </div>
    </div>

    <!-- Grid Layout for Information -->
    <div class="grid md:grid-cols-3 gap-6">
        <!-- Owner Information Card -->
        <div class="bg-white rounded-lg shadow-md dark:bg-gray-800 p-6">
            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4">
                Profile Owner
            </h3>
            <div class="flex items-center gap-4 mb-4">
                <div class="h-12 w-12 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                    <span class="text-lg font-semibold text-gray-600 dark:text-gray-300">
                        {{ strtoupper(substr($professional->user->first_name, 0, 1)) }}
                    </span>
                </div>
                <div>
                    <p class="font-medium text-gray-800 dark:text-gray-200">
                        {{ $professional->user->first_name }} {{ $professional->user->last_name }}
                    </p>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        {{ $professional->user->email }}
                    </p>
                </div>
            </div>
            <a href="{{ route('admin.users.show', $professional->user) }}" 
               class="inline-block w-full px-4 py-2 text-center bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                View User Profile
            </a>
        </div>

        <!-- Professional Details -->
        <div class="md:col-span-2 bg-white rounded-lg shadow-md dark:bg-gray-800 p-6">
            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4">
                Professional Details
            </h3>
            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <label class="text-sm text-gray-600 dark:text-gray-400">Title</label>
                    <p class="text-gray-800 dark:text-gray-200">{{ $professional->title }}</p>
                </div>
                <div>
                    <label class="text-sm text-gray-600 dark:text-gray-400">Category</label>
                    <p class="text-gray-800 dark:text-gray-200">{{ $professional->category->name }}</p>
                </div>
                <div>
                    <label class="text-sm text-gray-600 dark:text-gray-400">Expertise Areas</label>
                    <p class="text-gray-800 dark:text-gray-200">
                        {{ is_array($professional->expertise_areas) ? implode(', ', $professional->expertise_areas) : $professional->expertise_areas }}
                    </p>
                </div>
                <div>
                    <label class="text-sm text-gray-600 dark:text-gray-400">Specialization</label>
                    <p class="text-gray-800 dark:text-gray-200">{{ $professional->specialization }}</p>
                </div>
                <div>
                    <label class="text-sm text-gray-600 dark:text-gray-400">Availability Status</label>
                    <p class="text-gray-800 dark:text-gray-200">{{ $professional->availability_status }}</p>
                </div>
                <div>
                    <label class="text-sm text-gray-600 dark:text-gray-400">Certifications</label>
                    <p class="text-gray-800 dark:text-gray-200">
                        {{ is_array($professional->certifications) ? implode(', ', $professional->certifications) : $professional->certifications }}
                    </p>
                </div>
                <div>
                    <label class="text-sm text-gray-600 dark:text-gray-400">Languages</label>
                    <p class="text-gray-800 dark:text-gray-200">
                        {{ is_array($professional->languages) ? implode(', ', $professional->languages) : $professional->languages }}
                    </p>
                </div>
                <div>
                    <label class="text-sm text-gray-600 dark:text-gray-400">Rating</label>
                    <p class="text-gray-800 dark:text-gray-200">{{ number_format($professional->rating, 1) }}</p>
                </div>
                <div>
                    <label class="text-sm text-gray-600 dark:text-gray-400">Total Sessions</label>
                    <p class="text-gray-800 dark:text-gray-200">{{ $professional->total_sessions }}</p>
                </div>
                <div>
                    <label class="text-sm text-gray-600 dark:text-gray-400">Years of Experience</label>
                    <p class="text-gray-800 dark:text-gray-200">{{ $professional->years_of_experience }}</p>
                </div>
                <div>
                    <label class="text-sm text-gray-600 dark:text-gray-400">Hourly Rate</label>
                    <p class="text-gray-800 dark:text-gray-200">₦{{ number_format($professional->hourly_rate, 2) }}</p>
                </div>
                <div>
                    <label class="text-sm text-gray-600 dark:text-gray-400">Timezone</label>
                    <p class="text-gray-800 dark:text-gray-200">{{ $professional->timezone }}</p>
                </div>
                <div>
                    <label class="text-sm text-gray-600 dark:text-gray-400">Preferred Contact</label>
                    <p class="text-gray-800 dark:text-gray-200">{{ $professional->preferred_contact_method }}</p>
                </div>
                <div>
                    <label class="text-sm text-gray-600 dark:text-gray-400">Status</label>
                    <p class="mt-1">
                        <span class="px-2 py-1 text-sm rounded-full {{ $professional->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ ucfirst($professional->status) }}
                        </span>
                    </p>
                </div>
                <!-- Social Links -->
                <div class="md:col-span-2 grid md:grid-cols-3 gap-4">
                    @if($professional->linkedin_url)
                    <a href="{{ $professional->linkedin_url }}" target="_blank" 
                       class="flex items-center gap-2 text-blue-600 hover:text-blue-700">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/>
                        </svg>
                        LinkedIn
                    </a>
                    @endif
                    @if($professional->github_url)
                    <a href="{{ $professional->github_url }}" target="_blank"
                       class="flex items-center gap-2 text-gray-800 hover:text-gray-900 dark:text-gray-200 dark:hover:text-gray-100">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                        </svg>
                        GitHub
                    </a>
                    @endif
                    @if($professional->portfolio_url)
                    <a href="{{ $professional->portfolio_url }}" target="_blank"
                       class="flex items-center gap-2 text-purple-600 hover:text-purple-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        Portfolio
                    </a>
                    @endif
                </div>
                <div class="md:col-span-2">
                    <label class="text-sm text-gray-600 dark:text-gray-400">Bio</label>
                    <p class="text-gray-800 dark:text-gray-200">{{ $professional->bio }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

@include('admin.professionals.partials.edit-modal')
@include('admin.professionals.partials.status-modal')
@endsection

@push('scripts')
<script>
// ... JavaScript for modals and status toggle will be added next
</script>
@endpush 