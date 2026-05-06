<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class WelcomeBannerWidget extends Widget
{
    protected string $view = 'filament.widgets.welcome-banner-widget';

    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 0;

    public function getGreeting(): string
    {
        $hour = now()->hour;

        return match (true) {
            $hour < 12 => 'Goedemorgen',
            $hour < 18 => 'Goedemiddag',
            default    => 'Goedenavond',
        };
    }

    public function getUserFirstName(): string
    {
        return explode(' ', Auth::user()->name)[0];
    }

    public function getFormattedDate(): string
    {
        $days = ['Zondag', 'Maandag', 'Dinsdag', 'Woensdag', 'Donderdag', 'Vrijdag', 'Zaterdag'];
        $months = ['januari', 'februari', 'maart', 'april', 'mei', 'juni', 'juli', 'augustus', 'september', 'oktober', 'november', 'december'];

        $now = now();

        return $days[$now->dayOfWeek] . ' ' . $now->day . ' ' . $months[$now->month - 1] . ' ' . $now->year;
    }
}
