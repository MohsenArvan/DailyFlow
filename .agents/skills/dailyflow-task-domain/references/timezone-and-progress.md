# DailyFlow Timezone Handling & Progress Calculation

## 1. Timezone-Agnostic Due Dates vs Timed Tasks

### The Two Date Paradigms:

1. **Calendar Date Tasks (Date-Only, No Time)**:
   - Example: "Finish tax report by November 20".
   - Stored in database as: `due_date DATE` (`YYYY-MM-DD`).
   - Invariant: A date-only task belongs to that calendar date anywhere in the world. It must NEVER be converted to UTC midnight and shifted forward or backward into a different calendar day when formatted.
   - Frontend displays `YYYY-MM-DD` directly without timezone shift.

2. **Timed Tasks (Date + Clock Time)**:
   - Example: "Client call at 15:30".
   - Stored as: `due_date DATE` + `due_time TIME` OR converted to `due_datetime_utc TIMESTAMP WITH TIME ZONE`.
   - Stored in UTC in PostgreSQL with user's current timezone captured on their profile.
   - Converted to the user's localized time on the frontend.

---

## 2. Today, Upcoming, and Overdue Definitions

All date buckets are evaluated relative to the user's local date:
$$\text{User Today} = \text{Current timestamp in User's Timezone} \to \text{Date portion}$$

### Categorization Formulas:
- **Today**:
  $$\text{due\_date} = \text{User Today} \quad \text{AND} \quad \text{status} \ne \text{'completed'}$$
- **Overdue**:
  $$\text{due\_date} < \text{User Today} \quad \text{AND} \quad \text{status} \ne \text{'completed'}$$
  *(For timed tasks due today whose `due_time` has passed, they can also be visually flagged as overdue for the day).*
- **Upcoming (Next 7 Days)**:
  $$\text{User Today} < \text{due\_date} \le \text{User Today} + 7 \text{ days} \quad \text{AND} \quad \text{status} \ne \text{'completed'}$$
- **No Date / Someday**:
  $$\text{due\_date IS NULL} \quad \text{AND} \quad \text{status} \ne \text{'completed'}$$

---

## 3. Daily Progress & Velocity Tracking

DailyFlow provides users with daily completion momentum.

### Daily Metric Schema (`daily_progress`):
- `user_id`: UUID/BigInt
- `date`: DATE (`YYYY-MM-DD` in user's timezone)
- `total_tasks_scheduled`: integer (tasks due on this day)
- `tasks_completed`: integer (tasks completed on this day, regardless of original due date)
- `daily_completion_rate`: float ($$\frac{\text{tasks\_completed}}{\max(\text{total\_tasks\_scheduled}, 1)}$$)
- `streak_count`: integer (consecutive days where at least 1 priority task was completed or daily target was achieved)

### Invariants:
- Completing a task increments `tasks_completed` for the current local day.
- Reopening a task decrements `tasks_completed` for the date it was originally marked completed.
- Progress updates are atomic and idempotent.
