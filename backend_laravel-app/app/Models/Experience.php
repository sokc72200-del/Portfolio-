<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'company_name',
    'position',
    'start_date',
    'end_date',
    'is_current',
    'location',
    'description',
])]
class Experience extends Model
{
    protected $table = 'experience';

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'is_current' => 'boolean',
        ];
    }

    public function scopeLatestFirst(Builder $query): Builder
    {
        return $query->orderByDesc('start_date');
    }
}
