# DailyFlow Testing & Code Quality Reference

## 1. Pest Testing Conventions

All tests must be written in Pest PHP syntax.

### Test Structure
- `tests/Feature/Api/TaskTest.php`: Feature tests verifying HTTP status codes, validation errors, and JSON response structure.
- `tests/Unit/Actions/CreateTaskActionTest.php`: Unit tests verifying business logic isolation, event firing, and transaction safety.

### Feature Test Example
```php
use App\Models\Task;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->user = User::factory()->create(['timezone' => 'UTC']);
    Sanctum::actingAs($this->user);
});

it('creates a new task for the authenticated user', function () {
    $payload = [
        'title' => 'Complete DailyFlow architecture review',
        'priority' => 'high',
        'due_date' => now()->toDateString(),
    ];

    $response = $this->postJson('/api/v1/tasks', $payload);

    $response->assertCreated()
        ->assertJsonPath('data.title', $payload['title'])
        ->assertJsonPath('data.priority', 'high');

    $this->assertDatabaseHas('tasks', [
        'user_id' => $this->user->id,
        'title' => $payload['title'],
    ]);
});

it('cannot view another user\'s task', function () {
    $otherUser = User::factory()->create();
    $foreignTask = Task::factory()->for($otherUser)->create();

    $response = $this->getJson("/api/v1/tasks/{$foreignTask->id}");

    $response->assertForbidden();
});
```

---

## 2. Laravel Pint Standards

Code style must be formatted using Laravel Pint before committing or submitting code reviews.

```bash
# Check code style without changing files
./vendor/bin/pint --test

# Fix all code style issues
./vendor/bin/pint
```

### Key Style Rules
- PSR-12 and Laravel preset formatting.
- Strict types declaration `declare(strict_types=1);` where applicable.
- Clean docblocks, typed properties, and typed return types on all methods.
