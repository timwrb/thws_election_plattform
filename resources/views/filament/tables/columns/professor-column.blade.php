@php
    $record = $getRecord();
    $professor = $record->professor;
@endphp

<div class="flex items-center space-x-2">
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
                class="flex size-8 p-1 items-center justify-center rounded-full text-sm font-semibold text-white"
                style="background-color: {{ $bgColor }}"
            >
                {{ $initials }}
            </div>
        @endif

        <span class="text-sm text-gray-700 font-medium dark:text-gray-300">
            {{ $professor->full_name }}
        </span>
    @else
        <span class="text-sm text-neutral-300 dark:text-gray-500">
            No professor assigned
        </span>
    @endif
</div>
