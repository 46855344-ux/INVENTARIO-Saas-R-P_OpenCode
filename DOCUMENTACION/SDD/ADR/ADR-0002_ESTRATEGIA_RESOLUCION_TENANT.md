# ADR-0002 — Estrategia de resolución de la empresa activa (tenant)

**Proyecto:** SaaS Inversiones R&P
**Estado:** Aprobada — pendiente de implementación (US-0104)
**Fecha:** 2026-11-13
**Sprint:** 1 (EP-01)
**Historias afectadas:** US-0102, US-0104
**Documentos relacionados:** `SDD-04_DISENO_BASE_DATOS.md` §2, `PLAN_SPRINT_1_IDENTIDAD_MULTIEMPRESA.md` §6.2

---

## 1. Contexto

Un usuario puede pertenecer a varias empresas (`company_user`, DD-01). El backend
debe resolver **cuál** es la empresa activa para cada petición y garantizar que
**nunca** se pueda cruzar el ámbito manipulando la solicitud. `SDD-04` ya define
una base de datos compartida con discriminador `company_id`.

## 2. Alternativas evaluadas

| Alternativa | Descripción | Ventajas | Desventajas |
| --- | --- | --- | --- |
| **A. Cabecera validada `X-Company-Id`** | El cliente envía la empresa activa; el backend valida pertenencia | Explícito; permite cambiar de empresa sin re-login; fácil de probar | Requiere validación estricta en cada petición |
| **B. Empresa activa en sesión** | Al elegir empresa, se guarda en la sesión/token | No viaja en cada petición | Cambiar de empresa exige endpoint adicional; estado oculto |
| **C. Subdominio por empresa** | `empresa-a.app.com` | Aislamiento visual claro | Mayor complejidad de despliegue (DNS, certificados) |
| **D. Una sola empresa por usuario** | Se elimina la multipertenencia | Modelo más simple | Contradice DD-01 y el propósito SaaS |

## 3. Decisión propuesta

**Adoptar la alternativa A — cabecera `X-Company-Id` validada en servidor**, con
un endpoint `GET /api/v1/companies` que lista las empresas del usuario.

Reglas obligatorias:

1. El `company_id` del **cuerpo** de la petición se ignora siempre.
2. El backend verifica que exista una fila en `company_user` con estado `active`.
3. Si la empresa no pertenece al usuario o está inactiva → `403 FORBIDDEN_TENANT`.
4. El `TenantContext` expone la empresa activa a los `TenantScope` de los modelos.
5. Las pruebas T-08 y T-09 verifican que no se puede cruzar el ámbito.

## 4. Consecuencias

- **Positivas:** explícito, testeable y compatible con una base compartida.
- **Negativas:** cada petición autenticada debe incluir la cabecera; el frontend debe gestionarla.
- **Neutras:** no obliga a subdominios; se puede evolucionar a C en el futuro.

## 5. Criterios de aceptación de la decisión

- [x] El Líder técnico aprueba la alternativa A.
- [x] Se documenta el contrato de `GET /api/v1/companies` y la cabecera.
- [ ] El `TenantContext` y el `TenantScope` están implementados (US-0104).
- [ ] Las pruebas de aislamiento (T-08, T-09) están implementadas (US-0104).

## 6. Estado

Aprobada el **2026-11-13**. La resolución de la empresa activa y el aislamiento
de datos se implementarán en **US-0104**; US-0101 no requiere `X-Company-Id`
porque solo devuelve las empresas del usuario en el perfil de sesión.
