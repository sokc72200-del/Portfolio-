<?php

namespace Tests\Feature;

use App\Enums\ProjectStatus;
use App\Enums\Role;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProjectAdminUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_project_without_changing_thumbnail(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['role' => Role::Admin]);
        $project = Project::create([
            'title' => 'Library System',
            'slug' => 'library-system',
            'summary' => 'A sample project',
            'description' => 'Project details',
            'status' => ProjectStatus::Published,
            'featured' => false,
        ]);

        $response = $this->actingAs($admin, 'sanctum')
            ->post('/api/admin/projects/' . $project->slug, [
                '_method' => 'PUT',
                'title' => 'Library System',
                'summary' => 'A sample project',
                'description' => 'Updated details',
                'status' => 'published',
                'featured' => '1',
                'sort_order' => '0',
                'sync_skills' => '1',
            ]);

        $response->assertOk();
        $this->assertSame('Updated details', $project->fresh()->description);
    }

    public function test_admin_can_replace_thumbnail_when_updating_project(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['role' => Role::Admin]);
        $project = Project::create([
            'title' => 'Library System',
            'slug' => 'library-system',
            'summary' => 'A sample project',
            'description' => 'Project details',
            'status' => ProjectStatus::Published,
            'featured' => false,
            'thumbnail' => 'projects/old-thumbnail.jpg',
        ]);

        $response = $this->actingAs($admin, 'sanctum')
            ->post('/api/admin/projects/' . $project->slug, [
                '_method' => 'PUT',
                'title' => 'Library System',
                'summary' => 'A sample project',
                'description' => 'Updated details',
                'status' => 'published',
                'featured' => '1',
                'sort_order' => '0',
                'sync_skills' => '1',
                'thumbnail' => UploadedFile::fake()->image('new-thumbnail.jpg', 300, 300),
            ]);

        $response->assertOk();
        $this->assertNotNull($project->fresh()->thumbnail);
        $this->assertStringContainsString('projects/', $project->fresh()->thumbnail);
    }
}
