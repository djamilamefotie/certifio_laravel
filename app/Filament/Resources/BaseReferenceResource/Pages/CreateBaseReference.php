<?php

namespace App\Filament\Resources\BaseReferenceResource\Pages;

use App\Filament\Resources\BaseReferenceResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateBaseReference extends CreateRecord
{
    protected static string $resource = BaseReferenceResource::class;
}
