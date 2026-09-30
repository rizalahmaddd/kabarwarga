<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\DuesTypeResource;
use App\Http\Resources\PostResource;
use App\Models\Setting;
use App\Support\HomeFeed;
use Illuminate\Http\JsonResponse;

class HomeController extends Controller
{
    public function __invoke(HomeFeed $feed): JsonResponse
    {
        $home = $feed->build();

        return response()->json(['data' => [
            'settings' => Setting::allValues(),
            'popup_post' => $home['popupPost'] ? new PostResource($home['popupPost']) : null,
            'pinned' => PostResource::collection($home['pinned']),
            'upcoming' => PostResource::collection($home['upcoming']),
            'latest' => PostResource::collection($home['latest']),
            'progress' => $home['progress']->map(fn (array $item) => [
                'dues_type' => new DuesTypeResource($item['type']),
                'label' => $item['label'],
                'paid' => $item['paid'],
                'total' => $item['total'],
            ]),
        ]]);
    }
}
