<?php

namespace App\Services\Permissions;

use App\Models\User;
use Illuminate\Support\Facades\Gate;

trait PolicyTrait
{
    public function viewAny(User $user): bool
    {
        $slug = self::$slug;
        return Gate::allows(
            "filament.admin.resources.{$slug}.index"
        );
    }

    public function view(User $user): bool
    {
        $slug = self::$slug;
        return Gate::allows(
            "filament.admin.resources.{$slug}.view"
        );
    }

    public function create(User $user): bool
    {
        $slug = self::$slug;
        return Gate::allows(
            "filament.admin.resources.{$slug}.create"
        );
    }

    public function update(User $user): bool
    {
        $slug = self::$slug;
        return Gate::allows(
            "filament.admin.resources.{$slug}.edit"
        );
    }

    public function delete(User $user): bool
    {
        $slug = self::$slug;
        return Gate::allows(
            "filament.admin.resources.{$slug}.delete"
        );
    }

    public function restore(User $user): bool
    {
        $slug = self::$slug;
        return Gate::allows(
            "filament.admin.resources.{$slug}.delete"
        );
    }

    public function forceDelete(User $user): bool
    {
        return Gate::allows(
            "admin_full"
        );
    }
}
