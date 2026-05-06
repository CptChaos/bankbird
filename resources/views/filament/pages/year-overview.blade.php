<x-filament-panels::page>
    {{ $this->form }}

    @php $report = $this->getYearData(); @endphp

    {{-- Totalen gradient cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bb-report-card bb-gradient-green">
            <div class="bb-report-card-circle"></div>
            <div class="bb-report-card-inner">
                <p class="bb-report-card-label">Totaal inkomsten {{ $report['year'] }}</p>
                <p class="bb-report-card-value">€ {{ number_format($report['totalIncome'], 2, ',', '.') }}</p>
            </div>
        </div>

        <div class="bb-report-card bb-gradient-orange">
            <div class="bb-report-card-circle"></div>
            <div class="bb-report-card-inner">
                <p class="bb-report-card-label">Totaal uitgaven {{ $report['year'] }}</p>
                <p class="bb-report-card-value">€ {{ number_format($report['totalExpenses'], 2, ',', '.') }}</p>
            </div>
        </div>

        <div class="bb-report-card {{ $report['totalNet'] >= 0 ? 'bb-gradient-blue' : 'bb-gradient-navy' }}">
            <div class="bb-report-card-circle"></div>
            <div class="bb-report-card-inner">
                <p class="bb-report-card-label">Netto over jaar</p>
                <p class="bb-report-card-value">€ {{ number_format($report['totalNet'], 2, ',', '.') }}</p>
            </div>
        </div>
    </div>

    <x-filament::section>
        <x-slot name="heading">{{ $report['year'] }} — maand voor maand</x-slot>

        <div class="overflow-hidden rounded-xl border border-[#DDEAF3]">
            <table class="bb-report-table">
                <thead>
                    <tr>
                        <th>Maand</th>
                        <th class="text-right">Inkomsten</th>
                        <th class="text-right">Uitgaven</th>
                        <th class="text-right">Saldo</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($report['rows'] as $row)
                        <tr onclick="window.location.href='{{ \App\Filament\Pages\MonthlyReport::getUrl() }}?month={{ $row['monthKey'] }}'">
                            <td>
                                <span style="display:inline-flex;align-items:center;gap:8px;width:100%;font-weight:500;text-transform:capitalize;">
                                    {{ $row['month'] }}
                                    <x-heroicon-m-chevron-right style="width:14px;height:14px;color:#DDEAF3;margin-left:auto;" />
                                </span>
                            </td>
                            <td class="text-right" style="color:{{ $row['income'] > 0 ? '#0A9660' : '#94a3b8' }};font-weight:500;">
                                {{ $row['income'] > 0 ? '€ ' . number_format($row['income'], 2, ',', '.') : '—' }}
                            </td>
                            <td class="text-right" style="color:{{ $row['expenses'] > 0 ? '#D02B2B' : '#94a3b8' }};font-weight:500;">
                                {{ $row['expenses'] > 0 ? '€ ' . number_format($row['expenses'], 2, ',', '.') : '—' }}
                            </td>
                            <td class="text-right" style="font-weight:700;color:{{ $row['net'] >= 0 ? '#0A9660' : '#D02B2B' }};">
                                € {{ number_format($row['net'], 2, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td>Totaal</td>
                        <td class="text-right" style="color:#0A9660;">€ {{ number_format($report['totalIncome'], 2, ',', '.') }}</td>
                        <td class="text-right" style="color:#D02B2B;">€ {{ number_format($report['totalExpenses'], 2, ',', '.') }}</td>
                        <td class="text-right" style="color:{{ $report['totalNet'] >= 0 ? '#0A9660' : '#D02B2B' }};">
                            € {{ number_format($report['totalNet'], 2, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </x-filament::section>
</x-filament-panels::page>
