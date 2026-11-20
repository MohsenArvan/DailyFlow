# DailyFlow Agent Development Guidelines

Welcome to **DailyFlow**, a modern personal task manager engineered with Laravel, Vue 3, TypeScript, Tailwind CSS, PostgreSQL, Redis, and Docker.

---

## 1. Technical Stack Overview

- **Backend**: Laravel 11+, PHP 8.3+, Laravel Sanctum, Laravel Pint.
- **Frontend**: Vue 3 (Composition API, `<script setup lang="ts">`), TypeScript, Tailwind CSS, Vite.
- **Data & Storage**: PostgreSQL (Relational & JSONB data), Redis (Queues & Caching).
- **Runtime & Deployment**: Docker & Docker Compose.
- **Testing**: Pest PHP (Backend Feature & Unit tests), Vitest (Frontend unit/component tests), Playwright CLI (E2E browser testing).

---

## 2. AI Skill Mapping & Roles

Always activate and follow the corresponding skill before undertaking work in that domain:

| Domain / Workflow | Active Skill | Focus & Responsibilities |
| :--- | :--- | :--- |
| **Backend Engineering** | `dailyflow-laravel` | Thin controllers, Actions pattern, Form Requests, Policies, DB transactions, Pest testing, Pint style |
| **Task Domain Logic** | `dailyflow-task-domain` | Task lifecycle, state machine, idempotent recurring tasks, timezone-agnostic dates, progress metrics |
| **Frontend Architecture** | `vue-best-practices` | Vue 3 Composition API, TypeScript types, Pinia state, Composables, component reactivity |
| **Visual UI Design** | `frontend-design` | Distinctive aesthetics, intentional typography, color palettes, spacing, responsive layout |
| **Accessibility & UX Audit**| `web-design-guidelines` | Keyboard navigation, ARIA, focus rings, form usability, mobile responsiveness |
| **Browser E2E Testing** | `playwright-cli` | Real browser automation, cross-browser scenarios, form submissions, console error audits |
| **Engineering Methodology** | `superpowers` (`brainstorming`, `writing-plans`, `test-driven-development`, `systematic-debugging`, `requesting-code-review`, `verification-before-completion`) | Systematic planning, TDD (Red-Green-Refactor), root-cause debugging, code review, evidence before claims |

---

## 3. Core Working Principles

1. **Plan Before Code**: For any non-trivial feature or architectural change, always formulate a clear, step-by-step implementation plan (`writing-plans`) and obtain approval before writing code.
2. **Test-Driven Discipline**: Write automated tests for core business logic, status transitions, and critical API endpoints. Verify failures before implementing fixes.
3. **UI/UX Excellence**: DailyFlow is built for focused daily productivity. Interfaces must be responsive, keyboard-accessible, visually deliberate, and free from generic placeholder aesthetics.
4. **Strict User Data Isolation**: Every database interaction, API query, and mutation must be scoped by the authenticated user.
5. **Verification Before Completion**: Never declare a task complete without running relevant verification commands (tests, linting, builds) and inspecting actual outputs.
