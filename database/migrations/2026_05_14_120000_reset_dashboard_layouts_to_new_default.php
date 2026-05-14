<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Zet alle bestaande dashboard-layouts terug zodat iedereen — ook
 * gebruikers met een lopende installatie — de nieuwe default-layout
 * ziet (welkomstbalk en rekeningen span 2, top-merchants/laatste-
 * transacties maxItems=3, inkomsten-vs-uitgaven en categorie-uitgaven
 * verborgen). Bij de eerstvolgende dashboard-load wordt de layout
 * opnieuw uit `DashboardWidgetRegistry::defaultLayout()` opgebouwd.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('dashboard_layouts')->delete();
    }

    public function down(): void
    {
        // Een verwijderde layout is niet terug te draaien.
    }
};
