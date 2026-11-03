<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Education;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EducationController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Education::orderByDesc('start_date')->get());
    }

    public function store(Request $request): JsonResponse
    {
        return response()->json(Education::create($this->validated($request)), 201);
    }

    public function show(Education $education): JsonResponse
    {
        return response()->json($education);
    }

    public function update(Request $request, Education $education): JsonResponse
    {
        $education->update($this->validated($request, $education));

        return response()->json($education);
    }

    public function destroy(Education $education): JsonResponse
    {
        $education->delete();

        return response()->json(null, 204);
    }

    private function validated(Request $request, ?Education $education = null): array
    {
        $required = $education === null ? 'required' : 'sometimes|required';

        return $request->validate([
            'institution_name' => [$required, 'string', 'max:255'],
            'degree' => [$required, 'string', 'max:255'],
            'field_of_study' => [$required, 'string', 'max:255'],
            'start_date' => [$required, 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'is_current' => ['boolean'],
            'location' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);
    }
}
