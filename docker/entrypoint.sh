#!/bin/sh
set -eu

APP_DB_PATH="/app/db.sqlite3"
APP_MEDIA_PATH="/app/media"

DB_PATH="${DJANGO_DB_PATH:-/data/db.sqlite3}"
MEDIA_PATH="${DJANGO_MEDIA_ROOT:-/data/media}"

export DJANGO_DB_PATH="${DB_PATH}"
export DJANGO_MEDIA_ROOT="${MEDIA_PATH}"

mkdir -p "$(dirname "${DB_PATH}")" "${MEDIA_PATH}"

if [ ! -f "${DB_PATH}" ] && [ -f "${APP_DB_PATH}" ]; then
    cp "${APP_DB_PATH}" "${DB_PATH}"
fi

if [ -d "${APP_MEDIA_PATH}" ] && [ -z "$(ls -A "${MEDIA_PATH}" 2>/dev/null)" ]; then
    cp -a "${APP_MEDIA_PATH}/." "${MEDIA_PATH}/"
fi

python manage.py migrate --noinput

exec "$@"
