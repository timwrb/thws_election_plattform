@php
    $record = $getRecord();
    $professor = $record->professor;
@endphp

<div class="flex items-center gap-2">
    @if ($professor)
        @php
            $avatarUrl = $professor->getFilamentAvatarUrl();
            $initials = $professor->getInitials();
            $bgColor = $professor->getAvatarColor();
        @endphp

        @if ($avatarUrl)
            <img
                src="{{ $avatarUrl }}"
                alt="{{ $professor->full_name }}"
                class="size-8 rounded-full object-cover"
            />
        @else
            <div
                class="flex size-8 items-center justify-center rounded-full text-xs font-medium text-white"
                style="background-color: {{ $bgColor }}"
            >
                {{ $initials }}
            </div>
        @endif

        <span class="text-sm text-gray-700 dark:text-gray-300">
            {{ $professor->full_name }}
        </span>
    @else
        <span class="text-sm text-gray-400 dark:text-gray-500">
            No professor assigned
        </span>
    @endif
</div>
