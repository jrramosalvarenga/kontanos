#!/bin/bash
set -e

PORT="${PORT:-80}"
sed -ri "s/^Listen .*/Listen ${PORT}/" /etc/apache2/ports.conf
sed -ri "s/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

# Auto-initialize DB schema on first boot (Render provides DATABASE_URL)
if [ -n "$DATABASE_URL" ]; then
    DB_HOST=$(echo "$DATABASE_URL" | sed -n 's|.*@\([^:/]*\).*|\1|p')
    DB_PORT=$(echo "$DATABASE_URL" | sed -n 's|.*:\([0-9]*\)/.*|\1|p')
    DB_NAME=$(echo "$DATABASE_URL" | sed -n 's|.*/\([^?]*\).*|\1|p')
    DB_USER=$(echo "$DATABASE_URL" | sed -n 's|.*://\([^:]*\):.*|\1|p')
    export PGPASSWORD=$(echo "$DATABASE_URL" | sed -n 's|.*://[^:]*:\([^@]*\)@.*|\1|p')

    TABLE_EXISTS=$(psql -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USER" -d "$DB_NAME" \
        -tAc "SELECT EXISTS(SELECT 1 FROM information_schema.tables WHERE table_name='users');" 2>/dev/null || echo "f")

    if [ "$TABLE_EXISTS" = "f" ]; then
        echo "[entrypoint] Initializing database schema..."
        psql -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USER" -d "$DB_NAME" \
            -f /var/www/html/sql/schema.sql
        echo "[entrypoint] Schema initialized."
    fi
fi

exec "$@"
