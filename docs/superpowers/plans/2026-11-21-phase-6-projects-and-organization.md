# Phase 6: Projects & Task Organization Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Implement full Project management, task categorisation, project progress metrics, sidebar project navigation, and dedicated project views for DailyFlow.

**Architecture:** Build a robust backend domain with a `projects` schema, UUID routing, thin controllers with isolated actions (`CreateProjectAction`, `UpdateProjectAction`, `DeleteProjectAction`, `ToggleFavoriteProjectAction`, `ReorderProjectsAction`), user-scoped policies, and dynamic progress calculations. On the frontend, implement Pinia `useProjectStore`, project creation/edit modals with color palettes and icons, responsive project cards and grid views, dynamic sidebar project tree with task counters, and seamless task-to-project assignment in task details and quick-add components.

**Tech Stack:** Laravel 11+, PostgreSQL 16, Pest PHP, Laravel Pint, Vue 3 (Composition API, `<script setup lang="ts">`), TypeScript, Tailwind CSS, Lucide Icons, Pinia.

**Spec:** DailyFlow Product Specification — Section 7 ("دسته‌بندی و پروژه‌ها: سازمان‌دهی کارها بر اساس موضوعات، اهداف و حوزه‌های زندگی") & Section 22 ("فاز ۶: مدیریت پروژه‌ها و دسته‌بندی تسک‌ها").

---

## Global Constraints

- **Branching Rule:** All work must remain on `feat/phase-6-projects-and-organization`.
- **Docker-First:** All commands (`composer`, `artisan`, `pest`, `pint`, `npm`) must run inside containers.
- **Tenant Isolation:** Every project and task query/mutation must be strictly scoped to `auth()->id()`.
- **Project Progress Invariant:**
  $$\text{Project Progress} = \frac{\text{Completed Tasks Count}}{\text{Total Tasks Count}} \times 100$$
  If total tasks is 0, progress is $0\%$.
- **Project Deletion Strategy:** When a project is deleted, tasks can either have their `project_id` nullified (moved to Inbox) or be soft-deleted, depending on user confirmation (default: detach/nullify).
- **Design Consistency:** Calm, minimal, Things 3 / Linear inspired aesthetic, dark/light mode compatible, RTL Persian font support (`font-vazir`).

---

## Review Focus

1. **User Data Isolation on Project Access:** Attempting to view, update, delete, or associate a task with another user's project must return `403 Forbidden` or `404 Not Found`.
2. **Project Task Progress Edge Cases:** Progress percentage must correctly calculate integer values ($0$ to $100$), handling division by zero gracefully when a project has 0 tasks.
3. **Project Deletion Cascade/Detach:** Verify deleting a project with tasks either moves tasks safely to Inbox (`project_id = null`) without data loss, or soft-deletes them when `delete_tasks=true`.
4. **Dynamic Sidebar Synchronization:** Creating, renaming, or deleting a project immediately updates the sidebar project list and task badges without requiring a hard page refresh.
5. **Project-Scoped Quick Add:** Creating a task inside `/projects/:uuid` view automatically sets `project_id` to the active project.

---

## File Structure

```
backend/
├── app/
│   ├── Actions/Project/
│   │   ├── CreateProjectAction.php
│   │   ├── UpdateProjectAction.php
│   │   ├── DeleteProjectAction.php
│   │   ├── ReorderProjectsAction.php
│   │   └── ToggleFavoriteProjectAction.php
│   ├── Http/
│   │   ├── Controllers/Api/V1/ProjectController.php
│   │   ├── Requests/Project/
│   │   │   ├── StoreProjectRequest.php
│   │   │   ├── UpdateProjectRequest.php
│   │   │   └── ReorderProjectsRequest.php
│   │   └── Resources/ProjectResource.php
│   ├── Models/
│   │   ├── Project.php
│   │   └── Task.php (modify: add project relation)
│   └── Policies/ProjectPolicy.php
├── database/
│   ├── factories/ProjectFactory.php
│   └── migrations/2026_11_21_000003_create_projects_table.php
└── tests/Feature/Project/
    ├── ProjectCrudTest.php
    └── ProjectTaskAssociationTest.php

frontend/
├── resources/js/
│   ├── types/project.ts
│   ├── stores/projects.ts
│   ├── components/
│   │   ├── project/
│   │   │   ├── ProjectModal.vue
│   │   │   └── ProjectCard.vue
│   │   ├── layout/
│   │   │   └── AppSidebar.vue (modify: add projects list & add button)
│   │   └── task/
│   │       ├── TaskDetailPanel.vue (modify: add project selector)
│   │       └── TaskQuickAdd.vue (modify: support defaultProjectId)
│   ├── views/
│   │   ├── ProjectsView.vue (modify: replace placeholder with full grid & favorites)
│   │   └── ProjectDetailView.vue (create: view tasks for a single project)
│   └── router/index.ts (modify: add /projects/:uuid route)
```

---

## Tasks

### Task 1: Database Migration & Project Model

**Files:**
- Create: `database/migrations/2026_11_21_000003_create_projects_table.php`
- Create: `app/Models/Project.php`
- Modify: `app/Models/Task.php`
- Create: `database/factories/ProjectFactory.php`

**Interfaces:**
- Consumes: `users`, `tasks`
- Produces: `Project` model with `uuid`, `name`, `description`, `color`, `icon`, `is_favorite`, `order`, `tasks()`, `user()`

- [ ] **Step 1: Create migration `create_projects_table`**
  - Columns: `id`, `uuid` (unique index), `user_id` (foreignId cascadeOnDelete), `name` (string), `description` (text nullable), `color` (string 20, default `#3b82f6`), `icon` (string 50 nullable), `is_favorite` (boolean default false), `order` (integer default 0), `view_mode` (string 20 default 'list'), timestamps, softDeletes.
  - Add foreign key index.
- [ ] **Step 2: Run migration in Docker**
  - Run: `docker compose exec app php artisan migrate`
  - Expected: Migration `2026_11_21_000003_create_projects_table` runs successfully.
- [ ] **Step 3: Create `app/Models/Project.php` and Factory**
  - Implement UUID generation in boot creating hook.
  - Implement relationships: `user()`, `tasks()`, `pendingTasks()`, `completedTasks()`.
  - Implement scopes: `scopeForUser()`, `scopeFavorites()`, `scopeOrdered()`.
  - Implement computed accessors: `tasks_count`, `completed_tasks_count`, `progress_percentage`.
  - Add `project(): BelongsTo` in `app/Models/Task.php`.
- [ ] **Step 4: Create `database/factories/ProjectFactory.php`**
  - Support states: `favorite()`.

---

### Task 2: Project Actions & Policy

**Files:**
- Create: `app/Policies/ProjectPolicy.php`
- Create: `app/Actions/Project/CreateProjectAction.php`
- Create: `app/Actions/Project/UpdateProjectAction.php`
- Create: `app/Actions/Project/DeleteProjectAction.php`
- Create: `app/Actions/Project/ReorderProjectsAction.php`
- Create: `app/Actions/Project/ToggleFavoriteProjectAction.php`

**Interfaces:**
- Consumes: `User`, `Project`
- Produces: Encapsulated single-responsibility mutations with transactions

- [ ] **Step 1: Implement `ProjectPolicy.php`**
  - `view(User $user, Project $project): bool` => `$user->id === $project->user_id`
  - `update(User $user, Project $project): bool` => `$user->id === $project->user_id`
  - `delete(User $user, Project $project): bool` => `$user->id === $project->user_id`
- [ ] **Step 2: Implement `CreateProjectAction.php`**
  - Calculate max `order` for user + 1. Create project within database transaction.
- [ ] **Step 3: Implement `UpdateProjectAction.php`**
  - Update allowed attributes (`name`, `description`, `color`, `icon`, `is_favorite`, `view_mode`).
- [ ] **Step 4: Implement `DeleteProjectAction.php`**
  - Accepts `bool $deleteTasks = false`. If true, soft-deletes tasks; if false, updates `tasks()->update(['project_id' => null])`. Soft-deletes project.
- [ ] **Step 5: Implement `ReorderProjectsAction.php` & `ToggleFavoriteProjectAction.php`**
  - Reorders matching UUIDs in transaction. Toggles `is_favorite` boolean.

---

### Task 3: Project Requests, Resource, Controller & Routes

**Files:**
- Create: `app/Http/Requests/Project/StoreProjectRequest.php`
- Create: `app/Http/Requests/Project/UpdateProjectRequest.php`
- Create: `app/Http/Requests/Project/ReorderProjectsRequest.php`
- Create: `app/Http/Resources/ProjectResource.php`
- Create: `app/Http/Controllers/Api/V1/ProjectController.php`
- Modify: `routes/api.php`
- Modify: `app/Http/Resources/TaskResource.php` (include `project` summary)

**Interfaces:**
- Consumes: Actions, Form Requests
- Produces: 7 RESTful endpoints under `/api/v1/projects`

- [ ] **Step 1: Create Form Requests**
  - `StoreProjectRequest`: `name` (required, max 100), `description` (nullable, max 1000), `color` (nullable, regex:/^#[a-fA-F0-9]{6}$/), `icon` (nullable, max 50), `is_favorite` (boolean).
  - `UpdateProjectRequest`: same rules as store, nullable.
  - `ReorderProjectsRequest`: `project_uuids` (required, array of strings).
- [ ] **Step 2: Create `ProjectResource.php`**
  - Return: `id`, `uuid`, `name`, `description`, `color`, `icon`, `is_favorite`, `order`, `view_mode`, `tasks_count`, `completed_tasks_count`, `progress_percentage`, `created_at`, `updated_at`.
- [ ] **Step 3: Create `ProjectController.php`**
  - Methods: `index`, `store`, `show`, `update`, `destroy`, `reorder`, `toggleFavorite`.
  - Authorize through `ProjectPolicy`.
- [ ] **Step 4: Register routes in `routes/api.php`**
  - `Route::post('/projects/reorder', [ProjectController::class, 'reorder']);`
  - `Route::post('/projects/{project}/favorite', [ProjectController::class, 'toggleFavorite']);`
  - `Route::apiResource('projects', ProjectController::class);`

---

### Task 4: Backend Feature Tests (Pest PHP) & Pint Verification

**Files:**
- Create: `tests/Feature/Project/ProjectCrudTest.php`
- Create: `tests/Feature/Project/ProjectTaskAssociationTest.php`

**Interfaces:**
- Consumes: All Project endpoints
- Produces: Automated test evidence

- [ ] **Step 1: Implement `ProjectCrudTest.php`**
  - Test: user can list their projects (with accurate task counts).
  - Test: user cannot see another user's projects.
  - Test: user can create project with valid color, icon, name.
  - Test: project creation fails with invalid color format or missing name.
  - Test: user can view single project.
  - Test: user can update project name and color.
  - Test: user can toggle project favorite.
  - Test: user can reorder projects.
- [ ] **Step 2: Implement `ProjectTaskAssociationTest.php`**
  - Test: tasks can be assigned to a project on creation and update.
  - Test: tasks filterable by `project_id` in `/tasks?project_id=...`.
  - Test: project calculates accurate progress percentage ($0\%$, $50\%$, $100\%$).
  - Test: deleting project moves tasks to Inbox by default (`project_id = null`).
  - Test: deleting project with `delete_tasks=true` soft-deletes associated tasks.
- [ ] **Step 3: Run Pest and Pint in Docker**
  - Run: `docker compose exec app ./vendor/bin/pest tests/Feature/Project`
  - Run: `docker compose exec app ./vendor/bin/pint --test`
  - Expected: ALL tests pass, Pint clean.

---

### Task 5: Frontend Project Types & Pinia Store

**Files:**
- Create: `resources/js/types/project.ts`
- Create: `resources/js/stores/projects.ts`
- Modify: `resources/js/types/task.ts` (link `project?: Project`)

**Interfaces:**
- Consumes: `/api/v1/projects` endpoints
- Produces: Reactive Pinia state (`projects`, `favorites`, `currentProject`, `isLoading`, `isModalOpen`, `modalMode`, `modalProject`)

- [ ] **Step 1: Create `resources/js/types/project.ts`**
  - Define `Project`, `CreateProjectPayload`, `UpdateProjectPayload`.
- [ ] **Step 2: Create `resources/js/stores/projects.ts`**
  - State: `projects: ref<Project[]>([])`, `currentProject: ref<Project | null>(null)`, `isLoading: ref<boolean>(false)`, `isModalOpen: ref<boolean>(false)`, `modalMode: ref<'create' | 'edit'>('create')`, `editingProject: ref<Project | null>(null)`.
  - Getters: `favoriteProjects`, `standardProjects`, `projectOptions`.
  - Actions: `fetchProjects()`, `fetchProject(uuid)`, `createProject(payload)`, `updateProject(uuid, payload)`, `deleteProject(uuid, deleteTasks)`, `toggleFavorite(uuid)`, `openCreateModal()`, `openEditModal(project)`, `closeModal()`.
- [ ] **Step 3: Update `types/task.ts`**
  - Ensure `project?: Project | null` and `project_id?: number | null` are typed on `Task`.

---

### Task 6: Project UI Components & Modals

**Files:**
- Create: `resources/js/components/project/ProjectModal.vue`
- Create: `resources/js/components/project/ProjectCard.vue`
- Modify: `resources/js/components/task/TaskQuickAdd.vue` (support default `projectId`)
- Modify: `resources/js/components/task/TaskDetailPanel.vue` (support project selector dropdown)

**Interfaces:**
- Consumes: `useProjectStore`, `useTaskStore`
- Produces: Reusable UI cards, modal dialogs, and task-project linkages

- [ ] **Step 1: Create `ProjectModal.vue`**
  - Name input (with autofocus), description textarea.
  - Curated color picker preset chips (10 vibrant modern colors: Blue, Emerald, Amber, Rose, Violet, Indigo, Cyan, Orange, Pink, Slate) with active ring indicator.
  - Icon picker selector (Folder, Briefcase, BookOpen, Star, Code, Target, Heart, CheckCircle, Zap).
  - Favorite toggle button.
  - Save & Cancel buttons with loading state indicator.
- [ ] **Step 2: Create `ProjectCard.vue`**
  - Visual card showing project icon, custom color accent bar/badge, name, description.
  - Progress bar with completed/total tasks count.
  - Favorite star toggle button.
  - Dropdown menu with "ویرایش" (Edit) and "حذف" (Delete with prompt).
  - Clickable surface navigating to `/projects/:uuid`.
- [ ] **Step 3: Enhance `TaskQuickAdd.vue`**
  - Accept optional prop `projectId?: number | null`.
  - If provided, include `project_id` in task creation payload.
- [ ] **Step 4: Enhance `TaskDetailPanel.vue`**
  - Add Project selector dropdown in meta properties section, allowing assigning a task to any project or removing it (`null`).

---

### Task 7: Projects Views & Dynamic Sidebar Integration

**Files:**
- Modify: `resources/js/views/ProjectsView.vue`
- Create: `resources/js/views/ProjectDetailView.vue`
- Modify: `resources/js/router/index.ts` (add `/projects/:uuid`)
- Modify: `resources/js/components/layout/AppSidebar.vue` (add project list & quick-add project button)
- Modify: `resources/js/components/layout/AppLayout.vue` (mount `ProjectModal`)

**Interfaces:**
- Consumes: All project components, router
- Produces: Integrated end-to-end UX for projects

- [ ] **Step 1: Implement `ProjectsView.vue`**
  - Header: "پروژه‌ها" + description + "پروژه جدید" primary button.
  - Search input to filter projects by name in real-time.
  - Favorite projects section (if any) with grid of `ProjectCard`.
  - All projects section with grid of `ProjectCard`.
  - Empty state with "ایجاد اولین پروژه" CTA button.
- [ ] **Step 2: Create `ProjectDetailView.vue`**
  - Header with project color dot, icon, title, description, favorite star, edit/delete actions.
  - Overall project progress bar (`completed_tasks_count` of `tasks_count`).
  - Scoped `TaskQuickAdd` automatically assigning tasks to this project.
  - `TaskList` containing all tasks for this project, with task completion and detail panel interaction.
- [ ] **Step 3: Update `router/index.ts`**
  - Register `/projects/:uuid` mapped to `ProjectDetailView.vue` with `requiresAuth: true`.
- [ ] **Step 4: Update `AppSidebar.vue`**
  - Add collapsible "پروژه‌ها" section below navigation.
  - List projects with color dot indicator, name, and pending tasks count badge.
  - Quick "+" icon in sidebar header to open `ProjectModal.vue`.
  - Mobile drawer compatibility.
- [ ] **Step 5: Mount `ProjectModal` in `AppLayout.vue`**
  - Teleported modal accessible from sidebar, projects view, or cards.

---

### Task 8: Verification & Quality Assurance

**Interfaces:**
- Consumes: Entire application codebase
- Produces: Production build, passing test suite, Pint compliance

- [ ] **Step 1: Frontend Production Build**
  - Run: `docker run --rm -v "$(pwd)":/app -w /app node:22-alpine npm run build`
  - Expected: Clean build with 0 TypeScript or bundling errors.
- [ ] **Step 2: Backend Test Suite**
  - Run: `docker compose exec -T app ./vendor/bin/pest`
  - Expected: 100% passing tests across Auth, Tasks, and Projects.
- [ ] **Step 3: Code Style Check**
  - Run: `docker compose exec -T app ./vendor/bin/pint --test`
  - Expected: PASS on all files.
- [ ] **Step 4: Git Commit & Push**
  - Commit all changes to `feat/phase-6-projects-and-organization` with conventional commit message.
  - Push branch to `origin` and provide GitHub PR link to user.
