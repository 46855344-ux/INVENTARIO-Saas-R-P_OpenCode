# Documento técnico: Diagrama de flujo del sistema

**Proyecto:** SaaS Inversiones R&P  
**Versión:** 1.0  
**Fecha:** Noviembre de 2026  
**Tecnologías:** React, Laravel, MySQL y API REST

## 1. Objetivo

Documentar los flujos operativos y técnicos del SaaS Inversiones R&P para la gestión de inventarios y almacenes, desde la autenticación hasta la auditoría de operaciones.

## 2. Alcance

El MVP comprende autenticación, usuarios, roles, permisos y aislamiento por empresa; productos, categorías, marcas y variantes; unidades y conversiones; compras y proveedores; entradas, salidas, devoluciones, ajustes y reversiones; traslados; conteos físicos; métricas y alertas; auditoría; y chatbot de consulta de solo lectura.

Quedan fuera del MVP el POS completo, la facturación electrónica, la contabilidad, la aplicación móvil nativa y las modificaciones de inventario desde el chatbot.

## 3. Diagrama de flujo general

```mermaid
flowchart TD
    A([Inicio]) --> B[Acceder al sistema]
    B --> C[React: inicio de sesión]
    C --> D[API REST: enviar credenciales]
    D --> E[Laravel: autenticar]
    E --> F{¿Credenciales válidas?}
    F -- No --> G[Mostrar error]
    G --> C
    F -- Sí --> H[Resolver empresa, rol y permisos]
    H --> I{¿Acceso autorizado?}
    I -- No --> J[Acceso denegado]
    J --> C
    I -- Sí --> K[Panel principal]
    K --> L{Seleccionar módulo}

    L --> M[Catálogo de productos]
    M --> M1[Crear o actualizar productos, variantes y unidades]
    M1 --> V[Validar reglas de negocio]

    L --> N[Compras y proveedores]
    N --> N1[Registrar compra y verificar recepción]
    N1 --> N2{¿Recepción conforme?}
    N2 -- No --> N3[Registrar incidencia y revisar]
    N3 --> N1
    N2 -- Sí --> W[PurchaseReceivingService]

    L --> O[Movimientos de inventario]
    O --> O1[Elegir entrada, salida, devolución, ajuste o reversión]
    O1 --> O2[Indicar variante, ubicación y cantidad]
    O2 --> O3[Validar permisos y disponibilidad]
    O3 --> O4{¿Operación válida?}
    O4 -- No --> O5[Rechazar e informar motivo]
    O5 --> O
    O4 -- Sí --> X[InventoryMovementService]

    L --> P[Traslados entre ubicaciones]
    P --> P1[Definir origen, destino y cantidades]
    P1 --> P2{¿Stock suficiente?}
    P2 -- No --> P3[Informar insuficiencia]
    P3 --> P
    P2 -- Sí --> P4[Confirmar despacho y recepción]
    P4 --> Y[TransferService]

    L --> Q[Conteos físicos]
    Q --> Q1[Registrar cantidades físicas por ubicación]
    Q1 --> Q2[Comparar con stock teórico]
    Q2 --> Q3{¿Existen diferencias?}
    Q3 -- No --> Q4[Cerrar conteo]
    Q3 -- Sí --> Q5[Revisar discrepancias]
    Q5 --> Q6{¿Ajuste aprobado?}
    Q6 -- No --> Q7[Mantener incidencia pendiente]
    Q6 -- Sí --> Q8[Generar ajuste justificado]
    Q8 --> X

    L --> R[Métricas y alertas]
    R --> R1[Consultar indicadores autorizados]
    R1 --> AA[Mostrar resultados en React]

    L --> S[Auditoría]
    S --> S1[Consultar historial y responsables]
    S1 --> AA

    L --> T[Chatbot de solo lectura]
    T --> T1[Validar permisos y contexto de empresa]
    T1 --> T2[Consultar información autorizada]
    T2 --> AA

    V --> Z[(MySQL)]
    W --> X
    X --> Z
    Y --> Z
    Q4 --> Z
    Q7 --> Z
    Z --> AB[Persistir estado e historial]
    AB --> AC[Registrar trazabilidad]
    AC --> AD[Actualizar indicadores y alertas]
    AD --> AA
    AA --> AE{¿Realizar otra operación?}
    AE -- Sí --> K
    AE -- No --> AF[Cerrar sesión]
    AF --> AG([Fin])
```

> **Nota:** El diagrama representa el flujo funcional general. La persistencia y los cambios de stock se ejecutan bajo reglas, autorizaciones y transacciones del backend; las consultas no modifican existencias.

## 4. Arquitectura tecnológica

```mermaid
flowchart LR
    U[Usuario] --> FE[Frontend React]
    FE -->|HTTPS / JSON| API[API REST]
    API --> BE[Backend Laravel]
    BE --> AUTH[Autenticación y autorización]
    BE --> RULES[Servicios y reglas de negocio]
    RULES --> INV[InventoryMovementService]
    RULES --> DB[(MySQL)]
    INV --> DB
    DB --> BE
    BE -->|Respuesta JSON| API
    API --> FE
```

El frontend nunca se conecta directamente a MySQL. Laravel controla la autorización, validación, consistencia y modificación del inventario.

## 5. Descripción de procesos

| Proceso | Operación principal | Resultado |
|---|---|---|
| Autenticación | Validar identidad, empresa, rol y permisos | Acceso autorizado |
| Catálogo | Gestionar productos, variantes, códigos y unidades | Catálogo actualizado |
| Compras | Registrar compra y confirmar recepción | Movimiento de entrada |
| Inventario | Registrar entradas, salidas, devoluciones, ajustes y reversiones | Existencias e historial actualizados |
| Traslados | Mover existencias entre ubicaciones | Stock redistribuido |
| Conteos | Comparar stock físico con teórico y revisar diferencias | Cierre o ajuste autorizado |
| Métricas | Consultar indicadores y alertas | Información para decisiones |
| Auditoría | Consultar operaciones, responsables y motivos | Trazabilidad |
| Chatbot | Consultar datos permitidos | Respuesta sin modificar registros |

## 6. Reglas de negocio

1. **RN-01:** Todas las operaciones respetan los permisos del usuario.
2. **RN-02:** Cada empresa accede únicamente a sus propios datos.
3. **RN-03:** Laravel es la fuente de verdad para las reglas de negocio.
4. **RN-04:** Todo cambio de stock pasa por `InventoryMovementService`.
5. **RN-05:** No se permite stock negativo en operaciones ordinarias.
6. **RN-06:** Las existencias se controlan por empresa, variante y ubicación.
7. **RN-07:** Los cambios de inventario deben ser transaccionales y contemplar concurrencia.
8. **RN-08:** Los movimientos confirmados no se eliminan físicamente; se corrigen con reversiones o compensaciones.
9. **RN-09:** Guardar un conteo físico no modifica automáticamente el stock.
10. **RN-10:** Los ajustes por diferencias requieren aprobación autorizada.
11. **RN-11:** Los traslados deben mantener consistencia entre origen y destino y efecto neto global cero.
12. **RN-12:** El chatbot opera únicamente en modo lectura y con filtros de autorización.

## 7. Servicios de aplicación

### 7.1. `InventoryMovementService`

Valida y ejecuta movimientos que afectan existencias, registra trazabilidad y controla consistencia transaccional.

### 7.2. `PurchaseReceivingService`

Gestiona la confirmación de recepción de compras y delega la entrada de stock al servicio central de movimientos.

### 7.3. `TransferService`

Coordina traslados entre ubicaciones, validando disponibilidad y registrando la salida y entrada de manera consistente.

## 8. Decisiones pendientes

| Código | Decisión | Estado |
|---|---|---|
| DP-01 | Quién registra las salidas por ventas | Pendiente |
| DP-02 | Método de valoración del inventario | Pendiente |
| DP-03 | Reglas de conversión de unidades y redondeo | Pendiente |
| DP-04 | Manejo de conteos concurrentes con otros movimientos | Pendiente |
| DP-05 | Quién autoriza ajustes si el administrador no está disponible | Pendiente |

## 9. Conclusión

Este documento establece un flujo funcional para el MVP de SaaS Inversiones R&P. La arquitectura React–API REST–Laravel–MySQL separa la interfaz de las reglas de negocio y centraliza los cambios de inventario para favorecer la consistencia, seguridad, auditoría y trazabilidad. Las decisiones pendientes deberán resolverse antes de cerrar los procesos correspondientes.
