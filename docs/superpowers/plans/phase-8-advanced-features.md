# برنامه اجرایی فاز ۸: قابلیت‌های پیشرفته و هوشمند (Advanced Features)

> **سند مرجع فاز ۸ DailyFlow** — موتور تسک‌های تکرارشونده، سیستم یادآورها و صف پردازش، جعبه فرمان سریع (Command Palette) و خروجی داده‌ها.

---

## ۱. هدف و معماری فاز ۸

**هدف فاز:** تجهیز DailyFlow به قابلیت‌های بهره‌وری پیشرفته شامل:
1. **موتور تسک‌های تکرارشونده (Recurrence Engine):** پیاده‌سازی الگوی Next-Instance Materialization بر اساس قوانین تکرار روزانه، هفتگی، ماهانه و روزهای هفته (Weekday).
2. **یادآورها و پردازش صف (Reminders & Queue Workers):** تنظیم یادآورهای زمانی، جاب‌های زمان‌بندی‌شده در پس‌زمینه با Redis Queue و ارسال اعلان در فرانت‌اند.
3. **پالت فرمان سریع (Command Palette `Cmd+K` / `Ctrl+K`):** ایجاد مودال جستجو و ناوبری فوق‌سریع با پشتیبانی کامل از کلیدهای میانبر، جستجوی همزمان تسک‌ها، پروژه‌ها و اجرای اکشن‌های سریع (ساخت تسک، فیلتر نماها).
4. **خروجی داده‌ها و پشتیبان‌گیری کاربر (Data Export & Backup):** امکان استخراج داده‌های شخصی در قالب ساختاریافته استاندارد JSON و CSV.

**معماری بک‌اند:**
- افزودن فیلدهای تکرار به جدول `tasks` شامل `recurrence_rule` (JSONB یا String)، `recurrence_type` (daily, weekly, monthly, custom)، `recurrence_parent_id`، و `reminder_at`.
- ایجاد فرمان کنسول `php artisan tasks:generate-recurring` و ثبت آن در `routes/console.php` برای اجرا از طریق Scheduler لاراول.
- ایجاد اکشن اختصاصی `CompleteRecurringTaskAction` برای ایجاد خودکار تسک دوره بعدی به محض اتمام تسک فعلی یا بر اساس جدول زمان‌بندی.
- راه‌اندازی جاب `SendTaskReminderJob` و پردازش آن از طریق Redis Queue.
- اندپوینت `GET /api/v1/user/export` برای دانلود آرشیو تسک‌ها و پروژه‌ها.

**معماری فرانت‌اند:**
- کامپوننت `CommandPalette.vue` الهام‌گرفته از Raycast و Linear با کلید میانبر جهانی `Meta+K` / `Ctrl+K`.
- قابلیت انتخاب تکرار در `TaskModal.vue` با گزینه‌های از پیش تعریف‌شده و سفارشی.
- نشانگرهای بصری تکرار و زنگوله یادآور روی آیتم‌های لیست تسک.
- سرویس اعلان‌های درون‌برنامه‌ای و پشتیبانی از Web Notifications API مرورگر.

---

## ۲. اسکیل‌های هوش مصنوعی موردنیاز در فاز ۸ (Required AI Skills)

| نام اسکیل (Skill) | نحوه استفاده و مسئولیت در فاز ۸ |
| :--- | :--- |
| **`dailyflow-task-domain`** | اعمال قوانین چرخه حیات تسک‌های تکرارشونده، متریالایز کردن تسک دوره بعدی، عدم ایجاد تکرار تکراری (Idempotency)، مدیریت تایم‌زون در موعد یادآورها |
| **`dailyflow-laravel`** | ایجاد جاب‌های صف لاراول، دستورات Artisan کنسول، اکشن‌های تک‌مسئولیتی، اعتبارسنجی با Form Requests و استریم داده در خروجی CSV/JSON |
| **`vue-best-practices`** | پیاده‌سازی کامپوننت `CommandPalette.vue` با هندلینگ کامل کیبورد (ArrowDown, ArrowUp, Enter, Escape)، مدیریت لیسنرهای میانبر بدون نشت حافظه |
| **`frontend-design`** | طراحی بسیار تمیز و متمرکز برای مودال فرمان سریع (کنتراست مناسب، هایلایت کلمات کلیدی، افکت Glassmorphism مینیمال، تایپوگرافی واضح) |
| **`web-design-guidelines`** | مدیریت فوکوس (Focus Trap) در داخل Command Palette، نقش‌های ARIA (`role="combobox"`, `aria-expanded`, `aria-activedescendant`) |
| **`test-driven-development`** | نوشتن تست‌های دقیق Pest برای تولید خودکار تسک‌های تکراری، ارسال یادآورها و خروجی داده‌ها قبل از نهایی‌سازی کد |
| **`verification-before-completion`** | بررسی اجرای موفق جاب‌ها در ردیس، عدم وجود خطای بیلد Vite و گذراندن کامل آزمون‌ها با گزارش شواهد |

---

## ۳. وظایف و اقدامات اجرایی گام‌به‌گام (Tasks & Steps)

### وظیفه ۱: دیتابیس و مدل تکرار و یادآورها (Backend Migrations & Models)
- [ ] ساخت مایگریشن ارتقای جدول `tasks` برای افزودن:
  - `recurrence_type` (nullable enum: `daily`, `weekdays`, `weekly`, `monthly`, `yearly`, `custom`)
  - `recurrence_interval` (integer, پیش‌فرض ۱)
  - `recurrence_days_of_week` (jsonb nullable برای روزهای مشخص هفته)
  - `recurrence_end_date` (date nullable)
  - `reminder_at` (timestamp nullable)
  - `parent_task_id` (foreign key خودارجاعی nullable با `onDelete('set null')`)
- [ ] به‌روزرسانی مدل `Task.php` با Castهای لازم، ریلیشن‌های `parent()` و `recurringInstances()`.

### وظیفه ۲: موتور تولید و پیشبرد تکرار (Recurrence Engine Logic)
- [ ] ساخت کلاس اکشن `app/Actions/Tasks/GenerateNextRecurringTaskAction.php`:
  - محاسبه تاریخ موعد دوره بعدی بر اساس الگوی تعیین‌شده.
  - کپی فیلدهای اصلی تسک (عنوان، توضیحات، پروژه، اولویت، تگ‌ها، تکرار) با وضعیت `pending`.
- [ ] ادغام اکشن تکرار در متد `toggleStatus` یا `CompleteTaskAction`: به محض تکمیل تسک تکرارشونده، تسک بعدی بدون تاخیر تولید گردد.
- [ ] ساخت دستور کنسول `app/Console/Commands/ProcessRecurringTasksCommand.php` (`tasks:generate-recurring`) برای اطمینان از آماده بودن تسک‌های روز جاری.

### وظیفه ۳: سیستم یادآورها و جاب‌های صف (Reminders & Queue Jobs)
- [ ] ساخت جاب `app/Jobs/SendTaskReminderJob.php` با قابلیت اتصال به درایور صف `redis`.
- [ ] ساخت دستور کنسول `tasks:check-reminders` برای زمان‌بندی جاب یادآورها در فواصل یک‌دقیقه‌ای.
- [ ] اندپوینت دریافت اعلان‌های فعال: `GET /api/v1/notifications/unread`.

### وظیفه ۴: پالت فرمان سریع و جستجوی سراسری (Command Palette Backend & Frontend)
- [ ] اندپوینت جستجوی چندمنظوره سریع: `GET /api/v1/search?q={query}` (جستجو روی عنوان تسک‌ها، توضیحات و نام پروژه‌ها).
- [ ] ساخت کامپوننت `resources/js/components/common/CommandPalette.vue`:
  - شورتکات فعال‌ساز جهانی (`Cmd+K` در مک، `Ctrl+K` در ویندوز/لینوکس).
  - اینپوت با فوکوس خودکار و پاک‌کننده سریع (`Escape`).
  - بخش‌بندی نتایج: «ناوبری صفحات»، «پروژه‌ها»، «تسک‌ها»، «عملیات سریع» (مانند ساخت تسک جدید، تغییر تم).
  - پشتیبانی کامل از پیمایش با کلیدهای بالا/پایین و انتخاب با Enter.
- [ ] ادغام کامپوننت در `AppLayout.vue`.

### وظیفه ۵: خروجی داده‌های کاربر (Data Export)
- [ ] ساخت کنترلر `app/Http/Controllers/Api/V1/ExportController.php`:
  - اندپوینت `GET /api/v1/user/export?format=json`
  - اندپوینت `GET /api/v1/user/export?format=csv`
- [ ] ساخت اکشن `app/Actions/User/ExportUserDataAction.php` با ایزولاسیون کامل تسک‌ها، ساب‌تسک‌ها و پروژه‌های کاربر جاری.

### وظیفه ۶: ارتقای فرم و لیست تسک در فرانت‌اند (UI Task Modal & Card)
- [ ] افزودن فیلد انتخاب تکرار در `TaskModal.vue` با پیش‌نمایش متنی قابل‌فهم (مثلاً: «هر روز»، «شنبه تا چهارشنبه»، «هر دوشنبه»).
- [ ] افزودن انتخاب‌گر زمان یادآور (`reminder_at`).
- [ ] نمایش آیکون چرخه تکرار و ساعت یادآور در کارت تسک `TaskCard.vue`.

### وظیفه ۷: آزمون‌های خودکار و اعتبارسنجی (Pest Tests & Verification)
- [ ] ساخت `tests/Feature/Tasks/RecurringTaskTest.php`:
  - تست تکمیل تسک تکرارشونده و اطمینان از ایجاد تسک دوره جدید.
  - تست عدم ایجاد مضاعف در صورت اجرای مکرر دستور کنسول.
- [ ] ساخت `tests/Feature/Search/SearchEndpointTest.php`.
- [ ] ساخت `tests/Feature/User/UserExportTest.php`.
- [ ] اجرای تست‌ها روی پایگاه داده `dailyflow_testing`.
- [ ] فرمت کدها با Pint و بیلد موفقیت‌آمیز Vite.

---

## ۴. معیارهای پذیرش فاز ۸ (Acceptance Criteria)

1. با زدن تیک تکمیل یک تسک تکرارشونده، تسک دوره بعدی با تاریخ دقیق و وضعیت تاییدنشده بلافاصله ساخته شود.
2. با فشردن `Cmd+K` در هر کجای برنامه، پالت فرمان بدون لگ باز شده و امکان جستجو و ناوبری با کیبورد به روانی کار کند.
3. خروجی JSON/CSV بدون نقض امنیتی فقط داده‌های کاربر لاگین‌شده را بازگرداند.
4. تمام کارهای پس‌زمینه با Redis Queue بدون خطا دیسپچ و اجرا شوند.
5. کل تست‌های پروژه (شامل ۶۰+ تست قبلی و تست‌های جدید) کاملاً سبز پاس شوند.
