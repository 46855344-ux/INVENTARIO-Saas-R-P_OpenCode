# Validación con Docker Compose

**Proyecto:** SaaS Inversiones R&P
**Fecha:** 2026-11-17
**Estado:** Validado en entorno local (Windows + Docker Desktop + WSL2)
**Alcance:** backend Laravel, frontend React/Vite y MySQL 8 en contenedores

---

## 1. Entorno

| Elemento | Versión / valor |
| --- | --- |
| Docker Desktop | 29.9.3 (engine WSL2) |
| Docker Compose | v5.7.0 |
| Imagen backend | `saas-inversiones-rp-backend` (PHP 8.3 CLI) |
| Imagen frontend | `saas-inversiones-rp-frontend` (Node 22 Alpine) |
| Imagen base de datos | `mysql:8.0` |
| Host | Windows 11, proyecto en `D:` |

## 2. Puertos usados en la validación

Se usaron puertos alternativos para no chocar con los servicios locales
(MariaDB de XAMPP en `3306`, backend en `8000`, frontend en `5173`).

| Servicio | Contenedor | Host |
| --- | --- | --- |
| Backend | `8000` | `8001` |
| Frontend | `5173` | `5174` |
| MySQL | `3306` | `3307` |

Variables del `.env` de la raíz:

```dotenv
DOCKER_APP_PORT=8001
DOCKER_WEB_PORT=5174
DOCKER_DB_PORT=3307
APP_URL=http://localhost:8001
FRONTEND_URL=http://localhost:5174
SANCTUM_STATEFUL_DOMAINS=localhost,localhost:5174,127.0.0.1,127.0.0.1:8001,::1
VITE_API_URL=http://localhost:8001/api
```

## 3. Comandos ejecutados

```powershell
docker compose up -d --build
docker compose exec backend php artisan migrate:fresh --seed --force
docker compose ps
curl http://localhost:8001/api/health
```

Validación tipo release (código dentro de la imagen, sin bind mounts):

```powershell
docker compose -f docker-compose.yml -f docker-compose.release.yml up -d --build
$env:CYPRESS_BASE_URL = "http://localhost:5174"
npm run e2e
```

## 4. Evidencia

| Verificación | Resultado |
| --- | --- |
| `docker compose build` | Backend y frontend construidos |
| `docker compose ps` | 3 servicios `Up`; MySQL `healthy` |
| Migraciones dentro del contenedor | Aplicadas |
| `db:seed` dentro del contenedor | Aplicado |
| `/api/health` | `{"status":"ok"}` |
| Login SPA (`/sanctum/csrf-cookie` + `/api/v1/auth/login`) | `204` y `200` |
| CORS | `Access-Control-Allow-Origin` = origen del frontend |
| Sesiones en MySQL del contenedor | Persistidas (tabla `sessions`) |
| Frontend en `5174` | `200` con assets servidos por Vite |
| Cypress (smoke, flujo crítico, inventario) | **3/3 passing** |

## 5. Correcciones aplicadas durante la validación

1. **Entrypoint y TLS de MySQL 8** — el cliente MariaDB del contenedor
   rechazaba el certificado autofirmado de MySQL (`self-signed certificate in
   certificate chain`) y quedaba esperando indefinidamente. Se agregó
   `--skip-ssl` al `mysqladmin ping` de `entrypoint.backend.sh`.

2. **`artisan serve` descartaba variables de entorno** — el comando pasa al
   proceso hijo solo una lista blanca de variables, por lo que `DB_HOST=db`,
   `FRONTEND_URL` y demás valores de Compose se perdían y la aplicación tomaba
   los del `.env`. Se reemplazó el `CMD` por el servidor embebido de PHP
   directamente (`php -S`), conservando el router de Laravel.

3. **Bind mount lento en Windows** — montar `./backend` sobre WSL2 hacía que
   cada request tardara ~15 s por el costo de `stat` sobre NTFS. Se agregó
   `docker-compose.release.yml`, que elimina los bind mounts (`!reset`) y usa
   el código horneado en la imagen. Con eso el health pasó de ~16 s a ~0,05 s.

4. **MySQL lento en el primer arranque** — se desactivó el binlog y se usó
   `innodb_flush_log_at_trx_commit=2` en el servicio `db` (solo desarrollo).

## 6. Limitaciones

- El `CMD` del backend usa el servidor embebido de PHP: es adecuado para
  desarrollo y validación, **no para producción**. En producción usar
  PHP-FPM + Nginx o un servidor equivalente.
- La imagen de frontend ejecuta Vite en modo desarrollo; no es una build
  estática servida por Nginx.
- En este equipo el disco de Docker Desktop se movió a `D:\Docker\data`
  (`disk\docker_data.vhdx` y `main\ext4.vhdx`), verificado tras reiniciar con
  imágenes, contenedores y datos intactos. En otras máquinas, la vía soportada
  es **Settings → Resources → Advanced → Disk image location**.
- Esta validación **no sustituye staging ni HTTPS**. Siguen pendientes TLS,
  cookies `Secure`, backups reales, RPO/RTO y aceptación del Product Owner.
