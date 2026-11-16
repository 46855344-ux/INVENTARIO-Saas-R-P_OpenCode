# ADR-01 — Registro de decisiones arquitectónicas

**Proyecto:** SaaS Inversiones R&P | **Versión:** 1.0 | **Estado del registro:** inicial  
**Regla:** distinguir `Aceptada`, `Propuesta`, `Pendiente` y `Reemplazada`; ninguna propuesta se considera decisión vigente sin aprobación y contraste con código.

## 1. Propósito y formato

Cada ADR indica **contexto → decisión → alternativas → consecuencias → evidencia → estado**. Mantiene trazabilidad entre documentos maestros, SDD especializados, backlog y commits. Los nombres React/Laravel/MySQL reflejan la arquitectura **documentada**; la implementación debe confirmarse en el repositorio.

## 2. Índice

| ID | Tema | Estado documental | Dependencias |
|---|---|---|---|
| ADR-001 | Arquitectura React + Laravel + MySQL, monolito modular | Documentada originalmente; verificar código | SDD-01 |
| ADR-002 | Aislamiento multiempresa obligatorio en backend | Principio original; verificar implementación | Seguridad y diseño de datos |
| ADR-003 | Autenticación Google y Facebook | Propuesta nueva; pendiente implementación | AUTH-01 |
| ADR-004 | Vinculación de identidades sociales | Propuesta nueva; pendiente modelo definitivo | Canvas Datos |
| ADR-005 | Sesiones de aplicación tras OAuth | Pendiente de adaptar al repo | AUTH-01/API |
| ADR-006 | Diseño UI reusable y accesibilidad | Propuesta nueva | UI-01 |
| ADR-007 | Evolución incremental posterior al Sprint 5 | Estrategia recomendada | Backlog v1.1 |

## ADR-001 — Arquitectura de referencia

**Contexto:** Canvas técnico original plantea React frontend, Laravel REST, MySQL, contenedores y modularidad.  
**Decisión documentada:** conservar dichas tecnologías salvo hallazgo justificado.  
**Alternativas:** reescritura tecnológica o microservicios; no aprobadas.  
**Consecuencias:** menor riesgo de regresión; documentación y pruebas deben seguir arquitectura real.  
**Estado:** arquitectura de referencia, ejecución por verificar.  
**Evidencia necesaria:** `package.json`, `composer.json`, estructura backend, Docker y migrations.

## ADR-002 — Multiempresa y autorización

**Contexto:** el SaaS exige datos segregados por empresa y permisos de usuario independientes del método de login.  
**Decisión base:** autorización en backend y comprobación de empresa en consultas y operaciones.  
**Consecuencias:** tests entre tenants obligatorios, incluido OAuth.  
**Estado:** principio original; verificar consultas, middleware y tests.

## ADR-003 — Autenticación social Google/Facebook

**Contexto:** el sistema originalmente contempla correo y Facebook; el nuevo alcance añade Google.  
**Decisión propuesta:** mantener correo/contraseña y añadir autenticación con Google (OIDC) y Facebook (mecanismo oficial Meta). Laravel maneja el proceso del servidor; React presenta y comunica estados.  
**Alternativas:** solo correo, usar un broker de identidad externo, reemplazar por frontend-only; evaluar sin comprometer secretos.  
**Consecuencias:** credenciales por ambiente, callbacks, nuevas pruebas y dependencia de terceros.  
**Estado:** alcance aprobado por solicitante; diseño técnico pendiente de validar e implementar.  
**Referencia:** AUTH-01, US-0111/0112/0114.

## ADR-004 — Entidad de identidad externa

**Contexto:** un usuario puede utilizar varios métodos sin duplicar inventario, roles o historial.  
**Decisión propuesta:** una tabla `social_identities` o equivalente real, FK a usuario y único `(provider, provider_user_id)`; no fusionar por coincidencia de email.  
**Alternativas:** columnas `google_id/facebook_id` en usuarios; usuarios separados por proveedor.  
**Consecuencias:** migración aditiva, mantenimiento y resolución explícita de conflictos; nunca aumentar privilegios por OAuth.  
**Estado:** propuesta dependiente del esquema existente.  
**Referencia:** Canvas Datos v1.1, AUTH-01.

## ADR-005 — Manejo de sesión tras login social

**Contexto:** OAuth externo no equivale a sesión autorizada de la aplicación.  
**Decisión pendiente:** reutilizar la solución de autenticación que OpenCode encuentre en Laravel; emitir sesión/cookie de aplicación compatible con el frontend y CSRF.  
**Alternativas:** cookies de sesión, tokens propios de API; elegir tras revisión.  
**Consecuencias:** configuración CORS, SameSite, rotación y revocación según topología real.  
**Estado:** pendiente de evidencia.  
**Referencia:** AUTH-01 y SDD-05.

## ADR-006 — Sistema de diseño de UI

**Contexto:** interfaz consistente en módulos 1–12; minimizar errores en movimientos críticos.  
**Decisión propuesta:** componentes React reutilizables, tokens centralizados, objetivo WCAG 2.2 AA y pruebas manuales/automáticas.  
**Alternativas:** estilos locales por pantalla; desaconsejado por divergencia.  
**Consecuencias:** esfuerzo inicial y necesidad de pruebas visuales.  
**Estado:** propuesta, sujeto a librerías actuales.  
**Referencia:** UI-01, UX-01.

## ADR-007 — Incorporar cambios tras Sprint 5

**Contexto:** usuario informa avance hasta Sprint 5; no hay auditoría del repositorio aportada en esta documentación.  
**Decisión recomendada:** tratar Google/Facebook y mejoras UX como nuevas historias/incremento, sin reescribir cierres previos ni migraciones existentes.  
**Consecuencias:** backlog de cambios, pruebas regresión y trazabilidad.  
**Estado:** recomendación operativa pendiente de adopción.  
**Referencia:** Backlog v1.1, QA-01.

## 3. Plantilla para decisiones posteriores

```markdown
### ADR-XXX — Título
- Fecha:
- Autor/revisor:
- Contexto:
- Decisión:
- Alternativas consideradas:
- Consecuencias positivas/negativas:
- Impacto en código, datos, seguridad y pruebas:
- Evidencia (archivos/commits/PR):
- Estado: Propuesta | Aceptada | Reemplazada | Rechazada
- Referencias:
```

## 4. Proceso de actualización

1. OpenCode inspecciona repo y enumera hallazgos, sin suposiciones.
2. Se formula ADR con alternativas y riesgos.
3. Un responsable revisa y aprueba la decisión.
4. Se vincula a tareas del backlog y a SDD-01/04/05/09.
5. Se registra evidencia de implementación y pruebas.
6. Si cambia la decisión, crear nuevo ADR que reemplace al anterior; no borrar historia.

## 5. Pendientes prioritarios

- ¿Cuál es la tecnología exacta de sesión ya instalada?
- ¿Es global la identidad usuario o por empresa?
- ¿Cómo se realiza alta e invitación multiempresa?
- ¿Qué convención de rutas API y migrations existe?
- ¿Cuáles políticas de valorización, redondeo y conteo del backlog original siguen abiertas?

## 6. Checklist OpenCode

- [ ] Confirmar ADR existentes en `SDD/ADR/` para evitar colisiones.
- [ ] Si existe `ADR-01`, renombrar este registro como índice o ADR nuevo sin sobrescribir.
- [ ] Marcar cada propuesta con estado correcto.
- [ ] Vincular evidencia y revisión.
- [ ] Actualizar SDD y backlog afectados después de aprobar cambios.
