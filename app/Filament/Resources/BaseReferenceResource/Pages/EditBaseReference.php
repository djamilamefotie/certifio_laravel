<?php

namespace App\Filament\Resources\BaseReferenceResource\Pages;

use App\Filament\Resources\BaseReferenceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBaseReference extends EditRecord
{
    protected static string $resource = BaseReferenceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
