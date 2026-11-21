#!/usr/bin/env bash
set -euo pipefail

if [[ $# -ne 1 || ! -f "$1" ]]; then
    printf 'Usage: %s BACKUP.sql.gz\n' "$0" >&2
    exit 2
fi
archive="$(cd "$(dirname "$1")" && pwd)/$(basename "$1")"
gzip -t "$archive"

root="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$root"
compose=(docker compose --env-file "${DAILYFLOW_ENV_FILE:-.env.production}" -f compose.prod.yaml)
"${compose[@]}" exec -T database sh -ec 'pg_isready -U "$POSTGRES_USER" -d "$POSTGRES_DB"' </dev/null
printf 'Restore %s and overwrite its tables in the configured database? Type RESTORE to proceed: ' "$archive"
read -r confirmation
[[ "$confirmation" == RESTORE ]] || exit 1

gzip -dc "$archive" | "${compose[@]}" exec -T database sh -ec 'psql --single-transaction --file=- --set ON_ERROR_STOP=on -U "$POSTGRES_USER" -d "$POSTGRES_DB"'
