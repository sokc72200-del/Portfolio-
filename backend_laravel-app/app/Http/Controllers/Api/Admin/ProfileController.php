<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        return response()->json($request->user()->profile);
    }

    public function update(Request $request): JsonResponse
    {
        $profile = $request->user()->profile ?? new Profile(['user_id' => $request->user()->id]);

        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'title' => ['required', 'string', 'max:255'],
            'bio' => ['required', 'string'],
            'email' => ['required', 'email', 'max:255', Rule::unique('profile', 'email')->ignore($profile->id)],
            'phone_number' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'avatar' => ['nullable', 'image', 'max:2048'],
            'resume' => ['nullable', 'file', 'mimes:pdf', 'max:5120'],
        ]);

        foreach (['avatar', 'resume'] as $fileField) {
            if (! $request->hasFile($fileField)) {
                continue;
            }

            if ($profile->{$fileField}) {
                Storage::disk('public')->delete($profile->{$fileField});
            }

            $data[$fileField] = $request->file($fileField)->store('profile', 'public');
        }

        $profile->fill($data)->save();

        return response()->json($profile);
    }
}
