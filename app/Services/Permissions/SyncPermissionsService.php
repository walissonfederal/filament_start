<?php

namespace App\Services\Permissions;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Support\Facades\Route;

class SyncPermissionsService
{
    public static function handle()
    {
        // Não mexer nesse array ----
        $defaults = [
            [
                "name"        => "filament.admin.pages.dashboard",
                "description" => "Dashboard",
                "crud"        => "Dashboard",
                "default"     => 1
            ],
        ];

        foreach ($defaults as $default) {
            Permission::updateOrCreate([
                "name" => $default["name"],
            ], [
                "default"     => $default["default"],
                "description" => $default["description"],
                "crud"        => $default["crud"],
            ]);
        }

        $routes = Route::getRoutes();
        $routes = array_filter($routes->getRoutesByName(), function ($route) {
            $true = str_contains($route->getName(), "filament.admin.pages")
                    || str_contains($route->getName(), "filament.admin.resources")
                    || str_contains($route->getName(), "filament.site.resources");
            return $true;
        });

        foreach ($routes as $route) {
            $name      = explode(".", $route->getName());
            $firstName = self::changeNameMethod($name[4] ?? null);
            $lastName  = self::changeNameModel($name[3] ?? null);

            $mountName = $firstName . " " . $lastName;

            $default = 0;

            if ($route->getName() == "filament.admin.pages.dashboard") {
                $default = 1;
            }

            $permissao = Permission::updateOrCreate([
                "name" => $route->getName(),
            ], [
                "description" => $mountName,
                "default"     => $default,
                "crud"        => $lastName,
            ]);

            if ($default) {
                $permissao->roles()->syncWithoutDetaching(Role::pluck("id")->all());
            }

            if ($firstName == "Editar") {
                Permission::updateOrCreate([
                    "name" => str_replace(["edit"], ["delete"], $route->getName()),
                ], [
                    "description" => str_replace(["Editar"], ["Excluir"], $mountName),
                    "default"     => 0,
                    "crud"        => $lastName,
                ]);

                Permission::updateOrCreate([
                    "name" => str_replace(["edit"], ["force-delete"], $route->getName()),
                ], [
                    "description" => str_replace(["Editar"], ["Excluir Permanente"], $mountName),
                    "default"     => 0,
                    "crud"        => $lastName,
                ]);
            }
        }

        $acrescentarRotasFixas = [

        ];

        foreach ($acrescentarRotasFixas as $fixa) {
            Permission::updateOrCreate([
                "name" => $fixa["name"],
            ], [
                "default"     => $fixa["default"],
                "description" => $fixa["description"],
                "crud"        => $fixa["crud"],
            ]);
        }
    }

    public static function changeNameMethod($name = null)
    {
        $newName = $name;

        if (!$name) {
            $newName = "";
        }

        if ($name == "view") {
            $newName = "Visualizar";
        }

        if ($name == "view") {
            $newName = "Visualizar";
        }

        if ($name == "index") {
            $newName = "Lista de";
        }

        if ($name == "create") {
            $newName = "Criar";
        }

        if ($name == "edit") {
            $newName = "Editar";
        }

        if ($name == "approval") {
            $newName = "Aprovar";
        }

        if ($name == "editor") {
            $newName = "Editar";
        }

        if ($name == "manager") {
            $newName = "Gestor(a)";
        }

        return ucfirst($newName);
    }

    public static function changeNameModel($name = null)
    {
        $newName = $name;

        if (!$name) {
            $newName = "";
        }

        if (isset($name) && $name == "users") {
            $newName = "Usuários";
        }

        if (isset($name) && $name == "roles") {
            $newName = "Funções";
        }

        if (isset($name) && $name == "clients") {
            $newName = "Clientes";
        }

        return ucfirst($newName);
    }
}
