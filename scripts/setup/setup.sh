#!/usr/bin/env bash
#
# Prepara el entorno local del proyecto SaaS Inversiones R&P.
# Uso: bash scripts/setup/setup.sh
#
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$ROOT_DIR"

step() { printf '\n==> %s\n' "$1"; }

step "Verificando herramientas"
php -v >/dev/null
composer -V >/dev/null
node -v >/dev/null
npm -v >/dev/null

step "Preparando variables de entorno"
[ -f .env ] || cp .env.example .env
[ -f backend/.env ] || cp backend/.env.example backend/.env

step "Instalando dependencias del backend"
pushd backend >/dev/null
composer install --no-interaction --prefer-dist
php artisan key:generate

step "Ejecutando migraciones"
php artisan migrate --force || echo "Advertencia: revise la conexión MySQL en backend/.env"
popd >/dev/null

step "Instalando dependencias del frontend"
pushd frontend >/dev/null
npm install
popd >/dev/null

step "Instalando dependencias de pruebas E2E"
npm install

step "Verificación"
pushd backend >/dev/null
php artisan test
popd >/dev/null

pushd frontend >/dev/null
npm run test
popd >/dev/null

printf '\nEntorno preparado correctamente.\n'
printf 'Backend:  php artisan serve (en backend/)\n'
printf 'Frontend: npm run dev (en frontend/)\n'
printf 'E2E:      npm run e2e (con el frontend levantado)\n'
