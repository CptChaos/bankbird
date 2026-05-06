<x-filament-panels::page>
    {{ $this->form }}

    @php $report = $this->getReportData(); @endphp

    {{-- Totalen gradient cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bb-report-card bb-gradient-green">
            <div class="bb-report-card-circle"></div>
            <div class="bb-report-card-inner">
                <p class="bb-report-card-label">Inkomsten</p>
                <p class="bb-report-card-value">€ {{ number_format($report['totalIncome'], 2, ',', '.') }}</p>
            </div>
        </div>

        <div class="bb-report-card bb-gradient-orange">
            <div class="bb-report-card-circle"></div>
            <div class="bb-report-card-inner">
                <p class="bb-report-card-label">Uitgaven</p>
                <p class="bb-report-card-value">€ {{ number_format($report['totalExpenses'], 2, ',', '.') }}</p>
            </div>
        </div>

        <div class="bb-report-card {{ $report['net'] >= 0 ? 'bb-gradient-blue' : 'bb-gradient-navy' }}">
            <div class="bb-report-card-circle"></div>
            <div class="bb-report-card-inner">
                <p class="bb-report-card-label">Netto saldo</p>
                <p class="bb-report-card-value">€ {{ number_format($report['net'], 2, ',', '.') }}</p>
            </div>
        </div>
    </div>

    {{-- Categorieoverzicht --}}
    <x-filament::section>
        <x-slot name="heading">Uitgaven per categorie</x-slot>

        @if ($report['categoryBreakdown']->isEmpty())
            <p class="text-sm text-gray-500">Geen uitgaven gevonden voor deze maand.</p>
        @else
            <div class="overflow-hidden rounded-xl border border-[#DDEAF3]">
                <table class="bb-report-table">
                    <thead>
                        <tr>
                            <th>Categorie</th>
                            <th class="text-right">Transacties</th>
                            <th class="text-right">Bedrag</th>
                            <th class="text-right">%</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($report['categoryBreakdown'] as $cat)
                            <tr wire:click="goToCategory({{ $cat['id'] }})">
                                <td>
                                    <span style="display:inline-flex;align-items:center;gap:8px;width:100%;">
                                        <span style="width:10px;height:10px;border-radius:50%;flex-shrink:0;background-color:{{ $cat['color'] }};"></span>
                                        <span style="font-weight:500;">{{ $cat['name'] }}</span>
                                        <x-heroicon-m-chevron-right style="width:14px;height:14px;color:#DDEAF3;margin-left:auto;" />
                                    </span>
                                </td>
                                <td class="text-right" style="color:#667085;">{{ $cat['count'] }}</td>
                                <td class="text-right" style="font-weight:600;">€ {{ number_format($cat['amount'], 2, ',', '.') }}</td>
                                <td class="text-right" style="color:#667085;">{{ $cat['percentage'] }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-filament::section>
</x-filament-panels::page>
