<?php

namespace App\Enums;

enum TypePeopleEnum: int
{
    case F = 1;
    case J = 2;

    public function label(): string
    {
        return match ($this) {
            self::F => 'Física',
            self::J => 'Jurídica',
        };
    }
}
