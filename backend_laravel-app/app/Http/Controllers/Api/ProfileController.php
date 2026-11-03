<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Models\SocialLink;
use Illuminate\Http\JsonResponse;

class ProfileController extends Controller
{
    public function show(): JsonResponse
    {
        return response()->json([
            'profile' => Profile::first(),
            'social_links' => SocialLink::orderBy('sort_order', 'asc')->get(),
        ]);
    }
}
