<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $projects = Project::published()
            ->when($request->boolean('featured'), fn ($query) => $query->featured())
            ->with('skills:id,name,icon')
            ->ordered()
            ->get();

        return response()->json($projects);
    }

    public function show(string $slug): JsonResponse
    {
        $project = Project::published()
            ->where('slug', $slug)
            ->with(['image', 'skills'])
            ->firstOrFail();

        return response()->json($project);
    }
}
