<x-mail.layout>
    <x-slot:greeting>
        Hello {{ $enrollment->user->first_name }},
    </x-slot:greeting>

    @if($fieldUpdated === 'progress')
        <p>Your progress in the course "{{ $course->title }}" has been updated.</p>
    @elseif($fieldUpdated === 'payment_status')
        <p>Your payment status for the course "{{ $course->title }}" has been updated.</p>
    @else
        <p>Your enrollment status for the course "{{ $course->title }}" has been updated.</p>
    @endif

    <div style="margin: 20px 0; padding: 15px; background-color: #f8f9fa; border-radius: 6px;" class="info-box">
        <p style="margin: 0;"><strong>{{ $fieldLabel }}:</strong></p>
        <p style="margin: 5px 0;">Changed from: 
            @if($fieldUpdated === 'progress')
                {{ $oldValue }}
            @else
                {{ str_replace('_', ' ', ucfirst($oldValue)) }}
            @endif
        </p>
        <p style="margin: 5px 0;">To: 
            @if($fieldUpdated === 'progress')
                {{ $newValue }}
            @else
                {{ str_replace('_', ' ', ucfirst($newValue)) }}
            @endif
        </p>
    </div>

    <x-slot:closing>
        If you have any questions, please don't hesitate to contact us.
    </x-slot:closing>
</x-mail.layout> 