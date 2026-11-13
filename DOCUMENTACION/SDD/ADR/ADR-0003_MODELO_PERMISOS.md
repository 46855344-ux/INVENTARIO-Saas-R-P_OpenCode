# ADR-0003 — Modelo de roles, permisos y excepciones

**Proyecto:** SaaS Inversiones R&P
**Estado:** Aprobada — pendiente de implementación (US-0102/US-0103)
**Fecha:** 2026-11-13
**Sprint:** 1 (EP-01)
**Historias afectadas:** US-0102, US-0103
**Documentos relacionados:** `REGISTRO_DECISIONES_SPRINT_0.md` (DD-04), `SDD-04` §3.1, `PLAN_SPRINT_1_IDENTIDAD_MULTIEMPRESA.md` §6.4 y §8

---

## 1. Contexto

El canvas de Sprint 1 pide permisos por módulo y acción, y menciona
`rol_permiso`/`usuario_permiso` **«si se aprueba»**. `DD-04` (propuesta) define
roles base globales y roles personalizados por empresa.

## 2. Alternativas evaluadas

| Alternativa | Descripción | Ventajas | Desventajas |
| --- | --- | --- | --- |
| **A. Solo roles y permisos de rol** | `roles` + `permissions` + `permission_role` | Simple; cubre el caso común | Sin excepciones puntuales por usuario |
| **B. A + excepciones por usuario** | Añade `user_permission` (conceder/denegar) | Máxima flexibilidad | Más superficie de error y pruebas |
| **C. Permisos embebidos en el rol (sin catálogo)** | El rol lista sus permisos en código | Rápido | No auditable ni configurable; difícil de mantener |

## 3. Decisión propuesta

**Adoptar la alternativa A** para el MVP, dejando la **alternativa B** como
evolución documentada (no implementada).

Estructura:

- `roles`: base globales (`company_id = NULL`): `owner`, `admin`, `operator`, `viewer`; personalizados por empresa.
- `permissions`: catálogo global con convención `modulo.accion`.
- `permission_role`: relación muchos a muchos.
- `company_user.role_id`: rol del usuario **dentro** de cada empresa.
- Verificación con `Gate`/`Policy` en backend; la interfaz solo oculta/deshabilita.

**Nota:** `user_permission` queda **fuera del alcance** del Sprint 1 salvo
aprobación explícita; su necesidad se reevalúa con casos reales.

## 4. Consecuencias

- **Positivas:** modelo simple, auditable y alineado con DD-04.
- **Negativas:** las excepciones por usuario requieren un rol específico hasta que exista `user_permission`.
- **Neutras:** la matriz de permisos (§8 del plan) es la fuente de verdad inicial.

## 5. Criterios de aceptación de la decisión

- [x] Se aprueba DD-04 (roles base globales + por empresa).
- [x] Se aprueba el alcance sin `user_permission` para el Sprint 1.
- [x] La matriz de permisos del plan queda ratificada.
- [x] Se documenta la convención `modulo.accion` en el catálogo de permisos.

## 6. Estado

Aprobada el **2026-11-13**, junto con DD-04. El modelo de roles, permisos y
asignación por empresa se implementó en **US-0102** (`IdentitySeeder`,
`permission_role` y `company_user.role_id`). La aplicación de permisos en todos
los módulos corresponde a **US-0103**. `user_permission` queda fuera del alcance
del Sprint 1.
