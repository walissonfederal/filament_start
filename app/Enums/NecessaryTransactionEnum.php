<?php

namespace App\Enums;

enum NecessaryTransactionEnum: int
{
    case very_low  = 1;
    case low       = 2;
    case medium    = 3;
    case high      = 4;
    case very_high = 5;

    public function label(): string
    {
        return match ($this) {
            self::very_low  => 'Muito Baixa',
            self::low       => 'Baixa',
            self::medium    => 'Média',
            self::high      => 'Alta',
            self::very_high => 'Muito Alta',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::very_low  => 'gray',
            self::low       => 'info',
            self::medium    => 'success',
            self::high      => 'warning',
            self::very_high => 'danger',
        };
    }
}
