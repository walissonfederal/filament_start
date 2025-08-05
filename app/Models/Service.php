<?php

namespace App\Models;

use App\Filament\AvatarProviders\UiAvatarsProvider;
use Filament\Models\Contracts\HasAvatar;
use Illuminate\Database\Eloquent\Model;

class Service extends Model implements HasAvatar
{
    protected $fillable = [
        'name',
        'picture',
        'price_main',
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

    public function orders()
    {
        return $this->belongsToMany(
            Order::class,
            'order_service',
            'service_id',
            'order_id',
        )->withPivot([
            'service_id',
            'order_id',
            'observations',
            'quantity',
            'price',
        ]);
    }
}
