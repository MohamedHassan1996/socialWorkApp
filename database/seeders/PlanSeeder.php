<?php

namespace Database\Seeders;

use App\Enums\SubcriptionPlan\PlanBillingCycle;
use App\Models\UserSubscription\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $plans = [
            [
                'key' => 'free',
                'name' => 'Free Plan',
                'description' => 'Perfect for individuals getting started',
                'price' => 0.00,
                'billing_cycle' => PlanBillingCycle::LIFETIME->value,
                'sort_order' => 1,
                'is_active' => true,
                'is_popular' => false,
            ],
            [
                'key' => 'pro',
                'name' => 'Pro Plan',
                'description' => 'Best for growing teams and businesses',
                'price' => 29.99,
                'billing_cycle' => PlanBillingCycle::MONTHLY->value,
                'sort_order' => 2,
                'is_active' => true,
                'is_popular' => true,
            ],
            [
                'key' => 'premium',
                'name' => 'Premium Plan',
                'description' => 'Everything you need for large organizations',
                'price' => 99.99,
                'billing_cycle' => PlanBillingCycle::MONTHLY->value,

                'sort_order' => 3,
                'is_active' => true,
                'is_popular' => false,
            ],
            [
                'key' => 'enterprise',
                'name' => 'Enterprise Plan',
                'description' => 'Custom solutions for enterprise needs',
                'price' => 299.99,
                'billing_cycle' => PlanBillingCycle::MONTHLY->value,
                'sort_order' => 4,
                'is_active' => true,
                'is_popular' => false,
            ],
        ];

        foreach ($plans as $planData) {
            Plan::updateOrCreate(
                ['key' => $planData['key']],
                $planData
            );
        }
    }
}
