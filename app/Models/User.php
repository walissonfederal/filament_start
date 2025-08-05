<?php

namespace App\Models;

use App\Filament\AvatarProviders\UiAvatarsProvider;
use App\Observers\UserObserver;
use Filament\Models\Contracts\HasAvatar;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements HasAvatar
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        self::observe(UserObserver::class);
    }

    public function getFilamentAvatarUrl(): ?string
    {
        if ($this->avatar) {
            return asset('storage/' . $this->avatar);
        }
        else {
            $avatar = new UiAvatarsProvider();

            return $avatar->get($this);
        }
    }

    public function hasPermission(Permission $permission)
    {
        return $this->hasAnyRoles($permission->roles);
    }

    public function hasAnyRoles($roles)
    {
        if (is_object($roles)) {
            return !!$roles->intersect($this->roles)->count();
        }
        return $this->roles->contains('name', $roles);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
            "role_user",
            "user_id",
            "role_id",
        )->withPivot([
            "user_id",
            "role_id",
        ]);
    }
}
