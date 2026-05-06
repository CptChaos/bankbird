<x-filament-panels::page>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">

        {{-- Card: Backup maken --}}
        <x-filament::section>
            <x-slot name="heading">
                <span class="flex items-center gap-2">
                    <span class="bb-backup-icon-wrap bb-backup-icon-blue">
                        <x-filament::icon icon="heroicon-o-arrow-down-tray" class="w-5 h-5" />
                    </span>
                    Backup maken
                </span>
            </x-slot>
            <x-slot name="description">Exporteer al je gegevens naar je eigen computer</x-slot>

            <div class="bb-backup-body">
                <p class="bb-backup-description">
                    Maak een volledige back-up van je BankBird-gegevens. Je back-up bevat alle
                    transacties, categorieën en merchants. Het bestand wordt direct gedownload
                    naar je computer — standaard in je map <strong>Downloads</strong>.
                </p>

                <div class="bb-backup-info">
                    <x-filament::icon icon="heroicon-o-information-circle" class="w-4 h-4 shrink-0" />
                    <span>Bewaar je back-up op een veilige plek zodat je die later kunt terugzetten.</span>
                </div>

                <x-filament::button
                    wire:click="downloadBackup"
                    wire:loading.attr="disabled"
                    wire:target="downloadBackup"
                    icon="heroicon-o-arrow-down-tray"
                    size="lg"
                    style="width: 100%;"
                >
                    <span wire:loading.remove wire:target="downloadBackup">Backup downloaden</span>
                    <span wire:loading wire:target="downloadBackup">Even geduld…</span>
                </x-filament::button>
            </div>
        </x-filament::section>

        {{-- Card: Backup terugzetten --}}
        <x-filament::section>
            <x-slot name="heading">
                <span class="flex items-center gap-2">
                    <span class="bb-backup-icon-wrap bb-backup-icon-amber">
                        <x-filament::icon icon="heroicon-o-arrow-path" class="w-5 h-5" />
                    </span>
                    Backup terugzetten
                </span>
            </x-slot>
            <x-slot name="description">Herstel je gegevens vanuit een eerder gemaakt back-upbestand</x-slot>

            <div class="bb-backup-body">
                <p class="bb-backup-description">
                    Zet een eerder gemaakte back-up terug. Selecteer het bestand van je computer
                    en klik op terugzetten. Bestaande gegevens worden
                    <strong>niet overschreven</strong> — alleen ontbrekende categorieën en
                    merchants worden toegevoegd.
                </p>

                {{ $this->form }}

                <x-filament::button
                    wire:click="mountAction('restoreBackup')"
                    color="warning"
                    icon="heroicon-o-arrow-path"
                    size="lg"
                    style="width: 100%;"
                >
                    Terugzetten
                </x-filament::button>
            </div>
        </x-filament::section>

    </div>

    <x-filament-actions::modals />
</x-filament-panels::page>
