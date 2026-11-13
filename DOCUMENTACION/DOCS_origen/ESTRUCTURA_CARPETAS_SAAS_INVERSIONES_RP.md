# Estructura de carpetas — SaaS Inversiones R&P

**Estado:** propuesta de organización del repositorio.  
**Arquitectura:** frontend React + backend Laravel (monolito modular) + MySQL, comunicados mediante API REST/JSON.

## 1. Estructura raíz del proyecto

```text
SAAS-INVERSIONES-RP/
│
├── frontend/                  # Aplicación web React
├── backend/                   # API y reglas de negocio Laravel
├── database/                  # Documentación y scripts SQL auxiliares
│   ├── diagrams/              # Diagramas entidad-relación
│   ├── dictionaries/          # Diccionario de datos
│   └── scripts/               # Scripts auxiliares (no sustituyen migraciones)
├── tests/                     # Pruebas transversales y E2E
│   ├── e2e/                   # Cypress
│   ├── fixtures/              # Datos de prueba
│   ├── support/               # Utilidades de prueba
│   └── reports/               # Reportes generados
├── infrastructure/            # Configuración de infraestructura
│   ├── docker/                # Imágenes y configuraciones Docker
│   ├── apache/                # Virtual hosts y configuraciones Apache
│   └── xampp/                 # Guías/configuración para desarrollo local alternativo
├── DOCUMENTACION/             # Documentación del proyecto
│   ├── SDD/                   # Documentos de diseño del software
│   ├── TESTING/               # Planes, casos y resultados de pruebas
│   ├── DEVSECOPS/             # CI/CD, calidad y despliegue
│   ├── SEGURIDAD/             # Políticas, riesgos y controles
│   └── MANUALES/              # Manuales técnicos y de usuario
├── scripts/                   # Automatización operativa
│   ├── setup/                 # Preparación del entorno
│   ├── database/              # Respaldos y utilidades de BD
│   └── deployment/            # Despliegues
├── .github/
│   └── workflows/             # GitHub Actions
├── .gitignore
├── .env.example              # Variables de entorno de ejemplo, sin secretos
├── docker-compose.yml        # Orquestación de servicios locales
├── README.md                 # Inicio rápido y descripción general
├── CHANGELOG.md              # Historial de cambios
└── LICENSE                   # Licencia, según decisión del proyecto
```

## 2. Organización interna sugerida

### `frontend/` — React

```text
frontend/
├── public/
├── src/
│   ├── app/                   # Configuración de aplicación y rutas
│   ├── assets/                # Recursos estáticos
│   ├── components/            # Componentes reutilizables
│   ├── features/              # Funcionalidades por dominio
│   │   ├── auth/
│   │   ├── products/
│   │   ├── inventory/
│   │   ├── movements/
│   │   ├── suppliers/
│   │   ├── metrics/
│   │   └── alerts/
│   ├── hooks/
│   ├── layouts/
│   ├── services/              # Cliente HTTP y servicios API
│   ├── styles/
│   ├── types/
│   └── utils/
├── tests/                     # Pruebas unitarias y de componentes (Vitest)
├── .env.example
└── package.json
```

### `backend/` — Laravel

```text
backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   ├── Middleware/
│   │   └── Requests/
│   ├── Models/
│   ├── Policies/
│   ├── Services/              # Casos de uso y lógica de negocio
│   └── Modules/               # Módulos de dominio, según implementación
│       ├── Identity/
│       ├── Catalog/
│       ├── Inventory/
│       ├── Purchasing/
│       ├── Metrics/
│       └── Audit/
├── bootstrap/
├── config/
├── database/
│   ├── factories/
│   ├── migrations/            # FUENTE OFICIAL del esquema MySQL
│   └── seeders/
├── public/                    # Único directorio público del servidor web
├── resources/
├── routes/
│   └── api.php                # Rutas API (o estructura equivalente según versión)
├── storage/
├── tests/                     # PHPUnit / Pest
├── .env.example
├── artisan
└── composer.json
```

> Los árboles internos son una **propuesta de organización**, no una afirmación de carpetas ya creadas. Se ajustarán a la versión de Laravel, las convenciones del equipo y el avance del backlog.

## 3. Documentación SDD

```text
DOCUMENTACION/SDD/
├── SDD-01_ARQUITECTURA_GENERAL.md
├── SDD-02_ARQUITECTURA_FRONTEND.md
├── SDD-03_ARQUITECTURA_BACKEND.md
├── SDD-04_DISENO_BASE_DATOS.md
├── SDD-05_DISENO_API.md
├── SDD-06_SEGURIDAD.md
├── SDD-07_INTERFAZ_USUARIO.md
├── SDD-08_INFRAESTRUCTURA.md
├── SDD-09_ESTRUCTURA_REPOSITORIO.md
└── ADR/                       # Registros de decisiones arquitectónicas
```

**Nota:** `SDD-09` es un documento complementario propuesto para la estructura del repositorio; se conserva la numeración `SDD-01` a `SDD-08` de los documentos base.

## 4. Reglas de organización

1. **Separación de responsabilidades:** React consume la API REST/JSON; no se conecta directamente a MySQL.
2. **Reglas de negocio:** Laravel centraliza validaciones, permisos, transacciones y reglas de inventario.
3. **Esquema de base de datos:** `backend/database/migrations/` es la fuente oficial. `database/` en la raíz contiene documentación y scripts auxiliares.
4. **Multiempresa:** todas las operaciones y consultas deben respetar el aislamiento entre empresas.
5. **Integridad de inventario:** operaciones transaccionales, sin stock negativo; los movimientos confirmados se corrigen con movimientos compensatorios, no eliminándolos.
6. **Pruebas:** unitarias y de integración cerca de cada aplicación; E2E transversales en `tests/e2e/`.
7. **Seguridad:** nunca subir archivos `.env` reales, credenciales, claves ni respaldos sensibles al repositorio.
8. **Despliegue:** Docker como entorno reproducible; Apache/XAMPP como alternativa local documentada.
9. **Calidad:** automatizar pruebas, análisis con SonarQube y controles de dependencias mediante GitHub Actions, según el pipeline aprobado.
10. **Chatbot:** en el MVP se limita a consultas de solo lectura y respeta permisos de acceso.

## 5. Convenciones recomendadas

- Carpetas técnicas: nombres cortos y consistentes; mantener las convenciones de cada framework.
- Documentos: prefijo `SDD-XX` o `ADR-XXXX` y título descriptivo.
- Ramas: `main`, `develop` (si el equipo la adopta), `feature/*`, `fix/*`, `docs/*`.
- Commits: formato consistente, por ejemplo `feat(inventory): registrar entrada de stock`.
- Cada módulo debe incluir sus pruebas y documentación cuando corresponda.

## 6. Orden sugerido de implementación

1. Crear repositorio raíz, `.gitignore`, `README.md` y `.env.example`.
2. Inicializar React en `frontend/` y Laravel en `backend/`.
3. Configurar MySQL, Docker Compose y entorno local.
4. Crear migraciones iniciales y controles de aislamiento multiempresa.
5. Implementar autenticación, catálogo e inventario siguiendo el backlog.
6. Configurar Vitest, PHPUnit/Pest y Cypress.
7. Incorporar GitHub Actions, SonarQube y documentación SDD/ADR.

---

**Proyecto:** SaaS Inversiones R&P  
**Documento:** Estructura de carpetas y organización del repositorio  
**Versión:** 1.0 — propuesta inicial
