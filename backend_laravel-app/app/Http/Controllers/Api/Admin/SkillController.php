<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\SkillCategory;
use App\Http\Controllers\Controller;
use App\Models\Skill;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SkillController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        return response()->json(Skill::orderBy('category')->orderBy('name')->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        return response()->json(Skill::create($this->validated($request)), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Skill $skill): JsonResponse
    {
        return response()->json($skill);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Skill $skill): JsonResponse
    {
        $skill->update($this->validated($request, $skill));

        return response()->json($skill);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Skill $skill): JsonResponse
    {
        $skill->delete();

        return response()->json(null, 204);
    }

    private function validated(Request $request, ?Skill $skill = null): array
    {
        $required = $skill === null ? 'required' : 'sometimes|required';

        return $request->validate([
            'name' => [$required, 'string', 'max:255'],
            'category' => [$required, Rule::enum(SkillCategory::class)],
            'level' => [$required, 'integer', 'between:1,100'],
            'icon' => ['nullable', 'string', 'max:255'],
        ]);
    }
}
