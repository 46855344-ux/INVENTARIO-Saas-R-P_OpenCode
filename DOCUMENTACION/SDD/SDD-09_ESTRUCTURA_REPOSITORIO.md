# SDD-09 — Estructura del repositorio y convenciones

**Proyecto:** SaaS Inversiones R&P
**Versión:** 1.0 — Sprint 0
**Fecha:** 2026-11-12
**Estado:** Vigente

---

## 1. Estructura raíz

```text
SAAS-INVERSIONES-RP/
├── frontend/                  # Aplicación React
│   ├── public/
│   ├── src/
│   │   ├── app/               # Configuración y rutas
│   │   ├── assets/            # Recursos estáticos
│   │   ├── components/        # Componentes reutilizables
│   │   ├── features/          # Funcionalidades por dominio
│   │   │   ├── auth/
│   │   │   ├── products/
│   │   │   ├── inventory/
│   │   │   ├── movements/
│   │   │   ├── suppliers/
│   │   │   ├── metrics/
│   │   │   └── alerts/
│   │   ├── hooks/
│   │   ├── layouts/
│   │   ├── services/          # Cliente HTTP y servicios API
│   │   ├── styles/
│   │   ├── types/
│   │   └── utils/
│   └── tests/                 # Pruebas unitarias y de componentes (Vitest)
├── backend/                   # Laravel (monolito modular)
│   ├── app/
│   │   ├── Http/{Controllers,Middleware,Requests}
│   │   ├── Models/
│   │   ├── Policies/
│   │   ├── Services/
│   │   └── Modules/{Identity,Catalog,Inventory,Purchasing,Metrics,Audit}
│   ├── database/
│   │   ├── factories/
│   │   ├── migrations/        # Fuente oficial del esquema
│   │   └── seeders/
│   ├── routes/api.php
│   └── tests/                 # PHPUnit
├── database/                  # Documentación y scripts auxiliares
│   ├── diagrams/
│   ├── dictionaries/
│   └── scripts/
├── tests/                     # Pruebas transversales E2E
│   ├── e2e/
│   ├── fixtures/
│   ├── support/
│   └── reports/
├── infrastructure/
│   ├── docker/                # Dockerfiles, php.ini, entrypoint
│   ├── apache/                # Virtual hosts
│   └── xampp/                 # Guía de entorno local
├── DOCUMENTACION/
│   ├── SDD/
│   │   └── ADR/
│   ├── TESTING/
│   ├── DEVSECOPS/
│   ├── SEGURIDAD/
│   └── MANUALES/
├── scripts/
│   ├── setup/
│   ├── database/
│   └── deployment/
├── Reglas/ESTANDAR.md
├── .github/workflows/
├── .env.example
├── .gitignore
├── docker-compose.yml
├── README.md
├── CHANGELOG.md
└── LICENSE
```

## 2. Responsabilidades

| Carpeta | Responsabilidad | No debe contener |
| --- | --- | --- |
| `frontend/` | Interfaz SPA, consumo de API | Credenciales de BD, reglas de negocio críticas |
| `backend/` | API, reglas, validaciones, autorización | Recursos visuales del frontend |
| `backend/database/migrations/` | Esquema oficial | Scripts manuales divergentes |
| `database/` | Diagramas, diccionario, scripts auxiliares | Sustitutos de migraciones |
| `tests/` | Pruebas E2E y datos de prueba | Código de producción |
| `infrastructure/` | Docker, Apache, XAMPP | Lógica de aplicación |
| `DOCUMENTACION/` | Diseño, pruebas, DevSecOps, seguridad, manuales | Secretos |
| `scripts/` | Automatización operativa | Lógica de negocio |
| `Reglas/` | Estándar obligatorio | Código ejecutable |

## 3. Convenciones de nombres

| Ámbito | Regla | Ejemplo correcto | Ejemplo incorrecto |
| --- | --- | --- | --- |
| Idioma de código | Inglés | `company_id` | `empresa_id` |
| Idioma de interfaz | Español | «Empresa» | «Company» |
| Clases PHP | PascalCase | `StockMovementService` | `stock_movement_service` |
| Métodos/variables PHP | camelCase | `registerMovement()` | `registrar_movimiento()` |
| Tablas MySQL | snake_case plural | `stock_movements` | `movimientos_stock` |
| Columnas MySQL | snake_case | `company_id` | `CompanyId` |
| Componentes React | PascalCase | `ProductTable.tsx` | `product-table.tsx` |
| Hooks React | camelCase con `use` | `useProducts.ts` | `productsHook.ts` |
| Tipos TypeScript | PascalCase | `StockMovement` | `stock_movement` |
| Rutas API | kebab-case plural | `/api/v1/stock-movements` | `/api/v1/stockMovements` |
| Ramas | tipo/alcance | `feat/inventory-movement` | `cambios` |
| Commits | Conventional Commits | `feat(inventory): registrar entrada` | `arreglos` |

El glosario bilingüe completo está en `Reglas/ESTANDAR.md`, Anexo A.

## 4. Ramas y versionado

- `main`: rama estable y desplegable.
- `develop`: integración del trabajo en curso (si el equipo la adopta).
- `feature/<alcance>`: nuevas funcionalidades.
- `fix/<alcance>`: correcciones.
- `docs/<alcance>`: documentación.
- `chore/<alcance>`: mantenimiento.

Versionado semántico `MAJOR.MINOR.PATCH`. La API se versiona en la URL
(`/api/v1`). No se eliminan versiones sin período de transición documentado.

## 5. Revisión de código

Todo cambio requiere:

1. Rama propia y commits atómicos con mensaje convencional.
2. Pruebas automatizadas que cubran el cambio.
3. `pint --test` y `npm run lint` sin errores.
4. Al menos una revisión por otra persona.
5. Actualización de documentación y `CHANGELOG.md` cuando corresponda.

## 6. Configuración y secretos

- `.env.example` documenta las variables; `.env` nunca se versiona.
- Los secretos de producción se inyectan desde el orquestador.
- No se incluyen respaldos, claves ni credenciales en el repositorio.

## 7. Reglas de organización

1. React no conecta directamente con MySQL.
2. Las reglas de negocio viven en Laravel.
3. Las migraciones son la fuente oficial del esquema.
4. Toda consulta de dominio respeta el aislamiento por empresa.
5. Los movimientos confirmados no se eliminan físicamente.
6. Cada módulo incluye sus pruebas.
7. Los E2E transversales viven en `tests/e2e/`.
8. Docker es el entorno reproducible; XAMPP es la alternativa documentada.
9. El chatbot del MVP es de solo lectura y respeta permisos.
10. La documentación de decisiones se registra en `DOCUMENTACION/SDD/ADR/`.
