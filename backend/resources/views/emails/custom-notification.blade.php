<x-mail.layout>
    <div>
        <h2 class="text-xl font-bold mb-4">{{ $title }}</h2>
        
        <div class="text-gray-600 dark:text-gray-400">
            {!! nl2br(e($content)) !!}
        </div>

        <div class="mt-8 text-sm text-gray-500">
            <p>If you have any questions, please don't hesitate to contact support.</p>
        </div>
    </div>
</x-mail.layout> 