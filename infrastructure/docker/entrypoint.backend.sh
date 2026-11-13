#!/bin/sh
#
# Entrypoint del contenedor backend.
# Prepara el entorno Laravel la primera vez y luego ejecuta el CMD recibido.
#
set -e

cd /var/www/html

# Si el bind mount monta el código del host, vendor/ puede faltar.
if [ ! -d vendor ]; then
    echo "[entrypoint] Instalando dependencias de Composer..."
    composer install --no-interaction --prefer-dist
fi

# Archivo .env a partir de la plantilla si no existe.
if [ ! -f .env ] && [ -f .env.example ]; then
    echo "[entrypoint] Creando .env desde .env.example..."
    cp .env.example .env
fi

# APP_KEY obligatoria.
if [ -f .env ] && ! grep -q '^APP_KEY=base64:' .env; then
    echo "[entrypoint] Generando APP_KEY..."
    php artisan key:generate --force
fi

# Esperar a MySQL y migrar. `db` es el nombre del servicio en compose.
if [ "${DB_CONNECTION:-mysql}" = "mysql" ]; then
    echo "[entrypoint] Esperando a MySQL en ${DB_HOST:-db}:${DB_PORT:-3306}..."
    until mysqladmin ping -h "${DB_HOST:-db}" -P "${DB_PORT:-3306}" \
        -u "${DB_USERNAME:-saas}" -p"${DB_PASSWORD:-secret}" --silent 2>/dev/null; do
        sleep 2
    done
    echo "[entrypoint] Ejecutando migraciones..."
    php artisan migrate --force || true
fi

echo "[entrypoint] Iniciando: $*"
exec "$@"
