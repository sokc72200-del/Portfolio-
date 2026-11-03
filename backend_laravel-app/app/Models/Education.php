<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'institution_name',
    'degree',
    'field_of_study',
    'start_date',
    'end_date',
    'is_current',
    'location',
    'description',
])]
class Education extends Model
{
    protected $table = 'educations';

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'is_current' => 'boolean',
        ];
    }
}
