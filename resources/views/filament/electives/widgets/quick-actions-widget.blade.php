<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Quick Actions
        </x-slot>

        <x-slot name="description">
            Start your course enrollment process
        </x-slot>

        <div class="grid gap-4 md:grid-cols-3">
            {{-- AWPF Enrollment --}}
            <a
                href="{{ \App\Filament\Electives\Resources\Awpfs\AwpfResource::getUrl('enroll') }}"
                class="flex flex-col items-center gap-3 rounded-lg border border-gray-200 bg-white p-6 transition hover:border-primary-500 hover:shadow-md dark:border-gray-700 dark:bg-gray-800 dark:hover:border-primary-500"
            >
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-primary-500/10">
                    <x-filament::icon
                        icon="heroicon-o-academic-cap"
                        class="h-6 w-6 text-primary-500"
                    />
                </div>

                <div class="text-center">
                    <h4 class="font-semibold text-gray-900 dark:text-white">
                        Enroll in AWPF
                    </h4>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        General elective courses
                    </p>
                </div>
            </a>

            {{-- FWPM Enrollment --}}
            <a
                href="{{ \App\Filament\Electives\Resources\Fwpms\FwpmResource::getUrl('enroll') }}"
                class="flex flex-col items-center gap-3 rounded-lg border border-gray-200 bg-white p-6 transition hover:border-primary-500 hover:shadow-md dark:border-gray-700 dark:bg-gray-800 dark:hover:border-primary-500"
            >
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-primary-500/10">
                    <x-filament::icon
                        icon="heroicon-o-book-open"
                        class="h-6 w-6 text-primary-500"
                    />
                </div>

                <div class="text-center">
                    <h4 class="font-semibold text-gray-900 dark:text-white">
                        Enroll in FWPM
                    </h4>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Subject-specific electives
                    </p>
                </div>
            </a>

            {{-- Research Projects --}}
            <a
                href="{{ \App\Filament\Electives\Resources\ResearchProjects\ResearchProjectResource::getUrl('index') }}"
                class="flex flex-col items-center gap-3 rounded-lg border border-gray-200 bg-white p-6 transition hover:border-primary-500 hover:shadow-md dark:border-gray-700 dark:bg-gray-800 dark:hover:border-primary-500"
            >
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-primary-500/10">
                    <x-filament::icon
                        icon="heroicon-o-beaker"
                        class="h-6 w-6 text-primary-500"
                    />
                </div>

                <div class="text-center">
                    <h4 class="font-semibold text-gray-900 dark:text-white">
                        Research Projects
                    </h4>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Browse available projects
                    </p>
                </div>
            </a>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
