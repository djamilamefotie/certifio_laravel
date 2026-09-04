<?php

namespace App\Filament;

use Filament\AvatarProviders\Contracts\AvatarProvider;
use Illuminate\Database\Eloquent\Model;

class CertifioAvatarProvider implements AvatarProvider
{
    public function get(Model $record): string
    {
        $nom = str_replace(' ', '+', $record->name);

        return 'https://ui-avatars.com/api/?name=' . urlencode($nom) . '&color=FFFFFF&background=059669';
    }
}