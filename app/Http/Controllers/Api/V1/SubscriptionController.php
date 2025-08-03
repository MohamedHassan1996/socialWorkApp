<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\UserSubscription\Plan;
use App\Services\UserSubscription\FeatureAccessService;
use App\Services\UserSubscription\SubscriptionService;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function __construct(
        private SubscriptionService $subscriptionService,
        private FeatureAccessService $featureAccessService
    ) {}

    public function subscribe(Request $request, Plan $plan)
    {
        $subscription = $this->subscriptionService->subscribe($request->user(), $plan);

        return response()->json([
            'message' => 'Successfully subscribed',
            'subscription' => $subscription->load('plan')
        ]);
    }

    public function suspend(Request $request)
    {
        $result = $this->subscriptionService->suspend($request->user());

        return response()->json([
            'message' => $result ? 'Subscription suspended' : 'Failed to suspend subscription',
            'success' => $result
        ]);
    }

    public function resume(Request $request)
    {
        $result = $this->subscriptionService->resume($request->user());

        return response()->json([
            'message' => $result ? 'Subscription resumed' : 'Failed to resume subscription',
            'success' => $result
        ]);
    }

    public function status(Request $request)
    {
        $user = $request->user();
        $plan = $this->subscriptionService->getUserPlan($user);

        return response()->json([
            'plan' => $plan,
            'features' => $plan ? $plan->features : [],
        ]);
    }

    public function featureUsage(Request $request, string $featureKey)
    {
        $user = $request->user();

        return response()->json([
            'can_use' => $this->featureAccessService->canUseFeature($user, $featureKey),
            'remaining' => $this->featureAccessService->getRemainingUsage($user, $featureKey),
            'current_usage' => $this->featureAccessService->getUsageInCurrentPeriod($user, $featureKey)
        ]);
    }
}
