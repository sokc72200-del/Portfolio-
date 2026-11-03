<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'user_id',
    'full_name',
    'email',
    'phone_number',
    'address',
    'title',
    'bio',
    'avatar',
    'resume',
])]
class Profile extends Model
{
    protected $table = 'profile';

    protected $appends = ['avatar_url', 'resume_url'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function avatarUrl(): Attribute
    {
        return Attribute::get(fn () => $this->avatar ? Storage::url($this->avatar) : null);
    }

    protected function resumeUrl(): Attribute
    {
        return Attribute::get(fn () => $this->resume ? Storage::url($this->resume) : null);
    }
}
