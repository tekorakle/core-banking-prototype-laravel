<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\MobilePayment;

use App\Domain\MobilePayment\Services\ActivityFeedService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    public function __construct(
        private readonly ActivityFeedService $activityFeedService,
    ) {
    }

    /**
     * Get activity feed with cursor-based pagination.
     *
     * GET /v1/activity
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'cursor' => ['sometimes', 'string'],
            'limit'  => ['sometimes', 'integer', 'min:1', 'max:50'],
            'filter' => ['sometimes', 'string', 'in:all,income,expenses'],
        ]);

        /** @var \App\Models\User $user */
        $user = $request->user();

        $feed = $this->activityFeedService->getFeed(
            userId: $user->id,
            cursor: $request->input('cursor'),
            limit: (int) $request->input('limit', 20),
            filter: $request->input('filter', 'all'),
        );

        return response()->json([
            'success' => true,
            'data'    => $feed,
        ]);
    }
}
