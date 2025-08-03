<?php

namespace Database\Seeders;

use App\Enums\SubcriptionPlan\SubcriptionStatus;
use App\Enums\User\UserStatus;
use App\Enums\User\UserType;
use App\Models\User;
use App\Models\UserSubscription\Plan;
use App\Models\UserSubscription\Subscription;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SubscriptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
                // Create 3 users
        $users = [
            User::create([
                'name' => 'User A',
                'email' => 'usera@example.com',
                'password' =>'mans123456',
                'type' => UserType::CLIENT, // Assuming UserType is an enum similar to UserStatus
                'is_active' => UserStatus::ACTIVE, // Assuming is_active is a field in User model
            ]),
            User::create([
                'name' => 'User B',
                'email' => 'userb@example.com',
                'password' =>'mans123456',
                'type' => UserType::CLIENT, // Assuming UserType is an enum similar to UserStatus
                'is_active' => UserStatus::ACTIVE
            ]),
            User::create([
                'name' => 'User C',
                'email' => 'userc@example.com',
                'password' =>'mans123456',
                'type' => UserType::CLIENT, // Assuming UserType is an enum similar to UserStatus
                'is_active' => UserStatus::ACTIVE
            ]),
        ];

        // Fetch plans by slug
        $plans = [
            'free' => Plan::where('key', 'free')->first(),
            'pro' => Plan::where('key', 'pro')->first(),
            'premium' => Plan::where('key', 'premium')->first(),
        ];

        // Assign subscriptions
        Subscription::create([
            'user_id' => $users[0]->id,
            'plan_id' => $plans['free']->id,
            'status' => SubcriptionStatus::ACTIVE,
            'starts_at' => now(),
            'ends_at' => null, // Lifetime plan
            'suspended_at' => null,
        ]);

        Subscription::create([
            'user_id' => $users[1]->id,
            'plan_id' => $plans['pro']->id,
            'status' => SubcriptionStatus::ACTIVE,
            'starts_at' => now(),
            'ends_at' => now()->addMonth(), // Monthly plan
            'suspended_at' => null,
        ]);


        Subscription::create([
            'user_id' => $users[2]->id,
            'plan_id' => $plans['premium']->id,
            'status' => SubcriptionStatus::ACTIVE,
            'starts_at' => now(),
            'ends_at' => now()->addMonth(), // Monthly plan
            'suspended_at' => null,
        ]);

    }
}
