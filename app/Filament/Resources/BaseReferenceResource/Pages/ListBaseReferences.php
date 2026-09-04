<?php

namespace App\Filament\Resources\BaseReferenceResource\Pages;

use App\Filament\Resources\BaseReferenceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBaseReferences extends ListRecords
{
    protected static string $resource = BaseReferenceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
