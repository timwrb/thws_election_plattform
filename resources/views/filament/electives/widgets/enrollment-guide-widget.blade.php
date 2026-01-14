<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Enrollment Guide
        </x-slot>

        <x-slot name="description">
            Follow these steps to complete your course enrollment
        </x-slot>

        <div class="space-y-4">
            {{-- Step 1 --}}
            <div class="flex gap-4">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary-500 text-white font-semibold">
                    1
                </div>
                <div class="flex-1">
                    <h4 class="font-semibold text-gray-900 dark:text-white">
                        Browse Available Courses
                    </h4>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Explore AWPF (General Electives) and FWPM (Subject-Specific Electives) courses. Review course details, schedules, and requirements.
                    </p>
                </div>
            </div>

            {{-- Step 2 --}}
            <div class="flex gap-4">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary-500 text-white font-semibold">
                    2
                </div>
                <div class="flex-1">
                    <h4 class="font-semibold text-gray-900 dark:text-white">
                        Select Your Preferences
                    </h4>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Choose up to {{ config('electives.max_selections.awpf', 2) }} AWPF and {{ config('electives.max_selections.fwpm', 3) }} FWPM courses. Rank them by priority (1st choice, 2nd choice, etc.).
                    </p>
                </div>
            </div>

            {{-- Step 3 --}}
            <div class="flex gap-4">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary-500 text-white font-semibold">
                    3
                </div>
                <div class="flex-1">
                    <h4 class="font-semibold text-gray-900 dark:text-white">
                        Register for Research Projects (Optional)
                    </h4>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Browse available research projects and register if interested. Check supervisor availability and project capacity.
                    </p>
                </div>
            </div>

            {{-- Step 4 --}}
            <div class="flex gap-4">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary-500 text-white font-semibold">
                    4
                </div>
                <div class="flex-1">
                    <h4 class="font-semibold text-gray-900 dark:text-white">
                        Submit Your Selections
                    </h4>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Review your choices and submit your enrollment. Your selections will be marked as "Pending" awaiting approval.
                    </p>
                </div>
            </div>

            {{-- Step 5 --}}
            <div class="flex gap-4">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-success-500 text-white font-semibold">
                    5
                </div>
                <div class="flex-1">
                    <h4 class="font-semibold text-gray-900 dark:text-white">
                        Wait for Confirmation
                    </h4>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        The administration will review and approve your selections. You'll receive a confirmation once your enrollment is processed.
                    </p>
                </div>
            </div>
        </div>

        <div class="mt-6 rounded-lg bg-primary-50 p-4 dark:bg-primary-950">
            <div class="flex gap-3">
                <x-filament::icon
                    icon="heroicon-o-information-circle"
                    class="h-5 w-5 text-primary-500 shrink-0"
                />
                <div class="text-sm text-primary-700 dark:text-primary-400">
                    <strong>Important:</strong> Your 1st choice has the highest priority. The system will attempt to place you in your top preferences first. Make sure to rank courses carefully!
                </div>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
