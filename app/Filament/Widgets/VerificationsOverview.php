<?php

namespace App\Filament\Widgets;

use App\Models\Verification;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class VerificationsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $total = Verification::count();
        $authentique = Verification::where('statut', 'authentique')->count();
        $suspect = Verification::where('statut', 'suspect')->count();
        $ambigu = Verification::where('statut', 'ambigu')->count();

        $pourcentage = fn (int $valeur): string => $total > 0
            ? round(($valeur / $total) * 100, 1) . '%'
            : '0%';

        return [
            Stat::make('Total vérifications', $total)
                ->icon('heroicon-o-document-magnifying-glass')
                ->color('gray'),

            Stat::make('Authentiques', $authentique)
                ->description($pourcentage($authentique) . ' du total')
                ->icon('heroicon-o-check-circle')
                ->color('success'),

            Stat::make('Suspects', $suspect)
                ->description($pourcentage($suspect) . ' du total')
                ->icon('heroicon-o-x-circle')
                ->color('danger'),

            Stat::make('Ambigus', $ambigu)
                ->description($pourcentage($ambigu) . ' du total')
                ->icon('heroicon-o-exclamation-triangle')
                ->color('warning'),
        ];
    }
}