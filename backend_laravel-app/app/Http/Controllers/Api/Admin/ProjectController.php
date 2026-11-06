<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\ProjectStatus;
use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProjectController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(
            Project::with('skills:id,name')->ordered()->paginate(15),
        );
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);
        $this->storeThumbnail($request, $data);
        unset($data['skill_ids']);

        $project = Project::create($data);
        $project->skills()->sync($request->input('skill_ids', []));

        return response()->json($project->load(['skills', 'image']), 201);
    }

    public function show(Project $project): JsonResponse
    {
        return response()->json($project->load(['skills', 'image']));
    }

    public function update(Request $request, Project $project): JsonResponse
    {
        $data = $this->validated($request, $project);
        $this->storeThumbnail($request, $data, $project);
        unset($data['skill_ids']);

        $project->update($data);

        if ($request->has('skill_ids') || $request->boolean('sync_skills')) {
            $project->skills()->sync($request->input('skill_ids', []));
        }
        return response()->json($project->load(['skills', 'image']));
    }

    public function destroy(Project $project): JsonResponse
    {
        if ($project->thumbnail) {
            Storage::disk('public')->delete($project->thumbnail);
        }

        foreach ($project->image as $image) {
            Storage::disk('public')->delete($image->image_path);
        }

        $project->delete();

        return response()->json(null, 204);
    }

    private function validated(Request $request, ?Project $project = null): array
    {
        $titleRules = $project === null
            ? ['required', 'string', 'max:150']
            : ['sometimes', 'required', 'string', 'max:150'];
        $slugRules = $project === null
            ? ['nullable', 'string', 'max:160']
            : ['sometimes', 'nullable', 'string', 'max:160'];
        $summaryRules = $project === null
            ? ['required', 'string', 'max:255']
            : ['sometimes', 'required', 'string', 'max:255'];
        $statusRules = $project === null
            ? ['required', Rule::enum(ProjectStatus::class)]
            : ['sometimes', 'required', Rule::enum(ProjectStatus::class)];

        return $request->validate([
            'title' => $titleRules,
            'slug' => [...$slugRules, Rule::unique('projects', 'slug')->ignore($project?->id)],
            'summary' => $summaryRules,
            'description' => ['nullable', 'string'],
            'thumbnail' => ['nullable', 'image', 'max:2048'],
            'repo_url' => ['nullable', 'url', 'max:255'],
            'featured' => ['boolean'],
            'status' => $statusRules,
            'sort_order' => ['integer', 'min:0'],
            'skill_ids' => ['sometimes', 'array'],
            'skill_ids.*' => ['exists:skills,id'],
        ]);
    }

    private function storeThumbnail(Request $request, array &$data, ?Project $project = null): void
    {
        if (! $request->hasFile('thumbnail')) {
            return;
        }

        if ($project?->thumbnail) {
            Storage::disk('public')->delete($project->thumbnail);
        }

        $data['thumbnail'] = $request->file('thumbnail')->store('projects', 'public');
    }
}
