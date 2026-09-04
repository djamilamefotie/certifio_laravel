<?php

namespace App\Filament\Widgets;

use App\Models\Verification;
use Filament\Widgets\ChartWidget;

class VerificationsParInstitution extends ChartWidget
{
    protected static ?string $heading = 'Répartition par institution';

    protected function getData(): array
    {
        $donnees = Verification::join('base_references', 'verifications.base_reference_id', '=', 'base_references.id')
            ->selectRaw('base_references.institution as institution, COUNT(*) as total')
            ->groupBy('base_references.institution')
            ->orderByDesc('total')
            ->pluck('total', 'institution');

        return [
            'datasets' => [
                [
                    'label' => 'Vérifications',
                    'data' => $donnees->values()->toArray(),
                    'backgroundColor' => ['#f59e0b', '#3b82f6', '#10b981', '#ef4444'],
                ],
            ],
            'labels' => $donnees->keys()->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}