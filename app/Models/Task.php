<?php

namespace App\Models;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'user_id',
        'project_id',
        'parent_id',
        'title',
        'description',
        'status',
        'priority',
        'due_date',
        'due_time',
        'reminder_at',
        'recurrence_rule',
        'order',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => TaskStatus::class,
            'priority' => TaskPriority::class,
            'due_date' => 'immutable_date:Y-m-d',
            'reminder_at' => 'datetime',
            'completed_at' => 'datetime',
            'recurrence_rule' => 'array',
            'order' => 'integer',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Task $task) {
            if (empty($task->uuid)) {
                $task->uuid = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function subtasks(): HasMany
    {
        return $this->hasMany(Subtask::class)->orderBy('position');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Task::class, 'parent_id');
    }

    // Scopes
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', TaskStatus::PENDING->value);
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', TaskStatus::COMPLETED->value);
    }

    public function scopeToday(Builder $query, string $userTodayDate): Builder
    {
        return $query->pending()->where('due_date', $userTodayDate);
    }

    public function scopeOverdue(Builder $query, string $userTodayDate): Builder
    {
        return $query->pending()->where('due_date', '<', $userTodayDate);
    }

    public function scopeUpcoming(Builder $query, string $userTodayDate, int $days = 7): Builder
    {
        $startDate = $userTodayDate;
        $endDate = now()->parse($userTodayDate)->addDays($days)->format('Y-m-d');

        return $query->pending()
            ->where('due_date', '>', $startDate)
            ->where('due_date', '<=', $endDate);
    }

    public function scopeInbox(Builder $query): Builder
    {
        return $query->pending()->whereNull('due_date');
    }

    // Helpers
    public function isCompleted(): bool
    {
        return $this->status === TaskStatus::COMPLETED;
    }

    public function isPending(): bool
    {
        return $this->status === TaskStatus::PENDING;
    }

    public function isOverdue(string $userTodayDate): bool
    {
        if ($this->isCompleted() || ! $this->due_date) {
            return false;
        }

        $formattedDueDate = $this->due_date instanceof CarbonInterface
            ? $this->due_date->format('Y-m-d')
            : (string) $this->due_date;

        return $formattedDueDate < $userTodayDate;
    }

    public function isToday(string $userTodayDate): bool
    {
        if (! $this->due_date) {
            return false;
        }

        $formattedDueDate = $this->due_date instanceof CarbonInterface
            ? $this->due_date->format('Y-m-d')
            : (string) $this->due_date;

        return $formattedDueDate === $userTodayDate;
    }
}
