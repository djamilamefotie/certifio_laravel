<?php

namespace App\Filament\Resources\OffreAbonnementResource\Pages;

use App\Filament\Resources\OffreAbonnementResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOffreAbonnement extends EditRecord
{
    protected static string $resource = OffreAbonnementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
