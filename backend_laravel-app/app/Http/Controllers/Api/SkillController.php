<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Skill;
use Illuminate\Http\JsonResponse;

class SkillController extends Controller
{
    public function index(): JsonResponse
    {
        $skills = Skill::orderBy('name')
            ->get()
            ->groupBy(fn (Skill $skill) => $skill->category->value);

        return response()->json($skills);
    }
}
