# Deploy DailyFlow

Use a Linux host with Docker Engine and Compose v2, at least 2 CPU cores, 3 GB RAM, and storage for PostgreSQL and backups.

1. Copy `.env.production.example` to `.env.production` and restrict it with `chmod 600 .env.production`.
2. Set the public URL and its domain. Set `APP_KEY` to `base64:` followed by the output of `openssl rand -base64 32`. Generate separate database and Redis passwords with `openssl rand -hex 32`.
3. Build the images and start the services.

   ```sh
   docker compose --env-file .env.production -f compose.prod.yaml up -d --build
   docker compose --env-file .env.production -f compose.prod.yaml ps -a
   curl -fsS http://127.0.0.1:8080/up
   ```

The migration service runs and exits before the web, queue, and scheduler services start. After changing application or environment configuration, rerun the Compose start command. For an HTTP-only local smoke test, set the URL to `http://localhost:8080`, the Sanctum domain to `localhost:8080`, and `SESSION_SECURE_COOKIE=false`.

## Deploy with Coolify

Configure the Git-based application with the **Dockerfile** build pack, base directory `/`, Dockerfile location `/docker/php/Dockerfile`, and build stage `coolify`. Set **Ports Exposes** to `8080` and attach the public HTTPS domain to the application. In Healthcheck, use HTTP with path `/up`.

Set `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL` to the full HTTPS URL, `SANCTUM_STATEFUL_DOMAINS` to its hostname, `SESSION_SECURE_COOKIE=true`, and `LOG_CHANNEL=stderr`. Set `APP_KEY` using the steps above for a fresh installation. Preserve it when redeploying an existing installation.

Connect PostgreSQL and Redis as separate services reachable from the application. Set `DB_CONNECTION=pgsql`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD`, then set `REDIS_HOST`, `REDIS_PORT`, and `REDIS_PASSWORD` to match the Redis service. Set `CACHE_STORE=redis`, `SESSION_DRIVER=redis`, and `QUEUE_CONNECTION=redis`. Run a worker with `php artisan queue:work redis --sleep=3 --tries=3 --timeout=90` and schedule `php artisan schedule:run` every minute if you use background jobs and scheduled tasks.

Set `DAILYFLOW_MIGRATE=1` for a single application instance to apply migrations at startup. For multiple replicas, run `php artisan migrate --force` separately before rolling out the new image. After deploying, verify the public `/up` endpoint.

## Upgrade an existing Compose deployment

The standalone production Compose file uses its own PostgreSQL and Redis volumes. Back up the old PostgreSQL service before switching. Use a distinct Compose project name for the new deployment during migration, restore the backup into its database, then run production migrations again before switching traffic.

## Domain and TLS

Point the domain at your host. Configure the host's Nginx virtual host for that domain with `proxy_pass http://127.0.0.1:8080` and set `proxy_set_header Host $host` and `proxy_set_header X-Forwarded-Proto $scheme`. The latter lets PHP generate HTTPS asset URLs behind the proxy. Obtain a certificate with `certbot --nginx -d tasks.example.com` and enable the HTTPS redirect. Serve the HSTS header from the TLS virtual host once HTTPS is working. Keep the Compose web port bound to loopback.

## Backups and recovery

Run `./scripts/backup.sh` to write a timestamped gzip archive. Schedule it with cron using an absolute path and a user that can access Docker. The script retains backups for 30 days in `backups/` or the directory set in `DAILYFLOW_BACKUP_DIR`. Restrict archive access and copy backups to protected off-host storage.

Stop web, queue, and scheduler before restoring an archive. Run `./scripts/restore.sh /path/to/dailyflow_backup.sql.gz` and type `RESTORE` to confirm. The restore runs in a PostgreSQL transaction. Start services again after it succeeds and verify the application through `/up` and by opening a representative task.
