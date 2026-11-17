# Guía de producción, despliegue y recuperación

**Proyecto:** SaaS Inversiones R&P
**Versión:** 1.0 — Sprint 9
**Estado:** Runbook para release candidate; no acredita despliegue real

## 1. Requisitos previos

- PHP 8.3, Composer, Node 22, MySQL 8 y servidor web con HTTPS.
- Dominio y certificado TLS; `APP_URL` y `FRONTEND_URL` definitivos.
- Secretos en gestor/variables del entorno, nunca en Git.
- Respaldo verificado antes de migrar.
- Aprobación del responsable de cambio.

## 2. Variables críticas

| Variable | Uso |
| --- | --- |
| `APP_ENV=production` | Desactiva depuración |
| `APP_DEBUG=false` | No expone trazas |
| `APP_URL` / `FRONTEND_URL` | URLs públicas y redirecciones |
| `DB_*` | Conexión MySQL |
| `SANCTUM_STATEFUL_DOMAINS` | Dominios de la SPA |
| `GOOGLE_*` / `FACEBOOK_*` | OAuth opcional |
| `SESSION_SECURE_COOKIE=true` | Cookies solo por HTTPS |

## 3. Despliegue

1. Etiquetar el commit aprobado (`vX.Y.Z-rcN`).
2. Generar respaldo y verificar su tamaño/integridad.
3. Desplegar artefactos en staging y ejecutar migraciones:
   `php artisan migrate --force`.
4. Ejecutar `php artisan optimize` y limpiar cachés de configuración.
5. Compilar el frontend (`npm ci && npm run build`) y publicar `dist/`.
6. Ejecutar smoke tests: `/api/health`, login, una lectura de inventario y
   una consulta al asistente.
7. Revisar logs y métricas básicas.
8. Promover a producción con la misma secuencia y ventana aprobada.

## 4. Rollback

- Revertir el artefacto al tag anterior.
- Restaurar la base solo si la migración es incompatible; requiere aprobación.
- Invalidar cachés y verificar login, stock y permisos.
- Registrar el incidente y la causa.

## 5. Backup y restauración

Scripts existentes:

- `scripts/database/backup.ps1`
- `scripts/database/restore.ps1`

Reglas:

1. Copia diaria como mínimo; retención y frecuencia **pendientes DP-06**.
2. Almacenar fuera del servidor principal, con cifrado y acceso restringido.
3. Probar restauración en una base aislada antes de producción.
4. Medir tiempo real y compararlo con RPO/RTO aprobados (DP-07).
5. No restaurar sobre producción sin confirmación explícita y respaldo previo.

## 6. Monitoreo mínimo

- Disponibilidad de `/api/health`.
- Errores 5xx y latencia p95.
- Cola de alertas y última corrida de `alerts:evaluate`.
- Espacio en disco y estado de backups.
- Intentos de login fallidos y errores OAuth.

## 7. Controles de seguridad aplicados

- Cabeceras `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy` y
  `Permissions-Policy` en todas las respuestas.
- Rate limit de login: 10 intentos por minuto por IP.
- Rate limit del asistente: 30 solicitudes por minuto por usuario/IP.
- Cookies de sesión y CSRF mediante Sanctum.

## 7. Pendientes bloqueantes

- Validación en staging con MySQL real y HTTPS.
- Prueba de restauración documentada.
- Cierre de DP-06/DP-07.
- Aceptación formal del Product Owner.
