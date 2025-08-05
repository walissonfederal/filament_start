<?php

namespace App\Models;

use App\Filament\AvatarProviders\UiAvatarsProvider;
use Filament\Models\Contracts\HasAvatar;
use Illuminate\Database\Eloquent\Model;

class Product extends Model implements HasAvatar
{
    protected $fillable = [
        'name',
        'picture',
    ];

    public function getFilamentAvatarUrl(): ?string
    {
        if ($this->picture) {
            return asset('storage/' . $this->picture);
        }
        else {
            $picture = new UiAvatarsProvider();

            return $picture->get($this);
        }
    }
}
