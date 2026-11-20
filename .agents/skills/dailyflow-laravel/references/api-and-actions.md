# DailyFlow API & Actions Reference

## 1. Controllers (Thin Layer)

Controllers handle HTTP routing, dependency injection, and HTTP response mapping.

### Guidelines:
- Keep methods under 15 lines.
- No direct Eloquent writes (`$model->save()`, `$model->update()`) inside controllers.
- Use explicit Route Model Binding scoped by user, or retrieve models through authenticated user relations.

```php
namespace App\Http\Controllers\Api;

use App\Actions\Tasks\CreateTaskAction;
use App\Http\Requests\Tasks\StoreTaskRequest;
use App\Http\Resources\TaskResource;
use Illuminate\Http\JsonResponse;

class TaskController extends Controller
{
    public function store(StoreTaskRequest $request, CreateTaskAction $action): JsonResponse
    {
        $task = $action->execute($request->user(), $request->validated());

        return (new TaskResource($task))
            ->response()
            ->setStatusCode(201);
    }
}
```

---

## 2. Form Requests (Validation Layer)

Form Requests encapsulate all input validation, type casting rules, and authorization sanity checks.

### Guidelines:
- Always use typed rules (`array` syntax over pipe syntax).
- Normalize date fields and sanitize text inputs.
- Use custom error messages when validation semantics are domain-specific.

```php
namespace App\Http\Requests\Tasks;

use App\Enums\TaskPriority;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Endpoint-level authorization checked via middleware/policy
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'priority' => ['required', Rule::enum(TaskPriority::class)],
            'due_date' => ['nullable', 'date_format:Y-m-d'],
            'due_time' => ['nullable', 'date_format:H:i', 'required_with:due_date'],
            'project_id' => [
                'nullable',
                Rule::exists('projects', 'id')->where('user_id', $this->user()?->id),
            ],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:50'],
        ];
    }
}
```

---

## 3. Actions (Domain Logic Layer)

Actions live in `app/Actions/<Domain>/` and implement a single public `execute()` method.

### Guidelines:
- Accept strongly-typed parameters or Data Transfer Objects (DTOs) / validated arrays.
- Always require the `User $user` context to guarantee ownership.
- Manage database transactions when modifying multiple records.
- Fire domain events (e.g., `TaskCompletedEvent`, `RecurringTaskTriggeredEvent`).

```php
namespace App\Actions\Tasks;

use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreateTaskAction
{
    public function execute(User $user, array $data): Task
    {
        return DB::transaction(function () use ($user, $data) {
            $task = $user->tasks()->create([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'priority' => $data['priority'],
                'due_date' => $data['due_date'] ?? null,
                'due_time' => $data['due_time'] ?? null,
                'project_id' => $data['project_id'] ?? null,
            ]);

            if (!empty($data['tags'])) {
                $task->syncTags($data['tags']);
            }

            return $task->load(['project', 'tags']);
        });
    }
}
```

---

## 4. API Resources (Serialization Layer)

API Resources format the outbound JSON schema consistently.

### Guidelines:
- Never expose internal database column names or raw timestamps if formatted ISO 8601 strings are expected.
- Conditionally load relationships using `whenLoaded()`.
- Include computed flags (e.g., `is_overdue`, `is_today`) computed by model accessors.

```php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'priority' => $this->priority,
            'status' => $this->status,
            'is_completed' => (bool) $this->completed_at,
            'completed_at' => $this->completed_at?->toIso8601String(),
            'due_date' => $this->due_date?->format('Y-m-d'),
            'due_time' => $this->due_time,
            'is_overdue' => $this->is_overdue,
            'is_today' => $this->is_today,
            'subtasks_count' => $this->whenCounted('subtasks'),
            'completed_subtasks_count' => $this->whenCounted('completedSubtasks'),
            'project' => new ProjectResource($this->whenLoaded('project')),
            'subtasks' => SubtaskResource::collection($this->whenLoaded('subtasks')),
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
```
