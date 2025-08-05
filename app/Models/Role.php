<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'position',
        'system',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            "role_user",
            "role_id",
            "user_id",
        )->withPivot([
            "role_id",
            "user_id",
        ]);
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            Permission::class,
            "role_permission",
            "role_id",
            "permission_id",
        )->withPivot([
            "role_id",
            "permission_id",
        ]);
    }
}
