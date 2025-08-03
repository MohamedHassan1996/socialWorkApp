<?php

namespace Database\Seeders;

use App\Models\UserSubscription\Feature;
use App\Models\UserSubscription\Plan;
use Illuminate\Database\Seeder;

class PlanFeatureValueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $planFeatures = [
            'free' => [
                // Workspace
                'workspace_limit' => ['limit' => 1, 'highlighted' => true],
                'workspace_member_limit' => ['limit' => 2, 'highlighted' => true],

                // Content
                'posts_limit' => ['limit' => 10],
                'storage_limit' => ['limit' => 1], // 1 GB

            ],

            'pro' => [
                // Workspace
                'workspace_limit' => ['limit' => 5, 'highlighted' => true],
                'workspace_member_limit' => ['limit' => 15, 'highlighted' => true],

                // Content
                'posts_limit' => ['limit' => 100, 'highlighted' => true],
                'storage_limit' => ['limit' => 50], // 50 GB

            ],

            'premium' => [
                // Workspace
                'workspace_limit' => ['limit' => -1, 'highlighted' => true], // Unlimited
                'workspace_member_limit' => ['limit' => 100, 'highlighted' => true],

                // Content
                'posts_limit' => ['limit' => -1, 'highlighted' => true], // Unlimited
                'storage_limit' => ['limit' => 500], // 500 GB

            ],

            'enterprise' => [
                // Workspace
                'workspace_limit' => ['limit' => -1, 'highlighted' => true], // Unlimited
                'workspace_member_limit' => ['limit' => -1, 'highlighted' => true], // Unlimited

                // Content
                'posts_limit' => ['limit' => -1], // Unlimited
                'storage_limit' => ['limit' => -1, 'highlighted' => true], // Unlimited

            ],
        ];

        foreach ($planFeatures as $planKey => $features) {
            $plan = Plan::where('key', $planKey)->first();

            if (!$plan) {
                continue;
            }

            foreach ($features as $featureKey => $config) {
                $feature = Feature::where('key', $featureKey)->first();

                if ($feature) {
                    $plan->features()->syncWithoutDetaching([
                        $feature->id => [
                            'limit_value' => $config['limit']
                        ]
                    ]);
                }
            }
        }
    }
}
