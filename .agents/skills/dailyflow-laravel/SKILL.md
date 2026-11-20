---
name: dailyflow-laravel
description: Backend development standards, architectural patterns, and coding conventions for DailyFlow using Laravel 11+, Pest PHP, Sanctum, and PostgreSQL. Use whenever designing, creating, refactoring, or testing Laravel backend components, models, controllers, actions, policies, migrations, queues, or API endpoints.
metadata:
  framework: Laravel 11+
  language: PHP 8.3+
  testing: Pest 3+
  security: Laravel Sanctum, Policies
---

# DailyFlow Laravel Backend Standards

This skill defines the definitive backend development practices and architectural standards for the **DailyFlow** personal task manager.

## Core Architectural Principles

1. **Thin Controllers**: Controllers must only receive validated requests, delegate execution to an Action or Service, and return an API Resource. No business logic or raw database queries belong in controllers.
2. **Action-Based Domain Logic**: Business operations (e.g., `CreateTaskAction`, `ToggleTaskCompletionAction`, `GenerateRecurringTaskAction`) reside in single-purpose Action classes (`app/Actions/...`) with an `execute()` method.
3. **No Unnecessary Repositories**: Do NOT implement traditional DAO/Repository wrappers around Eloquent. Leverage Eloquent models, local query scopes (`scopeForUser`, `scopeDueToday`), and Eloquent relationships directly.
4. **Strict User Data Ownership**: Every query, mutation, and resource access MUST be strictly scoped to the authenticated user (`$request->user()->tasks()` or `$query->where('user_id', $user->id)`). Never rely on client-supplied `user_id`.
5. **Database Transactions**: Wrap multi-step mutations (e.g., recurring task roll-forward, subtask cascading, progress recalculation) in `DB::transaction()`.
6. **Authorization via Policies**: Enforce ownership and permissions using Laravel Policies (`TaskPolicy`, `ProjectPolicy`) hooked into controller authorization or action checks.
7. **Pest Testing Mandatory**: Every API endpoint and core domain action must have automated Pest feature and unit tests.
8. **Code Cleanliness via Laravel Pint**: All PHP code must adhere to the Laravel Pint standard (`./vendor/bin/pint`).

---

## Standard Request-Response Flow

```
HTTP Request
     │
     ▼
[Form Request] ────> Validates input & parses dates/types
     │
     ▼
[Controller] ──────> Calls Policy::authorize() -> Invokes Action
     │
     ▼
[Action / Service] ─> Executes business logic within DB::transaction()
     │
     ▼
[API Resource] ────> Serializes Eloquent model/collection to standardized JSON
     │
     ▼
HTTP Response (200, 201, 204, etc.)
```

---

## Detailed References

For implementation patterns, code templates, and detailed checklists, consult the companion reference guides:

- [API & Actions Reference](./references/api-and-actions.md): Controller structure, Form Requests, Actions pattern, API Resources, and JSON response formats.
- [Database, Security & Queue Reference](./references/database-and-security.md): Eloquent relationships, ownership scoping, policies, transactions, Sanctum authentication, background queues, and query optimization (preventing N+1).
- [Testing & Quality Reference](./references/testing-and-pint.md): Pest testing guidelines, database refresh, factories, and Pint code formatting.
