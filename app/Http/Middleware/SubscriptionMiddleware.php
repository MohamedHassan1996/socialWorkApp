<?php

namespace App\Http\Middleware;

use App\Services\UserSubscription\FeatureAccessService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SubscriptionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function __construct(
        private FeatureAccessService $featureAccessService
    ) {}

    public function handle(Request $request, Closure $next, ?string $featureKey): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        if ($featureKey && !$this->featureAccessService->canUseFeature($user, $featureKey)) {
            return response()->json([
                'error' => 'Feature not available in your current plan',
                'feature' => $featureKey
            ], 403);
        }

        return $next($request);
    }
}
