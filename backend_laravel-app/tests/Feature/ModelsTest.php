<?php

namespace Tests\Feature;

use App\Enums\ProjectStatus;
use App\Enums\Role;
use App\Enums\SkillCategory;
use App\Models\Education;
use App\Models\Experience;
use App\Models\Message;
use App\Models\Profile;
use App\Models\Project;
use App\Models\ProjectImage;
use App\Models\Skill;
use App\Models\SocialLink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ModelsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_roles_are_cast_and_checked_correctly(): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->create(['role' => Role::Admin]);

        $this->assertSame(Role::User, $user->fresh()->role);
        $this->assertTrue($user->hasRole(Role::User));
        $this->assertFalse($user->isAdmin());
        $this->assertTrue($admin->isAdmin());
    }

    public function test_profile_relation_and_storage_accessors_work(): void
    {
        Storage::fake();

        $user = User::factory()->create();
        $profile = Profile::create([
            'user_id' => $user->id,
            'full_name' => 'Taylor Example',
            'email' => 'taylor@example.test',
            'title' => 'Developer',
            'bio' => 'Portfolio profile',
            'avatar' => 'profiles/avatar.png',
            'resume' => 'profiles/resume.pdf',
        ]);

        $this->assertSame($profile->id, $user->profile->id);
        $this->assertSame(Storage::url('profiles/avatar.png'), $profile->avatar_url);
        $this->assertSame(Storage::url('profiles/resume.pdf'), $profile->resume_url);
    }

    public function test_project_fields_scopes_and_slug_work(): void
    {
        $project = Project::create([
            'title' => 'Portfolio App',
            'summary' => 'A sample project',
            'description' => 'Project details',
            'featured' => true,
            'status' => ProjectStatus::Published,
        ]);

        $this->assertSame('portfolio-app', $project->slug);
        $this->assertSame(ProjectStatus::Published, $project->fresh()->status);
        $this->assertSame(1, Project::published()->featured()->ordered()->count());
    }

    public function test_project_skill_relations_work(): void
    {
        $project = Project::create([
            'title' => 'Portfolio App',
            'summary' => 'A sample project',
            'description' => 'Project details',
            'featured' => true,
            'status' => ProjectStatus::Published,
        ]);
        $skill = Skill::create([
            'name' => 'Laravel',
            'category' => SkillCategory::Backend,
            'level' => 80,
        ]);
        $project->skills()->attach($skill);

        $this->assertSame($skill->id, $project->fresh()->skills->first()->id);
        $this->assertSame($project->id, $skill->projects->first()->id);
        $this->assertSame(80, $skill->fresh()->level);
    }

    public function test_project_images_use_the_expected_table_and_relation(): void
    {
        $project = Project::create([
            'title' => 'Portfolio App',
            'summary' => 'A sample project',
            'description' => 'Project details',
        ]);
        $image = ProjectImage::create([
            'project_id' => $project->id,
            'image_path' => 'projects/portfolio.png',
            'sort_order' => 1,
        ]);

        $this->assertSame($image->id, $project->image->first()->id);
        $this->assertSame(1, $image->fresh()->sort_order);
    }

    public function test_education_experience_message_and_social_link_models_work(): void
    {
        $education = Education::create([
            'institution_name' => 'Example University',
            'degree' => 'BS',
            'field_of_study' => 'Computer Science',
            'start_date' => '2020-01-01',
            'is_current' => false,
        ]);
        $experience = Experience::create([
            'company_name' => 'Example Co.',
            'position' => 'Engineer',
            'start_date' => '2024-01-01',
            'is_current' => true,
        ]);
        $message = Message::create([
            'name' => 'Taylor Example',
            'email' => 'message@example.test',
            'subject' => 'Hello',
            'body' => 'A test message',
        ]);
        $socialLink = SocialLink::create([
            'platform' => 'GitHub',
            'url' => 'https://github.com/example',
            'icon' => 'github',
            'sort_order' => '2',
        ]);

        $this->assertSame('educations', $education->getTable());
        $this->assertSame('experience', $experience->getTable());
        $this->assertFalse($message->is_read);
        $this->assertSame(1, Message::unread()->count());
        $message->markAsRead();
        $this->assertSame(0, Message::unread()->count());
        $this->assertSame('github', $socialLink->icon);
        $this->assertSame(2, $socialLink->sort_order);
    }
}
