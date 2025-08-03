<?php

namespace App\Enums\User;

enum UserStatus: int{

    case IN_ACTIVE = 0;
    case ACTIVE = 1;

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
