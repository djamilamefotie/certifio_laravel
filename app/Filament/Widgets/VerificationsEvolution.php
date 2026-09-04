<?php

namespace App\Filament\Widgets;

use App\Models\Verification;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class VerificationsEvolution extends ChartWidget
{
    protected static ?string $heading = 'Évolution des vérifications (30 derniers jours)';

    protected function getData(): array
    {
        $jours = collect(range(29, 0))->map(fn (int $i) => Carbon::today()->subDays($i));

        $donnees = Verification::selectRaw('DATE(created_at) as jour, COUNT(*) as total')
            ->where('created_at', '>=', Carbon::today()->subDays(29))
            ->groupBy('jour')
            ->pluck('total', 'jour');

        return [
            'datasets' => [
                [
                    'label' => 'Vérifications',
                    'data' => $jours->map(fn (Carbon $jour) => $donnees[$jour->toDateString()] ?? 0)->toArray(),
                    'borderColor' => '#f59e0b',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
                    'fill' => true,
                ],
            ],
            'labels' => $jours->map(fn (Carbon $jour) => $jour->format('d/m'))->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}