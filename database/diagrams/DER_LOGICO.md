# DER lógico — SaaS Inversiones R&P

**Proyecto:** SaaS Inversiones R&P
**Documento:** Diagrama entidad-relación lógico (US-0002)
**Versión:** 1.0 — Sprint 0
**Fecha:** 2026-11-12
**Estado:** Propuesta para revisión del Product Owner

---

## 1. Convenciones del modelo

- **Idioma interno:** tablas y columnas en inglés (`company_id`, no `empresa_id`),
  conforme a `Reglas/ESTANDAR.md`, Anexo A.
- **Idioma visible:** la interfaz muestra «Empresa», «Producto», «Existencia», etc.
- **Identificadores:** `BIGINT UNSIGNED` autoincremental.
- **Cantidades:** `DECIMAL(14,3)`; **dinero:** `DECIMAL(14,4)`.
- **Fechas:** `DATETIME` en UTC; la presentación se convierte a la zona local.
- **Borrado:** lógico (`deleted_at`) en maestros y catálogo (DD-05 propuesta).
- **Tenant:** `company_id` obligatorio en tablas de dominio, con FK a `companies`.

> La decisión del backlog de representar `empresa_id` se implementa con el nombre
> interno `company_id`, tal como exige el estándar bilingüe del proyecto.

## 2. Diagrama entidad-relación

```mermaid
erDiagram
    COMPANIES {
        bigint id PK
        varchar name
        varchar slug UK
        varchar tax_id
        varchar status
        datetime created_at
        datetime updated_at
    }

    USERS {
        bigint id PK
        varchar name
        varchar email UK
        varchar password
        varchar status
        datetime created_at
    }

    COMPANY_USER {
        bigint id PK
        bigint company_id FK
        bigint user_id FK
        bigint role_id FK
        varchar status
    }

    ROLES {
        bigint id PK
        bigint company_id FK
        varchar code
        varchar name
    }

    PERMISSIONS {
        bigint id PK
        varchar code UK
        varchar name
    }

    PERMISSION_ROLE {
        bigint role_id FK
        bigint permission_id FK
    }

    CATEGORIES {
        bigint id PK
        bigint company_id FK
        bigint parent_id FK
        varchar name
    }

    BRANDS {
        bigint id PK
        bigint company_id FK
        varchar name
    }

    PRODUCTS {
        bigint id PK
        bigint company_id FK
        bigint category_id FK
        bigint brand_id FK
        varchar name
        text description
        varchar status
    }

    PRODUCT_VARIANTS {
        bigint id PK
        bigint company_id FK
        bigint product_id FK
        varchar sku
        varchar barcode
        json attributes
        decimal cost
        decimal price
        varchar status
    }

    UNITS {
        bigint id PK
        bigint company_id FK
        varchar code
        varchar name
        varchar type
    }

    UNIT_CONVERSIONS {
        bigint id PK
        bigint company_id FK
        bigint from_unit_id FK
        bigint to_unit_id FK
        decimal factor
    }

    LOCATIONS {
        bigint id PK
        bigint company_id FK
        bigint parent_id FK
        varchar code
        varchar name
        varchar type
        varchar status
    }

    STOCK_BALANCES {
        bigint id PK
        bigint company_id FK
        bigint variant_id FK
        bigint location_id FK
        decimal quantity
        decimal reserved_quantity
        datetime updated_at
    }

    STOCK_MOVEMENTS {
        bigint id PK
        bigint company_id FK
        bigint variant_id FK
        bigint location_id FK
        varchar type
        decimal quantity
        decimal unit_cost
        varchar reference_type
        bigint reference_id
        varchar reason
        bigint created_by FK
        datetime created_at
    }

    STOCK_MOVEMENT_LINES {
        bigint id PK
        bigint company_id FK
        bigint stock_movement_id FK
        bigint variant_id FK
        bigint location_id FK
        decimal quantity
        decimal unit_cost
    }

    SUPPLIERS {
        bigint id PK
        bigint company_id FK
        varchar name
        varchar tax_id
        varchar email
        varchar phone
        varchar status
    }

    PURCHASES {
        bigint id PK
        bigint company_id FK
        bigint supplier_id FK
        varchar document_number
        varchar status
        datetime ordered_at
        datetime received_at
        decimal total
    }

    PURCHASE_ITEMS {
        bigint id PK
        bigint company_id FK
        bigint purchase_id FK
        bigint variant_id FK
        decimal quantity
        decimal unit_cost
        decimal subtotal
    }

    TRANSFERS {
        bigint id PK
        bigint company_id FK
        bigint from_location_id FK
        bigint to_location_id FK
        varchar status
        bigint created_by FK
        datetime created_at
    }

    TRANSFER_ITEMS {
        bigint id PK
        bigint company_id FK
        bigint transfer_id FK
        bigint variant_id FK
        decimal quantity
    }

    PHYSICAL_COUNTS {
        bigint id PK
        bigint company_id FK
        bigint location_id FK
        varchar status
        datetime started_at
        datetime closed_at
    }

    PHYSICAL_COUNT_ITEMS {
        bigint id PK
        bigint company_id FK
        bigint physical_count_id FK
        bigint variant_id FK
        decimal expected_quantity
        decimal counted_quantity
        decimal difference
    }

    STOCK_ALERTS {
        bigint id PK
        bigint company_id FK
        bigint variant_id FK
        bigint location_id FK
        varchar type
        decimal threshold
        varchar status
    }

    AUDIT_LOGS {
        bigint id PK
        bigint company_id FK
        bigint user_id FK
        varchar action
        varchar entity_type
        bigint entity_id
        json before
        json after
        varchar ip
        datetime created_at
    }

    COMPANIES ||--o{ COMPANY_USER : "tiene"
    USERS ||--o{ COMPANY_USER : "pertenece"
    ROLES ||--o{ COMPANY_USER : "asigna"
    ROLES ||--o{ PERMISSION_ROLE : "otorga"
    PERMISSIONS ||--o{ PERMISSION_ROLE : "incluye"

    COMPANIES ||--o{ CATEGORIES : "define"
    COMPANIES ||--o{ BRANDS : "define"
    COMPANIES ||--o{ PRODUCTS : "posee"
    CATEGORIES ||--o{ PRODUCTS : "clasifica"
    BRANDS ||--o{ PRODUCTS : "identifica"
    PRODUCTS ||--o{ PRODUCT_VARIANTS : "contiene"
    CATEGORIES ||--o{ CATEGORIES : "subcategoría"

    COMPANIES ||--o{ UNITS : "define"
    UNITS ||--o{ UNIT_CONVERSIONS : "origen"
    COMPANIES ||--o{ LOCATIONS : "administra"
    LOCATIONS ||--o{ LOCATIONS : "ubicación padre"

    PRODUCT_VARIANTS ||--o{ STOCK_BALANCES : "saldo"
    LOCATIONS ||--o{ STOCK_BALANCES : "almacena"
    PRODUCT_VARIANTS ||--o{ STOCK_MOVEMENTS : "mueve"
    LOCATIONS ||--o{ STOCK_MOVEMENTS : "afecta"
    STOCK_MOVEMENTS ||--o{ STOCK_MOVEMENT_LINES : "detalla"
    PRODUCT_VARIANTS ||--o{ STOCK_MOVEMENT_LINES : "incluye"
    LOCATIONS ||--o{ STOCK_MOVEMENT_LINES : "ubica"

    COMPANIES ||--o{ SUPPLIERS : "registra"
    SUPPLIERS ||--o{ PURCHASES : "provee"
    PURCHASES ||--o{ PURCHASE_ITEMS : "contiene"
    PRODUCT_VARIANTS ||--o{ PURCHASE_ITEMS : "compra"

    COMPANIES ||--o{ TRANSFERS : "realiza"
    LOCATIONS ||--o{ TRANSFERS : "origen"
    LOCATIONS ||--o{ TRANSFERS : "destino"
    TRANSFERS ||--o{ TRANSFER_ITEMS : "contiene"
    PRODUCT_VARIANTS ||--o{ TRANSFER_ITEMS : "traslada"

    COMPANIES ||--o{ PHYSICAL_COUNTS : "programa"
    LOCATIONS ||--o{ PHYSICAL_COUNTS : "cuenta"
    PHYSICAL_COUNTS ||--o{ PHYSICAL_COUNT_ITEMS : "detalla"
    PRODUCT_VARIANTS ||--o{ PHYSICAL_COUNT_ITEMS : "cuenta"

    COMPANIES ||--o{ STOCK_ALERTS : "configura"
    PRODUCT_VARIANTS ||--o{ STOCK_ALERTS : "alerta"
    LOCATIONS ||--o{ STOCK_ALERTS : "ubica"

    COMPANIES ||--o{ AUDIT_LOGS : "registra"
    USERS ||--o{ AUDIT_LOGS : "ejecuta"
```

## 3. Clasificación de tablas

| Clasificación | Tablas | Observación |
| --- | --- | --- |
| Globales | `permissions`, `plans` (futuro) | Catálogos compartidos entre empresas |
| Raíz del tenant | `companies` | Define la empresa/tenant |
| Identidad global | `users` | Un usuario puede pertenecer a varias empresas (DD-01) |
| Dependientes del tenant | `categories`, `brands`, `products`, `product_variants`, `units`, `unit_conversions`, `locations`, `stock_balances`, `stock_movements`, `stock_movement_lines`, `suppliers`, `purchases`, `purchase_items`, `transfers`, `transfer_items`, `physical_counts`, `physical_count_items`, `stock_alerts`, `audit_logs` | `company_id` obligatorio |
| Pivotes | `company_user`, `permission_role` | Relaciones muchos a muchos |
| Asociativa | `stock_balances` | Variante + ubicación con saldo propio |

## 4. Reglas de integridad

| Regla | Implementación |
| --- | --- |
| Una existencia por variante y ubicación | `UNIQUE (company_id, variant_id, location_id)` |
| SKU único por empresa | `UNIQUE (company_id, sku)` |
| Sin stock negativo | `CHECK (quantity >= 0)` + validación transaccional |
| Movimientos inmutables | Sin `DELETE` físico; reversión con movimiento compensatorio |
| Referencias cruzadas prohibidas | Validación de `company_id` en entidades relacionadas |
| Borrado lógico | `deleted_at` en maestros y catálogo |
| Trazabilidad | `created_by` + `audit_logs` |

## 5. Tablas fuera del MVP

Las tablas de ventas (`sales`, `sale_items`) **no se crean en el MVP**. Las
salidas por venta se registran con `stock_movements.type = 'sale_exit'` y
`reference_type`/`reference_id`, según se apruebe DP-01. Las tablas de planes y
suscripciones se reservan para la fase comercial (DP-08).

## 6. Documentos relacionados

- `database/dictionaries/DICCIONARIO_DATOS.md` — detalle de columnas y tipos.
- `DOCUMENTACION/SDD/SDD-04_DISENO_BASE_DATOS.md` — estrategia multiempresa.
- `DOCUMENTACION/SDD/ADR/REGISTRO_DECISIONES_SPRINT_0.md` — DP/DD pendientes.
