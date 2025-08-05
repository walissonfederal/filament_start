<?php

namespace App\Enums;

enum StatusOrderEnum: int
{
    case CRIADA     = 1;
    case APROVADA   = 2;
    case ENVIADA    = 3;
    case FINALIZADA = 4;

    public function label(): string
    {
        return match ($this) {
            self::CRIADA     => 'Criada',
            self::APROVADA   => 'Aprovada',
            self::ENVIADA    => 'Enviada',
            self::FINALIZADA => 'Finalizada',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::CRIADA     => 'primary',
            self::APROVADA   => 'success',
            self::ENVIADA    => 'warning',
            self::FINALIZADA => 'danger',
        };
    }

    public static function labelEnum($state) : string
    {
        return match ($state) {
            self::CRIADA->value     => self::CRIADA->label(),
            self::APROVADA->value   => self::APROVADA->label(),
            self::ENVIADA->value    => self::ENVIADA->label(),
            self::FINALIZADA->value => self::FINALIZADA->label(),
        };
    }

    public static function colorEnum($state) : string
    {
        return match ($state) {
            self::CRIADA->value     => self::CRIADA->color(),
            self::APROVADA->value   => self::APROVADA->color(),
            self::ENVIADA->value    => self::ENVIADA->color(),
            self::FINALIZADA->value => self::FINALIZADA->color(),
        };
    }
}
