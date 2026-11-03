<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\SocialLink;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SocialLinkController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(SocialLink::orderBy('sort_order')->get());
    }

    public function store(Request $request): JsonResponse
    {
        return response()->json(SocialLink::create($this->validated($request)), 201);
    }

    public function show(SocialLink $socialLink): JsonResponse
    {
        return response()->json($socialLink);
    }

    public function update(Request $request, SocialLink $socialLink): JsonResponse
    {
        $socialLink->update($this->validated($request, $socialLink));

        return response()->json($socialLink);
    }

    public function destroy(SocialLink $socialLink): JsonResponse
    {
        $socialLink->delete();

        return response()->json(null, 204);
    }

    private function validated(Request $request, ?SocialLink $socialLink = null): array
    {
        $required = $socialLink === null ? 'required' : 'sometimes|required';

        return $request->validate([
            'platform' => [$required, 'string', 'max:255'],
            'url' => [$required, 'url', 'max:255'],
            'icon' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['integer', 'min:0'],
        ]);
    }
}
