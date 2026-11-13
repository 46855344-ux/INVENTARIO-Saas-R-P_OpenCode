# Estándar de programación

**Proyecto:** SaaS Inversiones R&P
**Stack:** Laravel · React · MySQL · API REST/JSON
**Alcance:** código, pruebas, migraciones, configuración e infraestructura de todo el repositorio.
**Estado:** vigente
**Versión:** 1.0
**Última revisión:** 2026-11-12
**Responsable:** equipo de desarrollo R&P

---

## 1. Cómo se usa este documento

Estas reglas son **obligatorias en cada cambio**. Aplican a código nuevo y a código que se modifique.

Orden de prelación:

1. Un **ADR** aprobado en `DOCUMENTACION/SDD/`.
2. Una **decisión aprobada del backlog** (criterio de aceptación de la historia).
3. Este estándar.

Si una decisión de mayor jerarquía contradice una regla de aquí, la decisión manda y **este documento se actualiza en el mismo cambio**. Una desviación sin ADR se rechaza en la revisión.

### Reglas no negociables

| # | Regla |
| - | ----- |
| 1 | Toda consulta de negocio filtra por la empresa activa; `company_id` lo asigna el servidor. |
| 2 | Existencia y movimiento se escriben en la misma transacción. |
| 3 | Toda operación protegida se autentica y se autoriza en Laravel. |
| 4 | Secretos, `.env` reales y volcados de datos nunca entran al repositorio ni a los logs. |
| 5 | Ninguna función supera 150 líneas ni mezcla validación, permiso, persistencia y respuesta. |
| 6 | Todo cambio de comportamiento llega con la prueba que lo fija. |
| 7 | Los movimientos confirmados no se editan ni se borran: se compensan. |
| 8 | El cliente recibe un error con formato único, sin pila, SQL ni datos de otra empresa. |

---

## 2. Principios rectores

- **La regla de negocio manda sobre la comodidad del framework.** El controlador orquesta; el dominio decide.
- **Lo que no está probado no está terminado.** Un criterio de aceptación sin prueba automatizada está abierto.
- **El tenant es una frontera de seguridad, no un filtro opcional.** Se aplica por defecto, no por memoria del desarrollador.
- **Los errores son parte del contrato.** Un error previsto tiene código, mensaje y HTTP definidos.
- **Los cambios son pequeños y reversibles.** Un PR resuelve un problema; si necesita un párrafo para explicarse, se divide.
- **La deuda se registra.** Un atajo consciente se anota en el PR y en el backlog; no se esconde.

---

## 3. Normativas ISO

### 3.1 ISO/IEC 25001 y 25010 — planificación y calidad del producto

La calidad se planifica junto con la función: **cada entrega tiene un criterio de aceptación comprobable y la prueba que lo demuestra**. No se acepta una historia sin criterio verificable ni un criterio sin evidencia.

Características cuidadas en el código (ISO/IEC 25010):

| Característica | Cómo se cumple en este proyecto | Evidencia |
| -------------- | ------------------------------- | --------- |
| Adecuación funcional | El comportamiento coincide con la historia y sus criterios de aceptación | Pruebas de aceptación (Pest/Vitest/Cypress) |
| Fiabilidad | Las escrituras relacionadas ocurren en una sola transacción | Prueba de integración con fallo simulado |
| Seguridad | Autenticación, autorización y aislamiento por empresa | Pruebas de 401/403/404 cruzado |
| Mantenibilidad | Unidades pequeñas, nombres claros, una responsabilidad por clase | Revisión de PR + análisis estático |
| Compatibilidad | El frontend depende solo del contrato publicado de la API | Pruebas de servicios con contrato tipado |
| Usabilidad | Mensajes en español, errores accionables, estados de carga y vacío | Revisión de UI + pruebas E2E |
| Eficiencia | Paginación obligatoria, sin N+1, índices en filtros frecuentes | Revisión + pruebas de consultas |

### 3.2 ISO/IEC 27001 — seguridad de la información

Estas reglas cubren los **controles técnicos aplicables** del estándar; los controles organizativos (políticas, roles, continuidad) se documentan en `DOCUMENTACION/SEGURIDAD/`.

- Cada operación protegida se **autentica y se autoriza en Laravel** (Policy o Gate). Ocultar un botón en React no autoriza nada.
- Los datos de una empresa se leen y escriben **solo dentro de su ámbito**, incluso cuando el identificador de la ruta pertenece a otra empresa.
- **Secretos**: contraseñas, tokens, claves, `.env` reales y respaldos quedan fuera del repositorio y de los logs. El repositorio guarda únicamente `.env.example` sin valores secretos.
- **Contraseñas**: se almacenan con el hash por defecto de Laravel (bcrypt o Argon2), nunca con MD5/SHA1 ni en texto plano.
- **Sesiones y tokens**: se invalidan al cerrar sesión y al cambiar contraseña; los tokens tienen expiración y alcance mínimo.
- **Fuerza bruta**: el login y los endpoints sensibles tienen límite de intentos (`RateLimiter`).
- **Trazabilidad**: los cambios sensibles (sesión, permisos, usuarios, ajustes de inventario y movimientos confirmados) quedan en una bitácora append-only.
- **Higiene de errores**: el cliente recibe mensaje claro y código HTTP correcto, sin pila, SQL, rutas internas ni datos de otra empresa.
- **Dependencias**: se revisan vulnerabilidades conocidas (`composer audit`, `npm audit`) y se actualizan en un cambio propio.
- **Transporte**: TLS obligatorio en producción; cookies de sesión `Secure`, `HttpOnly` y `SameSite`.

### 3.3 ISO 19119 — servicios de información geográfica

Aplica **solo si el módulo publica información geográfica** (ubicaciones, mapas, rutas, coordenadas). En ese caso el servicio expone un contrato estable, metadatos e interoperabilidad acordes a la norma.

Mientras el requisito no pida datos geográficos, los módulos de identidad, catálogo e inventario usan el **modelo relacional del dominio** y esta sección se marca como **no aplicable** en el ADR del módulo.

### 3.4 ISO/IEC/IEEE 29119 — pruebas de software

- El comportamiento se especifica y se verifica: cada prueba documenta **escenario, dato y resultado esperado**.
- **Niveles y ubicación:**

| Nivel | Herramienta | Ubicación |
| ----- | ----------- | --------- |
| Unitaria | Pest / PHPUnit | `backend/tests/Unit/` |
| Integración (API, BD) | Pest / PHPUnit | `backend/tests/Feature/` |
| Unitaria / componentes | Vitest + Testing Library | `frontend/tests/` |
| E2E | Cypress | `tests/e2e/` |

- Toda historia que toque datos de empresa cubre **cinco resultados**: caso válido, dato inválido, usuario no autenticado, usuario sin permiso y acceso desde otra empresa (ver §8.2).
- Los reportes de ejecución se archivan en `tests/reports/` (no se versionan).

---

## 4. Convenciones de nombres

### 4.1 Idioma

- **Idioma:** la interfaz, los mensajes de validación visibles, los correos y los manuales de usuario van en **español**. Todo lo interno —código, tablas, columnas, rutas, archivos, variables y nombres de pruebas— va en **inglés**.
- **Equivalencia de dominio:** la «empresa» que ve el usuario es `Company` en código, `companies` en la base de datos y `company_id` como clave foránea. La interfaz dice «empresa»; el código nunca usa `empresa_id`, `Empresa` ni `tenant_id`.
- **Documentos de origen:** los canvases del backlog (`DOCUMENTACION/DOCS_origen/`) usan nombres en español (`empresa_id`, `producto`, `existencia`). Son material de planificación: al implementar migraciones, modelos y rutas, el nombre oficial es el inglés que fija este estándar (ver Anexo A). Cambiar esa decisión requiere un ADR.

### 4.2 Laravel y PHP (PSR-12, PSR-4)

- Clases en `PascalCase`: `ProductController`, `StoreProductRequest`, `ProductPolicy`, `RegisterStockEntry`.
- Métodos, argumentos y variables en `camelCase`.
- Constantes en `UPPER_SNAKE_CASE`; enums en `PascalCase` con casos en `PascalCase`.
- Tablas en `snake_case` plural: `products`, `stock_movements`, `audit_logs`.
- Columnas en `snake_case`: `company_id`, `created_at`, `quantity_after`.
- Rutas de API en plural y minúsculas: `/api/products`, `/api/stock-movements`.
- Nombres de archivo iguales al nombre de la clase (`ProductController.php`).
- El controlador recibe la petición, autoriza y devuelve la respuesta. La regla de negocio vive en `app/Services` o en una acción de un solo caso de uso (`app/Actions`).
- Nombres de clase por intención: `RegisterStockMovement`, no `StockService2` ni `Helper`.

| Elemento | Convención | Ejemplo |
| -------- | ---------- | ------- |
| Controlador | `{Recurso}Controller` | `ProductController` |
| Form Request | `{Acción}{Recurso}Request` | `StoreProductRequest` |
| Policy | `{Modelo}Policy` | `ProductPolicy` |
| Acción | Verbo + sustantivo | `RegisterStockMovement` |
| Evento | `{Modelo}{Participio}` | `StockMovementConfirmed` |
| Job | Verbo + sustantivo + `Job` | `SendLowStockAlertJob` |
| Migración | `create_{tabla}_table`, `add_{columna}_to_{tabla}_table` | `create_stock_movements_table` |
| Seeder / Factory | `{Modelo}Seeder` / `{Modelo}Factory` | `ProductSeeder` |

### 4.3 React

- Un **componente funcional por archivo**, archivo en `PascalCase`: `ProductTable.tsx`.
- Hooks con prefijo `use` y `camelCase`: `useProductFilters`, `useStockMovements`.
- Features en las carpetas de dominio: `auth`, `products`, `inventory`, `movements`, `suppliers`, `metrics`, `alerts`.
- Props tipadas como `{Componente}Props`. Banderas con prefijo `is`, `has` o `can`. Manejadores con prefijo `on`.
- El acceso HTTP vive en `services/`. El componente presenta datos y emite acciones; no arma URLs ni interpreta códigos de error.
- Sin lógica de negocio duplicada: si la regla existe en Laravel, el frontend la muestra, no la recalcula como fuente de verdad.

```ts
// features/products/types.ts
export interface Product {
  id: number;
  name: string;
  isActive: boolean;
  stock: number;
}

export interface ProductTableProps {
  products: Product[];
  isLoading: boolean;
  onDeactivate: (id: number) => void;
}
```

### 4.4 MySQL

- Motor **InnoDB**, juego de caracteres `utf8mb4`, collation `utf8mb4_unicode_ci`.
- Clave primaria `id`, entera, sin signo (`BIGINT UNSIGNED`).
- Cada tabla de negocio del tenant incluye `company_id` con clave foránea e índice.
- La unicidad de negocio por empresa usa **clave única compuesta** que empieza por `company_id` (ej. `uq_products_company_sku`).
- Importes y cantidades en `decimal`; **nunca** `float` ni `double`.
- Claves foráneas y columnas usadas en filtros u ordenamientos tienen índice.
- Baja lógica (`deleted_at`) solo cuando la historia lo pida; los movimientos confirmados **no se borran**.
- El esquema oficial vive en `backend/database/migrations/`. La carpeta `database/` de la raíz guarda diagramas, diccionario y scripts auxiliares, nunca el esquema fuente.

### 4.5 API

- Recursos REST en JSON, sustantivos en plural: `/api/products`, `/api/stock-movements`.
- Verbos HTTP con su semántica: `GET` lectura, `POST` alta, `PUT/PATCH` actualización, `DELETE` baja.
- La entrada se valida con **Form Request**. El permiso se resuelve con **Policy o Gate**.
- `company_id` se toma del **usuario autenticado**. El cuerpo de la petición no selecciona la empresa y, si lo intenta, se ignora o se rechaza con 422.
- Las respuestas usan **API Resources**; nunca se devuelve un modelo Eloquent crudo.
- Listados **siempre paginados** (`?page=`, `?per_page=` con máximo 100). Filtros y ordenamientos se validan contra una lista blanca.
- Fechas en ISO 8601 (`2026-11-12T14:30:00Z`); importes como string decimal con punto, no como número flotante.
- Si el contrato se publica fuera del equipo, se versiona en la ruta (`/api/v1/...`).

### 4.6 Pruebas

- Nombres descriptivos en tercera persona y en inglés técnico: `it('rejects an exit when stock is insufficient')`.
- Un `describe` por unidad o endpoint; un `it` por comportamiento.
- Factories para datos; **nunca** datos reales de producción en pruebas.
- Archivos espejo del código cuando aplique: `tests/Feature/StockMovementTest.php`.

### 4.7 Git

- Ramas: `feat/`, `fix/`, `chore/`, `docs/`, `test/`, `refactor/` + descripción corta en kebab-case (`feat/stock-exit-validation`).
- Commits en **Conventional Commits**: `tipo(alcance): descripción en imperativo`.
  - `feat(inventory): valida existencia antes de confirmar salida`
  - `fix(auth): rechaza token expirado en endpoints de tenant`
- Un commit = un cambio coherente que compila y pasa pruebas.
- El PR enlaza la historia, describe contexto, cambios, pruebas ejecutadas y riesgo. No se mezclan refactors masivos con cambios funcionales.

---

## 5. SOLID

| Principio | Aplicación concreta | Antipatrón prohibido |
| --------- | ------------------- | -------------------- |
| **S** — responsabilidad única | Una clase o función cambia por un solo motivo. El controlador no calcula stock ni resuelve el catálogo de otra empresa. | `ProductController` con validación, cálculo y envío de correo |
| **O** — abierto/cerrado | Un caso nuevo (otro tipo de movimiento, otra política de alerta) entra como clase o estrategia nueva y deja intactos los casos aceptados. | `switch` creciente dentro del servicio central |
| **L** — sustitución | Quien implementa una interfaz cumple sus precondiciones y su resultado; el consumidor sigue funcionando con cualquier implementación. | Implementación que lanza excepciones no declaradas o devuelve `null` donde se espera colección |
| **I** — segregación | Interfaces pequeñas y reales: puerta de persistencia, reloj, auditor. | Una interfaz que agrupa todo el módulo |
| **D** — inversión de dependencias | La regla de negocio depende de contratos; el contenedor de Laravel inyecta la implementación concreta. | `new PDO(...)` o `DB::` dentro del dominio |

```php
interface StockLedger
{
    public function lockForUpdate(int $stockId): Stock;
    public function append(StockMovement $movement): void;
}
```

El dominio depende de `StockLedger`; la implementación Eloquent se enlaza en un `ServiceProvider`. La prueba puede usar un doble sin base de datos.

---

## 6. Reglas propias del equipo

### 6.1 Enfoque BDD

- Cada criterio de aceptación se expresa como escenario observable: **contexto, acción y resultado comprobable**.
- El nombre de la prueba describe ese comportamiento; la aserción comprueba la respuesta, el estado guardado o la interfaz. El detalle de implementación queda fuera de la aserción.

```gherkin
Escenario: salida mayor a la existencia disponible
  Dado un producto con existencia de 5 unidades en la empresa A
  Y un usuario con permiso de inventario en la empresa A
  Cuando registra una salida de 8 unidades
  Entonces recibe el error INSUFFICIENT_STOCK con estado 409
  Y la existencia del producto sigue siendo 5
```

| Escenario | Nivel | Archivo |
| --------- | ----- | ------- |
| Regla de cálculo de existencia | Unitaria | `backend/tests/Unit/` |
| Endpoint con transacción y permisos | Integración | `backend/tests/Feature/` |
| Flujo de registro de salida en pantalla | E2E | `tests/e2e/stock-exit.cy.ts` |

### 6.2 Tamaño y complejidad de las funciones

- Ninguna función supera **150 líneas**.
- Complejidad ciclomática **≤ 10**; anidamiento **≤ 3 niveles**; parámetros **≤ 4** (si hay más, se agrupa en un DTO).
- Validación, permiso, persistencia y respuesta HTTP viven en unidades separadas aunque la función aún no llegue a 150 líneas.
- Una función que necesita comentarios para marcar fases se divide en funciones con nombre.
- Los comentarios explican **por qué**, no **qué**.

### 6.3 Arquitectura

```
React (features) → services/ → API REST/JSON → Laravel
                                               ├── Form Request (validación)
                                               ├── Policy / Gate (permiso)
                                               ├── Action / Service (regla de negocio)
                                               └── Eloquent + transacción → MySQL
```

- React envía y recibe datos por la API REST/JSON; no accede a la base de datos ni conoce credenciales.
- Laravel concentra validaciones, permisos, transacciones y reglas de inventario.
- Varias escrituras que forman una sola operación corren dentro de una transacción de base de datos.
- El frontend consume el contrato publicado; un cambio incompatible se coordina y se versiona.

### 6.4 Multiempresa

- Toda consulta y todo alta de datos de empresa filtran por la **empresa activa**.
- `company_id` se asigna en el servidor a partir de la sesión; nunca se confía en el cuerpo ni en el query string.
- Categoría, marca, producto, variante, depósito y demás relaciones se resuelven dentro de la **misma empresa**; no se puede asociar un producto de A a un movimiento de B.
- El acceso a un recurso de otra empresa responde **404**, no 403, para no revelar su existencia.
- Cada endpoint con datos de tenant incluye una **prueba de aislamiento** (§8.2).
- Se permite un `Global Scope` por empresa como red de seguridad, pero **no reemplaza** el filtro explícito ni la validación de pertenencia. Su desactivación (`withoutGlobalScope`) requiere justificación en el PR.

### 6.5 Integridad de inventario

- Una salida confirmada deja la existencia en **cero o en un valor positivo**; nunca negativa.
- Un movimiento ya confirmado se corrige con un **movimiento compensatorio**, no con un `UPDATE` ni un `DELETE`.
- La existencia y el movimiento se escriben en la **misma transacción**, con bloqueo de la fila (`lockForUpdate`) para evitar carreras.
- La operación registra el saldo resultante (`quantity_after`) y quién la ejecutó, para auditoría.
- Los ajustes manuales requieren motivo y permiso específico.

### 6.6 Modelos, consultas y datos

- Cada modelo declara `$fillable` o `$guarded`; no se usa `$guarded = []`.
- Las consultas usan Eloquent o **parámetros enlazados**. Se prohíbe interpolar variables en SQL.
- Los tipos se declaran con `casts` (`decimal:4`, `datetime`, `boolean`) y se documentan en el diccionario de datos.
- Se evita `SELECT *` en listados grandes; se seleccionan las columnas necesarias.
- Operaciones masivas se hacen por lotes (`chunkById`), nunca cargando toda la tabla en memoria.

### 6.7 Dinero, cantidades y fechas

| Dato | Tipo en MySQL | Tratamiento |
| ---- | ------------- | ----------- |
| Importe monetario | `decimal(15,2)` (o `decimal(18,4)` si el ADR lo fija) | Se opera como string decimal con `bcmath`; nunca `float` |
| Cantidad de inventario | `decimal(15,4)` | Igual que importes |
| Moneda | `char(3)` ISO 4217 | `ARS`, `USD` |
| Fecha y hora | `timestamp` / `datetime` | Se guarda en UTC y se presenta en la zona del usuario |

- El redondeo se define una sola vez (por defecto, medio hacia arriba) y se aplica en el dominio.
- No se comparan importes con `==` sobre flotantes; se usa `bccomp`.
- `APP_TIMEZONE=UTC`; la zona de presentación se configura aparte (`APP_DISPLAY_TIMEZONE`).

### 6.8 Errores y registro

**Formato único de error:**

```json
{
  "error": {
    "code": "INSUFFICIENT_STOCK",
    "message": "La existencia disponible no alcanza para confirmar la salida.",
    "details": { "stock_id": 42 },
    "trace_id": "01JB8Z3K5Q7YV9X2M4N6P8R0T1"
  }
}
```

| Situación | HTTP | `code` |
| --------- | ---- | ------ |
| Datos inválidos | 422 | `VALIDATION_ERROR` (con `details.fields`) |
| Sin autenticación o token vencido | 401 | `UNAUTHENTICATED` |
| Sin permiso sobre el recurso | 403 | `FORBIDDEN` |
| Recurso inexistente **o de otra empresa** | 404 | `NOT_FOUND` |
| Conflicto de estado / existencia insuficiente | 409 | `CONFLICT` / `INSUFFICIENT_STOCK` |
| Demasiados intentos | 429 | `RATE_LIMITED` |
| Error inesperado | 500 | `INTERNAL_ERROR` |

- El `trace_id` se genera en el servidor y aparece también en el log, para correlacionar sin exponer detalles.
- Las excepciones de dominio se mapean a su código en un único `Handler`; no se arman respuestas de error a mano en cada controlador.
- Una excepción capturada se **registra y se convierte en error de dominio, o se relanza**. Nunca se ignora en silencio.
- Los logs omiten contraseñas, tokens, cookies y cuerpos con secretos. Se registran identificadores, no payloads completos.

### 6.9 Secretos y configuración

- Credenciales, claves y volcados de datos reales se quedan en el entorno local o en el almacén de secretos del despliegue.
- Toda variable nueva se declara en `.env.example` **sin valor sensible** y se documenta en el README.
- La configuración se lee con `config()` en el código de aplicación; `env()` solo dentro de `config/`.
- Los respaldos se cifran y su acceso se restringe; nunca se versionan.

### 6.10 Rendimiento

- Las listas se paginan y se evita el **N+1** (`with()` / `load()`); la prueba o el PR lo evidencian.
- Los filtros y ordenamientos usados con frecuencia tienen índice.
- Las operaciones costosas (reportes, exportaciones) se ejecutan en cola y notifican al usuario.
- Se mide antes de optimizar: una optimización sin medición se justifica en el PR.

### 6.11 Estilo del cambio

- El código versionado está en uso: no se deja código muerto, comentado ni ramas abandonadas.
- Las dependencias nuevas quedan declaradas en el manifiesto y en el **lockfile** (`composer.lock`, `package-lock.json`).
- Un cambio de comportamiento llega con la prueba que lo fija; un cambio de esquema llega con su migración reversible.
- Las migraciones aplicadas no se editan: se corrige con una migración nueva.
- Un cambio que rompe el contrato de la API se anuncia y se versiona.

---

## 7. Forma esperada

### 7.1 Consulta acotada al tenant

```php
$product = Product::query()
    ->where('company_id', $request->user()->company_id)
    ->findOrFail($id);
```

La empresa sale del usuario autenticado. El identificador de la ruta **solo localiza un registro dentro de ese ámbito**; nunca amplía el alcance.

### 7.2 Escritura transaccional de inventario

```php
return DB::transaction(function () use ($movement): StockMovement {
    $stock = Stock::query()
        ->where('company_id', $movement->company_id)
        ->whereKey($movement->stock_id)
        ->lockForUpdate()
        ->firstOrFail();

    $newQuantity = $movement->type === MovementType::Exit
        ? bcsub($stock->quantity, $movement->quantity, 4)
        : bcadd($stock->quantity, $movement->quantity, 4);

    if (bccomp($newQuantity, '0', 4) < 0) {
        throw new InsufficientStockException();
    }

    $stock->quantity = $newQuantity;
    $stock->save();

    $movement->quantity_after = $newQuantity;
    $movement->save();

    return $movement;
});
```

El descuento y el movimiento se confirman juntos. Si la existencia no alcanza, la transacción termina **sin cambios**.

### 7.3 Componente que presenta y delega

```tsx
// features/products/components/ProductTable.tsx
import type { ProductTableProps } from '../types';

export function ProductTable({ products, isLoading, onDeactivate }: ProductTableProps) {
  if (isLoading) {
    return <p role="status">Cargando productos…</p>;
  }

  if (products.length === 0) {
    return <p>No hay productos registrados en esta empresa.</p>;
  }

  return (
    <table>
      <thead>
        <tr>
          <th scope="col">Producto</th>
          <th scope="col">Existencia</th>
          <th scope="col">Acciones</th>
        </tr>
      </thead>
      <tbody>
        {products.map((product) => (
          <tr key={product.id}>
            <td>{product.name}</td>
            <td>{product.stock}</td>
            <td>
              <button type="button" onClick={() => onDeactivate(product.id)}>
                Dar de baja
              </button>
            </td>
          </tr>
        ))}
      </tbody>
    </table>
  );
}
```

El componente recibe datos ya autorizados por la API y comunica la acción. La petición HTTP vive en el servicio de la feature.

### 7.4 Esquema con índices y claves foráneas

```sql
CREATE TABLE stock_movements (
    id             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    company_id     BIGINT UNSIGNED NOT NULL,
    stock_id       BIGINT UNSIGNED NOT NULL,
    type           ENUM('entry', 'exit', 'adjustment') NOT NULL,
    quantity       DECIMAL(15,4) NOT NULL,
    quantity_after DECIMAL(15,4) NOT NULL,
    reason         VARCHAR(255) NULL,
    created_by     BIGINT UNSIGNED NOT NULL,
    created_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_movements_company_created (company_id, created_at),
    KEY idx_movements_stock (stock_id),
    CONSTRAINT fk_movements_company FOREIGN KEY (company_id) REFERENCES companies (id),
    CONSTRAINT fk_movements_stock FOREIGN KEY (stock_id) REFERENCES stocks (id)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;
```

---

## 8. Pruebas y evidencia

### 8.1 Reglas generales

- Cada prueba es **independiente y determinista**: no depende del orden, de la hora real ni de datos externos. Reloj, correo y colas se simulan.
- Se usa `RefreshDatabase` y transacciones; ninguna prueba deja residuos.
- Se prueba el comportamiento observable, no el método privado.
- Una prueba que falla de forma intermitente se corrige o se elimina con justificación; no se reintenta hasta que pase.

### 8.2 Matriz obligatoria para endpoints de tenant

| # | Caso | Resultado esperado |
| - | ---- | ------------------ |
| 1 | Datos válidos, usuario autorizado | 2xx y datos persistidos en su empresa |
| 2 | Dato inválido (campo faltante, tipo, rango) | 422, sin escrituras |
| 3 | Usuario no autenticado | 401 |
| 4 | Usuario autenticado sin permiso | 403 |
| 5 | Usuario de otra empresa con el ID del recurso | 404, sin filtrar datos ni existencia |

```php
it('rejects an exit when stock is insufficient', function () {
    $company = Company::factory()->create();
    $user = User::factory()->for($company)->create();
    $stock = Stock::factory()->for($company)->create(['quantity' => '5.0000']);

    $this->actingAs($user)
        ->postJson('/api/stock-movements', [
            'stock_id' => $stock->id,
            'type' => 'exit',
            'quantity' => '8.0000',
        ])
        ->assertStatus(409)
        ->assertJsonPath('error.code', 'INSUFFICIENT_STOCK');

    expect($stock->fresh()->quantity)->toBe('5.0000');
});

it('hides resources that belong to another company', function () {
    $ownCompany = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $user = User::factory()->for($ownCompany)->create();
    $product = Product::factory()->for($otherCompany)->create();

    $this->actingAs($user)
        ->getJson("/api/products/{$product->id}")
        ->assertNotFound();
});
```

### 8.3 Umbrales mínimos de cobertura

| Ámbito | Umbral |
| ------ | ------ |
| `app/Actions`, `app/Services`, `app/Policies` | 90 % de líneas |
| Backend global | 70 % de líneas |
| Frontend (servicios y hooks) | 70 % de líneas |
| Flujos críticos (login, alta de movimiento, aislamiento) | 100 % cubiertos por E2E o integración |

Los umbrales pueden ajustarse por ADR; no se bajan para "hacer pasar" un PR.

---

## 9. Comprobación antes de cerrar un cambio

### 9.1 Checklist del autor

- [ ] Los nombres siguen la convención del framework y el idioma definido (§4).
- [ ] La función nueva o modificada tiene una sola responsabilidad y **≤ 150 líneas**.
- [ ] La entrada está validada con Form Request y la operación tiene Policy o Gate.
- [ ] Las consultas de tenant incluyen la empresa activa y existe **prueba de cruce entre empresas**.
- [ ] El esquema nuevo está en una **migración** con `down()` implementado.
- [ ] El escenario BDD del criterio de aceptación está automatizado en el nivel correcto.
- [ ] Las escrituras relacionadas están en una transacción y se probó el camino de fallo.
- [ ] No hay secretos, `.env` reales, volcados ni datos personales en el diff.
- [ ] El error devuelto cumple el formato único y no expone detalles internos.
- [ ] `composer test`, `npm run lint`, `npm run build` y `npx cypress run` pasan localmente.
- [ ] `.env.example` y README se actualizaron si cambió la configuración.
- [ ] CHANGELOG actualizado si el cambio es visible para el usuario.

### 9.2 Checklist del revisor

- [ ] ¿El PR resuelve **un** problema y enlaza su historia?
- [ ] ¿La regla de negocio está en el dominio y no en el controlador ni en el componente?
- [ ] ¿Se puede acceder a datos de otra empresa por algún parámetro?
- [ ] ¿Hay consultas N+1, `SELECT *` o filtros sin índice?
- [ ] ¿La prueba falla si se revierte el cambio? (verificación rápida de que prueba algo)
- [ ] ¿Se agregó una dependencia sin justificar y sin lockfile?
- [ ] ¿El mensaje de error es útil para el usuario y seguro para el sistema?

---

## 10. Excepciones, ADR y mantenimiento

- Una excepción a este estándar requiere un **ADR** en `DOCUMENTACION/SDD/` con: contexto, decisión, alternativas, consecuencias y fecha de revisión.
- El PR que introduce la excepción cita el ADR y actualiza este documento en el mismo cambio.
- Las decisiones pendientes se registran en el backlog con la etiqueta `deuda-tecnica`.
- Este documento se revisa **al menos cada trimestre** y en cada cambio de arquitectura o de stack.
- El historial de cambios del estándar se registra en `CHANGELOG.md`.

---

## 11. Herramientas de verificación

| Regla | Herramienta | Dónde se ejecuta |
| ----- | ----------- | ---------------- |
| Estilo PHP (PSR-12) | Laravel Pint (`--test`) | Local + CI |
| Análisis estático PHP | PHPStan / Larastan (nivel ≥ 6) | CI |
| Pruebas backend | Pest / PHPUnit | Local + CI |
| Estilo y tipos frontend | ESLint + `tsc --noEmit` | Local + CI |
| Pruebas frontend | Vitest | Local + CI |
| Pruebas E2E | Cypress | CI (flujo crítico) |
| Vulnerabilidades | `composer audit`, `npm audit` | CI semanal y en cada PR de dependencias |

El pipeline de `.github/workflows/ci.yml` es la fuente de verdad: **un PR no se integra con el pipeline en rojo**.

---

## Anexo A — Glosario de dominio (español → inglés)

Traducción oficial entre lo que se lee en la interfaz y los documentos (español) y lo que se escribe en el código (inglés). **Antes de crear una migración o un endpoint con un término nuevo, se agrega en esta tabla.** Si un término no está aquí, se consulta al equipo; no se inventa una segunda traducción.

| Español (interfaz y documentos) | Inglés (código, tablas y rutas) | Dónde aparece |
| ------------------------------- | ------------------------------- | ------------- |
| Empresa | Company | `companies`, `company_id`, `Company` |
| Usuario | User | `users`, `User` |
| Rol | Role | `roles`, `Role` |
| Permiso | Permission | `permissions`, `Permission` |
| Sesión | Session | `sessions` |
| Categoría | Category | `categories` |
| Marca | Brand | `brands` |
| Producto | Product | `products`, `/api/products` |
| Variante | Variant | `variants` |
| Código de variante | Variant code | `variant_codes.code` |
| Unidad de medida | Unit of measure (UoM) | `units_of_measure` |
| Conversión de unidad | Unit conversion | `unit_conversions` |
| Almacén | Warehouse | `warehouses` |
| Ubicación | Location | `locations` |
| Existencia / stock | Stock | `stocks`, `stocks.quantity` |
| Movimiento de inventario | Stock movement | `stock_movements`, `/api/stock-movements` |
| Entrada | Entry (inbound) | `type = 'entry'` |
| Salida | Exit (outbound) | `type = 'exit'` |
| Devolución | Return | `type = 'return'` |
| Ajuste | Adjustment | `type = 'adjustment'` |
| Reverso / compensación | Reversal | `type = 'reversal'`, `reversal_of_id` |
| Proveedor | Supplier | `suppliers` |
| Compra | Purchase | `purchases` |
| Recepción de compra | Purchase receipt | `purchase_receipts` |
| Traslado | Transfer | `transfers` |
| Conteo físico | Stock count | `stock_counts` |
| Diferencia de conteo | Count discrepancy | `count_discrepancies` |
| Alerta | Alert | `alerts`, `/api/alerts` |
| Regla de alerta | Alert rule | `alert_rules` |
| Métrica | Metric | `metrics`, `/api/metrics` |
| Auditoría | Audit log | `audit_logs` |
| Clave de idempotencia | Idempotency key | `idempotency_key` |
| Consulta del chatbot | Chat query | `chat_queries` |

Reglas de aplicación:

- El valor guardado en un `enum` va en inglés (`'entry'`, `'exit'`); la etiqueta que ve el usuario va en español («Entrada», «Salida»).
- Los mensajes de error usan un `code` en inglés (`INSUFFICIENT_STOCK`) y un `message` en español.
- Los nombres de carpetas de features en React (`products`, `inventory`, `movements`, `suppliers`, `metrics`, `alerts`) siguen la misma traducción.

---

## Anexo B — Glosario técnico

| Término | Significado |
| ------- | ----------- |
| **ADR** | Architecture Decision Record: registro de una decisión de arquitectura y sus consecuencias. |
| **BDD** | Behavior-Driven Development: especificación por escenarios de comportamiento observable. |
| **Tenant / empresa** | Ámbito de datos aislado al que pertenece un usuario. |
| **DTO** | Objeto de transferencia de datos que agrupa la entrada de una operación. |
| **N+1** | Patrón de consultas ineficiente: una consulta por cada elemento de una lista. |
| **DoD** | Definition of Done: condiciones que debe cumplir una historia para considerarse terminada. |
