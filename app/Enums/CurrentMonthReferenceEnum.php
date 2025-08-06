<?php

namespace App\Enums;

enum CurrentMonthReferenceEnum: int
{
    case janeiro   = 1;
    case fevereiro = 2;
    case marco     = 3;
    case abril     = 4;
    case maio      = 5;
    case junho     = 6;
    case julho     = 7;
    case agosto    = 8;
    case setembro  = 9;
    case outubro   = 10;
    case novembro  = 11;
    case dezembro  = 12;

    public function label(): string
    {
        return match ($this) {
            self::janeiro   => 'jan',
            self::fevereiro => 'fev',
            self::marco     => 'mar',
            self::abril     => 'abr',
            self::maio      => 'mai',
            self::junho     => 'jun',
            self::julho     => 'jul',
            self::agosto    => 'ago',
            self::setembro  => 'set',
            self::outubro   => 'out',
            self::novembro  => 'nov',
            self::dezembro  => 'dez',
        };
    }

    public function labelYear(): string
    {
        $year    = now()->year;
        return match ($this) {
            self::janeiro   => "jan/{$year}",
            self::fevereiro => "fev/{$year}",
            self::marco     => "mar/{$year}",
            self::abril     => "abr/{$year}",
            self::maio      => "mai/{$year}",
            self::junho     => "jun/{$year}",
            self::julho     => "jul/{$year}",
            self::agosto    => "ago/{$year}",
            self::setembro  => "set/{$year}",
            self::outubro   => "out/{$year}",
            self::novembro  => "nov/{$year}",
            self::dezembro  => "dez/{$year}",
        };
    }

    public function numberYear(): string
    {
        $year    = now()->year;
        return match ($this) {
            self::janeiro   => "01/{$year}",
            self::fevereiro => "02/{$year}",
            self::marco     => "03/{$year}",
            self::abril     => "04/{$year}",
            self::maio      => "05/{$year}",
            self::junho     => "06/{$year}",
            self::julho     => "07/{$year}",
            self::agosto    => "08/{$year}",
            self::setembro  => "09/{$year}",
            self::outubro   => "10/{$year}",
            self::novembro  => "11/{$year}",
            self::dezembro  => "12/{$year}",
        };
    }
}
