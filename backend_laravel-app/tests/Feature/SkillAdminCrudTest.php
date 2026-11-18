<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Enums\SkillCategory;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SkillAdminCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_an_existing_skill(): void
    {
        $admin = User::factory()->create(['role' => Role::Admin]);
        $skill = Skill::create([
            'name' => 'Laravel',
            'category' => SkillCategory::Backend,
            'level' => 80,
            'icon' => 'laravel',
        ]);

        $response = $this->actingAs($admin, 'sanctum')
            ->putJson('/api/admin/skills/'.$skill->id, [
                'name' => 'PHP',
                'category' => 'backend',
                'level' => 95,
                'icon' => 'php',
            ]);

        $response->assertOk();
        $this->assertSame('PHP', $skill->fresh()->name);
        $this->assertSame('backend', $skill->fresh()->category->value);
        $this->assertSame(95, $skill->fresh()->level);
        $this->assertSame('php', $skill->fresh()->icon);
    }
}
