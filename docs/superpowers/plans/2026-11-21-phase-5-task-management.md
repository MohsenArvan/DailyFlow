# Phase 5: Task Management Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Implement the full Task Management domain for DailyFlow: database models, migration schema, RESTful APIs, Action-based architecture, Subtasks hierarchy, Pinia state, Quick Add, inline editing, task details slide-over panel, and live view integration (Today, Inbox, Upcoming, Overdue, Completed).

**Architecture:** 
- **Backend:** Laravel 11 with PostgreSQL, PHP 8.3 Enums, Thin Controllers, Action Pattern (`app/Actions/Task/*`, `app/Actions/Subtask/*`), Policies for strict tenant isolation, Form Requests with validation, and API Resources.
- **Domain Rules:** Timezone-agnostic calendar dates (`due_date DATE`), deterministic Today/Overdue calculation based on user's timezone, idempotent completion/reopen state transitions.
- **Frontend:** Vue 3 Composition API (`<script setup lang="ts">`), Pinia task store (`useTaskStore`), optimistic UI updates, TaskItem with priority styling and inline actions, QuickAdd bar, and TaskDetailPanel slide-over.

**Tech Stack:** Laravel 11, PostgreSQL, Pest PHP, Vue 3, TypeScript, Pinia, Tailwind CSS, Lucide Icons.

**Spec:** Section 7 ("مدیریت تسک‌ها"), Section 9 ("زیرتسک‌ها"), Section 13 ("دیتابیس"), and Section 14 ("API").

---

## Global Constraints

- **Docker-First:** All artisan, composer, npm, and test commands execute inside containers.
- **Tenant Isolation:** Every task and subtask query is strictly scoped by `auth()->id()`. Cross-user access returns `404 Not Found` or `403 Forbidden`.
- **Timezone Invariant:** Date-only tasks use `due_date` (`YYYY-MM-DD`) without UTC offset shifting. Today and Overdue filters compute relative to the user's configured timezone.
- **Zero Monolithic Components:** Distinct responsibilities for TaskItem, QuickAdd, DetailPanel, SubtaskList.

## Review Focus

1. **User Data Isolation:** Ensure User A cannot view, update, delete, complete, or reorder User B's tasks or subtasks.
2. **Timezone Boundary Calculation:** Ensure tasks due on calendar date $D$ show under "Today" when user's local date is $D$, and under "Overdue" when user's local date is $> D$.
3. **Subtask Cascading & Progress Calculation:** Ensure completing a parent task or updating subtasks reflects correct progress percentage ($N/M$).
4. **Reorder Ordering Integrity:** Ensure moving tasks maintains valid integer sequence without collision.
5. **Quick Add Validation & Empty Strings:** Ensure typing a task and hitting Enter creates it immediately with trimmed title, default priority (`medium`), and correct default due date.

---

## Tasks

### Task 1: Database Migration & Schema Setup

**Files:**
- Create: `database/migrations/2026_11_21_000001_create_tasks_table.php`
- Create: `database/migrations/2026_11_21_000002_create_subtasks_table.php`

**Interfaces:**
- Consumes: PostgreSQL 16
- Produces: `tasks` and `subtasks` tables with proper indexes and foreign keys.

- [ ] **Step 1: Write migration for `tasks` table**
  - Fields: `id`, `uuid`, `user_id`, `project_id` (nullable), `parent_id` (nullable), `title`, `description`, `status` (default: pending), `priority` (default: medium), `due_date`, `due_time`, `reminder_at`, `recurrence_rule`, `order` (default: 0), `completed_at`, `created_at`, `updated_at`, `deleted_at`.
  - Indexes: `[user_id, status, due_date]`, `[user_id, order]`, `[uuid]`.
- [ ] **Step 2: Write migration for `subtasks` table**
  - Fields: `id`, `task_id`, `user_id`, `title`, `is_completed` (default: false), `position` (default: 0), `completed_at`, `created_at`, `updated_at`.
  - Indexes: `[task_id, position]`, `[user_id]`.
- [ ] **Step 3: Run migration inside Docker**
  - Command: `docker compose exec app php artisan migrate`
  - Expected: PASS

---

### Task 2: Models, Enums, Policies & Resources

**Files:**
- Create: `app/Enums/TaskStatus.php`
- Create: `app/Enums/TaskPriority.php`
- Create: `app/Models/Task.php`
- Create: `app/Models/Subtask.php`
- Create: `app/Policies/TaskPolicy.php`
- Create: `app/Http/Resources/TaskResource.php`
- Create: `app/Http/Resources/SubtaskResource.php`

**Interfaces:**
- Consumes: Eloquent ORM, PHP 8.3 Enums
- Produces: Fully typed domain models with scopes (`forUser`, `today`, `overdue`, `upcoming`, `inbox`, `completed`), relationships, accessors, and policies.

- [ ] **Step 1: Implement Enums**
  - `TaskStatus`: `PENDING = 'pending'`, `COMPLETED = 'completed'`.
  - `TaskPriority`: `NONE = 'none'`, `LOW = 'low'`, `MEDIUM = 'medium'`, `HIGH = 'high'`, `URGENT = 'urgent'`.
- [ ] **Step 2: Implement Models**
  - `Task.php`: HasUuid trait, SoftDeletes, relationships (`user`, `subtasks`, `parent`), scopes for date buckets and status, casts for enums and dates.
  - `Subtask.php`: relationships (`task`, `user`), casts.
- [ ] **Step 3: Implement TaskPolicy**
  - Methods: `viewAny`, `view`, `create`, `update`, `delete`, `restore`, `forceDelete`.
- [ ] **Step 4: Implement API Resources**
  - `TaskResource` (transforms task with subtasks, formatted dates, flags: `is_overdue`, `is_today`, progress percentage).
  - `SubtaskResource`.

---

### Task 3: Backend Actions, Form Requests & Controllers

**Files:**
- Create: `app/Http/Requests/Task/StoreTaskRequest.php`
- Create: `app/Http/Requests/Task/UpdateTaskRequest.php`
- Create: `app/Http/Requests/Task/ReorderTasksRequest.php`
- Create: `app/Http/Requests/Subtask/StoreSubtaskRequest.php`
- Create: `app/Http/Requests/Subtask/UpdateSubtaskRequest.php`
- Create: `app/Http/Requests/Subtask/ReorderSubtasksRequest.php`
- Create: `app/Actions/Task/CreateTaskAction.php`
- Create: `app/Actions/Task/UpdateTaskAction.php`
- Create: `app/Actions/Task/CompleteTaskAction.php`
- Create: `app/Actions/Task/ReopenTaskAction.php`
- Create: `app/Actions/Task/DeleteTaskAction.php`
- Create: `app/Actions/Task/ReorderTasksAction.php`
- Create: `app/Actions/Subtask/CreateSubtaskAction.php`
- Create: `app/Actions/Subtask/ToggleSubtaskAction.php`
- Create: `app/Actions/Subtask/DeleteSubtaskAction.php`
- Create: `app/Actions/Subtask/ReorderSubtasksAction.php`
- Create: `app/Http/Controllers/Api/V1/TaskController.php`
- Create: `app/Http/Controllers/Api/V1/SubtaskController.php`
- Modify: `routes/api.php`

**Interfaces:**
- Consumes: Models, Form Requests, Actions
- Produces: Endpoints for `/api/v1/tasks/*` and `/api/v1/subtasks/*`.

- [ ] **Step 1: Implement Form Requests**
- [ ] **Step 2: Implement Actions with database transactions**
- [ ] **Step 3: Implement TaskController and SubtaskController**
- [ ] **Step 4: Register API routes in `routes/api.php`**
- [ ] **Step 5: Verify routes with artisan route:list**
  - Command: `docker compose exec app php artisan route:list --path=tasks`

---

### Task 4: Pest PHP Test Suite for Task Domain

**Files:**
- Create: `tests/Feature/Task/TaskCrudTest.php`
- Create: `tests/Feature/Task/TaskLifecycleTest.php`
- Create: `tests/Feature/Task/SubtaskTest.php`

**Interfaces:**
- Consumes: Pest PHP, RefreshDatabase
- Produces: Automated verification for all task scenarios and edge cases.

- [ ] **Step 1: Write `TaskCrudTest.php`**
  - Test create, quick add, read, update, delete, isolation between users.
- [ ] **Step 2: Write `TaskLifecycleTest.php`**
  - Test completion, reopening, filters (today, overdue, upcoming, inbox, completed), timezone boundaries.
- [ ] **Step 3: Write `SubtaskTest.php`**
  - Test subtask creation, toggle, reorder, progress calculation.
- [ ] **Step 4: Run Pest tests**
  - Command: `docker compose exec app ./vendor/bin/pest tests/Feature/Task`
  - Expected: PASS

---

### Task 5: Frontend Task Types & Pinia Store

**Files:**
- Create: `resources/js/types/task.ts`
- Create: `resources/js/stores/tasks.ts`

**Interfaces:**
- Consumes: Axios `api`, `authStore`
- Produces: Reactive state for tasks, current view filtering, counts (todayCount, overdueCount, inboxCount), and CRUD actions.

- [ ] **Step 1: Define TypeScript interfaces in `types/task.ts`**
  - `Task`, `Subtask`, `TaskPriority`, `TaskStatus`, `CreateTaskPayload`, `UpdateTaskPayload`.
- [ ] **Step 2: Implement `useTaskStore` in `stores/tasks.ts`**
  - State: `tasks`, `currentTask`, `loading`, `activeFilter`.
  - Getters: `todayTasks`, `inboxTasks`, `upcomingTasks`, `overdueTasks`, `completedTasks`, `counts`.
  - Actions: `fetchTasks(filter)`, `createTask`, `updateTask`, `completeTask`, `reopenTask`, `deleteTask`, `addSubtask`, `toggleSubtask`, `deleteSubtask`.

---

### Task 6: Frontend Task UI Components

**Files:**
- Create: `resources/js/components/task/TaskItem.vue`
- Create: `resources/js/components/task/TaskQuickAdd.vue`
- Create: `resources/js/components/task/TaskDetailPanel.vue`
- Create: `resources/js/components/task/TaskList.vue`

**Interfaces:**
- Consumes: Base UI components (`BaseCheckbox`, `BaseBadge`, `BaseButton`, `BaseInput`), `useTaskStore`.
- Produces: Interactive task list, inline editing, quick add bar, and details slide-over panel.

- [ ] **Step 1: Implement `TaskItem.vue`**
  - Checkbox for completion with priority outline, inline title click, subtask count pill (`2/5`), date badge with overdue indicator, click to select.
- [ ] **Step 2: Implement `TaskQuickAdd.vue`**
  - Streamlined input, priority selector buttons, date picker toggle, Enter to submit.
- [ ] **Step 3: Implement `TaskDetailPanel.vue`**
  - Slide-over panel (or modal on mobile) for editing title, description, priority, due date, subtasks checklist, and delete button.
- [ ] **Step 4: Implement `TaskList.vue`**
  - Renders list of `TaskItem`s with animations and `BaseEmptyState` when empty.

---

### Task 7: View Integration & Sidebar Live Counters

**Files:**
- Modify: `resources/js/views/TodayView.vue`
- Modify: `resources/js/views/InboxView.vue`
- Modify: `resources/js/views/UpcomingView.vue`
- Modify: `resources/js/views/OverdueView.vue`
- Modify: `resources/js/views/CompletedView.vue`
- Modify: `resources/js/components/layout/AppSidebar.vue`
- Modify: `resources/js/components/layout/AppHeader.vue`

**Interfaces:**
- Consumes: `useTaskStore`, Task Components
- Produces: Fully interactive views with real-time tasks, live sidebar badge counters, and header Quick Add integration.

- [ ] **Step 1: Integrate `TodayView.vue`**
  - Fetch today tasks on mount, show progress bar based on completed today / daily goal, quick add bar.
- [ ] **Step 2: Integrate `InboxView.vue`, `UpcomingView.vue`, `OverdueView.vue`, `CompletedView.vue`**
- [ ] **Step 3: Connect `AppSidebar.vue` badge counts**
  - Display live badge count for Overdue (red badge) and Today/Inbox.
- [ ] **Step 4: Connect `AppHeader.vue` Quick Add**
  - Trigger Quick Add or focus input on `+` click or pressing `Q`.

---

### Task 8: Verification, Pint, Build & Git Commit

- [ ] **Step 1: Run Pest tests**
  - Command: `docker compose exec app ./vendor/bin/pest`
- [ ] **Step 2: Run Laravel Pint**
  - Command: `docker compose exec app ./vendor/bin/pint --test`
- [ ] **Step 3: Run Vite production build in Docker**
  - Command: `docker run --rm -v "$(pwd)":/app -w /app node:22-alpine npm run build`
- [ ] **Step 4: Verify via Playwright CLI**
  - Create tasks, complete tasks, verify subtasks, check responsive view.
- [ ] **Step 5: Git commit and push**
  - Push `feat/phase-5-task-management` to GitHub.
