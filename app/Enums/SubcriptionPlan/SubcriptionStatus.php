<?php

namespace App\Enums\SubcriptionPlan;

enum SubcriptionStatus: int{

    case IN_ACTIVE = 0;
    case ACTIVE = 1;
    case CANCELED = 2;
    case EXPIRED = 3;
    case SUSPENDED = 4;
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
