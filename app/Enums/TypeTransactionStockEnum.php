<?php

namespace App\Enums;

enum TypeTransactionStockEnum: int
{
    case ENTRADA = 1;
    case SAIDA   = 2;

    public function label(): string
    {
        return match ($this) {
            self::ENTRADA => 'Entrada',
            self::SAIDA   => 'Saída',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::ENTRADA => 'primary',
            self::SAIDA   => 'success',
        };
    }

    public static function labelEnum($state): string
    {
        return match ($state) {
            self::ENTRADA->value => self::ENTRADA->label(),
            self::SAIDA->value   => self::SAIDA->label(),
        };
    }

    public static function colorEnum($state): string
    {
        return match ($state) {
            self::ENTRADA->value => self::ENTRADA->color(),
            self::SAIDA->value   => self::SAIDA->color(),
        };
    }
}
