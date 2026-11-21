# برنامه اجرایی فاز ۱۰: آماده‌سازی نهایی پروداکشن و استقرار (Production Readiness & Deployment)

> **سند مرجع فاز ۱۰ DailyFlow** — کانتینریزاسیون حرفه‌ای پروداکشن، کانفیگ‌های امنیتی سرور، صف‌ها و زمان‌بند، اسکریپت‌های پشتیبان‌گیری و بازیابی، و مستندات کامل استقرار.

---

## ۱. هدف و معماری فاز ۱۰

**هدف فاز:** انتقال DailyFlow از یک پروژه توسعه محلی به یک محصول پایدار، ایمن، با کارایی بالا و آماده انتشار عملیاتی (Production-Ready) با ویژگی‌های زیر:
1. **کانتینریزاسیون بهینه چندمرحله‌ای (Multi-Stage Dockerfile):** تفکیک مراحل بیلد کلاینت و سرور، کاهش حجم ایمیج‌ها تا حداقل ممکن، اجرای پروسه‌ها تحت کاربر غیر روت (`www-data`).
2. **پیکربندی استقرار پروداکشن (`compose.prod.yaml`):** پیکربندی مستقل محیط عملیاتی با پروتکل‌های بازراه‌اندازی خودکار (`restart: unless-stopped`)، سهمیه‌بندی منابع (CPU & Memory Limits) و سلامت‌سنجی کانتینرها (Healthchecks).
3. **سرور وب امن و بهینه‌شده (Hardened Nginx Reverse Proxy):** تنظیم هدرهای امنیتی (CSP, HSTS, X-Content-Type-Options, X-Frame-Options)، فشرده‌سازی خودکار Gzip/Brotli، کش ماندگار فایل‌های استاتیک و آمادگی کامل برای اتصال SSL/TLS (Let's Encrypt / Certbot).
4. **مدیریت پردازش صف‌ها و تسک‌های زمان‌بندی‌شده (Worker & Scheduler Services):** جداسازی پروسه‌های `artisan queue:work` و `artisan schedule:work` در سرویس‌های دائمی اختصاصی.
5. **استراتژی پشتیبان‌گیری و بازیابی فاجعه (Automated Backup & Disaster Recovery):** اسکریپت‌های خودکار `backup.sh` و `restore.sh` با قابلیت خروجی فشرده از PostgreSQL، تاریخ‌گذاری، رمزنگاری اختیاری، و روتین پاکسازی نسخه‌های قدیمی (Retention Policy).
6. **مستندسازی و تحویل پروژه (`DEPLOYMENT.md` و راهنمای جامع):** دستورالعمل گام‌به‌گام راه‌اندازی با یک دستور روی هر سرور ابری (Ubuntu / Debian / Docker Host).

---

## ۲. اسکیل‌های هوش مصنوعی موردنیاز در فاز ۱۰ (Required AI Skills)

| نام اسکیل (Skill) | نحوه استفاده و مسئولیت در فاز ۱۰ |
| :--- | :--- |
| **`dailyflow-laravel`** | پیکربندی کش‌های پروداکشن لاراول (`config:cache`, `route:cache`, `view:cache`, `event:cache`), پیکربندی بهینه OPcache برای PHP-FPM, تنظیم لاگینگ ساختاریافته (Daily / Stack) |
| **`antigravity-guide` / DevOps** | طراحی Multi-stage Dockerfile، کوچک‌سازی ایمیج‌ها با حذف وابستگی‌های دِو (Dev Dependencies)، تامین امنیت و حقوق دسترسی پوشه‌های `storage` و `bootstrap/cache` |
| **`systematic-debugging`** | تست و ارزیابی عمیق کانتینرهای پروداکشن، لاگ‌های استارتاپ، ارتباط شبکه داکر بین PHP-FPM، Redis، Postgres و Nginx |
| **`verification-before-completion`** | تست استقرار شبیه‌سازی‌شده محلی با `compose.prod.yaml`، تست واقعی بازگردانی دیتابیس با `restore.sh` و ارائه شواهد سلامت خروجی |

---

## ۳. وظایف و اقدامات اجرایی گام‌به‌گام (Tasks & Steps)

### وظیفه ۱: داکرفایل و کانفیگ بهینه چندمرحله‌ای (Production Dockerfile)
- [ ] ساخت `docker/prod/php/Dockerfile`:
  - مرحله اول (Node Builder): نصب وابستگی‌های فرانت‌اند با `npm ci` و اجرای `npm run build`.
  - مرحله دوم (Composer Builder): نصب وابستگی‌های PHP با `--no-dev --optimize-autoloader`.
  - مرحله نهایی (Runtime): ایمیج سبک PHP 8.3-FPM، فعال‌سازی OPcache پروداکشن، کپی کدها از مراحل قبل، تنظیم مالکیت و مجوز دسترسی روی کاربر `www-data`.
- [ ] ساخت فایل `docker/prod/php/opcache.ini` با مقادیر تنظیم‌شده برای محیط تولید (`opcache.validate_timestamps=0` برای نهایت کارایی، `opcache.memory_consumption=256`).

### وظیفه ۲: وب‌سرور Nginx پروداکشن (Production Nginx Config)
- [ ] ساخت `docker/prod/nginx/default.conf`:
  - هدرهای امنیتی:
    - `X-Frame-Options "SAMEORIGIN"`
    - `X-XSS-Protection "1; mode=block"`
    - `X-Content-Type-Options "nosniff"`
    - `Referrer-Policy "strict-origin-when-cross-origin"`
    - `Content-Security-Policy` متناسب با نیازهای برنامه.
  - کش تهاجمی برای دارایی‌های بیلد شده (`/build/assets/` با هدر `Cache-Control "public, max-age=31536000, immutable"`).
  - فشرده‌سازی `gzip on` برای انواع متنی و اسکریپت‌ها.
  - بستن دسترسی به فایل‌های مخفی (`.env`, `.git`).

### وظیفه ۳: کامپوز پروداکشن و سرویس‌های کمکی (`compose.prod.yaml`)
- [ ] ساخت `compose.prod.yaml`:
  - سرویس `app` (PHP-FPM): فقط درگاه داخلی داکر، متصل به شبکه ایزوله.
  - سرویس `web` (Nginx): پورت‌های عمومی ۸۰ و ۴۴۳.
  - سرویس `database` (PostgreSQL 16): ولوم دیتای دائمی با تنظیمات منابع.
  - سرویس `redis` (Redis 7): ولوم پایداری AOF/RDB با کلمه عبور قوی.
  - سرویس `queue` (Laravel Queue Worker): اجرای دائمی `php artisan queue:work --sleep=3 --tries=3`.
  - سرویس `scheduler` (Cron Scheduler): اجرای متوالی `php artisan schedule:run`.
- [ ] تعریف دقیق سلامت‌سنجی (Healthcheck) برای سرویس‌های `database`, `redis`, و `web`.

### وظیفه ۴: اسکریپت‌های پشتیبان‌گیری و بازیابی خودکار دیتابیس (Disaster Recovery Scripts)
- [ ] ساخت `scripts/backup.sh`:
  - گرفتن خروجی از دیتابیس با `pg_dump` و فشرده‌سازی آنی با `gzip`.
  - ذخیره در پوشه با برچسب زمانی (`backups/dailyflow_backup_YYYYMMDD_HHMMSS.sql.gz`).
  - روتین حذف نسخه‌های قدیمی‌تر از ۳۰ روز.
- [ ] ساخت `scripts/restore.sh`:
  - بررسی صحت فایل بک‌آپ و دسترسی دیتابیس.
  - تخلیه و بازگردانی امن جداول و داده‌ها.
  - پیام‌های هشدار تایید کاربر قبل از بازنویسی داده‌ها.
- [ ] اعطای دسترسی اجرایی (`chmod +x scripts/backup.sh scripts/restore.sh`).

### وظیفه ۵: اسکریپت‌های ورود و کشینگ خودکار پروداکشن (Entrypoint & Warmup)
- [ ] ساخت اسکریپت ورودی کانتینر `docker/prod/php/entrypoint.sh`:
  - اجرای خودکار مایگریشن‌ها در صورت نیاز (`php artisan migrate --force`).
  - اعمال تمام کش‌های بهینه‌سازی:
    - `php artisan config:cache`
    - `php artisan route:cache`
    - `php artisan view:cache`
    - `php artisan event:cache`
  - اجرای پروسه اصلی `php-fpm`.

### وظیفه ۶: مستندات استقرار و راهنمای راه‌اندازی (Documentation)
- [ ] ساخت فایل راهنمای جامع `DEPLOYMENT.md`:
  - پیش‌نیازهای سرور (حداقل رم، پردازنده، داکر و کامپوز).
  - فایل نمونه متغیرهای محیطی پروداکشن (`.env.production.example`).
  - دستورات تک‌خطی برای کلون، ساخت، و بالا آوردن سرویس‌ها:
    `docker compose -f compose.prod.yaml up -d --build`
  - راهنمای اتصال دامنه، تنظیم گواهی SSL رایگان با Certbot.
  - راهنمای زمان‌بندی خودکار بک‌آپ‌ها در Crontab سرور اصلی.
- [ ] به‌روزرسانی `README.md` اصلی مخزن با بج‌های وضعیت، معرفی کامل فیچرها، و لینک به مستندات دپلوی.

### وظیفه ۷: راستی‌آزمایی و ارزیابی نهایی (Verification)
- [ ] تست اجرای آزمایشی بیلد پروداکشن داکر بدون خطا.
- [ ] تست اجرای اسکریپت `scripts/backup.sh` و تایید تولید فایل معتبر فشرده دیتابیس.
- [ ] تست بازگردانی با `scripts/restore.sh` روی دیتابیس تست و اعتبارسنجی عدم تخریب داده.
- [ ] چک استایل نهایی با Pint و اطمینان از پاس شدن تمام تست‌ها.
- [ ] کامیت و پوش روی برنچ `feat/phase-10-production-and-deployment`.

---

## ۴. معیارهای پذیرش فاز ۱۰ (Acceptance Criteria)

1. ایمیج کانتینر پروداکشن فاقد هرگونه فایل غیرضروری توسعه (تست‌ها، پکیج‌های dev، کدهای کامپایل‌نشده فرانت‌اند) بوده و حجم آن مینیمال باشد.
2. با اجرای فرمان `docker compose -f compose.prod.yaml up -d` تمام سرویس‌ها (وب، اپ، دیتابیس، ردیس، صف و اسکژولر) در حالت `healthy` قرار گیرند.
3. هدرهای امنیتی در تمام پاسخ‌های وب‌سرور وجود داشته باشد و فایل‌های استاتیک با کش ماندگار پاسخ داده شوند.
4. اسکریپت بکاپ‌گیری با یک دستور خروجی سالم تولید کند و اسکریپت ریستور قادر به بازیابی صددرصدی وضعیت بدون از دست رفتن رکوردها باشد.
5. فایل `DEPLOYMENT.md` شفاف و بدون ابهام باشد به طوری که هر توسعه‌دهنده یا ادمین سروری بتواند پروژه را در کمتر از ۱۰ دقیقه مستقر کند.
