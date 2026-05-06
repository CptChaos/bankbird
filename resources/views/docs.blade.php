@extends('layouts.public')

@section('title', 'Documentatie — BankBird')
@section('description', 'Technische documentatie voor BankBird. Architectuur, modellen, services en hoe alles samenwerkt.')

@section('content')

{{-- Hero --}}
<section style="background:linear-gradient(145deg,#0D47A1,#1565C0,#1E88E5);padding:4rem 1.5rem 6rem;position:relative;overflow:hidden;">
    <div style="position:absolute;top:-80px;left:-60px;width:400px;height:400px;background:radial-gradient(circle,rgba(255,255,255,0.07) 0%,transparent 70%);pointer-events:none;"></div>
    <div class="bb-wrap" style="position:relative;z-index:1;">
        <div style="display:flex;align-items:center;gap:2rem;flex-wrap:wrap;">
            <div style="flex:1;min-width:280px;">
                <div class="bb-pill" style="background:rgba(255,255,255,0.15);border-color:rgba(255,255,255,0.25);color:white;margin-bottom:1rem;">📚 Documentatie</div>
                <h1 style="font-size:clamp(2rem,4vw,3rem);font-weight:900;color:white;margin:0 0 1rem;line-height:1.1;letter-spacing:-0.02em;">
                    Hoe BankBird<br>in elkaar zit
                </h1>
                <p style="font-size:1.0625rem;color:rgba(255,255,255,0.8);margin:0;max-width:480px;line-height:1.65;">
                    Alles over de architectuur, modellen en services voor developers die BankBird willen begrijpen, aanpassen of uitbreiden.
                </p>
            </div>
            <div style="flex-shrink:0;animation:float 5s ease-in-out infinite;">
                <img src="{{ asset('images/bird.png') }}" alt="BankBird" style="height:180px;width:auto;filter:drop-shadow(0 16px 32px rgba(0,0,0,0.25));">
            </div>
        </div>
    </div>
    <div style="position:absolute;bottom:0;left:0;right:0;">
        <svg viewBox="0 0 1440 60" fill="none" xmlns="http://www.w3.org/2000/svg" style="display:block;width:100%;">
            <path d="M0,30 C480,60 960,0 1440,30 L1440,60 L0,60 Z" fill="#F0F6FF"/>
        </svg>
    </div>
</section>

{{-- Content --}}
<section style="background:#F0F6FF;padding:3rem 1.5rem 6rem;">
    <div class="bb-wrap">
        <div class="bb-grid-2" style="grid-template-columns:220px 1fr;gap:2.5rem;align-items:start;">

            {{-- Sidebar TOC --}}
            <div style="position:sticky;top:84px;" class="hidden-mobile">
                <div class="bb-card-flat" style="padding:1.25rem;">
                    <div style="font-size:0.75rem;font-weight:800;text-transform:uppercase;letter-spacing:0.08em;color:#6B7A99;margin-bottom:1rem;">Inhoud</div>
                    <nav style="display:flex;flex-direction:column;gap:0.125rem;">
                        @foreach([
                            ['#architectuur', '🏗️ Architectuur'],
                            ['#directory', '📁 Mapstructuur'],
                            ['#modellen', '🗄️ Modellen'],
                            ['#services', '⚙️ Services'],
                            ['#workflows', '🔄 Workflows'],
                            ['#testen', '🧪 Testen'],
                        ] as [$href, $label])
                        <a href="{{ $href }}" style="font-size:0.8125rem;color:#6B7A99;text-decoration:none;padding:0.4rem 0.75rem;border-radius:0.5rem;transition:color 0.15s,background 0.15s;" onmouseover="this.style.color='#1E88E5';this.style.background='#EEF5FF'" onmouseout="this.style.color='#6B7A99';this.style.background='transparent'">{{ $label }}</a>
                        @endforeach
                    </nav>
                </div>
            </div>

            {{-- Main content --}}
            <div style="display:flex;flex-direction:column;gap:1.5rem;min-width:0;">

                {{-- Architectuur --}}
                <div id="architectuur" class="bb-card-flat reveal" style="padding:2rem;">
                    <div style="display:flex;align-items:center;gap:0.75rem;margin-bottom:1.5rem;">
                        <span style="font-size:1.375rem;">🏗️</span>
                        <h2 style="font-size:1.375rem;font-weight:800;margin:0;color:#0B1F3A;">Architectuur</h2>
                    </div>
                    <p style="font-size:0.9375rem;color:#6B7A99;line-height:1.7;margin:0 0 1.5rem;">
                        BankBird is een Laravel 11 applicatie met Filament 5 als admin panel. De app draait volledig op jouw eigen server — geen externe services behalve de optionele AI API.
                    </p>
                    <div class="bb-code-block">
                        <div class="bb-code-bar">
                            <div class="bb-dot" style="background:#FF6058;"></div>
                            <div class="bb-dot" style="background:#FFBD2E;"></div>
                            <div class="bb-dot" style="background:#27C93F;"></div>
                            <span style="margin-left:auto;font-size:0.6875rem;color:rgba(255,255,255,0.3);font-family:monospace;">architectuur</span>
                        </div>
                        <pre><span class="tok-c">Browser / Livewire UI</span>
        ↓
<span class="tok-k">Filament 5</span> (Admin Panel)
        ↓
<span class="tok-fn">Controllers / Resources / Pages</span>
        ↓
<span class="tok-k">Services</span> (ImportService, AiCategorizationService, ...)
        ↓
<span class="tok-fn">Eloquent Models</span> (Transaction, Category, Merchant, ...)
        ↓
<span class="tok-k">Database</span> (MySQL of SQLite)
        ↓  (optioneel)
<span class="tok-s">AI API</span> (Anthropic Claude / OpenAI GPT)</pre>
                    </div>
                </div>

                {{-- Directory --}}
                <div id="directory" class="bb-card-flat reveal" style="padding:2rem;">
                    <div style="display:flex;align-items:center;gap:0.75rem;margin-bottom:1.5rem;">
                        <span style="font-size:1.375rem;">📁</span>
                        <h2 style="font-size:1.375rem;font-weight:800;margin:0;color:#0B1F3A;">Mapstructuur</h2>
                    </div>
                    <div class="bb-code-block">
                        <div class="bb-code-bar">
                            <div class="bb-dot" style="background:#FF6058;"></div>
                            <div class="bb-dot" style="background:#FFBD2E;"></div>
                            <div class="bb-dot" style="background:#27C93F;"></div>
                            <span style="margin-left:auto;font-size:0.6875rem;color:rgba(255,255,255,0.3);font-family:monospace;">mapstructuur</span>
                        </div>
                        <pre>bankbird/
├── <span class="tok-k">app/</span>
│   ├── <span class="tok-fn">Filament/</span>         <span class="tok-c"># Admin resources & pages</span>
│   ├── <span class="tok-fn">Models/</span>           <span class="tok-c"># Eloquent modellen</span>
│   └── <span class="tok-fn">Services/</span>         <span class="tok-c"># Business logica</span>
├── <span class="tok-k">database/</span>
│   ├── <span class="tok-fn">migrations/</span>       <span class="tok-c"># Database schema</span>
│   ├── <span class="tok-fn">factories/</span>        <span class="tok-c"># Test factories</span>
│   └── <span class="tok-fn">seeders/</span>          <span class="tok-c"># Startdata</span>
├── <span class="tok-k">resources/</span>
│   ├── <span class="tok-fn">views/</span>            <span class="tok-c"># Blade templates</span>
│   └── <span class="tok-fn">css/ js/</span>          <span class="tok-c"># Frontend assets</span>
└── <span class="tok-k">tests/</span>             <span class="tok-c"># Feature & unit tests</span></pre>
                    </div>
                </div>

                {{-- Modellen --}}
                <div id="modellen" class="bb-card-flat reveal" style="padding:2rem;">
                    <div style="display:flex;align-items:center;gap:0.75rem;margin-bottom:1.5rem;">
                        <span style="font-size:1.375rem;">🗄️</span>
                        <h2 style="font-size:1.375rem;font-weight:800;margin:0;color:#0B1F3A;">Modellen</h2>
                    </div>
                    <div class="bb-grid-2" style="gap:1rem;">
                        @foreach([
                            ['Transaction', '#EEF5FF', '#1E88E5', 'Kern van de app. Bevat bedrag, datum, omschrijving, categorie en merchant. Geïmporteerd uit bank-exports.'],
                            ['Category', '#F0FFF8', '#16C784', 'Hiërarchische categorieën (bijv. Boodschappen > Albert Heijn). Gekoppeld aan transactions.'],
                            ['Merchant', '#FFF8F0', '#FF8A3D', 'Winkel- of leverancierspatronen. Worden herkend via regex op transactieomschrijvingen.'],
                            ['ImportBatch', '#EEF5FF', '#1E88E5', 'Bijhoudt welke PDF of CSV is geïmporteerd, wanneer, hoeveel transacties en of AI is gebruikt.'],
                            ['User', '#F0FFF8', '#16C784', 'Standaard Laravel User, uitgebreid met 2FA en voorkeuren voor AI provider.'],
                            ['AiLog', '#FFF8F0', '#FF8A3D', 'Slaat AI-categorisatieresultaten op voor debugging en het verbeteren van de prompts.'],
                        ] as [$name, $bg, $color, $desc])
                        <div style="background:{{ $bg }};border-radius:1rem;padding:1.25rem;border:1px solid rgba(0,0,0,0.04);">
                            <div style="display:flex;align-items:center;gap:0.5rem;margin-bottom:0.625rem;">
                                <div style="width:8px;height:8px;background:{{ $color }};border-radius:50%;"></div>
                                <code style="font-size:0.9rem;font-weight:800;color:#0B1F3A;">{{ $name }}</code>
                            </div>
                            <p style="font-size:0.8125rem;color:#6B7A99;margin:0;line-height:1.6;">{{ $desc }}</p>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Services --}}
                <div id="services" class="bb-card-flat reveal" style="padding:2rem;">
                    <div style="display:flex;align-items:center;gap:0.75rem;margin-bottom:1.5rem;">
                        <span style="font-size:1.375rem;">⚙️</span>
                        <h2 style="font-size:1.375rem;font-weight:800;margin:0;color:#0B1F3A;">Services</h2>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:1rem;">
                        @foreach([
                            ['ImportService', 'Verwerkt geüploade PDF/CSV bestanden. Detecteert automatisch het bank-formaat en maakt Transaction-records aan.', ['import(UploadedFile $file)', 'detectFormat(string $content)', 'parseIng(string $content)']],
                            ['AiCategorizationService', 'Stuurt transactieomschrijvingen naar de geconfigureerde AI API en verwerkt de categorisatierespons.', ['categorize(Collection $transactions)', 'buildPrompt(array $transactions)', 'parseResponse(string $json)']],
                            ['MerchantMatchingService', 'Vergelijkt transactieomschrijvingen met opgeslagen merchant-patronen via regex en koppelt de categorie.', ['match(Transaction $transaction)', 'findPattern(string $description)', 'learnFromTransaction(Transaction $tx)']],
                            ['ReportService', 'Genereert overzichten, statistieken en exportbestanden op basis van gefilterde transacties.', ['monthly(int $year, int $month)', 'yearly(int $year)', 'export(Collection $transactions)']],
                        ] as [$name, $desc, $methods])
                        <div class="bb-card-flat" style="padding:1.5rem;background:#F0F6FF;">
                            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;flex-wrap:wrap;margin-bottom:0.875rem;">
                                <div>
                                    <code style="font-size:1rem;font-weight:800;color:#0B1F3A;">{{ $name }}</code>
                                    <p style="font-size:0.875rem;color:#6B7A99;margin:0.375rem 0 0;line-height:1.6;">{{ $desc }}</p>
                                </div>
                            </div>
                            <div style="display:flex;flex-wrap:wrap;gap:0.375rem;">
                                @foreach($methods as $method)
                                <code style="font-size:0.75rem;background:white;color:#1565C0;border:1px solid rgba(30,136,229,0.2);padding:0.2rem 0.6rem;border-radius:0.375rem;">{{ $method }}</code>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Workflows --}}
                <div id="workflows" class="bb-card-flat reveal" style="padding:2rem;">
                    <div style="display:flex;align-items:center;gap:0.75rem;margin-bottom:1.5rem;">
                        <span style="font-size:1.375rem;">🔄</span>
                        <h2 style="font-size:1.375rem;font-weight:800;margin:0;color:#0B1F3A;">Workflows</h2>
                    </div>
                    <div class="bb-grid-2" style="gap:1.25rem;">
                        <div style="background:#EEF5FF;border-radius:1rem;padding:1.5rem;">
                            <div style="font-weight:800;color:#0B1F3A;margin-bottom:1rem;">📥 Importflow</div>
                            <div style="display:flex;flex-direction:column;gap:0.625rem;">
                                @foreach(['Upload PDF/CSV', 'Format detecteren', 'Transacties parsen', 'Merchants matchen', 'AI categoriseren', 'Opslaan in DB'] as $i => $step)
                                <div style="display:flex;align-items:center;gap:0.625rem;">
                                    <div style="width:1.375rem;height:1.375rem;background:#1E88E5;color:white;border-radius:50%;font-size:0.6875rem;font-weight:800;display:flex;align-items:center;justify-content:center;flex-shrink:0;">{{ $i+1 }}</div>
                                    <span style="font-size:0.8125rem;color:#0B1F3A;font-weight:500;">{{ $step }}</span>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        <div style="background:#F0FFF8;border-radius:1rem;padding:1.5rem;">
                            <div style="font-weight:800;color:#0B1F3A;margin-bottom:1rem;">🤖 AI-categorisatieflow</div>
                            <div style="display:flex;flex-direction:column;gap:0.625rem;">
                                @foreach(['Ongecategoriseerde transacties ophalen', 'Batch prompt bouwen', 'API call naar Claude/GPT', 'JSON response parsen', 'Categorieën koppelen', 'AiLog opslaan'] as $i => $step)
                                <div style="display:flex;align-items:center;gap:0.625rem;">
                                    <div style="width:1.375rem;height:1.375rem;background:#16C784;color:white;border-radius:50%;font-size:0.6875rem;font-weight:800;display:flex;align-items:center;justify-content:center;flex-shrink:0;">{{ $i+1 }}</div>
                                    <span style="font-size:0.8125rem;color:#0B1F3A;font-weight:500;">{{ $step }}</span>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Testen --}}
                <div id="testen" class="bb-card-flat reveal" style="padding:2rem;">
                    <div style="display:flex;align-items:center;gap:0.75rem;margin-bottom:1.5rem;">
                        <span style="font-size:1.375rem;">🧪</span>
                        <h2 style="font-size:1.375rem;font-weight:800;margin:0;color:#0B1F3A;">Testen</h2>
                    </div>
                    <p style="font-size:0.9375rem;color:#6B7A99;line-height:1.7;margin:0 0 1.25rem;">BankBird gebruikt PHPUnit voor feature- en unittests. De testdatabase draait automatisch via SQLite in-memory.</p>
                    <div class="bb-code-block" style="margin-bottom:1.25rem;">
                        <div class="bb-code-bar">
                            <div class="bb-dot" style="background:#FF6058;"></div>
                            <div class="bb-dot" style="background:#FFBD2E;"></div>
                            <div class="bb-dot" style="background:#27C93F;"></div>
                            <span style="margin-left:auto;font-size:0.6875rem;color:rgba(255,255,255,0.3);font-family:monospace;">bash</span>
                        </div>
                        <pre><span class="tok-c"># Alle tests uitvoeren</span>
<span class="tok-k">php</span> artisan test --compact

<span class="tok-c"># Eén testbestand</span>
<span class="tok-k">php</span> artisan test tests/Feature/ImportTest.php

<span class="tok-c"># Filter op naam</span>
<span class="tok-k">php</span> artisan test --filter=testImportIng</pre>
                    </div>
                    <div class="bb-alert bb-alert-orange">
                        <span style="font-size:1.25rem;flex-shrink:0;">⚠️</span>
                        <div>Voer <strong>nooit</strong> tests uit op je productieschema. De tests wissen en seeden de testdatabase opnieuw.</div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>

@endsection
