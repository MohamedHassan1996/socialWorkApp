<?php
namespace App\Repositories;

use App\Models\UserSubscription\Plan;
use App\Models\UserSubscription\Feature;


class FeatureRepository
{
    public function findByKey(string $key): ?Feature
    {
        return Feature::where('key', $key)->first();
    }

    public function getPlanFeatures(Plan $plan): \Illuminate\Support\Collection
    {
        return $plan->features;
    }
}
