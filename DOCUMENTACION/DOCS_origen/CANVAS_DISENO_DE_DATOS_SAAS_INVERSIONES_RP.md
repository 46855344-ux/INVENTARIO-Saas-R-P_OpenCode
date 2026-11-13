# CANVAS DE DISEÑO DE DATOS

## Plataforma SaaS de Gestión de Inventario -- Inversiones R&P

  Campo            Detalle
  ---------------- -------------------------------------------
  Versión          1.0
  Documento base   Diccionario de Datos SaaS Inversiones R&P
  Base de datos    MySQL
  Backend          Laravel
  Frontend         React
  Estado           Propuesta para revisión y validación

------------------------------------------------------------------------

## 1. Propósito del modelo de datos

Diseñar una estructura de base de datos que permita almacenar,
relacionar y proteger la información de la plataforma SaaS de
inventario, garantizando integridad, trazabilidad, consistencia y
escalabilidad para múltiples empresas.

## 2. Problema que resuelve

La empresa necesita dejar atrás el control manual del inventario y
disponer de información centralizada para conocer:

-   Qué productos y variantes existen.
-   Cuántas unidades hay disponibles.
-   En qué ubicación se encuentra cada producto.
-   Qué entradas, salidas y traslados se realizaron.
-   Quién realizó cada operación.
-   Qué proveedores suministraron los productos.
-   Cómo evolucionaron los precios de compra.
-   Qué productos necesitan reposición.

## 3. Objetivo del modelo

Construir una estructura de datos que permita:

1.  Administrar empresas y usuarios.
2.  Gestionar productos, categorías, marcas y variantes.
3.  Controlar unidades de medida y conversiones.
4.  Registrar existencias por ubicación.
5.  Gestionar movimientos de inventario.
6.  Registrar proveedores y compras.
7.  Mantener el historial de operaciones y precios.
8.  Generar información para métricas y alertas.
9.  Administrar conteos físicos y auditorías.
10. Preparar la plataforma para su evolución multiempresa.

## 4. Entidades y grupos de tablas

  --------------------------------------------------------------------------
  Grupo                   Tablas principales         Finalidad
  ----------------------- -------------------------- -----------------------
  Organización            `empresa`                  Identificar cada
                                                     empresa o tenant.

  Seguridad               `usuario`, `rol`,          Controlar acceso y
                          `permiso`, `usuario_rol`,  autorizaciones.
                          `rol_permiso`,             
                          `usuario_permiso`          

  Catálogo                `categoria`, `marca`,      Organizar e identificar
                          `producto`, `variante`,    productos.
                          `codigo_variante`          

  Unidades                `unidad_medida`,           Administrar unidades y
                          `conversion_unidad`        equivalencias.

  Ubicaciones             `ubicacion`                Representar tienda,
                                                     almacén y espacios
                                                     internos.

  Existencias             `existencia`               Mantener saldos por
                                                     variante y ubicación.

  Inventario              `movimiento_inventario`,   Registrar entradas,
                          `detalle_movimiento`       salidas y ajustes.

  Compras                 `proveedor`, `compra`,     Registrar
                          `detalle_compra`           abastecimiento e
                                                     historial de costos.

  Ventas                  `venta`, `detalle_venta`   Asociar salidas
                                                     comerciales de forma
                                                     básica.

  Traslados               `traslado`,                Controlar
                          `detalle_traslado`         transferencias entre
                                                     ubicaciones.

  Control                 `auditoria`,               Mantener trazabilidad y
                          `conteo_inventario`,       diferencias físicas.
                          `detalle_conteo`           

  Alertas                 `alerta`                   Informar situaciones
                                                     relevantes del
                                                     inventario.

  SaaS                    `plan`, `suscripcion`      Preparar la gestión
                                                     comercial de la
                                                     plataforma.

  Personalización         `temporada`                Administrar temáticas
                                                     comerciales
                                                     estacionales.
  --------------------------------------------------------------------------

## 5. Clasificación de las tablas

El modelo contempla cuatro clasificaciones principales.

### A. Tablas independientes

-   `empresa`
-   `permiso`
-   `plan`
-   `unidad_medida`

Son entidades que pueden existir sin depender directamente de otra tabla
de negocio. Algunas pueden tener alcance global o empresarial, según la
configuración que se apruebe.

### B. Tablas dependientes

-   `usuario`
-   `producto`
-   `variante`
-   `codigo_variante`
-   `existencia`
-   `movimiento_inventario`
-   `detalle_movimiento`
-   `compra`
-   `detalle_compra`
-   `venta`
-   `detalle_venta`
-   `traslado`
-   `detalle_traslado`
-   `conteo_inventario`
-   `detalle_conteo`
-   `alerta`

Dependen de una entidad principal o de una relación necesaria para su
existencia.

### C. Tablas pivote

-   `usuario_rol`
-   `rol_permiso`
-   `usuario_permiso`

Resuelven relaciones de muchos a muchos entre usuarios, roles y
permisos.

### D. Tablas asociativas

-   `existencia`

Relaciona una variante con una ubicación, pero además almacena
información propia del negocio, como el saldo disponible. Por ello, no
es un pivote puro.

**Tablas polimórficas:** no existe una implementación polimórfica
confirmada en las fuentes. La tabla `auditoria` podría evaluarse para
una referencia polimórfica en el futuro, pero no se considera una
decisión aprobada.

## 6. Relaciones principales

-   Una empresa puede tener muchos usuarios, productos, proveedores,
    ubicaciones y movimientos.
-   Un producto puede tener varias variantes.
-   Una variante puede tener varios códigos alternativos.
-   Una variante puede tener existencias en diferentes ubicaciones.
-   Una ubicación puede almacenar existencias de muchas variantes.
-   Un movimiento puede contener varios detalles.
-   Una compra puede contener varios productos o variantes.
-   Un traslado relaciona una ubicación de origen con una ubicación de
    destino.
-   Un usuario puede tener varios roles y permisos.
-   Un rol puede contener varios permisos.
-   Una empresa puede tener una suscripción asociada a un plan.

## 7. Reglas de negocio que debe garantizar la base de datos

  -----------------------------------------------------------------------
  Código                  Regla                   Control esperado
  ----------------------- ----------------------- -----------------------
  RN-01                   Identificación única de Evitar códigos internos
                          productos               duplicados por empresa.

  RN-02                   Identificación de       Cada variante debe
                          variantes               contar con
                                                  identificación propia.

  RN-03                   Stock por ubicación     Un saldo por variante y
                                                  ubicación.

  RN-04                   Conversiones válidas    Respetar las
                                                  equivalencias entre
                                                  unidades.

  RN-05                   Cantidades decimales    Utilizar tipos
                                                  `DECIMAL` cuando
                                                  corresponda.

  RN-06                   No permitir salidas     Validación
                          superiores al stock     transaccional en
                                                  Laravel y MySQL.

  RN-07                   Traslados sin alterar   Registrar salida y
                          el stock global         entrada de forma
                                                  coordinada.

  RN-08                   Responsable de cada     Registrar el usuario
                          movimiento              que ejecutó la
                                                  operación.

  RN-09                   Movimientos confirmados Utilizar reversión
                          no se eliminan          mediante movimientos
                                                  compensatorios.

  RN-10                   Ajustes autorizados     Exigir justificación y
                                                  autorización.

  RN-11                   Conteos físicos         Registrar cantidades y
                          trazables               diferencias
                                                  encontradas.

  RN-12                   Aislamiento empresarial Evitar que una empresa
                                                  acceda a datos de otra.
  -----------------------------------------------------------------------

## 8. Estructura técnica de los datos

Convenciones propuestas:

-   **Identificadores:** `BIGINT UNSIGNED`.
-   **Cantidades y costos:** `DECIMAL`.
-   **Fechas y horas:** `DATETIME` o `TIMESTAMP` según el uso.
-   **Códigos:** `VARCHAR`.
-   **Estados:** `VARCHAR` con catálogo o restricciones controladas.
-   **Relaciones:** claves foráneas.
-   **Identificación:** restricciones `UNIQUE` e índices.
-   **Auditoría:** `created_at` y `updated_at` cuando corresponda.
-   **Eliminación:** preferencia por baja lógica en entidades maestras.

La definición exacta de tipos, longitudes, nulabilidad, claves y valores
por defecto se encuentra desarrollada en el Diccionario de Datos.

## 9. Métodos y responsabilidades

Los métodos representan operaciones del negocio; no son columnas de las
tablas.

  -----------------------------------------------------------------------
  Entidad o servicio                  Métodos sugeridos
  ----------------------------------- -----------------------------------
  Producto                            `crear()`, `actualizar()`,
                                      `desactivar()`,
                                      `consultarHistorial()`

  Variante                            `crear()`, `asignarCodigo()`,
                                      `consultarExistencias()`

  Existencia                          `consultarSaldo()`,
                                      `incrementar()`, `disminuir()`

  Movimiento                          `registrarEntrada()`,
                                      `registrarSalida()`, `revertir()`

  Traslado                            `validarDisponibilidad()`,
                                      `ejecutarTraslado()`

  Compra                              `registrarCompra()`,
                                      `calcularCosto()`,
                                      `consultarHistorial()`

  Conteo                              `iniciarConteo()`,
                                      `registrarDiferencia()`,
                                      `aprobarAjuste()`

  Usuario                             `asignarRol()`, `asignarPermiso()`,
                                      `verificarAcceso()`

  Alerta                              `generarAlerta()`,
                                      `consultarAlertas()`,
                                      `marcarAtendida()`
  -----------------------------------------------------------------------

Estos métodos deberán implementarse mediante servicios, acciones y
políticas de Laravel, según la responsabilidad de cada operación.

## 10. Seguridad e integridad

El modelo debe contemplar:

-   Separación de información por empresa.
-   Control de acceso mediante roles y permisos.
-   Validación de claves foráneas.
-   Restricciones para evitar duplicidad.
-   Transacciones para operaciones de inventario.
-   Control de concurrencia al actualizar existencias.
-   Registro de responsables y fechas de operaciones.
-   Protección del historial de movimientos.
-   Restricción de información sensible según el rol.

## 11. Tecnologías y componentes

  -----------------------------------------------------------------------
  Componente              Tecnología              Responsabilidad
  ----------------------- ----------------------- -----------------------
  Interfaz                React                   Formularios, navegación
                                                  y visualización.

  Backend                 Laravel                 Validaciones, permisos
                                                  y reglas de negocio.

  Base de datos           MySQL                   Persistencia e
                                                  integridad de datos.

  Comunicación            API REST                Intercambio de
                                                  información mediante
                                                  HTTP/HTTPS y JSON.

  Pruebas                 BDD                     Verificar
                                                  comportamientos y
                                                  reglas de negocio.
  -----------------------------------------------------------------------

**Principio arquitectónico:** React no debe conectarse directamente a
MySQL. Todas las operaciones deben pasar por Laravel.

## 12. Riesgos del modelo de datos

  -----------------------------------------------------------------------
  Riesgo                  Consecuencia            Tratamiento propuesto
  ----------------------- ----------------------- -----------------------
  Duplicidad de productos Inventario              Restricciones `UNIQUE`.
                          inconsistente           

  Actualizaciones         Saldos incorrectos      Transacciones y bloqueo
  simultáneas                                     de filas.

  Relaciones incorrectas  Pérdida de integridad   Claves foráneas.

  Acceso entre empresas   Exposición de           Aislamiento
                          información             multiempresa y
                                                  autorización.

  Eliminación de          Pérdida de trazabilidad Reversiones y baja
  movimientos                                     lógica.

  Conversiones erróneas   Cantidades incorrectas  Validación de unidades
                                                  y precisión decimal.

  Cambios de estructura   Incompatibilidad de     Migraciones versionadas
                          datos                   y respaldos.
  -----------------------------------------------------------------------

## 13. Decisiones pendientes de validación

1.  Definir si los correos de usuario serán únicos globalmente o por
    empresa.
2.  Determinar si el stock mínimo se manejará por producto, variante o
    ubicación.
3.  Definir cómo se registrará la ubicación en los detalles de
    movimientos.
4.  Aprobar el método de valoración del inventario.
5.  Definir el tratamiento de las ventas sin implementar un POS
    completo.
6.  Establecer el alcance de los roles y permisos globales o
    empresariales.
7.  Determinar la estrategia definitiva de aislamiento multiempresa.
8.  Definir la política de conservación, recuperación y eliminación
    lógica.
9.  Confirmar si se necesitarán relaciones polimórficas para auditorías
    o documentos.

## 14. Entregables derivados

A partir de este Canvas se deberán desarrollar:

1.  Diccionario de datos detallado.
2.  Modelo Entidad-Relación (MER).
3.  Modelo relacional.
4.  Modelo físico de MySQL.
5.  Scripts de creación y restricciones.
6.  Migraciones de Laravel.
7.  Seeders y datos iniciales.
8.  Pruebas de integridad y reglas de negocio.
9.  Documentación técnica de las relaciones y métodos.

## 15. Criterio de éxito

El diseño de datos se considerará validado cuando:

-   Todas las entidades necesarias estén identificadas.
-   Las relaciones y cardinalidades estén aprobadas.
-   Las tablas estén correctamente clasificadas.
-   Las reglas de negocio puedan garantizarse desde el backend y la base
    de datos.
-   Se mantenga la integridad de las existencias.
-   Se garantice la trazabilidad de los movimientos.
-   Se valide el aislamiento entre empresas.
-   El modelo pueda implementarse mediante migraciones Laravel y MySQL.

------------------------------------------------------------------------

## Nota de alcance

Este Canvas resume el diccionario de datos propuesto. No representa
todavía un esquema físico aprobado ni evidencia de implementación. Las
decisiones pendientes deberán resolverse antes de consolidar las
migraciones definitivas.
