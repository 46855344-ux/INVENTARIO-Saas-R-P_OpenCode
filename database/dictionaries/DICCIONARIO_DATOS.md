# Diccionario de datos — SaaS Inversiones R&P

**Proyecto:** SaaS Inversiones R&P
**Documento:** Diccionario de datos del modelo lógico (US-0002)
**Versión:** 1.0 — Sprint 0
**Fecha:** 2026-11-12
**Motor:** MySQL 8.0 · `utf8mb4` · InnoDB
**Estado:** Propuesta para revisión del Product Owner

---

## 1. Convenciones generales

| Elemento | Convención |
| --- | --- |
| Nombres | Inglés, `snake_case`; tablas en plural |
| PK | `id BIGINT UNSIGNED AUTO_INCREMENT` |
| Tenant | `company_id BIGINT UNSIGNED NOT NULL` con FK a `companies.id` |
| Fechas | `DATETIME` en UTC |
| Auditoría | `created_at`, `updated_at`; `deleted_at` para baja lógica |
| Dinero | `DECIMAL(14,4)` |
| Cantidades | `DECIMAL(14,3)` |
| Booleanos | `TINYINT(1)` |
| Textos | `VARCHAR(n)` o `TEXT` |
| JSON | `JSON` (atributos variables) |
| Estados | `VARCHAR(30)` con valores controlados por aplicación |
| Motor | InnoDB, `utf8mb4_unicode_ci` |

> **Nota de idioma:** el backlog usa `empresa_id`; el identificador interno
> aprobado por el estándar es `company_id`. La interfaz muestra «Empresa».

## 2. Matriz de ámbito

| Tabla | ¿Tiene `company_id`? | Justificación |
| --- | --- | --- |
| `companies` | No (es el tenant) | Raíz del aislamiento |
| `users` | No | Identidad global, se vincula por `company_user` |
| `permissions` | No | Catálogo global de permisos |
| `company_user` | Sí | Pertenencia y rol dentro de una empresa |
| `roles` | Sí (nullable en roles base) | Roles base globales + roles por empresa (DD-04) |
| `categories`, `brands`, `products`, `product_variants` | Sí | Catálogo por empresa |
| `units`, `unit_conversions`, `locations` | Sí | Configuración por empresa |
| `stock_balances`, `stock_movements`, `stock_movement_lines` | Sí | Inventario por empresa |
| `suppliers`, `purchases`, `purchase_items` | Sí | Compras por empresa |
| `transfers`, `transfer_items` | Sí | Traslados por empresa |
| `physical_counts`, `physical_count_items` | Sí | Conteos por empresa |
| `stock_alerts`, `audit_logs` | Sí | Métricas y trazabilidad por empresa |

## 3. Identidad y acceso

### 3.1 `companies`

| Columna | Tipo | Nulo | Default | Clave / índice | Descripción |
| --- | --- | --- | --- | --- | --- |
| `id` | BIGINT UNSIGNED | No | AI | PK | Identificador de la empresa |
| `name` | VARCHAR(150) | No | — | — | Nombre comercial |
| `slug` | VARCHAR(100) | No | — | UNIQUE | Identificador legible |
| `tax_id` | VARCHAR(30) | Sí | NULL | INDEX | Identificación fiscal |
| `status` | VARCHAR(20) | No | `active` | INDEX | `active`, `suspended`, `inactive` |
| `created_at` | DATETIME | Sí | NULL | — | Alta |
| `updated_at` | DATETIME | Sí | NULL | — | Última modificación |
| `deleted_at` | DATETIME | Sí | NULL | INDEX | Baja lógica |

### 3.2 `users`

| Columna | Tipo | Nulo | Default | Clave / índice | Descripción |
| --- | --- | --- | --- | --- | --- |
| `id` | BIGINT UNSIGNED | No | AI | PK | Identificador |
| `name` | VARCHAR(150) | No | — | — | Nombre completo |
| `email` | VARCHAR(190) | No | — | UNIQUE | Correo único global (DD-01) |
| `password` | VARCHAR(255) | No | — | — | Hash de contraseña |
| `status` | VARCHAR(20) | No | `active` | INDEX | `active`, `inactive`, `blocked` |
| `email_verified_at` | DATETIME | Sí | NULL | — | Verificación de correo |
| `remember_token` | VARCHAR(100) | Sí | NULL | — | Token de sesión |
| `created_at` / `updated_at` | DATETIME | Sí | NULL | — | Auditoría |
| `deleted_at` | DATETIME | Sí | NULL | INDEX | Baja lógica |

### 3.3 `company_user`

| Columna | Tipo | Nulo | Default | Clave / índice | Descripción |
| --- | --- | --- | --- | --- | --- |
| `id` | BIGINT UNSIGNED | No | AI | PK | Identificador |
| `company_id` | BIGINT UNSIGNED | No | — | FK → `companies.id`, INDEX | Empresa |
| `user_id` | BIGINT UNSIGNED | No | — | FK → `users.id` | Usuario |
| `role_id` | BIGINT UNSIGNED | Sí | NULL | FK → `roles.id` | Rol principal |
| `status` | VARCHAR(20) | No | `active` | INDEX | `active`, `invited`, `inactive` |
| `created_at` / `updated_at` | DATETIME | Sí | NULL | — | Auditoría |

Restricción: `UNIQUE (company_id, user_id)`.

### 3.4 `roles`

| Columna | Tipo | Nulo | Default | Clave / índice | Descripción |
| --- | --- | --- | --- | --- | --- |
| `id` | BIGINT UNSIGNED | No | AI | PK | Identificador |
| `company_id` | BIGINT UNSIGNED | Sí | NULL | FK → `companies.id` | `NULL` = rol base global |
| `code` | VARCHAR(50) | No | — | UNIQUE (`company_id`, `code`) | `owner`, `admin`, `operator`, `viewer` |
| `name` | VARCHAR(100) | No | — | — | Nombre visible en español |
| `created_at` / `updated_at` | DATETIME | Sí | NULL | — | Auditoría |

### 3.5 `permissions`

| Columna | Tipo | Nulo | Default | Clave / índice | Descripción |
| --- | --- | --- | --- | --- | --- |
| `id` | BIGINT UNSIGNED | No | AI | PK | Identificador |
| `code` | VARCHAR(100) | No | — | UNIQUE | Ej. `inventory.approve` |
| `name` | VARCHAR(150) | No | — | — | Descripción visible |
| `created_at` / `updated_at` | DATETIME | Sí | NULL | — | Auditoría |

### 3.6 `permission_role`

| Columna | Tipo | Nulo | Default | Clave / índice | Descripción |
| --- | --- | --- | --- | --- | --- |
| `role_id` | BIGINT UNSIGNED | No | — | PK compuesta, FK | Rol |
| `permission_id` | BIGINT UNSIGNED | No | — | PK compuesta, FK | Permiso |

## 4. Catálogo

### 4.1 `categories`

| Columna | Tipo | Nulo | Default | Clave / índice | Descripción |
| --- | --- | --- | --- | --- | --- |
| `id` | BIGINT UNSIGNED | No | AI | PK | Identificador |
| `company_id` | BIGINT UNSIGNED | No | — | FK, INDEX | Empresa |
| `parent_id` | BIGINT UNSIGNED | Sí | NULL | FK → `categories.id` | Jerarquía |
| `name` | VARCHAR(150) | No | — | UNIQUE (`company_id`, `parent_id`, `name`) | Nombre |
| `created_at` / `updated_at` | DATETIME | Sí | NULL | — | Auditoría |
| `deleted_at` | DATETIME | Sí | NULL | INDEX | Baja lógica |

### 4.2 `brands`

| Columna | Tipo | Nulo | Default | Clave / índice | Descripción |
| --- | --- | --- | --- | --- | --- |
| `id` | BIGINT UNSIGNED | No | AI | PK | Identificador |
| `company_id` | BIGINT UNSIGNED | No | — | FK, INDEX | Empresa |
| `name` | VARCHAR(150) | No | — | UNIQUE (`company_id`, `name`) | Nombre |
| `created_at` / `updated_at` | DATETIME | Sí | NULL | — | Auditoría |
| `deleted_at` | DATETIME | Sí | NULL | INDEX | Baja lógica |

### 4.3 `products`

| Columna | Tipo | Nulo | Default | Clave / índice | Descripción |
| --- | --- | --- | --- | --- | --- |
| `id` | BIGINT UNSIGNED | No | AI | PK | Identificador |
| `company_id` | BIGINT UNSIGNED | No | — | FK, INDEX | Empresa |
| `category_id` | BIGINT UNSIGNED | Sí | NULL | FK, INDEX | Categoría |
| `brand_id` | BIGINT UNSIGNED | Sí | NULL | FK, INDEX | Marca |
| `name` | VARCHAR(200) | No | — | INDEX (`company_id`, `name`) | Nombre |
| `description` | TEXT | Sí | NULL | — | Descripción |
| `status` | VARCHAR(20) | No | `active` | INDEX | `active`, `inactive` |
| `created_at` / `updated_at` | DATETIME | Sí | NULL | — | Auditoría |
| `deleted_at` | DATETIME | Sí | NULL | INDEX | Baja lógica |

### 4.4 `product_variants`

| Columna | Tipo | Nulo | Default | Clave / índice | Descripción |
| --- | --- | --- | --- | --- | --- |
| `id` | BIGINT UNSIGNED | No | AI | PK | Identificador |
| `company_id` | BIGINT UNSIGNED | No | — | FK, INDEX | Empresa |
| `product_id` | BIGINT UNSIGNED | No | — | FK, INDEX | Producto |
| `sku` | VARCHAR(80) | No | — | UNIQUE (`company_id`, `sku`) | Código interno |
| `barcode` | VARCHAR(80) | Sí | NULL | INDEX (`company_id`, `barcode`) | Código de barras |
| `attributes` | JSON | Sí | NULL | — | Talla, color, etc. |
| `cost` | DECIMAL(14,4) | No | `0.0000` | — | Costo actual |
| `price` | DECIMAL(14,4) | No | `0.0000` | — | Precio de venta |
| `min_stock` | DECIMAL(14,3) | No | `0.000` | — | Stock mínimo por defecto (DD-02) |
| `status` | VARCHAR(20) | No | `active` | INDEX | `active`, `inactive` |
| `created_at` / `updated_at` | DATETIME | Sí | NULL | — | Auditoría |
| `deleted_at` | DATETIME | Sí | NULL | INDEX | Baja lógica |

## 5. Unidades y ubicaciones

### 5.1 `units`

| Columna | Tipo | Nulo | Default | Clave / índice | Descripción |
| --- | --- | --- | --- | --- | --- |
| `id` | BIGINT UNSIGNED | No | AI | PK | Identificador |
| `company_id` | BIGINT UNSIGNED | No | — | FK, INDEX | Empresa |
| `code` | VARCHAR(20) | No | — | UNIQUE (`company_id`, `code`) | Ej. `UND`, `KG` |
| `name` | VARCHAR(100) | No | — | — | Nombre |
| `type` | VARCHAR(30) | No | — | — | `unit`, `weight`, `volume`, `length` |
| `created_at` / `updated_at` | DATETIME | Sí | NULL | — | Auditoría |

### 5.2 `unit_conversions`

| Columna | Tipo | Nulo | Default | Clave / índice | Descripción |
| --- | --- | --- | --- | --- | --- |
| `id` | BIGINT UNSIGNED | No | AI | PK | Identificador |
| `company_id` | BIGINT UNSIGNED | No | — | FK, INDEX | Empresa |
| `from_unit_id` | BIGINT UNSIGNED | No | — | FK, UNIQUE (`from`, `to`) | Unidad origen |
| `to_unit_id` | BIGINT UNSIGNED | No | — | FK | Unidad destino |
| `factor` | DECIMAL(18,6) | No | — | — | Factor de conversión > 0 |
| `created_at` / `updated_at` | DATETIME | Sí | NULL | — | Auditoría |

### 5.3 `locations`

| Columna | Tipo | Nulo | Default | Clave / índice | Descripción |
| --- | --- | --- | --- | --- | --- |
| `id` | BIGINT UNSIGNED | No | AI | PK | Identificador |
| `company_id` | BIGINT UNSIGNED | No | — | FK, INDEX | Empresa |
| `parent_id` | BIGINT UNSIGNED | Sí | NULL | FK → `locations.id` | Jerarquía |
| `code` | VARCHAR(30) | No | — | UNIQUE (`company_id`, `code`) | Código |
| `name` | VARCHAR(150) | No | — | — | Nombre visible |
| `type` | VARCHAR(30) | No | `warehouse` | INDEX | `store`, `warehouse`, `zone`, `shelf` |
| `status` | VARCHAR(20) | No | `active` | INDEX | `active`, `inactive` |
| `created_at` / `updated_at` | DATETIME | Sí | NULL | — | Auditoría |
| `deleted_at` | DATETIME | Sí | NULL | INDEX | Baja lógica |

## 6. Inventario

### 6.1 `stock_balances`

| Columna | Tipo | Nulo | Default | Clave / índice | Descripción |
| --- | --- | --- | --- | --- | --- |
| `id` | BIGINT UNSIGNED | No | AI | PK | Identificador |
| `company_id` | BIGINT UNSIGNED | No | — | FK, INDEX | Empresa |
| `variant_id` | BIGINT UNSIGNED | No | — | FK, UNIQUE (`company_id`, `variant_id`, `location_id`) | Variante |
| `location_id` | BIGINT UNSIGNED | No | — | FK | Ubicación |
| `quantity` | DECIMAL(14,3) | No | `0.000` | CHECK `>= 0` | Existencia disponible |
| `reserved_quantity` | DECIMAL(14,3) | No | `0.000` | CHECK `>= 0` | Reservado |
| `updated_at` | DATETIME | Sí | NULL | — | Última actualización |

### 6.2 `stock_movements`

| Columna | Tipo | Nulo | Default | Clave / índice | Descripción |
| --- | --- | --- | --- | --- | --- |
| `id` | BIGINT UNSIGNED | No | AI | PK | Identificador |
| `company_id` | BIGINT UNSIGNED | No | — | FK, INDEX (`company_id`, `variant_id`, `created_at`) | Empresa |
| `variant_id` | BIGINT UNSIGNED | No | — | FK | Variante |
| `location_id` | BIGINT UNSIGNED | No | — | FK | Ubicación (DD-03) |
| `type` | VARCHAR(30) | No | — | INDEX | `purchase_entry`, `sale_exit`, `return`, `adjustment`, `transfer_in`, `transfer_out`, `reversal` |
| `quantity` | DECIMAL(14,3) | No | — | — | Cantidad con signo o absoluta según tipo |
| `unit_cost` | DECIMAL(14,4) | Sí | NULL | — | Costo unitario |
| `reference_type` | VARCHAR(50) | Sí | NULL | INDEX (`reference_type`, `reference_id`) | `purchase`, `transfer`, `physical_count`, `sale` |
| `reference_id` | BIGINT UNSIGNED | Sí | NULL | — | ID del documento origen |
| `reason` | VARCHAR(255) | Sí | NULL | — | Motivo o justificación |
| `created_by` | BIGINT UNSIGNED | No | — | FK → `users.id` | Responsable |
| `created_at` | DATETIME | No | CURRENT_TIMESTAMP | INDEX | Fecha del movimiento |

> Los movimientos confirmados no se actualizan ni eliminan. Una corrección crea
> un movimiento `reversal` que referencia al original.

### 6.3 `stock_movement_lines`

| Columna | Tipo | Nulo | Default | Clave / índice | Descripción |
| --- | --- | --- | --- | --- | --- |
| `id` | BIGINT UNSIGNED | No | AI | PK | Identificador |
| `company_id` | BIGINT UNSIGNED | No | — | FK, INDEX | Empresa |
| `stock_movement_id` | BIGINT UNSIGNED | No | — | FK | Movimiento |
| `variant_id` | BIGINT UNSIGNED | No | — | FK | Variante |
| `location_id` | BIGINT UNSIGNED | No | — | FK | Ubicación |
| `quantity` | DECIMAL(14,3) | No | — | — | Cantidad |
| `unit_cost` | DECIMAL(14,4) | Sí | NULL | — | Costo unitario |

## 7. Compras y traslados

### 7.1 `suppliers`

| Columna | Tipo | Nulo | Default | Clave / índice | Descripción |
| --- | --- | --- | --- | --- | --- |
| `id` | BIGINT UNSIGNED | No | AI | PK | Identificador |
| `company_id` | BIGINT UNSIGNED | No | — | FK, INDEX | Empresa |
| `name` | VARCHAR(200) | No | — | INDEX (`company_id`, `name`) | Razón social |
| `tax_id` | VARCHAR(30) | Sí | NULL | INDEX | Identificación fiscal |
| `email` | VARCHAR(190) | Sí | NULL | — | Correo |
| `phone` | VARCHAR(40) | Sí | NULL | — | Teléfono |
| `status` | VARCHAR(20) | No | `active` | INDEX | `active`, `inactive` |
| `created_at` / `updated_at` | DATETIME | Sí | NULL | — | Auditoría |
| `deleted_at` | DATETIME | Sí | NULL | INDEX | Baja lógica |

### 7.2 `purchases`

| Columna | Tipo | Nulo | Default | Clave / índice | Descripción |
| --- | --- | --- | --- | --- | --- |
| `id` | BIGINT UNSIGNED | No | AI | PK | Identificador |
| `company_id` | BIGINT UNSIGNED | No | — | FK, INDEX | Empresa |
| `supplier_id` | BIGINT UNSIGNED | No | — | FK | Proveedor |
| `document_number` | VARCHAR(60) | No | — | UNIQUE (`company_id`, `supplier_id`, `document_number`) | Número de compra |
| `status` | VARCHAR(20) | No | `draft` | INDEX | `draft`, `ordered`, `received`, `cancelled` |
| `ordered_at` | DATETIME | Sí | NULL | — | Fecha de pedido |
| `received_at` | DATETIME | Sí | NULL | — | Fecha de recepción |
| `total` | DECIMAL(14,4) | No | `0.0000` | — | Total |
| `created_at` / `updated_at` | DATETIME | Sí | NULL | — | Auditoría |

### 7.3 `purchase_items`

| Columna | Tipo | Nulo | Default | Clave / índice | Descripción |
| --- | --- | --- | --- | --- | --- |
| `id` | BIGINT UNSIGNED | No | AI | PK | Identificador |
| `company_id` | BIGINT UNSIGNED | No | — | FK, INDEX | Empresa |
| `purchase_id` | BIGINT UNSIGNED | No | — | FK, UNIQUE (`purchase_id`, `variant_id`) | Compra |
| `variant_id` | BIGINT UNSIGNED | No | — | FK | Variante |
| `quantity` | DECIMAL(14,3) | No | — | — | Cantidad |
| `unit_cost` | DECIMAL(14,4) | No | — | — | Costo unitario |
| `subtotal` | DECIMAL(14,4) | No | — | — | `quantity * unit_cost` |

### 7.4 `transfers`

| Columna | Tipo | Nulo | Default | Clave / índice | Descripción |
| --- | --- | --- | --- | --- | --- |
| `id` | BIGINT UNSIGNED | No | AI | PK | Identificador |
| `company_id` | BIGINT UNSIGNED | No | — | FK, INDEX | Empresa |
| `from_location_id` | BIGINT UNSIGNED | No | — | FK | Ubicación origen |
| `to_location_id` | BIGINT UNSIGNED | No | — | FK | Ubicación destino |
| `status` | VARCHAR(20) | No | `draft` | INDEX | `draft`, `in_transit`, `completed`, `cancelled` |
| `created_by` | BIGINT UNSIGNED | No | — | FK → `users.id` | Responsable |
| `created_at` / `updated_at` | DATETIME | Sí | NULL | — | Auditoría |

Restricción: `from_location_id <> to_location_id`.

### 7.5 `transfer_items`

| Columna | Tipo | Nulo | Default | Clave / índice | Descripción |
| --- | --- | --- | --- | --- | --- |
| `id` | BIGINT UNSIGNED | No | AI | PK | Identificador |
| `company_id` | BIGINT UNSIGNED | No | — | FK, INDEX | Empresa |
| `transfer_id` | BIGINT UNSIGNED | No | — | FK, UNIQUE (`transfer_id`, `variant_id`) | Traslado |
| `variant_id` | BIGINT UNSIGNED | No | — | FK | Variante |
| `quantity` | DECIMAL(14,3) | No | — | — | Cantidad trasladada |

## 8. Conteos, alertas y auditoría

### 8.1 `physical_counts`

| Columna | Tipo | Nulo | Default | Clave / índice | Descripción |
| --- | --- | --- | --- | --- | --- |
| `id` | BIGINT UNSIGNED | No | AI | PK | Identificador |
| `company_id` | BIGINT UNSIGNED | No | — | FK, INDEX | Empresa |
| `location_id` | BIGINT UNSIGNED | No | — | FK | Ubicación contada |
| `status` | VARCHAR(20) | No | `open` | INDEX | `open`, `counting`, `pending_approval`, `closed`, `cancelled` |
| `started_at` | DATETIME | Sí | NULL | — | Inicio |
| `closed_at` | DATETIME | Sí | NULL | — | Cierre |
| `created_by` | BIGINT UNSIGNED | Sí | NULL | FK → `users.id` | Responsable |
| `created_at` / `updated_at` | DATETIME | Sí | NULL | — | Auditoría |

### 8.2 `physical_count_items`

| Columna | Tipo | Nulo | Default | Clave / índice | Descripción |
| --- | --- | --- | --- | --- | --- |
| `id` | BIGINT UNSIGNED | No | AI | PK | Identificador |
| `company_id` | BIGINT UNSIGNED | No | — | FK, INDEX | Empresa |
| `physical_count_id` | BIGINT UNSIGNED | No | — | FK, UNIQUE (`physical_count_id`, `variant_id`) | Conteo |
| `variant_id` | BIGINT UNSIGNED | No | — | FK | Variante |
| `expected_quantity` | DECIMAL(14,3) | No | `0.000` | — | Saldo del sistema |
| `counted_quantity` | DECIMAL(14,3) | Sí | NULL | — | Cantidad contada |
| `difference` | DECIMAL(14,3) | Sí | NULL | — | `counted - expected` |
| `approved_by` | BIGINT UNSIGNED | Sí | NULL | FK → `users.id` | Aprobador |
| `approved_at` | DATETIME | Sí | NULL | — | Fecha de aprobación |

### 8.3 `stock_alerts`

| Columna | Tipo | Nulo | Default | Clave / índice | Descripción |
| --- | --- | --- | --- | --- | --- |
| `id` | BIGINT UNSIGNED | No | AI | PK | Identificador |
| `company_id` | BIGINT UNSIGNED | No | — | FK, INDEX | Empresa |
| `variant_id` | BIGINT UNSIGNED | No | — | FK, INDEX | Variante |
| `location_id` | BIGINT UNSIGNED | Sí | NULL | FK | Ubicación |
| `type` | VARCHAR(30) | No | — | INDEX | `low_stock`, `out_of_stock`, `overstock` |
| `threshold` | DECIMAL(14,3) | Sí | NULL | — | Umbral configurado |
| `status` | VARCHAR(20) | No | `open` | INDEX | `open`, `acknowledged`, `resolved` |
| `created_at` / `updated_at` | DATETIME | Sí | NULL | — | Auditoría |

### 8.4 `audit_logs`

| Columna | Tipo | Nulo | Default | Clave / índice | Descripción |
| --- | --- | --- | --- | --- | --- |
| `id` | BIGINT UNSIGNED | No | AI | PK | Identificador |
| `company_id` | BIGINT UNSIGNED | No | — | FK, INDEX (`company_id`, `entity_type`, `entity_id`) | Empresa |
| `user_id` | BIGINT UNSIGNED | Sí | NULL | FK → `users.id` | Actor |
| `action` | VARCHAR(30) | No | — | INDEX | `created`, `updated`, `deleted`, `approved`, `reversed` |
| `entity_type` | VARCHAR(80) | No | — | — | Nombre lógico de la entidad |
| `entity_id` | BIGINT UNSIGNED | No | — | — | ID afectado |
| `before` | JSON | Sí | NULL | — | Estado anterior |
| `after` | JSON | Sí | NULL | — | Estado posterior |
| `ip` | VARCHAR(45) | Sí | NULL | — | IPv4/IPv6 |
| `created_at` | DATETIME | No | CURRENT_TIMESTAMP | INDEX | Fecha |

## 9. Reglas de negocio en base de datos

| Código | Regla | Control |
| --- | --- | --- |
| RN-01 | Identificación única de variantes | `UNIQUE (company_id, sku)` |
| RN-02 | Variante con identificación propia | `sku NOT NULL` |
| RN-03 | Un saldo por variante y ubicación | `UNIQUE (company_id, variant_id, location_id)` |
| RN-04 | Conversiones válidas | `factor > 0`, unidades existentes |
| RN-05 | Cantidades decimales | `DECIMAL(14,3)` |
| RN-06 | Sin salidas mayores al stock | Transacción + `CHECK (quantity >= 0)` |
| RN-07 | Traslados sin alterar stock global | Movimientos `transfer_out` + `transfer_in` |
| RN-08 | Responsable de cada movimiento | `created_by NOT NULL` |
| RN-09 | Movimientos confirmados inmutables | Reversión con movimiento compensatorio |
| RN-10 | Ajustes autorizados | `reason` + permiso `inventory.approve` |
| RN-11 | Conteos trazables | `physical_count_items` + auditoría |
| RN-12 | Aislamiento empresarial | `company_id` + pruebas de acceso cruzado |

## 10. Pendientes que condicionan tipos y reglas

| Decisión | Efecto en el diccionario |
| --- | --- |
| DD-01 | Unicidad de `users.email` (propuesta: global) |
| DD-02 | `min_stock` en variante, ubicación o ambos (propuesta: variante + ubicación) |
| DD-03 | `location_id` obligatorio en movimientos (propuesta: obligatorio) |
| DD-04 | `roles.company_id` nullable para roles base globales (propuesta) |
| DD-05 | `deleted_at` y `audit_logs` (propuesta: ambos) |
| DP-02 | Método de valorización (propuesta: costo promedio ponderado) |
| DP-03 | Precisión y redondeo de unidades (propuesta: 3 decimales, `HALF_UP`) |

## 11. Documentos relacionados

- `database/diagrams/DER_LOGICO.md`
- `DOCUMENTACION/SDD/SDD-04_DISENO_BASE_DATOS.md`
- `DOCUMENTACION/SDD/SDD-05_DISENO_API.md`
- `DOCUMENTACION/SDD/ADR/REGISTRO_DECISIONES_SPRINT_0.md`
