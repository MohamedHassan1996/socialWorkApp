<?php

namespace App\Enums\SubcriptionPlan;

enum PlanBillingCycle: int{

    case MONTHLY = 0;

    case YEARLY = 1;

    case LIFETIME = 2;
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
