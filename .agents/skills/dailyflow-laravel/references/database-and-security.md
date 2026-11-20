# DailyFlow Database, Security & Queue Reference

## 1. Strict User Data Ownership & Policies

Every task, project, tag, and reminder belongs to a single authenticated user.

### Eloquent Relationships
```php
// User model
public function tasks(): HasMany
{
    return $this->hasMany(Task::class);
}

// Task model
public function user(): BelongsTo
{
    return $this->belongsTo(User::class);
}
```

### Global or Local Scopes
Always query tasks scoped to the user:
```php
// Querying tasks
$user->tasks()
    ->with(['project', 'tags'])
    ->dueToday($user->timezone)
    ->orderByPriority()
    ->get();
```

### Authorization Policies
```php
namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function view(User $user, Task $task): bool
    {
        return $task->user_id === $user->id;
    }

    public function update(User $user, Task $task): bool
    {
        return $task->user_id === $user->id;
    }

    public function delete(User $user, Task $task): bool
    {
        return $task->user_id === $user->id;
    }
}
```

---

## 2. Authentication with Laravel Sanctum

- Use Laravel Sanctum token-based authentication for Single Page Application (SPA) / API tokens.
- Protect all `/api/v1/*` routes behind the `auth:sanctum` middleware.
- Ensure token revocation on logout.

---

## 3. Query Optimization & Preventing N+1

- **Eager Loading**: Always eager load relationships required by API resources (`->with(['project', 'tags', 'subtasks'])`).
- **Counts**: Use `withCount(['subtasks', 'subtasks as completed_subtasks_count' => fn ($q) => $q->whereNotNull('completed_at')])` rather than loading collections solely for counting.
- **Indexes**: Migrations must include compound indexes:
  - `tasks (user_id, status, due_date)`
  - `tasks (user_id, project_id)`
  - `subtasks (task_id, position)`

---

## 4. Background Queues & Scheduler

- Use Redis as the queue connection driver (`QUEUE_CONNECTION=redis`).
- Long-running or async tasks (e.g., calculating daily summary statistics, sending push/email reminders, cleaning up deleted items) must be dispatched as queued jobs (`implements ShouldQueue`).
- The Laravel Scheduler (`routes/console.php` or `app/Console/Kernel.php`) handles:
  - Checking overdue tasks and dispatching reminders: runs every minute or every 15 minutes.
  - Generating next occurrences for active recurring tasks: runs daily at midnight (per user timezone).
