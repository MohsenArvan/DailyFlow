# DailyFlow

![PHP 8.3](https://img.shields.io/badge/PHP-8.3-777BB4)
![Laravel 12](https://img.shields.io/badge/Laravel-12-FF2D20)
![Vue 3](https://img.shields.io/badge/Vue-3-42B883)
![PostgreSQL 16](https://img.shields.io/badge/PostgreSQL-16-4169E1)

DailyFlow is a Persian-first personal task manager with a right-to-left interface, Jalali and Gregorian dates, and light and dark themes.

- Plan tasks in Today, Inbox, Upcoming, Overdue, and Completed. Add priorities, tags, and subtasks, or work through Today one task at a time in Focus Mode.
- Organize work in projects and track daily goals, completion streaks, and activity over the past 7 or 30 days.
- Schedule recurring tasks and reminders. Search tasks and projects with the command palette using Cmd+K or Ctrl+K.
- Download your tasks, projects, and subtasks from Settings in JSON or CSV.

## Local development

Use PHP 8.3 or later, Composer 2, Node.js 22.12 or later, PostgreSQL, and Redis. Copy `.env.example` to `.env` and set its database, Redis, and application URL values for your environment.

```sh
composer install
npm ci
php artisan key:generate
php artisan migrate
npm run build
php artisan serve
```

If you use `php artisan serve` on its default port, set `APP_URL=http://127.0.0.1:8000`, `SANCTUM_STATEFUL_DOMAINS=127.0.0.1:8000`, and `SESSION_DOMAIN=null`. Run `npm run dev` in a separate terminal for live frontend changes. Start `php artisan queue:work redis` and `php artisan schedule:work` to process reminders in development.

## Tests

Create a PostgreSQL database named `dailyflow_testing` and configure its connection through environment variables before running Pest. The browser suite creates a temporary SQLite database when run without `E2E_BASE_URL`.

```sh
vendor/bin/pest
vendor/bin/pint --test
npm run type-check
npm test
npm run build
npx playwright install chromium
npm run test:e2e
```

## Production deployment

See [DEPLOYMENT.md](DEPLOYMENT.md) to run DailyFlow with Docker, configure TLS, and back up or restore PostgreSQL.
