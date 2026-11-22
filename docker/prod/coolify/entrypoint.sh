#!/bin/sh
set -eu

su-exec www-data dailyflow true
exec "$@"
