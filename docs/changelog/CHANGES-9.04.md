# CHANGES — Tarea 9.04: Actualización de Gestión (Riesgos y Costo-Beneficio)

**Épica:** 9  
**Rama:** `9.04`  
**Fecha:** Mayo 2026  
**Equipo:** HIGHMIND

---

## Objetivo

Actualizar la documentación de gestión del proyecto para la entrega de Épica 9, abarcando:
1. Revisión y creación de la **matriz de riesgos** con foco en la integración de APIs externas.
2. **Análisis financiero** del proyecto mediante un estudio de costo-beneficio.

---

## Archivos creados

### `docs/gestion/matriz-riesgos.md`

Matriz de riesgos del proyecto con 12 riesgos identificados distribuidos en tres categorías:

- **Riesgos de integración de API** (foco principal de la tarea):
  - `R01` — Caída de Firebase Authentication API
  - `R02` — Cambios o deprecación en la API de Mercado Pago
  - `R03` — Expiración de ID token de Firebase durante sesión activa
  - `R04` — Rate limiting de APIs externas
  - `R05` — Latencia elevada en verificación de tokens
  - `R06` — Fallo en validación de pagos del lado del servidor

- **Riesgos de seguridad:**
  - `R07` — Exposición de credenciales en el repositorio
  - `R08` — Ataques XSS sobre el frontend
  - `R09` — Ataques CSRF sobre endpoints de mutación

- **Riesgos operacionales:**
  - `R10` — Dependencia total en Google como proveedor de identidad
  - `R11` — Pérdida de disponibilidad por vencimiento del hosting
  - `R12` — Inconsistencia entre entornos de desarrollo y producción

Cada riesgo incluye: categoría, probabilidad, impacto, nivel de riesgo (con matriz de valoración) y plan de mitigación.

**Distribución de niveles:**
- Crítico: 0
- Alto: 3 (R02, R06, R11)
- Medio: 7 (R01, R03, R07, R08, R09, R10, R12)
- Bajo: 2 (R04, R05)

---

### `docs/gestion/analisis-costo-beneficio.md`

Análisis financiero del proyecto a 2 años con los siguientes bloques:

- **Costos de desarrollo:** 320 horas totales (4 integrantes × 80 horas) valorizadas a USD 12/hora = **USD 3.840**
- **Costos de infraestructura:** Hosting + dominio = **USD 75/año** (Firebase y SSL gratuitos)
- **Inversión total a 2 años:** USD 3.990
- **Proyección de ingresos:** Basada en 25 a 80 transacciones mensuales con ticket promedio de ARS 18.000 y comisión de Mercado Pago del 3,99%
- **ROI estimado a 2 años:** ~127,7%
- **Punto de equilibrio:** ~17 meses de operación

---

## Notas

- Los valores del análisis financiero son estimaciones referenciales para la entrega académica.
- El tipo de cambio utilizado es ARS 1.050 por USD (referencial mayo 2026).
- Los riesgos R08 (XSS) y R09 (CSRF) fueron mitigados en tareas anteriores (9.03 y épica base respectivamente).
- El riesgo R11 (hosting vencido) es el de mayor urgencia operativa en el momento actual.
