# DailyFlow Task Lifecycle & Recurrence Reference

## 1. Task Lifecycle & State Transitions

### Allowed Transitions:
- `pending` -> `in_progress` -> `completed`
- `pending` -> `completed` (direct check-off)
- `completed` -> `pending` (reopen)
- `pending` / `in_progress` -> `cancelled`
- `cancelled` -> `pending` (restore)
- `completed` -> `archived` (after retention window or explicit archive)

### Transition Handlers:

#### On Task Completion (`MarkTaskCompleted`):
1. Set `status = 'completed'` and `completed_at = now()`.
2. Check subtasks:
   - If subtasks exist, mark unfinished subtasks as completed OR enforce that all subtasks must be completed first (configurable per user preference, default: cascade complete subtasks with `completed_at = now()`).
3. If the task is recurring:
   - Trigger `HandleRecurringTaskCompletionAction`.
4. Update `DailyProgress` record for `(user_id, task_date)`.
5. Dispatch `TaskCompleted` event.

#### On Task Reopen (`ReopenTask`):
1. Set `status = 'pending'` and `completed_at = null`.
2. If subtasks were cascaded, subtasks remain in their state unless explicitly specified.
3. Update `DailyProgress` record (decrement completed count for the completion date).
4. Dispatch `TaskReopened` event.

---

## 2. Recurring Tasks Architecture

Recurring tasks can be modeled in two ways; DailyFlow supports the **Next-Instance Materialization Pattern**:

### Rules for Recurrence:
1. **Recurrence Rule Definition (RRULE / Cadence)**:
   - `frequency`: `daily`, `weekly`, `monthly`, `custom`
   - `interval`: e.g., every 2 days, every 3 weeks
   - `by_weekday`: e.g., `['monday', 'wednesday', 'friday']`
   - `end_date` / `max_occurrences`: optional limits
2. **No Duplication Invariant**:
   - Every recurring task template has a unique identifier `recurrence_rule_id`.
   - Each materialized instance has `(recurrence_rule_id, recurrence_index)` or unique constraint `(user_id, recurrence_rule_id, due_date)`.
   - When a recurring task is completed:
     - The current task instance stays marked as `completed` for historical tracking and daily progress stats.
     - The NEXT instance is generated and scheduled for the next calculated date.
     - If the next instance already exists in the database for that date (e.g., generated earlier by a cron job), the generation action returns the existing instance without creating a duplicate.
3. **Skipping / Missed Recurring Tasks**:
   - If a recurring task is overdue and the user completes it, the next due date is calculated from:
     - Option A (Strict Cadence): based on the original due date.
     - Option B (Completion Relative): based on the completion date.
     - Default in DailyFlow: **Strict Cadence** to maintain routine consistency.

---

## 3. Subtasks Hierarchy & Ordering

1. Subtasks are ordered checklist items belonging to a parent task (`task_id`).
2. Each subtask has:
   - `id`, `task_id`, `title`, `is_completed`, `completed_at`, `position` (integer for drag-and-drop ordering).
3. Reordering subtasks updates `position` indices within a single database transaction.
4. Parent task progress percentage is dynamically derived:
   $$\text{Progress} = \frac{\text{Completed Subtasks Count}}{\text{Total Subtasks Count}} \times 100$$
   If total subtasks is 0, progress is $100\%$ if task is completed, else $0\%$.
