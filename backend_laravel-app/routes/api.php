<?php

use App\Http\Controllers\Api\Admin\EducationController as AdminEducationController;
use App\Http\Controllers\Api\Admin\ExperienceController as AdminExperienceController;
use App\Http\Controllers\Api\Admin\MessageController;
use App\Http\Controllers\Api\Admin\ProfileController as AdminProfileController;
use App\Http\Controllers\Api\Admin\ProjectController as AdminProjectController;
use App\Http\Controllers\Api\Admin\ProjectImageController;
use App\Http\Controllers\Api\Admin\SkillController as AdminSkillController;
use App\Http\Controllers\Api\Admin\SocialLinkController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\EducationController;
use App\Http\Controllers\Api\ExperienceController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\SkillController;
use Illuminate\Support\Facades\Route;

Route::get('profile', [ProfileController::class, 'show']);
Route::get('projects', [ProjectController::class, 'index']);
Route::get('projects/{slug}', [ProjectController::class, 'show']);
Route::get('skills', [SkillController::class, 'index']);
Route::get('experience', [ExperienceController::class, 'index']);
Route::get('educations', [EducationController::class, 'index']);
Route::post('contact', [ContactController::class, 'store'])->middleware('throttle:5,1');

Route::post('admin/login', [AuthController::class, 'login'])->middleware('throttle:10,1');

Route::middleware(['auth:sanctum', 'role:admin'])
    ->prefix('admin')
    ->group(function (): void {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);

        Route::apiResource('projects', AdminProjectController::class);
        Route::post('projects/{project}/images', [ProjectImageController::class, 'store']);
        Route::delete('images/{image}', [ProjectImageController::class, 'destroy']);

        Route::apiResource('skills', AdminSkillController::class);
        Route::apiResource('experiences', AdminExperienceController::class);
        Route::apiResource('educations', AdminEducationController::class);
        Route::apiResource('social-links', SocialLinkController::class)
            ->parameters(['social-links' => 'socialLink']);

        Route::get('profile', [AdminProfileController::class, 'show']);
        Route::post('profile', [AdminProfileController::class, 'update']);

        Route::get('messages', [MessageController::class, 'index']);
        Route::get('messages/{message}', [MessageController::class, 'show']);
        Route::patch('messages/{message}/read', [MessageController::class, 'markAsRead']);
        Route::delete('messages/{message}', [MessageController::class, 'destroy']);
    });
