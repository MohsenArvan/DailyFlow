#!/usr/bin/env bash
set -euo pipefail
umask 077

root="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$root"
compose=(docker compose --env-file "${DAILYFLOW_ENV_FILE:-.env.production}" -f compose.prod.yaml)
target="${DAILYFLOW_BACKUP_DIR:-$root/backups}"
mkdir -p "$target"
name="dailyflow_backup_$(date -u +%Y%m%d_%H%M%S).sql.gz"
temporary="$(mktemp "$target/.backup.XXXXXX")"
trap 'rm -f "$temporary"' EXIT

"${compose[@]}" exec -T database sh -ec 'pg_dump --clean --if-exists --no-owner --no-privileges -U "$POSTGRES_USER" "$POSTGRES_DB"' | gzip > "$temporary"
gzip -t "$temporary"
ln "$temporary" "$target/$name"
find "$target" -type f -name 'dailyflow_backup_*.sql.gz' -mtime +30 -delete
printf '%s\n' "$target/$name"
