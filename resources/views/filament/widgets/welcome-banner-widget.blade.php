<x-filament-widgets::widget>
    <style>
        @keyframes bb-float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        @keyframes bb-fade-in-up {
            from { opacity: 0; transform: translateY(12px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .bb-welcome-banner { animation: bb-fade-in-up 0.6s ease both; }
        .bb-welcome-bird { animation: bb-float 4s ease-in-out infinite; }
    </style>

    <div class="bb-welcome-banner" style="
        background: linear-gradient(135deg, #0D47A1 0%, #1565C0 40%, #1E88E5 70%, #42A5F5 100%);
        border-radius: 1rem;
        padding: 1.75rem 2rem;
        overflow: hidden;
        position: relative;
        display: flex;
        align-items: center;
        justify-content: space-between;
        min-height: 120px;
    ">
        {{-- Decoratieve achtergrondcirkels --}}
        <div style="position:absolute;top:-40px;right:200px;width:200px;height:200px;border-radius:50%;background:rgba(255,255,255,0.06);pointer-events:none;"></div>
        <div style="position:absolute;bottom:-60px;left:40%;width:160px;height:160px;border-radius:50%;background:rgba(255,255,255,0.04);pointer-events:none;"></div>

        {{-- Tekst links --}}
        <div style="position:relative;z-index:1;">
            <div style="color:rgba(255,255,255,0.75);font-size:0.8125rem;font-weight:500;letter-spacing:0.02em;margin-bottom:0.25rem;">
                {{ $this->getFormattedDate() }}
            </div>
            <div style="color:#ffffff;font-size:1.625rem;font-weight:800;letter-spacing:-0.02em;line-height:1.2;">
                {{ $this->getGreeting() }}, {{ $this->getUserFirstName() }}!
            </div>
            <div style="color:rgba(255,255,255,0.7);font-size:0.875rem;font-weight:400;margin-top:0.5rem;">
                Hier is een overzicht van je financiën.
            </div>
        </div>

        {{-- BankBird mascotte rechts --}}
        <div class="bb-welcome-bird" style="position:relative;z-index:1;flex-shrink:0;">
            <img
                src="{{ asset('images/bird.png') }}"
                alt="BankBird"
                style="height:80px;width:auto;object-fit:contain;filter:drop-shadow(0 8px 20px rgba(0,0,0,0.25));"
            >
        </div>
    </div>
</x-filament-widgets::widget>
