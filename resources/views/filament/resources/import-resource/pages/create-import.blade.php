<x-filament-panels::page>

    @if (!$this->analyzed)
        <form wire:submit="analyze" class="space-y-4">
            {{ $this->form }}
            <x-filament::button type="submit" icon="heroicon-o-magnifying-glass">
                Analyseren
            </x-filament::button>
        </form>

    @else
        <form wire:submit="save" class="space-y-6">
            <x-filament::section>
                <x-slot name="heading">
                    Geanalyseerd — {{ count($this->persistedFiles) }} bestand{{ count($this->persistedFiles) === 1 ? '' : 'en' }}
                </x-slot>
                <x-slot name="description">Per bestand: gedetecteerde IBAN + rekening om aan te koppelen.</x-slot>

                @php $accountOptions = $this->getAccountOptions(); @endphp

                <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-white/10">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-white/10">
                        <thead class="bg-gray-50 dark:bg-white/5">
                            <tr>
                                <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Bestand</th>
                                <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">IBAN</th>
                                <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Match</th>
                                <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 w-72">Rekening</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-white/10 bg-white dark:bg-transparent">
                            @foreach ($this->persistedFiles as $idx => $file)
                                <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                                    <td class="px-4 py-3 align-middle">
                                        <div class="flex items-center gap-2 min-w-0">
                                            <x-filament::icon icon="heroicon-o-document-text" class="h-4 w-4 text-gray-400 shrink-0" />
                                            <span class="font-mono text-sm text-gray-950 dark:text-white truncate" title="{{ $file['name'] }}">{{ $file['name'] }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 align-middle">
                                        @if ($file['iban'])
                                            <span class="font-mono text-xs text-gray-600 dark:text-gray-300">{{ $file['iban'] }}</span>
                                        @else
                                            <span class="text-xs italic text-gray-400">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 align-middle whitespace-nowrap">
                                        @if ($file['iban'] && $file['account_id'])
                                            <span class="inline-flex items-center gap-1 rounded-full bg-success-50 dark:bg-success-400/10 px-2 py-0.5 text-xs font-medium text-success-700 dark:text-success-400">
                                                <x-filament::icon icon="heroicon-m-check-circle" class="h-3.5 w-3.5" />
                                                automatisch
                                            </span>
                                        @elseif ($file['iban'])
                                            <span class="inline-flex items-center gap-1 rounded-full bg-warning-50 dark:bg-warning-400/10 px-2 py-0.5 text-xs font-medium text-warning-700 dark:text-warning-400">
                                                <x-filament::icon icon="heroicon-m-exclamation-triangle" class="h-3.5 w-3.5" />
                                                geen match
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 rounded-full bg-warning-50 dark:bg-warning-400/10 px-2 py-0.5 text-xs font-medium text-warning-700 dark:text-warning-400">
                                                <x-filament::icon icon="heroicon-m-exclamation-triangle" class="h-3.5 w-3.5" />
                                                geen IBAN
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 align-middle">
                                        <x-filament::input.wrapper>
                                            <x-filament::input.select wire:model.live="persistedFiles.{{ $idx }}.account_id">
                                                <option value="">— Kies rekening —</option>
                                                @foreach ($accountOptions as $id => $label)
                                                    <option value="{{ $id }}">{{ $label }}</option>
                                                @endforeach
                                            </x-filament::input.select>
                                        </x-filament::input.wrapper>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-filament::section>

            <div class="flex items-center gap-3">
                <x-filament::button
                    type="submit"
                    icon="heroicon-o-arrow-up-tray"
                    :disabled="! $this->ready_to_import"
                >
                    {{ count($this->persistedFiles) === 1 ? 'Importeren' : 'Alles importeren (' . count($this->persistedFiles) . ')' }}
                </x-filament::button>

                <x-filament::button
                    type="button"
                    color="gray"
                    wire:click="$set('analyzed', false)"
                    icon="heroicon-o-arrow-left"
                >
                    Andere bestanden
                </x-filament::button>
            </div>
        </form>
    @endif

</x-filament-panels::page>
