<x-filament-panels::page>
    {{-- Terug knop --}}
    <div class="-mt-2 mb-2">
        <a
            href="{{ $this->getBackUrl() }}"
            wire:navigate
            style="display:inline-flex;align-items:center;gap:6px;font-size:0.875rem;color:#1E88E5;font-weight:500;text-decoration:none;transition:color 0.12s ease;"
        >
            <x-heroicon-m-arrow-left style="width:16px;height:16px;" />
            Terug naar Maandrapport
        </a>
    </div>

    @php $data = $this->getPageData(); @endphp

    {{-- Totaal gradient card --}}
    @if (! empty($data['merchants']))
        @php $totaal = array_sum(array_column($data['merchants'], 'amount')); @endphp
        <div class="bb-report-card bb-gradient-orange" style="margin-bottom:0;">
            <div class="bb-report-card-circle"></div>
            <div class="bb-report-card-inner">
                <p class="bb-report-card-label">Totaal uitgegeven</p>
                <p class="bb-report-card-value">€ {{ number_format($totaal, 2, ',', '.') }}</p>
                <p style="color:rgba(255,255,255,0.6);font-size:0.75rem;margin-top:0.25rem;">
                    {{ count($data['merchants']) }} {{ count($data['merchants']) === 1 ? 'merchant' : 'merchants' }}
                </p>
            </div>
        </div>
    @endif

    @if (empty($data['merchants']))
        <x-filament::section>
            <p class="text-sm" style="color:#667085;">Geen uitgaven gevonden voor deze categorie.</p>
        </x-filament::section>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach ($data['merchants'] as $merchant)
                <div class="bb-merchant-card">
                    {{-- Merchant naam & totaal --}}
                    <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;margin-bottom:12px;">
                        <div style="display:flex;align-items:center;gap:10px;">
                            @if (!empty($merchant['logo_url']))
                                <div style="width:36px;height:36px;flex-shrink:0;overflow:hidden;border-radius:8px;background:#F7FBFF;display:flex;align-items:center;justify-content:center;">
                                    <img src="{{ $merchant['logo_url'] }}" alt="" style="width:32px;height:32px;object-fit:contain;">
                                </div>
                            @else
                                <div style="width:36px;height:36px;flex-shrink:0;border-radius:8px;background:#EAF6FF;display:flex;align-items:center;justify-content:center;">
                                    <x-heroicon-o-building-storefront style="width:18px;height:18px;color:#1E88E5;" />
                                </div>
                            @endif
                            <div>
                                <p style="font-weight:700;color:#0B1F3A;font-size:0.9rem;line-height:1.2;">{{ $merchant['name'] }}</p>
                                <p style="font-size:0.75rem;color:#667085;margin-top:2px;">
                                    {{ $merchant['count'] }} {{ $merchant['count'] === 1 ? 'transactie' : 'transacties' }}
                                </p>
                            </div>
                        </div>
                        <p style="font-size:1.125rem;font-weight:800;color:#D02B2B;white-space:nowrap;tabular-nums;">
                            € {{ number_format($merchant['amount'], 2, ',', '.') }}
                        </p>
                    </div>

                    {{-- Transacties lijst --}}
                    <div style="border-top:1px solid #F0F6FC;padding-top:8px;margin:0 -4px;">
                        @foreach ($merchant['transactions'] as $tx)
                            <div style="display:flex;align-items:center;justify-content:space-between;padding:5px 8px;border-radius:6px;transition:background 0.1s;">
                                <span style="font-size:0.8125rem;color:#667085;font-variant-numeric:tabular-nums;">
                                    {{ $tx['date']->format('d-m-Y') }}
                                </span>
                                <span style="font-size:0.8125rem;font-weight:600;color:#0B1F3A;font-variant-numeric:tabular-nums;">
                                    € {{ number_format($tx['amount'], 2, ',', '.') }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</x-filament-panels::page>
