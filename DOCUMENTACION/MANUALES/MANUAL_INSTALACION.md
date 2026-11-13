# Manual de instalación — SaaS Inversiones R&P

**Proyecto:** SaaS Inversiones R&P
**Producto:** Sistema SaaS de gestión de inventario y almacén
**Stack:** React + Laravel + MySQL + Docker (opcional) + Cypress
**Versión:** 0.1.0 — Sprint 0
**Última actualización:** 2026-11-12

Este manual permite levantar el proyecto de forma reproducible en un equipo de
desarrollo. Se presentan dos caminos:

1. **Docker** (recomendado): levanta backend, frontend y MySQL con un solo comando.
2. **Local con XAMPP / PHP + Node**: alternativa cuando Docker no está disponible
   o no se tienen permisos de administrador.

---

## 1. Requisitos

| Herramienta | Versión mínima | Obligatorio | Notas |
| --- | --- | --- | --- |
| Node.js | 20.x (probado con 26.x) | Sí | Incluye npm |
| PHP | 8.3 | Sí | Extensiones: `mbstring`, `pdo_mysql`, `pdo_sqlite`, `openssl`, `intl`, `zip` |
| Composer | 2.x | Sí | Se puede usar `composer.phar` portable |
| MySQL | 8.0 | Sí | O el contenedor incluido en Docker |
| Docker Desktop | 24.x | No | Requiere permisos de administrador |
| Git | 2.x | Sí | Control de versiones |
| Cypress | 16.x | No | Se instala con `npm install` en la raíz |

> **Windows sin permisos de administrador:** se puede usar PHP y Composer
> portables (ver sección 4.3). Este repositorio se desarrolló inicialmente así.

---

## 2. Variables de entorno

1. Copiar la plantilla raíz:

   ```bash
   cp .env.example .env
   ```

2. Completar los valores locales. Variables relevantes:

| Variable | Descripción | Ejemplo local |
| --- | --- | --- |
| `APP_NAME` | Nombre visible de la aplicación | `"SAAS Inversiones R&P"` |
| `APP_URL` | URL del backend | `http://localhost:8000` |
| `APP_TIMEZONE` | Zona horaria de persistencia | `UTC` |
| `APP_DISPLAY_TIMEZONE` | Zona horaria de presentación | `America/Argentina/Buenos_Aires` |
| `APP_LOCALE` | Idioma de la interfaz | `es` |
| `DB_HOST` | Host de MySQL | `127.0.0.1` (Docker: `db`) |
| `DB_DATABASE` | Nombre de la base | `saas_inversiones_rp` |
| `DB_USERNAME` | Usuario de MySQL | `saas` |
| `DB_PASSWORD` | Contraseña de MySQL | `secret` (solo desarrollo) |
| `MYSQL_ROOT_PASSWORD` | Clave de root del contenedor | `root` (solo desarrollo) |
| `VITE_API_URL` | URL base de la API para React | `http://localhost:8000/api` |

3. Copiar la plantilla del backend:

   ```bash
   cp backend/.env.example backend/.env
   ```

4. Generar la clave de aplicación:

   ```bash
   cd backend
   php artisan key:generate
   ```

> **Seguridad:** nunca subir `.env`, respaldos ni credenciales reales al
> repositorio. `.gitignore` ya excluye esos archivos.

---

## 3. Opción A — Docker (recomendada)

### 3.1 Instalar Docker Desktop

1. Descargar Docker Desktop desde <https://www.docker.com/products/docker-desktop/>.
2. Ejecutar el instalador **como administrador**.
3. Reiniciar el equipo cuando lo solicite.
4. Abrir Docker Desktop y esperar a que el motor esté en estado *Running*.
5. Verificar en una terminal nueva:

   ```bash
   docker --version
   docker compose version
   ```

### 3.2 Levantar el entorno

```bash
docker compose up --build
```

Servicios resultantes:

| Servicio | URL / puerto | Descripción |
| --- | --- | --- |
| `backend` | <http://localhost:8000> | API Laravel (`php artisan serve`) |
| `frontend` | <http://localhost:5173> | React + Vite con recarga en caliente |
| `db` | `localhost:3306` | MySQL 8.0 con `utf8mb4` |

Primer arranque: el entrypoint del backend instala dependencias si faltan,
crea `.env`, genera `APP_KEY`, espera a MySQL y ejecuta `php artisan migrate`.

Verificación rápida:

```bash
curl http://localhost:8000/api/health
# {"status":"ok","service":"SAAS Inversiones R&P","time":"..."}
```

Comandos útiles:

```bash
docker compose logs -f backend     # ver logs del backend
docker compose exec backend php artisan migrate
docker compose exec backend php artisan test
docker compose exec db mysql -u saas -p saas_inversiones_rp
docker compose down                # detener sin borrar datos
docker compose down -v             # detener y borrar el volumen de MySQL
```

> El proyecto incluye `infrastructure/docker/Dockerfile.backend`,
> `Dockerfile.frontend`, `php.ini` y `entrypoint.backend.sh`.

---

## 4. Opción B — Entorno local (XAMPP / PHP + Node)

### 4.1 Instalar XAMPP (MySQL y Apache)

1. Descargar XAMPP desde <https://www.apachefriends.org/>.
2. Instalar con los módulos **Apache** y **MySQL**.
3. Abrir el Panel de Control de XAMPP e iniciar **MySQL**.
4. Crear la base y el usuario (phpMyAdmin o consola):

   ```sql
   CREATE DATABASE saas_inversiones_rp
     CHARACTER SET utf8mb4
     COLLATE utf8mb4_unicode_ci;

   CREATE USER 'saas'@'localhost' IDENTIFIED BY 'secret';
   GRANT ALL PRIVILEGES ON saas_inversiones_rp.* TO 'saas'@'localhost';
   FLUSH PRIVILEGES;
   ```

   También se puede ejecutar `database/scripts/01-init.sql` adaptando el
   nombre de la base si se cambió.

5. (Opcional) Configurar Apache con el vhost de referencia
   `infrastructure/apache/000-default.conf`.

### 4.2 Instalar PHP y Composer

- Instalador oficial de Windows: <https://windows.php.net/download/> (PHP 8.3 NTS x64).
- Composer: <https://getcomposer.org/download/>.

Agregar al `PATH` la carpeta de PHP y la ubicación de `composer.phar`.

### 4.3 PHP y Composer portables (sin administrador)

```powershell
# Estructura usada durante el Sprint 0 (fuera del repositorio)
# D:\Proyectos Open Code\Inventario_Saas_RYP\.tools\php\php.exe
# D:\Proyectos Open Code\Inventario_Saas_RYP\.tools\composer\composer.phar

# Ejemplo de uso directo
& "D:\Proyectos Open Code\Inventario_Saas_RYP\.tools\php\php.exe" -v
& "D:\Proyectos Open Code\Inventario_Saas_RYP\.tools\php\php.exe" `
    "D:\Proyectos Open Code\Inventario_Saas_RYP\.tools\composer\composer.phar" install
```

Habilitar en `php.ini` las extensiones: `curl`, `fileinfo`, `gd`, `intl`,
`mbstring`, `openssl`, `pdo_mysql`, `pdo_sqlite`, `sqlite3`, `zip`.

### 4.4 Instalar el backend

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
```

### 4.5 Instalar el frontend

```bash
cd frontend
npm install
```

### 4.6 Instalar Cypress (raíz del repositorio)

```bash
npm install
npx cypress verify
```

### 4.7 Ejecutar en desarrollo

En dos terminales:

```bash
# Terminal 1 — API
cd backend
php artisan serve
```

```bash
# Terminal 2 — interfaz
cd frontend
npm run dev
```

Abrir <http://localhost:5173>.

---

## 5. Comandos de calidad y pruebas

### Backend

```bash
cd backend
php artisan test                 # PHPUnit (SQLite en memoria)
vendor/bin/pint --test           # verificar estilo
vendor/bin/pint                  # corregir estilo
```

### Frontend

```bash
cd frontend
npm run lint                     # oxlint
npm run test                     # Vitest
npm run coverage                 # cobertura HTML/LCOV
npm run build                    # compilación de producción
```

### End-to-End

```bash
# Con el frontend levantado en http://localhost:5173
npm run cypress:open             # modo interactivo
npm run e2e                      # modo headless
```

Los reportes quedan en `tests/reports/`.

---

## 6. Estructura del repositorio

```text
SAAS-INVERSIONES-RP/
├── frontend/                  # React 19 + Vite + TypeScript
├── backend/                   # Laravel 13 (API REST)
│   └── database/migrations/   # Fuente oficial del esquema MySQL
├── database/
│   ├── diagrams/              # Diagramas entidad-relación
│   ├── dictionaries/          # Diccionario de datos
│   └── scripts/               # Scripts auxiliares e inicialización
├── tests/
│   ├── e2e/                   # Casos Cypress
│   ├── fixtures/              # Datos de prueba
│   ├── support/               # Comandos y configuración
│   └── reports/               # Reportes generados
├── infrastructure/
│   ├── docker/                # Dockerfiles, php.ini, entrypoint
│   ├── apache/                # Virtual hosts
│   └── xampp/                 # Guía de entorno local
├── DOCUMENTACION/
│   ├── SDD/                   # Diseño de software y ADR
│   ├── TESTING/               # Planes y evidencias de prueba
│   ├── DEVSECOPS/             # CI/CD y calidad
│   ├── SEGURIDAD/             # Políticas y controles
│   └── MANUALES/              # Manuales técnicos y de usuario
├── scripts/                   # Automatización (setup, database, deployment)
├── Reglas/ESTANDAR.md         # Estándar obligatorio de programación
├── .github/workflows/ci.yml   # Integración continua
├── docker-compose.yml
└── .env.example
```

---

## 7. Solución de problemas

| Síntoma | Causa probable | Solución |
| --- | --- | --- |
| `php` no se reconoce | PHP fuera del `PATH` | Usar ruta completa o agregar PHP al `PATH` |
| `npm.ps1` bloqueado por directiva de ejecución | Política de PowerShell | Usar `npm.cmd` o `Set-ExecutionPolicy -Scope Process Bypass` |
| `SQLSTATE[HY000] [2002]` | MySQL apagado o host/puerto incorrectos | Iniciar MySQL y revisar `DB_HOST`/`DB_PORT` |
| `Access denied for user` | Usuario/clave incorrectos | Revisar `DB_USERNAME`/`DB_PASSWORD` y permisos en MySQL |
| `APP_KEY` vacía | `.env` sin clave | `php artisan key:generate` |
| Cypress no abre | Navegador o dependencias faltantes | `npx cypress verify` / `npx cypress install` |
| Docker no inicia | Virtualización deshabilitada | Habilitar VT-x/AMD-V en BIOS y WSL 2 |
| Puerto ocupado | Otro proceso usa 8000/5173/3306 | Cambiar los puertos en `.env` |

---

## 8. Credenciales de desarrollo

> Solo para entornos locales. **No usar en producción.**

| Servicio | Usuario | Contraseña |
| --- | --- | --- |
| MySQL (Docker) | `saas` | `secret` |
| MySQL root (Docker) | `root` | `root` |
| MySQL (XAMPP) | `root` | *(vacía por defecto)* |

---

## 9. Próximos pasos

1. Aprobar las decisiones pendientes del registro (`DOCUMENTACION/SDD/ADR/`).
2. Implementar el modelo multiempresa (US-0101 en adelante).
3. Configurar SonarQube y OWASP Dependency-Check en el pipeline.
4. Completar los manuales de usuario a medida que avancen los módulos.

---

**Documentos relacionados**

- `README.md` — inicio rápido.
- `Reglas/ESTANDAR.md` — reglas obligatorias de desarrollo.
- `DOCUMENTACION/SDD/SDD-01_ARQUITECTURA_GENERAL.md` — arquitectura.
- `DOCUMENTACION/SDD/SDD-04_DISENO_BASE_DATOS.md` — modelo de datos y multiempresa.
- `DOCUMENTACION/DEVSECOPS/CI_CD.md` — pipeline.
- `DOCUMENTACION/TESTING/PLAN_PRUEBAS_SPRINT_0.md` — plan y evidencias.
