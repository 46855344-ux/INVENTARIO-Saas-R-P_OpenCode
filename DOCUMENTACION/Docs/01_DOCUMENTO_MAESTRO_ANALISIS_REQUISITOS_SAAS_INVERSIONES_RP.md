# DOCUMENTO MAESTRO DE ANÁLISIS Y DEFINICIÓN DE REQUISITOS

## Plataforma SaaS de Gestión de Inventarios — Inversiones R&P

| Campo | Información |
|---|---|
| Proyecto | Sistema SaaS de gestión y control de inventarios |
| Empresa piloto | Inversiones R&P |
| Código propuesto | DOC-REQ-INVRP-001 |
| Versión de trabajo | 1.1 — Propuesta de actualización |
| Fecha | 15 de noviembre de 2026 |
| Documento de referencia | Documento Maestro de Análisis y Definición de Requisitos, versión 1.0, 1 de noviembre de 2026 |
| Estado | Propuesta para revisión y validación en OpenCode |
| Avance comunicado por el responsable | Desarrollo hasta Sprint 5; **no verificado contra repositorio** |
| Alcance de esta actualización | CX, UX, UI, Google/Facebook y trazabilidad con Sprints 0–5 |

> **Control documental:** este archivo es una versión propuesta del documento original, no evidencia de que las nuevas funcionalidades ya estén implementadas. Los requisitos nuevos llevan el prefijo `NUEVO` y necesitan validación técnica/empresarial. Los objetivos numéricos son propuestas, no compromisos aprobados.

---

## 1. Resumen ejecutivo

Inversiones R&P comercializa productos de manualidades, pasamanería, bisutería y textiles. Su control manual mediante cuadernos dificulta conocer las existencias reales, localizar productos, identificar variantes y conservar información de operaciones y proveedores.

El proyecto desarrolla un **MVP web para la empresa piloto**, con evolución prevista hacia una plataforma **SaaS multiempresa por suscripción**. Sus prioridades originales son integridad del inventario, trazabilidad, control de acceso, facilidad de uso, calidad y escalabilidad.

**Actualización 1.1 propuesta:** incorporar CX (experiencia integral del cliente), UX (experiencia del usuario), UI (interfaz de usuario) e inicio de sesión con **Google y Facebook**, además de correo y contraseña. Las mejoras deben conservar las reglas y transacciones existentes.

### 1.1 Estado del proyecto

- **Declarado por el responsable:** trabajo avanzado hasta Sprint 5.
- **No comprobado en esta actualización:** código, cobertura, migraciones ejecutadas, despliegue o historias terminadas.
- **Acción obligatoria:** OpenCode deberá elaborar un informe de correspondencia entre requisitos, código y pruebas antes de marcar cualquier requisito como completado.

## 2. Descripción del negocio

| Elemento | Descripción en el documento original |
|---|---|
| Empresa | Inversiones R&P |
| Actividad | Manualidades, pasamanería, bisutería y productos textiles |
| Tiendas | 1 |
| Almacenes | 1 |
| Distribución | Tienda en primer piso y almacén en segundo piso |
| Personal | 1 cajera que también actúa como almacenera y 2 vendedores |
| Control inicial | Cuadernos y registros manuales |
| Identificación de productos | Sin codificación uniforme |
| Proyección | Comercialización mediante membresías SaaS |

### 2.1 Problemática

Desorganización física, existencias desconocidas, dificultad de localización, productos similares sin variantes bien identificadas, ausencia de historiales centralizados, alertas, indicadores y trazabilidad integral.

### 2.2 Necesidades

Conocer qué productos existen; cantidad y ubicación de cada variante; costos, proveedores, precios históricos; alta y baja rotación; reposición necesaria; y responsables de movimientos.

### 2.3 Necesidad adicional de CX/UX/UI — NUEVO

Reducir el esfuerzo de aprendizaje, facilitar tareas repetitivas (búsqueda, entradas, salidas y traslados), clarificar los errores y brindar una experiencia coherente desde el acceso hasta el soporte del servicio.

## 3. Visión, objetivos y principios

### 3.1 Visión

Plataforma SaaS web que gestione, controle y analice inventarios de forma centralizada, trazable y actualizada, inicialmente para Inversiones R&P y posteriormente para otras empresas con aislamiento de datos.

### 3.2 Objetivos originales

1. Digitalizar el inventario y reducir errores.
2. Facilitar la ubicación física de productos.
3. Actualizar existencias según operaciones registradas.
4. Conservar precios históricos y proveedores.
5. Identificar rotación, agotamiento y necesidades de reposición.
6. Ofrecer métricas, alertas, auditorías y conteos.
7. Aplicar permisos y trazabilidad.
8. Preparar la comercialización SaaS.

### 3.3 Objetivos adicionales — NUEVO

- **CX:** facilitar incorporación, atención y continuidad de uso para empresas suscriptoras.
- **UX:** diseñar recorridos breves, claros y resistentes a errores, sin omitir confirmaciones críticas.
- **UI:** crear un sistema visual consistente, responsive y accesible.
- **Autenticación:** ofrecer correo/contraseña, Google y Facebook, manteniendo una sola identidad interna por usuario y un control de acceso independiente del proveedor.

## 4. Alcance funcional del MVP

Se preservan los **12 módulos** descritos en el documento maestro original.

| Módulo | Alcance conservado | Ampliación propuesta |
|---|---|---|
| M01. Usuarios y autenticación | Cuentas, administrador/trabajador, permisos, auditoría, acceso por correo y Facebook | Incorporar Google; flujo y pantalla unificados; vinculación segura de proveedores |
| M02. Productos | Productos, categorías, marcas, variantes, códigos, QR/barras, historial | Formularios claros, validación contextual, búsqueda y estados de interfaz |
| M03. Unidades | Unidades, equivalencias generales y por producto, decimales | Mostrar conversiones y errores de unidades antes de confirmar |
| M04. Ubicaciones | Almacenes, ambientes, estantes, cajas, códigos | Jerarquía navegable y búsqueda por ubicación |
| M05. Entradas y salidas | Movimientos con cantidad, unidad, origen, fecha, usuario, motivo | Flujos guiados; mostrar stock disponible y confirmación previa |
| M06. Traslados | Solicitud, origen/destino, entrega, recepción, confirmación | Pasos visibles y estados comprensibles del traslado |
| M07. Proveedores/precios | Compras, precios históricos, comparación, márgenes | Formularios y presentación de costos según permisos |
| M08. Métricas | Indicadores de existencias y actividad | Orden, filtros, estados vacíos y lectura accesible |
| M09. Alertas | Stock mínimo, agotados, inactivos, rotación | Jerarquía visual y acciones de consulta pertinentes |
| M10. Auditorías/conteos | Conteos, diferencias, revisión, autorización | Flujos de revisión sin ajustes accidentales |
| M11. Chatbot | Consultas estrictamente de lectura | Señalizar fuentes, límites y errores de datos |
| M12. Temáticas | Temas decorativos de campañas peruanas | Mantener legibilidad y accesibilidad sin afectar operación |

### 4.1 Requisitos del módulo M01 — autenticación ampliada

| ID | Requisito | Origen | Prioridad |
|---|---|---|---|
| RF-AUTH-01 | Permitir acceso por correo electrónico y contraseña conforme al mecanismo vigente | Conservado | Alta |
| RF-AUTH-02 | Permitir acceso mediante Facebook con el flujo oficial admitido por Meta | Original, detallado | Alta |
| RF-AUTH-03 | Permitir acceso mediante Google con OpenID Connect | NUEVO | Alta |
| RF-AUTH-04 | Mostrar las tres opciones de autenticación en una interfaz coherente | NUEVO | Alta |
| RF-AUTH-05 | Relacionar uno o más proveedores externos con un usuario interno, con restricción de unicidad por proveedor e identificador externo | NUEVO | Alta |
| RF-AUTH-06 | La coincidencia de correo no será suficiente para vincular automáticamente identidades; exigir una comprobación segura | NUEVO | Alta |
| RF-AUTH-07 | Proteger la redirección de autenticación y el retorno usando estado antifalsificación y demás controles aplicables | NUEVO | Alta |
| RF-AUTH-08 | No conceder roles, acceso a empresas o permisos basándose en el proveedor social | NUEVO | Alta |
| RF-AUTH-09 | Gestionar cancelaciones, errores, revocaciones y cuentas sin correo verificable sin crear cuentas duplicadas inseguras | NUEVO | Alta |
| RF-AUTH-10 | Permitir desvinculación segura sin dejar al usuario sin un método válido de recuperación/acceso | NUEVO | Media |
| RF-AUTH-11 | Registrar eventos de inicio, error y vinculación evitando guardar tokens o datos sensibles innecesarios en auditoría | NUEVO | Alta |

> **Decisión abierta:** definir si el primer ingreso social crea una cuenta pendiente de asignación o requiere invitación previa de una empresa. Hasta aprobarlo, no habilitar incorporación automática a un tenant.

### 4.2 Excluido del MVP

Se mantienen las exclusiones del documento original: POS completo, caja, pagos, facturación electrónica, contabilidad, clientes completos, chatbot con escritura, aplicación móvil nativa, integraciones comerciales avanzadas y compras automáticas. **Google/Facebook son métodos de ingreso; no implican integración con redes sociales para ventas o marketing.**

## 5. Actores y permisos

### 5.1 Actores

- **Administrador:** administra usuarios, permisos, productos, operaciones, proveedores, conteos, métricas y márgenes autorizados.
- **Trabajador:** consulta y realiza operaciones según permisos configurados.
- **Proveedor:** actor externo registrado indirectamente en compras.
- **Cliente:** actor externo sin acceso al MVP.
- **Proveedor de identidad externo:** Facebook (original) y Google (**NUEVO**).
- **Servicio LLM:** procesamiento acotado de consultas de solo lectura.
- **Futuro:** administrador de plataforma SaaS, suscriptor, responsable de suscripción y servicio de pagos.

### 5.2 Política de autorizaciones

Los permisos son asignados y comprobados **por el backend**. El inicio de sesión con Google/Facebook identifica al usuario, pero **no asigna por sí mismo rol, empresa o acceso a recursos**. Un trabajador no puede consultar márgenes de ganancia; los datos de distintas empresas no pueden cruzarse.

## 6. Casos de uso y recorridos UX

Se mantienen los casos originales: acceso, permisos, productos y variantes, lectura de códigos, unidades/conversiones, ubicaciones, entradas, salidas, traslados, compras/proveedores, historial, métricas, alertas, conteos, auditorías, chatbot y temas.

### 6.1 CU-AUTH-01: iniciar sesión con correo

**Actor:** usuario activo. **Precondición:** cuenta válida. **Flujo:** abrir login → ingresar credenciales → backend valida → establecer sesión → mostrar pantalla autorizada. **Excepciones:** credenciales incorrectas, cuenta desactivada, sesión vencida.

### 6.2 CU-AUTH-02: iniciar sesión con Google — NUEVO

**Actor:** usuario autorizado. **Precondición:** integración configurada, proveedor disponible, identidad correctamente vinculada o proceso de alta aprobado.

1. Seleccionar **Continuar con Google**.
2. Redirigir al proveedor con parámetros de seguridad definidos.
3. El usuario autoriza o cancela.
4. El backend recibe y valida la respuesta de autenticación.
5. Identificar la cuenta interna vinculada mediante identificador estable del proveedor.
6. Comprobar estado del usuario, tenant activo y permisos.
7. Crear sesión propia de la aplicación y redirigir a una pantalla autorizada.

**Alternativas:** cancelación; identidad no vinculada; correo no verificable; usuario desactivado; proveedor no disponible; intento de acceso a tenant no autorizado.

### 6.3 CU-AUTH-03: iniciar sesión con Facebook — NUEVO detalle

Mismo principio de CU-AUTH-02, adaptando validaciones y datos disponibles al flujo oficial de Meta. **No asumir que Facebook siempre proporciona dirección de correo**.

### 6.4 CU-UX-01: registrar salida de inventario — NUEVO refinamiento

Elegir producto/variante → visualizar existencia y ubicación → ingresar cantidad/unidad → validar conversión y disponibilidad → revisar resumen → confirmar → mostrar resultado y código de movimiento. Si la cantidad supera el stock permitido, bloquear la operación y explicar el motivo.

### 6.5 CU-UX-02: ejecutar traslado — NUEVO refinamiento

Seleccionar origen y destino → elegir variantes/cantidades → comprobar reglas → confirmar solicitud → registrar entrega/recepción conforme al flujo aprobado → consultar estado y responsables.

## 7. Historias de usuario

### 7.1 Conservación del catálogo original

Las historias **HU-01 a HU-22** del documento 1.0 se conservan; no deben eliminarse ni renumerarse. Incluyen acceso, permisos, consulta y registro de productos, códigos, conversiones, ubicaciones, movimientos, traslados, compras, indicadores, alertas, conteos, auditoría, chatbot, temas, suscripciones y separación multiempresa.

### 7.2 Historias nuevas propuestas

| ID | Historia | Prioridad | Dependencia |
|---|---|---|---|
| HU-23 | Como usuario autorizado, quiero iniciar sesión con Google para acceder sin otra contraseña | Alta | Sprint 1 / AUTH |
| HU-24 | Como usuario autorizado, quiero iniciar sesión con Facebook para acceder con mi identidad vinculada | Alta | Sprint 1 / AUTH |
| HU-25 | Como usuario, quiero vincular mis identidades sociales de forma segura para acceder a una misma cuenta | Alta | HU-23, HU-24 |
| HU-26 | Como usuario, quiero mensajes comprensibles de inicio de sesión fallido para corregir el problema | Alta | Login |
| HU-27 | Como trabajador, quiero encontrar productos mediante búsqueda y filtros claros para operar más rápido | Alta | Sprint 2 |
| HU-28 | Como trabajador autorizado, quiero ver el stock y las consecuencias antes de confirmar una salida | Alta | Sprint 4 |
| HU-29 | Como trabajador, quiero identificar origen, destino y estado del traslado durante todo el proceso | Alta | Sprint 5 |
| HU-30 | Como usuario, quiero una interfaz consistente y accesible en distintos tamaños de pantalla | Alta | Módulos React |
| HU-31 | Como empresa nueva, quiero comprender los primeros pasos del sistema para comenzar a operar | Media | CX/onboarding |
| HU-32 | Como administrador, quiero mensajes y ayudas contextuales que reduzcan errores de mis trabajadores | Media | UX transversal |

### 7.3 Escenarios BDD: autenticación

```gherkin
Característica: Acceso con proveedores de identidad

  Escenario: Ingreso con Google previamente vinculado
    Dado que existe un usuario activo con identidad Google vinculada
    Y tiene acceso autorizado a la empresa correspondiente
    Cuando completa correctamente la autenticación con Google
    Entonces el sistema establece una sesión interna segura
    Y conserva sus roles y permisos existentes

  Escenario: Acceso social sin pertenencia empresarial
    Dado que una persona se autentica correctamente con Google o Facebook
    Pero no tiene una empresa asignada y autorizada
    Cuando intenta consultar el inventario de una empresa
    Entonces el sistema deniega el acceso a dicho inventario

  Escenario: Evitar vinculación por coincidencia de correo
    Dado que existe un usuario con un correo registrado
    Cuando se autentica una identidad social no vinculada con el mismo correo
    Entonces el sistema no vincula automáticamente ambas identidades
    Y requiere el flujo seguro de verificación definido

  Escenario: Proveedor cancela la autenticación
    Dado que el usuario inicia autenticación social
    Cuando cancela en la página del proveedor
    Entonces el sistema no crea una sesión autenticada
    Y presenta un mensaje comprensible sin exponer detalles sensibles
```

### 7.4 Escenarios BDD originales que siguen vigentes

- Salida de 5 unidades desde 20 → existencia 15.
- Salida de 8 desde 5 → rechazo e información de insuficiencia.
- Restricción de margen de ganancia para trabajador.
- Ajuste pendiente hasta autorización.
- Stock mínimo de 5, existencia 4 → alerta.
- Inactividad configurada a 30 días → identificación de producto estancado.
- Empresa A no puede acceder a datos de empresa B.

## 8. Reglas de negocio

### 8.1 Reglas originales preservadas

| Código | Regla |
|---|---|
| RN-01 | Identificador único por producto |
| RN-02 | Identificación independiente por variante |
| RN-03 | Existencias por producto, variante y ubicación |
| RN-04 | Conversiones mediante equivalencias válidas |
| RN-05 | Decimales cuando la unidad los admita |
| RN-06 | Sin salidas normales superiores a stock disponible |
| RN-07 | Traslados sin cambiar stock global |
| RN-08 | Entrada/salida confirmada con responsable |
| RN-09 | Historial de costos de compra |
| RN-10 | Devoluciones como operaciones independientes |
| RN-11 | Movimientos confirmados no eliminados físicamente |
| RN-12 | Correcciones conservan movimiento original |
| RN-13 | Ajustes justificados y autorizados |
| RN-14 | Vendedores sin acceso a márgenes |
| RN-15 | Verificación de permisos en cada operación |
| RN-16 | Stock mínimo configurable |
| RN-17 | Período de inactividad configurable |
| RN-18 | Conteos conservan resultados originales |
| RN-19 | Diferencias revisadas antes de modificar existencias |
| RN-20 | Chatbot exclusivamente de lectura |
| RN-21 | Aislamiento de datos entre empresas |
| RN-22 | Trazabilidad de operaciones relevantes |
| RN-23 | Ubicaciones con múltiples productos |
| RN-24 | Producto en múltiples ubicaciones |

### 8.2 Nuevas reglas propuestas

| Código | Regla |
|---|---|
| RN-25 | El método de login no modifica rol ni permisos del usuario |
| RN-26 | Solo se permite acceso a tenants autorizados explícitamente |
| RN-27 | Identidad externa identificada por par único proveedor + identificador del proveedor |
| RN-28 | No vincular identidades distintas únicamente porque el correo coincida |
| RN-29 | Ningún secreto de proveedor social se expone en React o repositorio |
| RN-30 | Errores visuales no revelan si una identidad ajena está registrada |
| RN-31 | Confirmaciones UX no sustituyen validaciones transaccionales del backend |
| RN-32 | Cambios visuales o temáticos no reducen contraste, accesibilidad ni permisos |

## 9. Modelo conceptual de datos

El modelo original incluye empresas, usuarios, roles, permisos, productos, variantes, unidades, ubicaciones, existencias, movimientos, compras, proveedores, traslados, auditorías, conteos, alertas y planes/suscripciones.

### 9.1 Extensión conceptual propuesta — NUEVO

**Entidad `identidad_social` (nombre físico por validar):**

| Campo conceptual | Regla |
|---|---|
| `id` | Identificador interno |
| `usuario_id` | Referencia al usuario interno |
| `proveedor` | `google` o `facebook` |
| `identificador_externo` | ID estable entregado por el proveedor |
| `correo_observado` | Opcional; no es clave primaria de vinculación |
| `correo_verificado` | Opcional y dependiente de datos efectivamente proporcionados |
| `fecha_vinculacion` | Fecha de asociación |
| `ultimo_acceso` | Fecha del último acceso satisfactorio, según política de privacidad |
| `estado` | Activa, desvinculada o bloqueada, conforme al diseño final |

**Restricciones:** índice único `(proveedor, identificador_externo)`; FK a usuario; política de borrado y privacidad por definir; no almacenar tokens persistentes salvo necesidad técnica aprobada, y en tal caso protegerlos adecuadamente.

**Relaciones conceptuales:** `usuario 1:N identidad_social`; `usuario N:M empresa` o relación efectiva ya definida en el código; controles multiempresa se apoyan en asociaciones autorizadas, no en atributos del proveedor social.

> La nomenclatura, tablas y relaciones finales deben contrastarse con las **migraciones reales existentes** antes de cambiar el esquema.

## 10. Requisitos no funcionales

### 10.1 Requisitos originales

| Código | Categoría | Objetivo |
|---|---|---|
| RNF-01 | Seguridad | Autenticación y autorización |
| RNF-02 | Confidencialidad | Proteger información empresarial |
| RNF-03 | Integridad | Evitar inconsistencias de inventario |
| RNF-04 | Trazabilidad | Registrar modificaciones y operaciones |
| RNF-05 | Disponibilidad | Mantener acceso al servicio |
| RNF-06 | Rendimiento | Consultas ágiles |
| RNF-07 | Usabilidad | Interfaz comprensible para no técnicos |
| RNF-08 | Compatibilidad | Navegadores modernos |
| RNF-09 | Escalabilidad | Evolución multiempresa |
| RNF-10 | Mantenibilidad | Facilitar correcciones y evolución |
| RNF-11 | Recuperación | Recuperación ante incidentes |
| RNF-12 | Interoperabilidad | Futuras integraciones |
| RNF-13 | Precisión | Conversiones y cálculos correctos |
| RNF-14 | Privacidad | Proteger información personal y empresarial |

### 10.2 Requisitos complementarios — NUEVO

| Código | Requisito | Criterio de comprobación propuesto |
|---|---|---|
| RNF-UX-01 | Los flujos más frecuentes deben poder completarse sin instrucciones externas extensas | Pruebas observadas con trabajadores representativos |
| RNF-UX-02 | Errores de formulario deben indicar campo y acción correctiva | Revisión funcional y accesible |
| RNF-UI-01 | Interfaz consistente en botones, tablas, formularios, alertas y estados | Revisión contra sistema de diseño |
| RNF-UI-02 | Accesibilidad alineada con WCAG 2.2 nivel AA | Auditoría automática + revisión manual |
| RNF-UI-03 | Adaptación a escritorio y móvil web sin ocultar acciones críticas | Pruebas por tamaños de pantalla |
| RNF-AUTH-01 | Autenticación social con protección de redirección, cookies/sesiones y validación de respuesta | Pruebas de integración y seguridad |
| RNF-AUTH-02 | Tokens y secretos protegidos | Inspección de repositorio, configuración y registros |
| RNF-CX-01 | Incorporación y soporte documentados | Validación con empresa piloto |

**Indicadores CX/UX sugeridos para evaluación, aún no aprobados:** tasa de éxito de tareas, errores por operación, tiempo para primera operación y satisfacción. Definir líneas base, valores meta, muestra y método con los usuarios piloto antes de establecer cifras oficiales.

## 11. CX, UX y UI: especificación transversal — NUEVO

### 11.1 CX — Experiencia del cliente

Recorrido esperado: **conocimiento del servicio → registro/invitación → ingreso → incorporación → configuración inicial → primera operación → soporte → uso recurrente → eventual suscripción**.

**Entregables:** mapa del recorrido del cliente, guías de primer uso, catálogo de preguntas frecuentes, criterios de atención y medición de satisfacción.

### 11.2 UX — Experiencia del usuario

- Definir perfiles: administrador/cajera y trabajadores/vendedores.
- Documentar flujos de login, búsqueda, creación de variantes, entrada, salida, compra y traslado.
- Evitar decisiones ambiguas sobre unidad, cantidad, ubicación y variante.
- Señalizar consecuencias antes de operaciones con efecto sobre existencias.
- Mostrar carga, vacío, éxito, error, validaciones y recuperación.
- No reducir validaciones de backend para hacer un flujo aparentemente más rápido.

### 11.3 UI — Interfaz de usuario

- Componentes reutilizables en React.
- Paleta, tipografía, espacios, iconos, botones, formularios y tablas documentados.
- Diseño responsive y navegación por teclado.
- Etiquetas, ayuda contextual y mensajes de estado comprensibles.
- Estados de accesibilidad compatibles con los temas comerciales del M12.

### 11.4 Pantallas priorizadas

Login/recuperación; panel principal; productos/variantes; categorías; unidades; ubicaciones; entradas/salidas; proveedores/compras; traslados. Las pantallas de métricas, alertas, conteos, auditoría y chatbot se incorporarán a medida que se desarrollen sus sprints.

## 12. Seguridad y privacidad

Se conservan los principios originales: confidencialidad, integridad, disponibilidad, mínimo privilegio, autorización, segregación de funciones y auditoría.

### 12.1 Controles complementarios de autenticación — NUEVO

1. Usar configuración oficial de Google y Meta, con credenciales exclusivamente en el backend/gestor de secretos.
2. Registrar y validar origen, destino y estado del proceso de autorización.
3. Usar HTTPS en entornos operativos; proteger cookies con atributos pertinentes.
4. Verificar respuestas del proveedor según su protocolo y la biblioteca escogida.
5. No confiar en un correo no verificado para vincular identidades.
6. Aplicar limitación y monitoreo de intentos relevantes, sin revelar información sensible.
7. Comprobar usuario, rol, permisos y tenant en cada operación de negocio.
8. Considerar desconexiones, cancelaciones, ausencia de correo, bloqueo de cuenta y disponibilidad del proveedor.
9. Documentar política de conservación de eventos de acceso y datos personales.
10. Probar que una cuenta social válida **no** permite leer o modificar información de otra empresa.

## 13. Riesgos y mitigaciones

| Riesgo | Consecuencia | Tratamiento propuesto |
|---|---|---|
| Vincular cuentas por correo coincidente | Suplantación | Flujo explícito de vinculación/verificación |
| Conceder tenant por proveedor social | Exposición entre empresas | Autorización independiente de autenticación |
| Cambiar formularios de inventario sin pruebas | Errores de stock | Pruebas de regresión E2E y transaccionales |
| Variantes/unidades poco claras | Movimientos en cantidades erróneas | Diseño UX y validación doble |
| Dependencia de Google/Meta | Inicio de sesión social temporalmente no disponible | Mantener login tradicional y comunicar el estado |
| Proveedores sin email verificado | Identidades duplicadas o incorrectas | Tratar ausencia/no verificación como caso explícito |
| Actualizar documentación sin cotejar código | Falsa declaración de avance | Matriz requisito-código-prueba-evidencia |
| Temas comerciales con bajo contraste | Barreras de accesibilidad | Verificación WCAG en cada tema |

## 14. Procesos empresariales

### 14.1 Recepción de mercadería (TO-BE)

Recepción física → identificación de proveedor/documento → selección de variantes y unidades → validación de cantidades/conversiones → ubicación → confirmación de entrada → actualización transaccional de existencias → evidencia y auditoría.

### 14.2 Salida (TO-BE)

Identificación del producto → revisión de stock → ingreso de cantidad/unidad → comprobación de autorización y existencia → confirmación → movimiento auditable.

### 14.3 Conteo físico (TO-BE)

Definir alcance → capturar conteos → comparar con sistema → registrar diferencias → revisar → autorizar ajustes cuando corresponda → conservar historial.

### 14.4 Autenticación social (TO-BE) — NUEVO

Pantalla de login → proveedor elegido → autenticación externa → callback validado → búsqueda de identidad interna → validación de empresa/permisos → sesión propia → interfaz autorizada. Los casos no vinculados siguen el proceso de incorporación que apruebe el negocio.

## 15. Indicadores de inventario y experiencia

El documento original establece ocho indicadores principales: total de productos; productos por categorías; entradas/salidas; stock bajo; más vendidos; sin movimiento; rotación; valor monetario del inventario. Sus fórmulas, períodos y reglas deben documentarse de acuerdo con el diseño existente.

**Indicadores CX/UX adicionales propuestos:** finalización exitosa de tareas, tiempo de tarea, errores por operación, dificultades del primer ingreso y satisfacción de usuarios. No mezclar métricas de experiencia con indicadores contables de inventario.

## 16. Chatbot e inteligencia artificial

Se mantiene la capacidad **exclusiva de lectura** para existencias, alertas, movimientos, proveedores, precios históricos y novedades. Las consultas solo podrán retornar información que el usuario ya esté autorizado a ver. No podrá inventar movimientos ni efectuar cambios.

La mejora UX deberá aclarar qué puede consultar el chatbot, cuándo faltan datos y cuándo corresponde utilizar una pantalla transaccional.

## 17. Gobierno, calidad y estándares

**Referencias originales:** PMBOK, BPMN, BDD, DevSecOps, ISO 9001, ISO/IEC 25001, ISO/IEC 27001, ISO/IEC 29119 y COBIT 2019.

**Referencias complementarias propuestas:** ISO 9241-210 (diseño centrado en personas), ISO 9241-11 (usabilidad), ISO/IEC 25010 (calidad de producto) y WCAG 2.2 AA (accesibilidad web). Su uso es una guía técnica, **no una certificación**.

## 18. Criterios generales de aceptación del MVP

### 18.1 Criterios originales a conservar

1. Inicio de sesión válido y respeto de permisos.
2. Productos, variantes y ubicaciones correctamente administrados.
3. Conversiones consistentes.
4. Entradas, salidas y traslados sin alterar indebidamente existencias.
5. Compras y precios históricos consultables.
6. Indicadores y alertas verificables.
7. Conteos, revisión de diferencias y autorización de ajustes.
8. Trazabilidad de operaciones confirmadas.
9. Sin acceso indebido a márgenes.
10. Chatbot limitado a información autorizada.
11. Aislamiento empresarial y procedimientos de recuperación.

### 18.2 Criterios añadidos — NUEVO

- Google, Facebook y correo permiten iniciar sesión cuando las identidades y configuraciones son válidas.
- Un login social no modifica autorizaciones ni permite acceso entre tenants.
- La vinculación de cuentas resiste coincidencias de correo no comprobadas.
- Cancelar o fallar el flujo social no genera sesiones válidas ni errores que filtren datos.
- Los principales formularios muestran validaciones útiles y estados de éxito/error.
- Las tareas críticas se prueban con usuarios reales o representativos.
- La interfaz se revisa respecto a WCAG 2.2 AA y dispositivos definidos.
- Las modificaciones a pantallas de Sprints 1–5 no rompen reglas de inventario o compras.

## 19. Supuestos y limitaciones

**Originales:** una tienda, un almacén, cajera/almacenera y vendedores; datos históricos confiables limitados; inventario inicial dependiente de conteo; multiempresa comercial proyectado para fases posteriores.

**Nuevos:** integración con Google/Meta sujeta a credenciales, configuración y requisitos operativos de los proveedores; ausencia de acceso directo a repositorio para esta versión; diseño y prototipos sujetos a validación con usuarios piloto.

## 20. Decisiones pendientes

### 20.1 Pendientes originales DP-01 a DP-10

| ID | Decisión |
|---|---|
| DP-01 | Quién registra salidas de ventas |
| DP-02 | Método de valoración del inventario |
| DP-03 | Conversiones y reglas de redondeo |
| DP-04 | Conteos concurrentes con ventas |
| DP-05 | Autorización cuando el administrador no está disponible |
| DP-06 | Retención y recuperación de registros |
| DP-07 | Metas cuantitativas de disponibilidad y rendimiento |
| DP-08 | Límites de los planes SaaS |
| DP-09 | Precio/margen por variante |
| DP-10 | Alcance de importación histórica |

### 20.2 Nuevas decisiones propuestas

| ID | Decisión |
|---|---|
| DP-11 | Política de alta por Google/Facebook: invitación, aprobación o cuenta pendiente |
| DP-12 | Flujo seguro de vinculación y desvinculación de identidades |
| DP-13 | Tratamiento de usuarios sociales sin correo verificable |
| DP-14 | Estrategia actual de sesión y librerías compatibles con el código existente |
| DP-15 | Sistema visual, pruebas de accesibilidad y dispositivos objetivo |
| DP-16 | Métricas y metas CX/UX aprobadas por negocio |

## 21. Gestión de cambios y sprints

El backlog original prevé diez sprints, del 0 al 9. La ubicación de funcionalidades nuevas en un sprint no constituye evidencia de implementación.

| Sprint | Alcance del backlog | Acción documental sobre CX/UX/UI/AUTH |
|---|---|---|
| 0 | Fundación y arquitectura | Registrar nuevas decisiones y dependencias |
| 1 | Usuarios/autenticación | Analizar impacto de Google/Facebook en código vigente |
| 2 | Catálogo | Auditar UX de productos, categorías y variantes |
| 3 | Unidades y ubicaciones | Documentar controles y mensajes de validación |
| 4 | Inventario central | Revisar UX de entradas/salidas con pruebas de integridad |
| 5 | Compras, proveedores y traslados | Revisar estados y confirmaciones del flujo existente |
| 6 | Métricas y alertas | Diseñar visualización, filtros y accesibilidad desde el inicio |
| 7 | Conteos y auditoría | Diseñar flujos de revisión y autorización |
| 8 | Chatbot | Aplicar políticas UX de confianza y lectura autorizada |
| 9 | Estabilización | Pruebas integradas de accesibilidad, usabilidad y seguridad |

**Control de cambios:** crear historias de refinamiento o deuda técnica sin alterar retrospectivamente lo ya desarrollado. Cualquier cambio de datos, permisos o lógica de inventario requiere análisis de impacto, pruebas y respaldo.

## 22. Matriz de trazabilidad inicial

| Requisito | Documento relacionado | Evidencia exigida | Estado |
|---|---|---|---|
| RF-AUTH-02 | AUTH-01 / SDD | Configuración Meta, endpoints, pruebas | Pendiente de verificar |
| RF-AUTH-03 | AUTH-01 / SDD | Configuración Google, endpoints, pruebas | Nuevo; pendiente |
| RF-AUTH-05/06 | Diseño de datos / SEC-01 | Migración, unicidad y pruebas de vinculación | Nuevo; pendiente |
| RN-06 | SDD / Backlog Sprint 4 | Pruebas de bloqueo de sobreventa | Pendiente de verificar |
| RN-07 | SDD / Backlog Sprint 5 | Pruebas de conservación de stock en traslado | Pendiente de verificar |
| RN-14/15/21 | SDD / Seguridad | Pruebas de permisos y multiempresa | Pendiente de verificar |
| RNF-UX-01 | UX-01 | Pruebas observadas de tareas | Nuevo; pendiente |
| RNF-UI-02 | UI-01 / QA-01 | Informe WCAG | Nuevo; pendiente |
| RNF-AUTH-01 | AUTH-01 / QA-01 | Pruebas de flujo/callback/sesión | Nuevo; pendiente |

## 23. Plan inmediato de validación en OpenCode

1. Localizar frontend, backend, migraciones y tests reales.
2. Contrastar historias de Sprint 0–5 con archivos, commits y casos de prueba.
3. Completar estados de trazabilidad: **implementado y probado / implementado sin pruebas / parcial / pendiente / bloqueado**.
4. Elaborar un informe de impacto para Google, Facebook y cambios de React antes de codificar.
5. Actualizar SDD-01, Canvas de Datos y Backlog de manera consistente con este documento.
6. Implementar o programar nuevas historias según dependencias verificadas.
7. Ejecutar pruebas de regresión antes de seguir con Sprint 6.

## 24. Conclusión

La actualización a CX, UX, UI y autenticación social **complementa** el propósito original: gestionar inventarios confiables, seguros y trazables. Google y Facebook serán mecanismos de identidad, nunca sustitutos de permisos y aislamiento multiempresa. La documentación deberá evolucionar junto con el código verificado, sin presumir que el avance declarado hasta Sprint 5 equivale a cierre técnico de todas las historias.

---

## Anexo A. Registro de cambios

| Versión | Fecha | Cambio | Estado |
|---|---|---|---|
| 1.0 | 01/11/2026 | Documento maestro inicial | Documento fuente |
| 1.1 | 15/11/2026 | Propuesta de CX, UX, UI, Google y refinamiento de Facebook; requisitos, historias, reglas, pruebas y trazabilidad | Pendiente de validación |

## Anexo B. Fuentes internas utilizadas

- `DOC_ANALISIS_INVEN_ALAMACEN _INVR&P_CHATGPT.pdf` (documento maestro original).
- `CANVA_1_ DISEÑO TECNICO.pdf` (diseño técnico existente, a conciliar con el repositorio).
- `CANVA_2_RESUMEN_EJECUTIVO.pdf` (resumen del proyecto).
- `CANVAS_ 3_DE DISEÑO DE DATOS.pdf` (diseño de datos).
- `BACKLOG_TECNICO_IMPLEMENTACION_SAAS_INVERSIONES_RP.pdf` (plan de sprints).

**Nota editorial:** las ampliaciones marcadas `NUEVO` son especificaciones propuestas para revisión; no deben confundirse con texto aprobado en el documento de referencia.
