<?php

namespace App\Enums\SubcriptionPlan;

enum PlanFeatureType: int{

    case COUNTABLE = 0;

    case BOOLEAN = 1;

    case UNLIMITED = 2;
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
