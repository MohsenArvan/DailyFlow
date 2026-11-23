<?php

namespace App\Models;

use App\Enums\ProjectNoteReviewStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectNote extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = ['title', 'body', 'review_status'];

    protected $attributes = ['review_status' => ProjectNoteReviewStatus::Unreviewed->value];

    protected function casts(): array
    {
        return [
            'pinned_at' => 'immutable_datetime',
            'review_status' => ProjectNoteReviewStatus::class,
        ];
    }

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
