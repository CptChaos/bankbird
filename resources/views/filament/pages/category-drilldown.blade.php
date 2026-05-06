<x-filament-panels::page>
    {{ $this->form }}

    @php $summary = $this->getSummary(); @endphp

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bb-report-card bb-gradient-orange">
            <div class="bb-report-card-circle"></div>
            <div class="bb-report-card-inner">
                <p class="bb-report-card-label">Totaal uitgegeven</p>
                <p class="bb-report-card-value">€ {{ number_format($summary['total'], 2, ',', '.') }}</p>
            </div>
        </div>

        <div class="bb-report-card bb-gradient-blue">
            <div class="bb-report-card-circle"></div>
            <div class="bb-report-card-inner">
                <p class="bb-report-card-label">Aantal transacties</p>
                <p class="bb-report-card-value">{{ $summary['count'] }}</p>
            </div>
        </div>

        <div class="bb-report-card bb-gradient-navy">
            <div class="bb-report-card-circle"></div>
            <div class="bb-report-card-inner">
                <p class="bb-report-card-label">Gemiddeld per transactie</p>
                <p class="bb-report-card-value">€ {{ number_format($summary['avg'], 2, ',', '.') }}</p>
            </div>
        </div>
    </div>

    {{ $this->table }}
</x-filament-panels::page>
