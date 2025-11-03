<?php

namespace Database\Seeders;

use App\Enums\SubcriptionPlan\SubcriptionStatus;
use App\Enums\User\UserStatus;
use App\Enums\User\UserType;
use App\Models\User;
use App\Models\UserSubscription\Plan;
use App\Models\UserSubscription\Subscription;
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
                'email' => 'heshamatef050@gmail.com',
                'password' =>'mans123456',
                'type' => UserType::CLIENT, // Assuming UserType is an enum similar to UserStatus
                'is_active' => UserStatus::ACTIVE, // Assuming is_active is a field in User model
            ]),
            User::create([
                'name' => 'User B',
                'email' => 'mr10dev10@gmail.com',
                'password' =>'mans123456',
                'type' => UserType::CLIENT, // Assuming UserType is an enum similar to UserStatus
                'is_active' => UserStatus::ACTIVE
            ]),
            User::create([
                'name' => 'User1',
                'email' => 'user1@example.com',
                'password' =>'mans123456',
                'type' => UserType::CLIENT, // Assuming UserType is an enum similar to UserStatus
                'is_active' => UserStatus::ACTIVE
            ]),
             User::create([
                'name' => 'User2',
                'email' => 'user2@example.com',
                'password' =>'mans123456',
                'type' => UserType::CLIENT, // Assuming UserType is an enum similar to UserStatus
                'is_active' => UserStatus::ACTIVE
            ]),
             User::create([
                'name' => 'User3',
                'email' => 'user3@example.com',
                'password' =>'mans123456',
                'type' => UserType::CLIENT, // Assuming UserType is an enum similar to UserStatus
                'is_active' => UserStatus::ACTIVE
            ]),
             User::create([
                'name' => 'User4',
                'email' => 'user4@example.com',
                'password' =>'mans123456',
                'type' => UserType::CLIENT, // Assuming UserType is an enum similar to UserStatus
                'is_active' => UserStatus::ACTIVE
            ]),
             User::create([
                'name' => 'User5',
                'email' => 'user5@example.com',
                'password' =>'mans123456',
                'type' => UserType::CLIENT, // Assuming UserType is an enum similar to UserStatus
                'is_active' => UserStatus::ACTIVE
            ]),
             User::create([
                'name' => 'User6',
                'email' => 'user6@example.com',
                'password' =>'mans123456',
                'type' => UserType::CLIENT, // Assuming UserType is an enum similar to UserStatus
                'is_active' => UserStatus::ACTIVE
            ]),
            User::create([
                'name' => 'User7',
                'email' => 'user7@example.com',
                'password' =>'mans123456',
                'type' => UserType::CLIENT, // Assuming UserType is an enum similar to UserStatus
                'is_active' => UserStatus::ACTIVE
            ]),
            User::create([
                'name' => 'User8',
                'email' => 'user8@example.com',
                'password' =>'mans123456',
                'type' => UserType::CLIENT, // Assuming UserType is an enum similar to UserStatus
                'is_active' => UserStatus::ACTIVE
            ]),
            User::create([
                'name' => 'User9',
                'email' => 'user9@example.com',
                'password' =>'mans123456',
                'type' => UserType::CLIENT, // Assuming UserType is an enum similar to UserStatus
                'is_active' => UserStatus::ACTIVE
            ]),
            User::create([
                'name' => 'User10',
                'email' => 'user10@example.com',
                'password' =>'mans123456',
                'type' => UserType::CLIENT, // Assuming UserType is an enum similar to UserStatus
                'is_active' => UserStatus::ACTIVE
            ]),
             User::create([
                'name' => 'Umberto Arillotta',
                'email' => 'u.arillotta@arcaprocessing.com',
                'password' =>'umberto123456',
                'type' => UserType::CLIENT, // Assuming UserType is an enum similar to UserStatus
                'is_active' => UserStatus::ACTIVE
            ]),
            User::create([
                'name' => 'Giorgio Manara',
                'email' => 'giorgio.manara@ma-estro.com',
                'password' =>'giorgio123456',
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
            'plan_id' => $plans['premium']->id,
            'status' => SubcriptionStatus::ACTIVE,
            'starts_at' => now(),
            'ends_at' => null, // Lifetime plan
            'suspended_at' => null,
        ]);

        Subscription::create([
            'user_id' => $users[1]->id,
            'plan_id' => $plans['premium']->id,
            'status' => SubcriptionStatus::ACTIVE,
            'starts_at' => now(),
            'ends_at' => null, // Monthly plan
            'suspended_at' => null,
        ]);


        Subscription::create([
            'user_id' => $users[2]->id,
            'plan_id' => $plans['premium']->id,
            'status' => SubcriptionStatus::ACTIVE,
            'starts_at' => now(),
            'ends_at' => null, // Monthly plan
            'suspended_at' => null,
        ]);

        Subscription::create([
            'user_id' => $users[3]->id,
            'plan_id' => $plans['premium']->id,
            'status' => SubcriptionStatus::ACTIVE,
            'starts_at' => now(),
            'ends_at' => null, // Monthly plan
            'suspended_at' => null,
        ]);

        Subscription::create([
            'user_id' => $users[4]->id,
            'plan_id' => $plans['premium']->id,
            'status' => SubcriptionStatus::ACTIVE,
            'starts_at' => now(),
            'ends_at' => null, // Monthly plan
            'suspended_at' => null,
        ]);

        Subscription::create([
            'user_id' => $users[5]->id,
            'plan_id' => $plans['premium']->id,
            'status' => SubcriptionStatus::ACTIVE,
            'starts_at' => now(),
            'ends_at' => null, // Monthly plan
            'suspended_at' => null,
        ]);

        Subscription::create([
            'user_id' => $users[6]->id,
            'plan_id' => $plans['premium']->id,
            'status' => SubcriptionStatus::ACTIVE,
            'starts_at' => now(),
            'ends_at' => null, // Monthly plan
            'suspended_at' => null,
        ]);

        Subscription::create([
            'user_id' => $users[7]->id,
            'plan_id' => $plans['premium']->id,
            'status' => SubcriptionStatus::ACTIVE,
            'starts_at' => now(),
            'ends_at' => null, // Monthly plan
            'suspended_at' => null,
        ]);

        Subscription::create([
            'user_id' => $users[8]->id,
            'plan_id' => $plans['premium']->id,
            'status' => SubcriptionStatus::ACTIVE,
            'starts_at' => now(),
            'ends_at' => null, // Monthly plan
            'suspended_at' => null,
        ]);

        Subscription::create([
            'user_id' => $users[9]->id,
            'plan_id' => $plans['premium']->id,
            'status' => SubcriptionStatus::ACTIVE,
            'starts_at' => now(),
            'ends_at' => null, // Monthly plan
            'suspended_at' => null,
        ]);

        Subscription::create([
            'user_id' => $users[10]->id,
            'plan_id' => $plans['premium']->id,
            'status' => SubcriptionStatus::ACTIVE,
            'starts_at' => now(),
            'ends_at' => null, // Monthly plan
            'suspended_at' => null,
        ]);

        Subscription::create([
            'user_id' => $users[11]->id,
            'plan_id' => $plans['premium']->id,
            'status' => SubcriptionStatus::ACTIVE,
            'starts_at' => now(),
            'ends_at' => null, // Monthly plan
            'suspended_at' => null,
        ]);

        Subscription::create([
            'user_id' => $users[12]->id,
            'plan_id' => $plans['premium']->id,
            'status' => SubcriptionStatus::ACTIVE,
            'starts_at' => now(),
            'ends_at' => null, // Monthly plan
            'suspended_at' => null,
        ]);

        Subscription::create([
            'user_id' => $users[13]->id,
            'plan_id' => $plans['premium']->id,
            'status' => SubcriptionStatus::ACTIVE,
            'starts_at' => now(),
            'ends_at' => null, // Monthly plan
            'suspended_at' => null,
        ]);


    }
}
