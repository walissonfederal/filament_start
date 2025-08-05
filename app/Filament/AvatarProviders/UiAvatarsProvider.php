<?php

namespace App\Filament\AvatarProviders;

use Filament\AvatarProviders\Contracts;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class UiAvatarsProvider implements Contracts\AvatarProvider
{
    public function get(Model | Authenticatable $record): string
    {
        return "https://ui-avatars.com/api/?name={$record->name}&size=128&background=random";
    }
}
