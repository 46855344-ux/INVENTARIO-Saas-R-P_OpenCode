# Release notes — v1.0.0-rc1

**Proyecto:** SaaS Inversiones R&P
**Fecha:** 2026-11-17
**Estado:** Release candidate 1 — **no producción**
**Commit base:** `f0c6b93`

## 1. Alcance incluido

- Sprints 0 a 9: identidad, catálogo, unidades/ubicaciones, inventario,
  compras/traslados, métricas/alertas, conteos/auditoría, asistente de lectura
  y estabilización.
- Incremento v1.1: autenticación social Google/Facebook (mocks), cuenta y
  componentes UX/UI.
- Refresh visual y asistente flotante.

## 2. Evidencia

| Control | Resultado |
| --- | --- |
| Backend PHPUnit | 157 pruebas / 626 aserciones |
| Frontend Vitest | 88 pruebas |
| Cypress | 3 specs / 3 passing |
| Pint / Oxlint / build | OK |
| Composer/npm audit | Sin vulnerabilidades |
| Lighthouse | 100 accesibilidad en login, métricas y conteos |
| Migraciones demo | Aplicadas |

## 3. Cómo ejecutar

```powershell
# Backend
cd backend
php artisan serve --host=127.0.0.1 --port=8000

# Frontend
cd frontend
npm run dev
```

Ingreso demo: `admin@empresa-demo.test` / `password`.

## 4. Limitaciones conocidas

- Validación real con MySQL/Docker pendiente.
- MySQL/MariaDB local validado con migraciones, seed y E2E 3/3; Docker Desktop pendiente.
- HTTPS, cookies seguras y CORS de staging pendientes.
- Backup/restore real y RPO/RTO (DP-06/DP-07) pendientes.
- OAuth Google/Meta reales pendientes.
- Prueba manual con lector de pantalla y móvil físico pendiente.
- E2E aún no integrado en GitHub Actions.
- DP-02 (valorización), DP-09 (márgenes) y DP-05 (aprobaciones) abiertos.

## 5. Criterio para v1.0.0

Cerrar las limitaciones de la sección 4, completar staging y obtener aceptación
formal del Product Owner. **No usar este tag como aprobación de producción.**
