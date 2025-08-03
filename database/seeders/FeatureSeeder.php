<?php

namespace Database\Seeders;

use App\Enums\SubcriptionPlan\PlanFeatureType;
use App\Models\UserSubscription\Feature;
use Illuminate\Database\Seeder;

class FeatureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
        public function run()
    {
        $features = [
            // Workspace Features
            [
                'key' => 'workspace_limit',
                'name' => 'Workspaces',
                'description' => 'Number of workspaces you can create',
                'type' => PlanFeatureType::COUNTABLE,
                'category' => 'workspace',
                'unit' => 'workspaces',
                'sort_order' => 1,
            ],
            [
                'key' => 'workspace_member_limit',
                'name' => 'Members per Workspace',
                'description' => 'Number of members you can invite to each workspace',
                'type' => PlanFeatureType::COUNTABLE,
                'category' => 'workspace',
                'unit' => 'members',
                'sort_order' => 2,
            ],

            // Content Features
            [
                'key' => 'posts_limit',
                'name' => 'Posts per Month',
                'description' => 'Number of posts you can create monthly',
                'type' => PlanFeatureType::COUNTABLE,
                'category' => 'content',
                'unit' => 'posts',
                'sort_order' => 3,
            ],
            [
                'key' => 'storage_limit',
                'name' => 'Storage',
                'description' => 'Total file storage available',
                'type' => PlanFeatureType::COUNTABLE,
                'category' => 'content',
                'unit' => 'GB',
                'sort_order' => 4,
            ],

        ];

        foreach ($features as $featureData) {
            Feature::updateOrCreate(
                ['key' => $featureData['key']],
                $featureData
            );
        }
    }

}
