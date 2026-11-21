# DailyFlow UI, UX and Kanban Implementation Plan

### Goal
Make DailyFlow easier to scan and use on desktop and mobile, and give each project a persistent list or kanban view.

### Architecture
Use shared design tokens and existing Vue components for the visual refresh. Use the project's `view_mode` for the view preference and extend the task lifecycle with `in_progress` for the board. Keep date-based views and dashboard calculations compatible with existing tasks.

## 1. Shared layout and visual language

- Refine the surface colors, typography hierarchy, spacing, and focus indicators in `resources/css/app.css` and `tailwind.config.js`.
- Update `AppLayout.vue`, `AppSidebar.vue`, and `AppHeader.vue` for clearer navigation and a usable mobile layout.
- Refresh shared input, button, empty-state, dialog, and task components, then apply the same visual system to the dashboard, task views, projects, settings, and authentication screens.

## 2. Project list and board

- Add Pest coverage before extending `TaskStatus` and transition handling. Verify that moving a task to in progress keeps it visible in schedule views and that completing it still updates metrics and generates recurring instances.
- Add a board to `ProjectDetailView.vue` with pending, in-progress, and completed columns. Persist list or board through the project API.
- Support drag and drop and a keyboard- and touch-accessible status control. Keep the task in its previous column if saving fails and verify project-scoped access.

## 3. Dashboard and verification

- Verify the dashboard with the migrated development database. Refine loading, empty, error, and populated states in `DashboardView.vue`.
- Run Pest, Pint, Vitest, TypeScript, and the production build. Check dashboard and board flows in Playwright at desktop and mobile sizes in both themes.
- Keep the work on the existing local branch `feat/phase-7-to-10`.
