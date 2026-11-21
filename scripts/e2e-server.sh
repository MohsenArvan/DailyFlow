#!/usr/bin/env bash
set -euo pipefail

scratch="$(mktemp -d)"
server=''
port=${E2E_PORT:-8765}
trap 'if [[ -n "$server" ]]; then kill "$server" 2>/dev/null || true; wait "$server" 2>/dev/null || true; fi; rm -rf "$scratch"' EXIT
trap 'exit 0' TERM INT

export APP_ENV=testing APP_DEBUG=false APP_URL="http://127.0.0.1:$port"
export APP_KEY="base64:$(openssl rand -base64 32)"
export DB_CONNECTION=sqlite DB_DATABASE="$scratch/e2e.sqlite" CACHE_STORE=array SESSION_DRIVER=file
export VITE_HOT_FILE="$scratch/hot" SESSION_DOMAIN=null SESSION_COOKIE=dailyflow_e2e_session
export SESSION_SECURE_COOKIE=false SANCTUM_STATEFUL_DOMAINS="127.0.0.1:$port"
export APP_CONFIG_CACHE="$scratch/config.php" APP_ROUTES_CACHE="$scratch/routes.php"
touch "$DB_DATABASE"
"${E2E_PHP:-php}" artisan migrate --force
router="$PWD/vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php"
(
    cd public
    exec "${E2E_PHP:-php}" -S "127.0.0.1:$port" "$router"
) &
server=$!
wait "$server"
