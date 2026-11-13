# Backlog técnico de implementación del MVP
**Proyecto:** SaaS Inversiones R&P  
**Producto:** Sistema SaaS de gestión de inventario y almacén  
**Stack:** React · Laravel · MySQL · REST API  
**Versión del documento fuente:** 1.0 · Noviembre 2026

> Transcripción estructurada del documento original. Se conservan las historias, tareas, criterios, prioridades y secuencia de sprints.

## 1. Objetivo y alcance del backlog

Este documento convierte la planificación de implementación del producto SaaS Inversiones R&P en un backlog técnico priorizado. Sirve como base para crear el Product Backlog en la herramienta de gestión del equipo y planificar cada Sprint.
La estrategia recomendada es definir desde el inicio el modelo lógico completo y las reglas estructurales de datos, pero construir el esquema físico mediante migraciones incrementales. Cada historia debe entregar una funcionalidad completa y comprobable, conectando persistencia, lógica de negocio, API, interfaz y pruebas.

### 1.1 Alcance funcional considerado para el MVP

- Autenticación, usuarios, roles y permisos.
- Separación de información por empresa/tenant desde el diseño inicial.
- Productos, categorías, marcas, variantes y códigos de identificación.
- Unidades de medida, conversiones y ubicaciones de almacén.
- Existencias por variante y ubicación; entradas, salidas, devoluciones, ajustes y reversiones.
- Proveedores, compras, costos históricos y traslados entre ubicaciones.
- Métricas operativas y alertas de stock.
- Conteos físicos, diferencias, autorizaciones y trazabilidad.
- Chatbot de consulta exclusivamente de lectura y sujeto a permisos.
- Pruebas, seguridad, calidad, despliegue y recuperación.

### 1.2 Fuera del alcance del MVP

- Punto de venta (POS) completo, caja y procesamiento de pagos.
- Facturación electrónica y contabilidad.
- Aplicación móvil nativa.
- Acciones de escritura ejecutadas por el chatbot.
- Automatización de compras a proveedores.
- Microservicios y escalamiento distribuido prematuro.
- Módulo comercial completo de suscripciones y cobros SaaS, salvo preparación de la estructura de datos que se apruebe.

## 2. Arquitectura y reglas técnicas que condicionan el backlog

Capa                  Tecnología / responsabilidad     Regla de implementación
Presentación, formularios, tablas, navegación y consumo de API. No
Frontend              React
conecta directamente con MySQL.
Fuente única de verdad para reglas de negocio, validaciones,
Backend               Laravel
autorización y operaciones.
Integridad referencial, restricciones, índices, transacciones y
Persistencia          MySQL
aislamiento de datos.
Contratos versionados y respuestas consistentes; autenticación y
Comunicación          API REST / JSON
autorización en servidor.
Pruebas unitarias, integración y componentes; pruebas de aceptación
Pruebas               PHPUnit/Pest y Vitest
por historia.
SonarQube y OWASP Dependency-    Análisis continuo; atender vulnerabilidades y defectos antes de cerrar
Calidad y seguridad
Check                            entregas.
Entrega               Docker y GitHub Actions          Entornos reproducibles y pipeline automatizado de validación.

### 2.1 Principios obligatorios

- Backend modular monolítico para el MVP; no dividir en microservicios sin una necesidad demostrada.

- Todas las operaciones que cambien existencias deben pasar por un servicio central de inventario en Laravel.
- Las operaciones confirmadas no se eliminan físicamente: se corrigen mediante reversiones o movimientos compensatorios trazables.
- Los movimientos que afecten varias existencias se ejecutan dentro de transacciones; bloquear registros cuando exista riesgo de concurrencia.
- La existencia se controla por empresa, variante y ubicación, según el modelo aprobado.
- El tenant_id/empresa_id y la estrategia de aislamiento se definen desde Sprint 0; no se agregan al final.
- El frontend solo muestra datos permitidos; los permisos se verifican también en el backend.
- El chatbot consulta datos autorizados y no puede crear, editar ni eliminar movimientos.

## 3. Estructura del Product Backlog

Prioridades: P0 = imprescindible para el MVP o bloqueo técnico; P1 = necesaria para completar el MVP; P2 = conveniente o condicionada a una decisión.
Épica          Nombre                           Resultado esperado                                              Sprint
Proyecto ejecutable, arquitectura acordada, pipeline y modelo
EP-00          Fundación técnica y decisiones                                                                   0
inicial.
Identidad, acceso y              Usuarios autenticados y acceso controlado por empresa y
EP-01                                                                                                           1
multiempresa                     permisos.
EP-02          Catálogo de productos            Productos, categorías, marcas, variantes y códigos.             2
EP-03          Unidades y ubicaciones           Unidades, conversiones y ubicaciones configurables.             3
EP-04          Núcleo de inventario             Existencias y movimientos seguros y auditables.                 4
EP-05          Compras y traslados              Recepción de compras y transferencias entre ubicaciones.        5
EP-06          Métricas y alertas               Indicadores operativos y avisos de stock.                       6
EP-07          Conteos y auditoría              Conteos físicos, diferencias y ajustes autorizados.             7
EP-08          Consulta inteligente             Chatbot de solo lectura y contexto de temporada.                8
EP-09          Calidad y salida a producción    Sistema validado, seguro, desplegado y recuperable.             9

## 4. Backlog detallado por épicas y sprints

### EP-00 — Fundación técnica y decisiones

Objetivo: preparar la base de trabajo, cerrar decisiones que afectan el diseño y dejar un esqueleto ejecutable sin desarrollar prematuramente todos los módulos.

#### US-0001 — Acordar arquitectura y estructura del repositorio

Historia: Como equipo de desarrollo, queremos una arquitectura y estructura de repositorio acordadas para trabajar con responsabilidades claras.
Prioridad: P0 | Sprint sugerido: 0

**Criterios de aceptación:**
- Se documenta React separado de Laravel y comunicación mediante API REST/JSON.
- Se define backend modular monolítico y MySQL como persistencia.
- Se establecen convenciones de ramas, commits, revisión de código y versionado de API.

**Tareas técnicas:**
- Crear repositorio Git y estructura de carpetas.
- Crear proyecto base Laravel y React.
- Documentar módulos, convenciones y estrategia de ramas.
- Definir configuración por ambiente sin guardar secretos en el repositorio.

#### US-0002 — Definir modelo de datos y estrategia multiempresa

Historia: Como responsable del producto, quiero aprobar el modelo de datos y la estrategia de aislamiento para evitar rediseños y exposición de información entre empresas.
Prioridad: P0 | Sprint sugerido: 0

**Criterios de aceptación:**
- Se revisan entidades y relaciones del modelo lógico.
- Se decide cómo se representa empresa_id en tablas pertenecientes a cada tenant.
- Se documentan restricciones, claves, índices y relaciones principales.
- No se habilita desarrollo funcional dependiente de decisiones aún abiertas.

**Tareas técnicas:**
- Revisar entidades: empresa, usuario, rol, permiso, producto, variante, existencia, movimientos, compras, ventas, traslados, conteos, auditoría y alertas.
- Definir alcance de empresa_id y claves únicas por tenant.
- Definir estrategia de aislamiento compartido y pruebas de separación.
- Preparar diagrama entidad-relación y diccionario de datos.

#### US-0003 — Preparar entorno local y pipeline inicial

Historia: Como desarrollador, quiero levantar el sistema de forma reproducible y validar cambios automáticamente.
Prioridad: P0 | Sprint sugerido: 0

**Criterios de aceptación:**
- Docker permite iniciar los servicios locales acordados.
- GitHub Actions ejecuta instalación, análisis y pruebas base.
- La configuración local, test y producción está separada.

**Tareas técnicas:**
- Crear Dockerfile/compose para los componentes necesarios.
- Configurar conexión Laravel-MySQL mediante variables de entorno.
- Configurar workflow de CI para dependencias y pruebas.
- Añadir plantilla de variables de entorno y guía de instalación.

#### US-0004 — Cerrar decisiones de negocio que afectan el diseño

Historia: Como Product Owner, quiero resolver las decisiones abiertas para que el equipo implemente reglas coherentes.
Prioridad: P0 | Sprint sugerido: 0

**Criterios de aceptación:**
- Cada decisión tiene responsable, decisión, fecha y evidencia.
- Las decisiones no resueltas quedan marcadas como bloqueantes para las historias afectadas.

**Tareas técnicas:**
- Resolver DP-01 a DP-10: registro de salidas por venta, valorización, conversiones/redondeo, conteo concurrente, autorizaciones, retención/recuperación, objetivos de disponibilidad, límites de planes, precios/márgenes por variante e importación histórica.
- Resolver decisiones de datos: unicidad del email, nivel de stock mínimo, ubicación en detalle de movimiento, roles globales o por tenant, borrado lógico y auditoría.
- Actualizar modelo, reglas y criterios de aceptación.

### EP-01 — Identidad, acceso y multiempresa

#### US-0101 — Iniciar sesión y cerrar sesión

Historia: Como usuario, quiero autenticarme para acceder únicamente a las funciones autorizadas.
Prioridad: P0 | Sprint sugerido: 1

**Criterios de aceptación:**
- El usuario puede iniciar y cerrar sesión.
- Las credenciales inválidas no permiten el acceso.
- Las rutas protegidas rechazan solicitudes no autenticadas.
- La sesión/token se gestiona de forma segura para la arquitectura SPA.

**Tareas técnicas:**
- Crear migraciones y modelos de usuario/empresa.
- Implementar autenticación Laravel; evaluar Laravel Sanctum para SPA.
- Definir flujo CSRF/cookies o token según despliegue.
- Crear endpoints y pantallas React de acceso.
- Agregar pruebas de autenticación y expiración.

#### US-0102 — Administrar usuarios y asignar roles

Historia: Como administrador, quiero crear y administrar usuarios para controlar quién utiliza el sistema.
Prioridad: P0 | Sprint sugerido: 1

**Criterios de aceptación:**
- Se puede registrar, editar, activar y desactivar usuarios según permisos.
- Cada usuario pertenece al ámbito empresarial definido.
- No se permite asignar roles fuera del ámbito autorizado.

**Tareas técnicas:**
- Crear migraciones usuario, rol y tablas pivote.
- Implementar modelos, relaciones, validaciones y políticas.
- Crear endpoints CRUD y pantallas React.
- Probar acceso entre empresas y asignaciones no autorizadas.

#### US-0103 — Aplicar permisos por módulo y acción

Historia: Como administrador, quiero definir permisos para que cada trabajador vea y ejecute solo lo permitido.
Prioridad: P0 | Sprint sugerido: 1

**Criterios de aceptación:**
- El backend verifica permisos en cada endpoint protegido.
- La interfaz oculta o deshabilita acciones no autorizadas sin sustituir la validación del servidor.
- Los trabajadores sin permiso no pueden consultar costos/márgenes restringidos.

**Tareas técnicas:**
- Crear permisos y relaciones rol_permiso/usuario_permiso si se aprueba.
- Implementar middleware/policies/gates.
- Proteger endpoints y componentes de navegación.
- Crear pruebas positivas y negativas por rol.

#### US-0104 — Aislar datos por empresa

Historia: Como empresa cliente, quiero que mis registros no sean visibles ni modificables por otra empresa.

Prioridad: P0 | Sprint sugerido: 1

**Criterios de aceptación:**
- Toda consulta tenant-scoped aplica el contexto de empresa.
- Las operaciones de lectura y escritura no permiten cambiar empresa_id manipulando la petición.
- Las pruebas demuestran que un tenant no accede a otro.

**Tareas técnicas:**
- Implementar resolución del tenant autenticado.
- Aplicar scopes/repositorios/policies en modelos y consultas.
- Validar tenant en relaciones y operaciones compuestas.
- Crear pruebas automatizadas de aislamiento.

### EP-02 — Catálogo de productos

#### US-0201 — Gestionar categorías y marcas

Historia: Como administrador, quiero mantener categorías y marcas para clasificar el catálogo.
Prioridad: P1 | Sprint sugerido: 2

**Criterios de aceptación:**
- Se pueden crear, editar, listar y desactivar categorías/marcas.
- Se evita duplicidad según la regla de unicidad aprobada.
- Los registros están aislados por empresa cuando corresponda.

**Tareas técnicas:**
- Crear migraciones y relaciones.
- Implementar validaciones, endpoints y políticas.
- Construir formularios, tablas, filtros y estados en React.
- Agregar pruebas de API y UI.

#### US-0202 — Registrar y mantener productos

Historia: Como encargado de almacén, quiero registrar productos con sus datos principales para controlar el catálogo.
Prioridad: P1 | Sprint sugerido: 2

**Criterios de aceptación:**
- Se puede crear, consultar, editar y desactivar un producto.
- Los campos obligatorios y códigos únicos se validan en backend.
- No se elimina un producto que tenga movimientos históricos; se restringe o desactiva.

**Tareas técnicas:**
- Crear tabla producto y relaciones.
- Implementar modelo, servicio de catálogo, requests y resource API.
- Crear listado, búsqueda, detalle y formulario React.
- Agregar pruebas de integridad e historial.

#### US-0203 — Gestionar variantes y códigos

Historia: Como encargado, quiero registrar variantes y sus códigos para identificar presentaciones distintas del producto.
Prioridad: P1 | Sprint sugerido: 2

**Criterios de aceptación:**

- Un producto puede tener una o más variantes según reglas aprobadas.
- Los códigos de variante no se duplican dentro del ámbito definido.
- La variante se puede seleccionar en las operaciones de inventario.

**Tareas técnicas:**
- Crear variante y codigo_variante con claves e índices.
- Implementar relaciones y validaciones.
- Exponer endpoints y formularios de variantes.
- Probar unicidad y referencias.

### EP-03 — Unidades y ubicaciones

#### US-0301 — Configurar unidades de medida

Historia: Como encargado, quiero definir unidades para registrar cantidades de manera consistente.
Prioridad: P1 | Sprint sugerido: 3

**Criterios de aceptación:**
- Se pueden administrar unidades activas.
- Las cantidades aceptan decimales según precisión acordada.
- No se permiten unidades incompatibles.

**Tareas técnicas:**
- Crear unidad_medida y relaciones necesarias.
- Definir precisión/escala decimal en conjunto con negocio.
- Crear endpoints y pantallas de mantenimiento.
- Agregar pruebas de validación.

#### US-0302 — Configurar conversiones de unidades

Historia: Como encargado, quiero convertir unidades compatibles para operar con distintas presentaciones.
Prioridad: P1 | Sprint sugerido: 3

**Criterios de aceptación:**
- La conversión respeta factores y redondeo aprobados.
- Se rechazan conversiones no definidas o incompatibles.
- La cantidad original y la cantidad normalizada son trazables cuando aplique.

**Tareas técnicas:**
- Crear conversion_unidad.
- Implementar servicio de conversión reutilizable.
- Definir tratamiento de redondeo y precisión.
- Crear pruebas de límites, decimales y conversiones inversas.

#### US-0303 — Administrar ubicaciones de almacén

Historia: Como encargado, quiero definir almacenes y ubicaciones para separar existencias físicamente.
Prioridad: P1 | Sprint sugerido: 3

**Criterios de aceptación:**
- Se pueden crear, editar y desactivar ubicaciones.
- Cada existencia queda asociada a una ubicación.
- No se desactiva una ubicación con stock sin una operación de traslado o resolución.

**Tareas técnicas:**

- Crear ubicacion y restricciones.
- Implementar endpoints y pantallas de mantenimiento.
- Definir índice único de existencia por empresa/variante/ubicación.
- Agregar pruebas de restricciones.

### EP-04 — Núcleo de inventario

#### US-0401 — Consultar existencias

Historia: Como encargado, quiero consultar stock por producto, variante y ubicación para conocer la disponibilidad.
Prioridad: P0 | Sprint sugerido: 4

**Criterios de aceptación:**
- Se muestra cantidad disponible por variante y ubicación.
- Los resultados respetan empresa y permisos.
- Las cantidades se calculan/consultan desde registros consistentes.

**Tareas técnicas:**
- Crear tabla existencia y claves únicas.
- Implementar consultas paginadas y filtros.
- Crear endpoints y vistas React de stock.
- Agregar pruebas de aislamiento y consistencia.

#### US-0402 — Registrar entradas de inventario

Historia: Como encargado autorizado, quiero registrar entradas para incrementar las existencias.
Prioridad: P0 | Sprint sugerido: 4

**Criterios de aceptación:**
- La entrada requiere producto/variante, ubicación, cantidad y responsable.
- La operación confirmada incrementa el stock una sola vez.
- Se registra fecha, usuario y motivo/documento según corresponda.
- La operación es transaccional y trazable.

**Tareas técnicas:**
- Diseñar movimiento_inventario y detalle_movimiento.
- Implementar InventoryMovementService para entradas.
- Usar transacciones y protección contra doble envío.
- Crear endpoint, formulario React y pruebas de concurrencia/idempotencia.

#### US-0403 — Registrar salidas y validar stock disponible

Historia: Como usuario autorizado, quiero registrar salidas para descontar existencias sin generar stock negativo.
Prioridad: P0 | Sprint sugerido: 4

**Criterios de aceptación:**
- No se confirma una salida superior al stock disponible.
- La operación requiere permiso y responsable.
- La salida genera movimiento histórico no eliminable.
- La regla se verifica en backend, no solo en interfaz.

**Tareas técnicas:**
- Implementar salida en InventoryMovementService.
- Aplicar bloqueo de filas/transacción para evitar carreras.

- Resolver vínculo con ventas según DP-01.
- Crear endpoint, formulario y pruebas de stock insuficiente/concurrencia.

#### US-0404 — Registrar devoluciones

Historia: Como usuario autorizado, quiero registrar devoluciones como operaciones independientes para conservar trazabilidad.
Prioridad: P1 | Sprint sugerido: 4

**Criterios de aceptación:**
- La devolución referencia el origen cuando corresponda.
- La cantidad devuelta y el destino se validan.
- Se genera movimiento separado y auditable.

**Tareas técnicas:**
- Definir tipos y estados de devolución.
- Implementar servicio y validaciones.
- Crear endpoint y pantalla.
- Probar devoluciones parciales y totales.

#### US-0405 — Realizar ajustes autorizados

Historia: Como responsable autorizado, quiero ajustar existencias con justificación para corregir diferencias justificadas.
Prioridad: P0 | Sprint sugerido: 4

**Criterios de aceptación:**
- Todo ajuste requiere motivo y usuario autorizado.
- El ajuste queda registrado como movimiento.
- No se modifica directamente la existencia desde el frontend ni desde módulos externos.

**Tareas técnicas:**
- Implementar flujo de autorización si aplica.
- Implementar ajuste en InventoryMovementService.
- Registrar auditoría y motivo.
- Crear pruebas de permisos y stock resultante.

#### US-0406 — Revertir movimientos confirmados

Historia: Como administrador autorizado, quiero revertir un movimiento incorrecto sin borrar el historial.
Prioridad: P0 | Sprint sugerido: 4

**Criterios de aceptación:**
- Un movimiento confirmado no se elimina físicamente.
- La reversión genera movimiento compensatorio enlazado al original.
- No se permite revertir dos veces sin regla explícita.

**Tareas técnicas:**
- Diseñar referencia al movimiento original.
- Implementar reglas de reversión por tipo.
- Registrar actor, fecha y motivo.
- Crear pruebas de trazabilidad y saldo.

### EP-05 — Compras y traslados

#### US-0501 — Gestionar proveedores

Historia: Como encargado de compras, quiero mantener proveedores para asociarlos a las compras.
Prioridad: P1 | Sprint sugerido: 5

**Criterios de aceptación:**
- Se pueden registrar, consultar, editar y desactivar proveedores.
- Los datos se validan y quedan asociados a la empresa.
- No se pierde el historial de compras al desactivar.

**Tareas técnicas:**
- Crear proveedor y relaciones.
- Implementar endpoints, policies y pantallas.
- Agregar pruebas CRUD y tenant.

#### US-0502 — Registrar compras y costos históricos

Historia: Como encargado, quiero registrar compras con proveedor, fecha, cantidades y precios para conservar el costo histórico.
Prioridad: P1 | Sprint sugerido: 5

**Criterios de aceptación:**
- La compra registra proveedor, fecha, detalle, cantidad y precio.
- La recepción confirmada incrementa stock mediante InventoryMovementService.
- El costo histórico no se sobrescribe por compras posteriores.
- La valorización global depende del método aprobado.

**Tareas técnicas:**
- Crear compra y detalle_compra.
- Definir estados borrador/confirmada/recibida si el proceso lo requiere.
- Implementar servicio de recepción integrado al núcleo de inventario.
- Crear endpoints, pantallas y pruebas de costos históricos.

#### US-0503 — Trasladar existencias entre ubicaciones

Historia: Como encargado, quiero trasladar stock entre ubicaciones para reflejar movimientos físicos internos.
Prioridad: P1 | Sprint sugerido: 5

**Criterios de aceptación:**
- Se descuenta origen y se incrementa destino en una única transacción.
- No se permite trasladar más stock del disponible.
- El efecto neto global del traslado es cero.
- Se conserva origen, destino, usuario, fecha y estado.

**Tareas técnicas:**
- Crear traslado y detalle_traslado.
- Implementar flujo de salida/entrada atómico o estados controlados.
- Integrar con InventoryMovementService.
- Crear pruebas de reversión, concurrencia y saldo neto.

### EP-06 — Métricas y alertas

#### US-0601 — Visualizar indicadores de inventario

Historia: Como administrador, quiero visualizar métricas para supervisar el estado del almacén.
Prioridad: P1 | Sprint sugerido: 6

**Criterios de aceptación:**
- Se muestran total de productos, detalle de stock, agotados, stock mínimo, valor total, más salidas y sin movimiento según definición aprobada.
- Los indicadores respetan permisos y tenant.
- Las métricas se calculan desde datos persistidos y reglas documentadas.
- No se presenta valorización/margen como cifra oficial hasta aprobar el método de valorización.

**Tareas técnicas:**
- Definir consultas/servicio InventoryMetricsService.
- Optimizar agregaciones e índices.
- Crear endpoints de métricas.
- Construir panel React con estados vacíos, carga y errores.
- Validar resultados contra datos de prueba.

#### US-0602 — Configurar alertas de stock mínimo e inactividad

Historia: Como encargado, quiero recibir alertas cuando el stock llegue al mínimo o un producto permanezca sin movimiento.
Prioridad: P1 | Sprint sugerido: 6

**Criterios de aceptación:**
- El umbral mínimo y periodo de inactividad son configurables según decisión.
- Las alertas no se duplican innecesariamente.
- La alerta identifica producto, variante/ubicación cuando aplique, fecha y motivo.
- El usuario solo ve alertas autorizadas.

**Tareas técnicas:**
- Implementar InventoryAlertService.
- Crear tabla alerta y estados.
- Configurar Laravel Scheduler para evaluación periódica.
- Crear listado/filtros de alertas en React.
- Probar umbrales y duplicados.

#### US-0603 — Programar tareas y notificaciones

Historia: Como administrador, quiero que las verificaciones periódicas se ejecuten automáticamente.
Prioridad: P2 | Sprint sugerido: 6

**Criterios de aceptación:**
- Las tareas programadas se ejecutan según calendario definido.
- Los errores quedan registrados y pueden diagnosticarse.
- Se evita enviar notificaciones repetidas por la misma condición.

**Tareas técnicas:**
- Configurar Laravel Scheduler.
- Evaluar Queue/Redis solo si hay tareas asíncronas o volumen que lo justifique.

- Configurar canal de notificación aprobado.
- Agregar pruebas y logs.

### EP-07 — Conteos físicos y auditoría

#### US-0701 — Ejecutar conteos físicos

Historia: Como encargado, quiero registrar conteos físicos para comparar el stock real con el sistema.
Prioridad: P1 | Sprint sugerido: 7

**Criterios de aceptación:**
- El conteo conserva cantidades registradas y responsable.
- Se identifica el stock teórico usado como referencia.
- El conteo no modifica stock automáticamente al guardarse.

**Tareas técnicas:**
- Crear conteo_inventario y detalle_conteo.
- Definir estados abierto, en revisión, aprobado/cerrado.
- Implementar captura de cantidades y diferencias.
- Crear pantallas y pruebas.

#### US-0702 — Revisar y aprobar diferencias

Historia: Como supervisor, quiero revisar diferencias antes de autorizar ajustes.
Prioridad: P0 | Sprint sugerido: 7

**Criterios de aceptación:**
- Las diferencias se muestran claramente.
- Solo un rol autorizado puede aprobar ajustes.
- La decisión y justificación quedan registradas.
- La concurrencia con ventas/movimientos sigue la política aprobada DP-04.

**Tareas técnicas:**
- Implementar workflow de revisión/aprobación.
- Definir tratamiento de movimientos ocurridos durante el conteo.
- Integrar ajuste aprobado con InventoryMovementService.
- Crear pruebas de permisos, concurrencia y auditoría.

#### US-0703 — Consultar trazabilidad y auditoría

Historia: Como administrador, quiero consultar quién realizó cada operación y cuándo para investigar cambios.
Prioridad: P1 | Sprint sugerido: 7

**Criterios de aceptación:**
- Se registra actor, fecha, tipo de operación, entidad y contexto permitido.
- Los registros de auditoría no son editables por usuarios ordinarios.
- Los datos sensibles no se exponen en logs.

**Tareas técnicas:**
- Crear auditoria y estrategia de eventos.
- Registrar eventos relevantes de autenticación, catálogo y movimientos.
- Crear consulta filtrable de auditoría.
- Agregar pruebas de integridad y permisos.

### EP-08 — Consulta inteligente

#### US-0801 — Consultar inventario mediante chatbot de solo lectura

Historia: Como usuario autorizado, quiero consultar información del inventario en lenguaje natural para encontrar datos con rapidez.
Prioridad: P2 | Sprint sugerido: 8

**Criterios de aceptación:**
- El chatbot responde solo con información que el usuario está autorizado a consultar.
- No puede crear, modificar, eliminar ni confirmar operaciones.
- Las consultas se ejecutan mediante servicios/endpoints autorizados, no mediante acceso directo del modelo a la base de datos.
- Cuando no hay datos suficientes, lo comunica sin inventar resultados.

**Tareas técnicas:**
- Definir proveedor/modelo LLM y costos/privacidad.
- Crear capa Laravel de orquestación.
- Implementar consultas limitadas y filtradas por permisos/tenant.
- Enviar al LLM solo el contexto mínimo necesario.
- Crear interfaz de chat y pruebas de prompt injection/filtración.

#### US-0802 — Consultar información de temporada

Historia: Como usuario, quiero considerar información de temporada al interpretar consultas de inventario cuando esa funcionalidad esté definida.
Prioridad: P2 | Sprint sugerido: 8

**Criterios de aceptación:**
- La fuente y alcance de datos estacionales están documentados.
- La información se presenta como contexto y no altera existencias.
- El acceso respeta tenant y permisos.

**Tareas técnicas:**
- Confirmar alcance funcional de temporada.
- Diseñar fuente y persistencia si corresponde.
- Integrar contexto en capa de consulta.
- Probar que no modifica datos.

### EP-09 — Calidad y salida a producción

#### US-0901 — Automatizar pruebas y controles de calidad

Historia: Como equipo, queremos detectar defectos de forma continua para reducir riesgos antes de desplegar.
Prioridad: P0 | Sprint sugerido: 9

**Criterios de aceptación:**
- Backend y frontend tienen pruebas automatizadas ejecutadas en CI.
- SonarQube analiza el código según quality gate acordado.
- OWASP Dependency-Check identifica dependencias vulnerables.
- Los defectos críticos bloquean el despliegue.

**Tareas técnicas:**
- Completar suites PHPUnit/Pest y Vitest.

- Configurar SonarQube y quality gate.
- Configurar OWASP Dependency-Check.
- Añadir pruebas de integración API y aislamiento multiempresa.
- Documentar resultados y excepciones aceptadas.

#### US-0902 — Preparar despliegue productivo

Historia: Como administrador del sistema, quiero desplegar la aplicación en un entorno productivo seguro.
Prioridad: P0 | Sprint sugerido: 9

**Criterios de aceptación:**
- Frontend, backend y MySQL están configurados en infraestructura aprobada.
- HTTPS está habilitado.
- Secretos y credenciales se gestionan fuera del código.
- Hay procedimiento de despliegue y reversión.

**Tareas técnicas:**
- Preparar servidor/hosting para Laravel y publicación de React.
- Configurar dominio, TLS/HTTPS, variables y permisos.
- Ejecutar migraciones controladas.
- Configurar logs, monitoreo básico y manejo de errores.
- Documentar procedimiento de release.

#### US-0903 — Configurar copias de seguridad y recuperación

Historia: Como responsable, quiero recuperar los datos ante fallos para proteger la continuidad del negocio.
Prioridad: P0 | Sprint sugerido: 9

**Criterios de aceptación:**
- Existe política aprobada de frecuencia, retención y responsables.
- Las copias se almacenan separadas del servidor principal.
- Se realiza una prueba documentada de restauración.
- Los objetivos RPO/RTO se definen antes de producción.

**Tareas técnicas:**
- Cerrar DP-06 y DP-07.
- Configurar backup de MySQL y almacenamiento seguro.
- Documentar cifrado, retención y acceso.
- Ejecutar simulacro de restauración y registrar resultados.

#### US-0904 — Ejecutar pruebas de aceptación y estabilización

Historia: Como Product Owner, quiero validar los flujos completos para aceptar el MVP.
Prioridad: P0 | Sprint sugerido: 9

**Criterios de aceptación:**
- Los criterios de aceptación del MVP están verificados.
- No quedan defectos críticos o altos sin tratamiento aprobado.
- Se valida seguridad, permisos, multiempresa y trazabilidad.
- Se entrega documentación de operación y soporte.

**Tareas técnicas:**
- Preparar datos de prueba representativos.
- Ejecutar pruebas end-to-end de catálogo, entradas, salidas, compras, traslados, alertas y conteos.

- Validar usuarios y permisos por rol.
- Corregir defectos y registrar evidencias.
- Realizar revisión final con Product Owner.

## 5. Orden de implementación de base de datos

El siguiente orden es orientativo para las migraciones. Las dependencias reales deben reflejarse en claves foráneas y revisarse con el diagrama aprobado.
Orden          Grupo de tablas                                      Dependencias / notas
1              empresa                                              Base para aislamiento multiempresa.
usuario, rol, permiso, usuario_rol, rol_permiso,
2                                                                   Confirmar roles globales o por empresa y unicidad del email.
usuario_permiso
categoria, marca, producto, variante,
3                                                                   Catálogo; definir unicidad de códigos.
codigo_variante
4              unidad_medida, conversion_unidad                     Definir precisión y redondeo.
5              ubicacion                                            Ubicaciones dentro del ámbito empresarial.
6              existencia                                           Clave única sugerida por empresa + variante + ubicación.
7              movimiento_inventario, detalle_movimiento            Registro central de entradas, salidas, ajustes, devoluciones y reversiones.
8              proveedor, compra, detalle_compra                    Recepciones integradas al servicio de inventario.
9              traslado, detalle_traslado                           Origen y destino; operación transaccional.
10             conteo_inventario, detalle_conteo                    Captura, revisión y aprobación de diferencias.
11             auditoria, alerta                                    Trazabilidad y alertas operativas.
12             plan, suscripcion                                    Solo si el alcance SaaS comercial se aprueba para una fase posterior.

## 6. Servicios de backend previstos

Servicio / componente                Responsabilidad                                                                Cuándo
Auth / Access Policies               Autenticación, autorización, roles y permisos.                                 Sprint 1
TenantContext / TenantScope          Resolver empresa activa y limitar consultas.                                   Sprint 1
CatalogService                       Reglas de productos, categorías, marcas y variantes.                           Sprint 2
UnitConversionService                Conversiones y precisión de cantidades.                                        Sprint 3
InventoryMovementService             Única vía para registrar cambios de stock.                                     Sprint 4
PurchaseReceivingService             Confirmar recepción de compras e invocar inventario.                           Sprint 5
TransferService                      Traslados entre ubicaciones con consistencia transaccional.                    Sprint 5
InventoryMetricsService              Cálculo de indicadores operativos.                                             Sprint 6
InventoryAlertService                Evaluación y generación de alertas.                                            Sprint 6
StockCountService                    Conteos, diferencias y aprobación.                                             Sprint 7
Desde Sprint 4;
AuditService / Domain Events         Registro de trazabilidad de operaciones.
consolidar en 7
ChatAssistantService                 Consultas de lectura con contexto autorizado.                                  Sprint 8

Los servicios son componentes internos de Laravel; no implican desplegar microservicios independientes. Los controladores deben ser delgados: reciben la petición, validan el contrato, delegan al servicio y devuelven una respuesta API.

## 7. Plan de sprints sugerido

Sprint        Objetivo de la iteración            Incremento demostrable                                        Dependencia clave
Repositorios, proyectos base, entorno, CI, modelo y           Aprobación de arquitectura y
0             Fundación y decisiones
decisiones críticas.                                          reglas.
1             Identidad y multiempresa            Inicio de sesión, usuarios, roles, permisos y aislamiento.    Tenant strategy.
2             Catálogo                            Productos, categorías, marcas, variantes y códigos.           Modelo de catálogo.
Precisión y reglas de
3             Unidades y almacenes                Unidades, conversiones y ubicaciones.
conversión.
4             Inventario central                  Consultar stock, entradas, salidas, devoluciones, ajustes     Reglas de stock y concurrencia.

y reversiones.
Proveedores, compras, costos históricos y traslado entre
5             Compras y traslados                                                                                   Proceso de compra/recepción.
ubicaciones.
Definiciones de indicadores y
6             Métricas y alertas                    Panel operativo y alertas configurables.
umbrales.
Conteos, revisión, ajustes aprobados y consulta de              Política de conteos
7             Conteos y auditoría
trazabilidad.                                                   concurrentes.
Proveedor LLM y política de
8             Chatbot de lectura                    Consultas conversacionales con permisos.
datos.
Pruebas finales, seguridad, backups, despliegue y               Infraestructura y objetivos
9             Estabilización y producción
aceptación.                                                     operativos.

## 8. Definition of Ready (DoR)

**Una historia puede entrar al Sprint cuando:**
- Tiene objetivo y valor de negocio comprensibles.
- Tiene criterios de aceptación verificables.
- Se conocen dependencias de datos, API, permisos e interfaz.
- Las decisiones de negocio necesarias están resueltas o explícitamente excluidas.
- El equipo entiende el alcance y puede estimar el trabajo.
- Los diseños o contratos necesarios están disponibles.

## 9. Definition of Done (DoD)

**Una historia se considera terminada únicamente cuando:**
- Cumple todos los criterios de aceptación.
- Migraciones, relaciones, índices y restricciones están implementados y revisados.
- Las reglas de negocio están en Laravel y no duplicadas como autoridad en React.
- Validaciones y autorización del backend están activas.
- La interfaz React consume la API y contempla carga, error y estado vacío.
- Existen pruebas unitarias y/o de integración pertinentes; pasan en CI.
- No introduce vulnerabilidades críticas conocidas ni defectos bloqueantes.
- Se actualizó la documentación técnica y API cuando corresponde.
- El código fue revisado y el Product Owner acepta el incremento.

## 10. Riesgos técnicos y acciones preventivas

Riesgo                                Impacto                             Prevención / respuesta                                             Momento
Aislamiento multiempresa              Exposición de datos entre           Diseñar empresa_id y TenantScope desde Sprint 0; pruebas
0–1
incompleto                            clientes.                           negativas en CI.
Actualizaciones directas de stock                                                                                                            4 en
Saldos inconsistentes.              Centralizar toda mutación en InventoryMovementService.
desde módulos                                                                                                                                adelante
Concurrencia en salidas y             Stock negativo o doble              Transacciones, bloqueo de filas, idempotencia y pruebas
4–5
traslados                             descuento.                          concurrentes.
Reglas de conversión/redondeo
Diferencias de cantidades.          Cerrar DP-03 antes de terminar Sprint 3.                           0–3
sin definir
Valorización no acordada              Métricas financieras incorrectas.   No publicar valor/margen como definitivo hasta DP-02 y DP-09.      0–6
Conteo simultáneo con                 Diferencias falsas o ajustes
Definir DP-04 y congelamiento/corte o conciliación.                0–7
operaciones                           incorrectos.
Permisos aplicados solo en                                                                                                                   1 en
Acceso no autorizado por API.       Policies/middleware Laravel y pruebas por rol.
frontend                                                                                                                                     adelante
Filtración o modificación de        Solo lectura, consultas filtradas, mínimo contexto y pruebas de
Chatbot con acceso excesivo                                                                                                                  8
datos.                              seguridad.
Backups sin prueba de                 Pérdida de datos ante incidente.    Política aprobada y simulacro de restauración antes de             9

restauración                                                        producción.
Sobrecarga de alcance            Retrasos y MVP inconcluso.         Proteger exclusiones, priorizar P0/P1 y mover P2 a backlog futuro.   Todos

## 11. Decisiones pendientes y bloqueos

Estas decisiones deben registrarse en el Product Backlog como tareas de análisis. No deben resolverse silenciosamente por el equipo técnico.
ID          Decisión pendiente                                                    Historias afectadas
DP-01       Quién registra las salidas por venta: vendedor, cajero o ambos.       US-0403; integración futura con ventas.
DP-02       Método de valorización del inventario.                                US-0601; reportes de valor.
DP-03       Reglas de conversión y redondeo de unidades.                          US-0302; US-0402 a US-0503.
Cómo operar conteos físicos mientras continúan las
DP-04                                                                             US-0701; US-0702.
ventas/movimientos.
DP-05       Quién autoriza operaciones si el administrador no está disponible.    US-0405; US-0702.
DP-06       Retención de datos y política de recuperación.                        US-0903.
DP-07       Objetivos de disponibilidad y rendimiento.                            US-0902; US-0903.
DP-08       Límites y reglas de planes SaaS.                                      Fase comercial posterior.
DP-09       Precios y márgenes por variante.                                      US-0601; reportes financieros.
DP-10       Alcance y formato de importación de inventario histórico.             Sprint 0 / carga inicial.
DD-01       Unicidad global o por empresa del correo electrónico.                 US-0101; US-0102.
Nivel donde se configura stock mínimo: producto, variante o
DD-02                                                                             US-0602.
ubicación.
DD-03       Alcance de ubicación en detalle de movimiento.                        US-0402 a US-0503.
DD-04       Roles y permisos globales o por empresa.                              US-0102; US-0103.
DD-05       Borrado lógico y relaciones de auditoría.                             Catálogo y trazabilidad.

## 12. Recomendación para ejecutar el backlog

1.      Primero realizar Sprint 0 y cerrar decisiones estructurales. No empezar construyendo todas las tablas ni todos los
módulos en bloque.
2.      En cada sprint, seleccionar historias completas y desarrollarlas de extremo a extremo: migración → modelo →
servicio/regla → endpoint → interfaz → pruebas.
3.      Diseñar el contrato API y criterios de aceptación antes de implementar la pantalla.
4.      Mantener el núcleo de inventario como prioridad técnica principal; compras, traslados, conteos y futuras ventas
deben reutilizar el mismo servicio.
5.      Demostrar al Product Owner un incremento funcional al cierre de cada sprint y ajustar el backlog con lo aprendido.
6.      Mantener Redis/colas como opción, no como requisito inicial; incorporarlos cuando exista una necesidad real de
procesamiento asíncrono.
7.      Dejar suscripciones comerciales, POS, facturación electrónica, app móvil y microservicios para fases posteriores
salvo cambio formal del alcance.

## 13. Entregables de gestión recomendados

Artefacto                  Contenido mínimo                                                                     Responsable sugerido
Product Backlog            Épicas, historias, prioridad, estimación, dependencias y estado.                     Product Owner + equipo
Sprint Backlog             Historias seleccionadas y tareas técnicas del sprint.                                Equipo Scrum
Modelo de datos            ERD, diccionario, reglas, migraciones y decisiones.                                  Backend / BD
Contrato API               Endpoints, payloads, errores, autenticación y permisos.                              Backend + frontend
Plan de pruebas            Casos unitarios, integración, aceptación y evidencia.                                Equipo de desarrollo / QA
Pipeline CI/CD             Build, pruebas, análisis de calidad y controles de dependencias.                     Equipo técnico
Guía de despliegue         Configuración, secretos, migraciones, backup y rollback.                             Responsable técnico

## 14. Nota de control de alcance

Este backlog es una propuesta de implementación derivada del análisis y diseño del proyecto. La asignación de sprints es inicial y debe confirmarse con la capacidad del equipo, la estimación de historias y la prioridad del Product Owner.
Las historias condicionadas por decisiones pendientes deben permanecer en estado “Por definir” o no entrar al Sprint hasta que exista una decisión aprobada.
La lista no presupone que todas las funcionalidades SaaS comerciales estén dentro del MVP. Se prepara el aislamiento multiempresa desde el inicio, pero la gestión de planes, límites y cobros puede permanecer fuera de esta primera entrega.
