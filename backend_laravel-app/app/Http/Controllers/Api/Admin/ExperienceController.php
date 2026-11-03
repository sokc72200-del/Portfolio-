<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Experience;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExperienceController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Experience::latestFirst()->get());
    }

    public function store(Request $request): JsonResponse
    {
        return response()->json(Experience::create($this->validated($request)), 201);
    }

    public function show(Experience $experience): JsonResponse
    {
        return response()->json($experience);
    }

    public function update(Request $request, Experience $experience): JsonResponse
    {
        $experience->update($this->validated($request, $experience));

        return response()->json($experience);
    }

    public function destroy(Experience $experience): JsonResponse
    {
        $experience->delete();

        return response()->json(null, 204);
    }

    private function validated(Request $request, ?Experience $experience = null): array
    {
        $required = $experience === null ? 'required' : 'sometimes|required';

        return $request->validate([
            'company_name' => [$required, 'string', 'max:255'],
            'position' => [$required, 'string', 'max:255'],
            'start_date' => [$required, 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'is_current' => ['boolean'],
            'location' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);
    }
}
