<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['project_id', 'skill_id'])]
class ProjectSkill extends Model
{
    protected $table = 'project_skill';
}
