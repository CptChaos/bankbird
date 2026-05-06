<x-filament-panels::page>
    @php $record = $this->getRecord(); @endphp

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <x-filament::section>
            <div class="space-y-1">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</p>
                <p class="text-lg font-semibold text-gray-950 dark:text-white">{{ $record->status->getLabel() }}</p>
            </div>
        </x-filament::section>

        <x-filament::section>
            <div class="space-y-1">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Totaal</p>
                <p class="text-lg font-semibold text-gray-950 dark:text-white">{{ $record->total }}</p>
            </div>
        </x-filament::section>

        <x-filament::section>
            <div class="space-y-1">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Nieuw</p>
                <p class="text-lg font-semibold text-success-600 dark:text-success-400">{{ $record->new }}</p>
            </div>
        </x-filament::section>

        <x-filament::section>
            <div class="space-y-1">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Dubbelen</p>
                <p class="text-lg font-semibold text-gray-500 dark:text-gray-400">{{ $record->duplicates }}</p>
            </div>
        </x-filament::section>
    </div>

    {{ $this->table }}
</x-filament-panels::page>
