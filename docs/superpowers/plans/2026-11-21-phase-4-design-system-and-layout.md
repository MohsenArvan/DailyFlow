# Phase 4: Design System & Layout Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build a complete, calm, and responsive Design System, Component Library, and App Layout Shell (Sidebar, Mobile Drawer, Header, and Base UI Components) for DailyFlow.

**Architecture:** Implement a modular atomic design system in Vue 3 (`resources/js/components/ui/`) styled with Tailwind CSS, supporting seamless Light/Dark modes and Persian RTL / English LTR. Wrap authenticated routes in an ergonomic `AppLayout` featuring a collapsible sidebar, mobile drawer, toast notification container, and contextual header.

**Tech Stack:** Vue 3 (Composition API, `<script setup lang="ts">`), TypeScript, Tailwind CSS, Lucide Vue Next, Pinia, Playwright.

**Spec:** Section 6 ("طراحی UI/UX: مهمترین ویژگی محصول") & Section 22 ("فاز 4: Design System و Layout") from project specifications.

---

## Global Constraints

- **Docker-First:** All build, test, and run commands must execute inside Docker containers (`docker compose exec ...` or `docker run ...`).
- **Clean Architecture & Vue Best Practices:** SFC script order: `<script setup lang="ts">` → `<template>` → `<style scoped>` (if needed). Keep view components thin as composition surfaces.
- **Calm, Minimal, Focused, Fast:** Inspired by Things 3, Linear, and Todoist. Avoid clutter, aggressive shadows, or generic templates.
- **Accessibility & UX Floor:** Keyboard accessible (Escape closes modals/drawers, visible focus rings), WCAG contrast compliant, touch targets >= 44px on mobile, zero horizontal overflow.
- **Internationalization Ready:** Support Persian RTL (`font-vazir`) and English LTR (`font-sans`), with date formats (Jalali & Gregorian) from user preferences.

## Review Focus

1. **Mobile Drawer Viewport Transition:** Verify opening/closing the mobile drawer doesn't cause page jitter or trap scroll on body.
2. **Sidebar Collapse State Persistence:** Verify sidebar collapse toggle shrinks navigation while preserving icon access, stored in `localStorage`.
3. **Theme Transition Artifacts:** Verify switching between Dark and Light mode has smooth color transitions without flashing unstyled elements.
4. **Modal/Dialog Traps & Escape Key:** Verify clicking backdrop or pressing Escape dismisses modals without swallowing unintended events.
5. **Toast Auto-Dismissal & Stacking:** Verify multiple rapid toasts stack cleanly and auto-dismiss after their timeout without leaking timers.

---

## Tasks

### Task 1: Design Tokens & Base CSS Enhancements

**Files:**
- Modify: `tailwind.config.js`
- Modify: `resources/css/app.css`

**Interfaces:**
- Consumes: Tailwind CSS 3.4
- Produces: CSS custom properties (`--bg-sidebar`, `--border-subtle`, etc.), typography utility classes, and custom scrollbar styles.

- [ ] **Step 1: Extend Tailwind CSS configuration**
  - Add refined tokens for sidebar, modals, task priority accents, and subtle borders.
- [ ] **Step 2: Add custom utility classes and scrollbar styles in `resources/css/app.css`**
  - Add custom slim scrollbars for sidebar and content area, focus ring utility, and transition timings.
- [ ] **Step 3: Verify build**
  - Run: `docker run --rm -v "$(pwd)":/app -w /app node:22-alpine npm run build`
  - Expected: PASS

---

### Task 2: Primitive UI Components Library

**Files:**
- Create: `resources/js/components/ui/BaseButton.vue`
- Create: `resources/js/components/ui/BaseInput.vue`
- Create: `resources/js/components/ui/BaseTextarea.vue`
- Create: `resources/js/components/ui/BaseCheckbox.vue`
- Create: `resources/js/components/ui/BaseBadge.vue`
- Create: `resources/js/components/ui/BaseModal.vue`
- Create: `resources/js/components/ui/BaseDropdown.vue`
- Create: `resources/js/components/ui/BaseTooltip.vue`
- Create: `resources/js/components/ui/BaseEmptyState.vue`
- Create: `resources/js/components/ui/BaseSkeleton.vue`
- Create: `resources/js/components/ui/BaseProgressBar.vue`

**Interfaces:**
- Consumes: `lucide-vue-next` icons, Tailwind design tokens.
- Produces: Reusable, typed UI primitives for all subsequent feature views.

- [ ] **Step 1: Implement `BaseButton.vue`**
  - Props: `variant` ('primary' | 'secondary' | 'outline' | 'ghost' | 'danger'), `size` ('xs' | 'sm' | 'md' | 'lg'), `loading` (boolean), `disabled` (boolean), `type`.
- [ ] **Step 2: Implement `BaseInput.vue` & `BaseTextarea.vue`**
  - Props: `modelValue`, `label`, `error`, `hint`, `leadingIcon`, `trailingIcon`, `clearable`.
- [ ] **Step 3: Implement `BaseCheckbox.vue`**
  - Props: `modelValue` (boolean), `label`, `priority` ('urgent' | 'high' | 'medium' | 'low' | 'none'), custom checkmark animation.
- [ ] **Step 4: Implement `BaseBadge.vue`**
  - Props: `variant` ('default' | 'brand' | 'success' | 'warning' | 'danger' | 'urgent' | 'high' | 'medium' | 'low'), `size` ('sm' | 'md').
- [ ] **Step 5: Implement `BaseModal.vue`**
  - Props: `isOpen` (boolean), `title`, `description`, `size` ('sm' | 'md' | 'lg' | 'xl').
  - Emits: `close`. Teleports to `body`, handles Escape key and backdrop clicks.
- [ ] **Step 6: Implement `BaseDropdown.vue` & `BaseTooltip.vue`**
  - Accessible contextual menu container with click-outside detection and hover tooltips.
- [ ] **Step 7: Implement `BaseEmptyState.vue`, `BaseSkeleton.vue`, and `BaseProgressBar.vue`**
  - Empty state with illustration/icon slot and action button; loading skeleton blocks; animated progress bar.
- [ ] **Step 8: Verify build**
  - Run: `docker run --rm -v "$(pwd)":/app -w /app node:22-alpine npm run build`
  - Expected: PASS

---

### Task 3: Global Toast Notification System

**Files:**
- Create: `resources/js/composables/useToast.ts`
- Create: `resources/js/components/ui/ToastContainer.vue`

**Interfaces:**
- Consumes: Reactive state, timer management.
- Produces: `useToast()` composable with `success()`, `error()`, `info()`, `warning()` methods.

- [ ] **Step 1: Implement `useToast.ts` composable**
  - Maintains a reactive queue of active toast items with unique ID, type, message, and auto-dismiss duration.
- [ ] **Step 2: Implement `ToastContainer.vue`**
  - Fixed floating container (bottom-left in LTR, bottom-right in RTL), smooth enter/leave transitions, dismiss button.
- [ ] **Step 3: Verify build**
  - Run: `docker run --rm -v "$(pwd)":/app -w /app node:22-alpine npm run build`
  - Expected: PASS

---

### Task 4: Layout Shell (Sidebar, Header, Mobile Drawer, AppLayout)

**Files:**
- Create: `resources/js/components/layout/AppSidebar.vue`
- Create: `resources/js/components/layout/AppHeader.vue`
- Create: `resources/js/components/layout/AppLayout.vue`

**Interfaces:**
- Consumes: `useAuthStore`, `vue-router`, UI primitives.
- Produces: Complete app layout shell for authenticated users.

- [ ] **Step 1: Implement `AppSidebar.vue`**
  - Navigation links:
    - Inbox (`/inbox`, icon: Inbox)
    - Today (`/today`, icon: Calendar)
    - Upcoming (`/upcoming`, icon: CalendarDays)
    - Overdue (`/overdue`, icon: AlertCircle, red badge counter)
    - Completed (`/completed`, icon: CheckCircle2)
    - Projects (`/projects`, collapsible group)
    - Settings (`/settings`, icon: Settings)
  - Features: Collapsible toggle, active route highlighting, subtle badge counts.
- [ ] **Step 2: Implement `AppHeader.vue`**
  - Mobile hamburger toggle, page title/breadcrumbs, theme switcher, user avatar dropdown, Quick Add button trigger (`+`).
- [ ] **Step 3: Implement `AppLayout.vue`**
  - Integrates Sidebar, Mobile Drawer with overlay backdrop, Top Header, Main content container, and `ToastContainer`.
- [ ] **Step 4: Verify build**
  - Run: `docker run --rm -v "$(pwd)":/app -w /app node:22-alpine npm run build`
  - Expected: PASS

---

### Task 5: Integration, Placeholder Views & Routing

**Files:**
- Create: `resources/js/views/InboxView.vue`
- Create: `resources/js/views/TodayView.vue`
- Create: `resources/js/views/UpcomingView.vue`
- Create: `resources/js/views/OverdueView.vue`
- Create: `resources/js/views/CompletedView.vue`
- Create: `resources/js/views/ProjectsView.vue`
- Modify: `resources/js/views/SettingsView.vue`
- Modify: `resources/js/router/index.ts`
- Modify: `resources/js/App.vue`

**Interfaces:**
- Consumes: `AppLayout`, Base Components.
- Produces: Integrated SPA navigation for all core views.

- [ ] **Step 1: Create core views using `AppLayout` and `BaseEmptyState`**
  - Each view provides its header title, action buttons, and empty state placeholder ready for Phase 5 task logic.
- [ ] **Step 2: Update `router/index.ts`**
  - Register `/inbox`, `/today`, `/upcoming`, `/overdue`, `/completed`, `/projects` with `requiresAuth: true`.
  - Redirect `/` to `/today` if authenticated, or landing page if guest.
- [ ] **Step 3: Update `App.vue`**
  - Ensure global initialization, toast container, and seamless layout switching between Guest and Authenticated states.
- [ ] **Step 4: Verify build**
  - Run: `docker run --rm -v "$(pwd)":/app -w /app node:22-alpine npm run build`
  - Expected: PASS

---

### Task 6: Comprehensive Verification & Browser Testing

**Files:**
- Test backend: `tests/Feature/Auth/*`, `tests/Feature/HealthCheckTest.php`
- Code quality: Laravel Pint, TypeScript `vue-tsc`
- Browser verification: Playwright CLI / Curl

- [ ] **Step 1: Run TypeScript compiler check**
  - Run: `docker run --rm -v "$(pwd)":/app -w /app node:22-alpine npm run build`
  - Expected: 0 errors
- [ ] **Step 2: Run Pest tests**
  - Run: `docker compose exec app ./vendor/bin/pest`
  - Expected: 19 passed
- [ ] **Step 3: Run Laravel Pint**
  - Run: `docker compose exec app ./vendor/bin/pint --test`
  - Expected: PASS
- [ ] **Step 4: Browser verification using curl / Playwright**
  - Verify `http://localhost:8080/today`, `http://localhost:8080/inbox`, `http://localhost:8080/settings`.
  - Verify console logs and clean rendering.
- [ ] **Step 5: Git commit and push**
  - Commit all changes to `feat/phase-4-design-system-and-layout` and push to GitHub.
