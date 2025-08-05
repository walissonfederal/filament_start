<?php

namespace App\Enums;

enum SocialEnum: string
{
    case facebook  = "facebook";
    case twitter   = "twitter";
    case pinterest = "pinterest";
    case instagram = "instagram";
    case youtube   = "youtube";

    public function label(): string
    {
        return match ($this) {
            self::facebook  => 'Facebook',
            self::twitter   => 'Twitter',
            self::pinterest => 'Pinterest',
            self::instagram => 'Instagram',
            self::youtube   => 'Youtube',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::facebook  => 'fab fa-facebook-f',
            self::twitter   => 'fab fa-twitter',
            self::pinterest => 'fab fa-pinterest',
            self::instagram => 'fab fa-instagram',
            self::youtube   => 'fab fa-youtube',
        };
    }
}
