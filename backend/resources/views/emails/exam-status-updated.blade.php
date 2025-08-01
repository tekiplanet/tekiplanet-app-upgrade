@php
\Log::info('Rendering exam-status-updated template:', [
    'variables' => get_defined_vars()['__data'],
    'action_exists' => isset($action),
    'userexam_exists' => isset($userExam),
    'userexam_action_exists' => isset($userExam->action)
]);
@endphp

<x-mail.layout>
    <x-slot:greeting>
        Hello {{ $userExam->user->name }},
    </x-slot:greeting>

    @if($action === 'score')
        <p style="color: #374151; margin-bottom: 1rem;">Your results for <strong>{{ $userExam->courseExam->title }}</strong> are now available.</p>

        @php
            $bgColor = $passed ? '#d1fae5' : '#fee2e2';
            $borderColor = $passed ? '#059669' : '#dc2626';
            $textColor = $passed ? '#065f46' : '#991b1b';
            $resultText = $passed ? 'PASSED' : 'FAILED';
        @endphp

        <div style="margin: 20px 0; padding: 15px; background-color: {{ $bgColor }}; border: 1px solid {{ $borderColor }}; border-radius: 5px;">
            <div style="margin-bottom: 10px;">
                <span style="font-size: 18px; font-weight: bold; color: {{ $textColor }};">
                    <strong>Result:</strong> {{ $resultText }}
                </span>
            </div>
            <div style="margin: 10px 0; color: #1f2937;">
                <strong style="color: #1f2937;">Score:</strong> {{ $userExam->score }}/{{ $userExam->total_score }}
                ({{ round($scorePercentage) }}%)
            </div>
            <div style="margin: 10px 0; color: #1f2937;">
                <strong style="color: #1f2937;">Required to Pass:</strong> {{ $userExam->courseExam->pass_percentage }}%
            </div>
        </div>
    @else
        <p style="color: #374151; margin-bottom: 1rem;">Your exam status for <strong>{{ $userExam->courseExam->title }}</strong> has been updated.</p>

        <div style="margin: 20px 0; padding: 15px; background-color: #f3f4f6; border: 1px solid #d1d5db; border-radius: 5px;">
            <div style="color: #1f2937;">
                <strong style="color: #1f2937;">Status:</strong> {{ str_replace('_', ' ', ucfirst($userExam->status)) }}
            </div>
        </div>
    @endif

    <x-slot:closing>
        You can view your exam details by logging into your account.
    </x-slot:closing>
</x-mail.layout> 