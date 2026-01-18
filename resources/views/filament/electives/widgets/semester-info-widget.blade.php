<x-filament-widgets::widget>
    <x-filament::section>
        @if($this->semester)
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-primary-500/10">
                    <x-filament::icon
                        icon="heroicon-o-calendar"
                        class="h-6 w-6 text-primary-500"
                    />
                </div>

                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Current Semester: {{ $semester->getLabel() }}
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        {{ $this->semester->season->value }} {{ $this->semester->year }}
                    </p>
                </div>

                <div class="text-right">
                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        Enrollment Status
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Open for registration
                    </p>
                </div>
            </div>
        @else
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-warning-500/10">
                    <x-filament::icon
                        icon="heroicon-o-exclamation-triangle"
                        class="h-6 w-6 text-warning-500"
                    />
                </div>

                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        No Active Semester
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Please contact the administration for more information.
                    </p>
                </div>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
