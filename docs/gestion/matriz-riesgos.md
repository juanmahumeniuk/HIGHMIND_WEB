# Matriz de Riesgos — HIGHMIND Web

**Proyecto:** HIGHMIND — Tienda online de indumentaria  
**Épica:** 9  
**Tarea:** 9.04 — Actualización de Gestión (Riesgos y Costo-Beneficio)  
**Fecha de revisión:** Mayo 2026  
**Responsable de gestión:** Equipo HIGHMIND

---

## Escala de valoración

### Probabilidad

| Nivel | Descripción |
|-------|-------------|
| Alta | Es probable que ocurra durante el ciclo de vida del proyecto (>60%) |
| Media | Puede ocurrir bajo ciertas condiciones (30–60%) |
| Baja | Poco probable que ocurra (<30%) |

### Impacto

| Nivel | Descripción |
|-------|-------------|
| Alto | Afecta funcionalidad crítica, genera pérdida económica o compromete la seguridad del sistema |
| Medio | Afecta parcialmente la operación pero tiene solución alternativa disponible |
| Bajo | Efecto menor, no afecta la operación ni la experiencia del usuario de forma significativa |

### Nivel de riesgo (Probabilidad × Impacto)

|               | Impacto Bajo | Impacto Medio | Impacto Alto |
|---------------|-------------|--------------|-------------|
| **Prob. Alta**  | Medio       | Alto         | Crítico      |
| **Prob. Media** | Bajo        | Medio        | Alto         |
| **Prob. Baja**  | Bajo        | Bajo         | Medio        |

---

## Matriz de Riesgos

### Riesgos de Integración de API (foco principal)

| ID | Descripción del Riesgo | Categoría | Probabilidad | Impacto | Nivel de Riesgo | Plan de Mitigación | Estado |
|----|------------------------|-----------|-------------|---------|----------------|-------------------|--------|
| R01 | **Caída o indisponibilidad de Firebase Authentication API.** Los usuarios no pueden iniciar sesión ni completar el flujo de autenticación con Google. | Técnico / API | Baja | Alto | Medio | Implementar mensajes de error descriptivos ante fallos de conexión. Configurar timeout en `FirebaseClient.php`. Dado el SLA de Google (99.95%), el riesgo residual es aceptable. | Activo |
| R02 | **Cambios de versión o deprecación en la API de Mercado Pago.** Los endpoints actuales (`/v1/payments`) dejan de funcionar, inutilizando el checkout. | Técnico / API | Media | Alto | Alto | Suscribirse a las notificaciones de cambio del [Dev Center de Mercado Pago](https://developers.mercadopago.com). Documentar los endpoints utilizados. Ejecutar tests en sandbox ante cada actualización del proyecto. | Activo |
| R03 | **Expiración del ID token de Firebase durante una sesión activa.** Los tokens expiran a la hora; si el backend recibe un token vencido, rechaza la operación. | Técnico / Seguridad | Alta | Bajo | Medio | El Firebase SDK renueva el token automáticamente antes de que expire. El backend valida el token en cada request sensible. El flujo de `login.js` ya contempla la renovación. | Mitigado |
| R04 | **Rate limiting de APIs externas bajo alta concurrencia.** Firebase y Mercado Pago aplican cuotas de requests; superarlas bloquea temporalmente las operaciones. | Técnico / Rendimiento | Baja | Medio | Bajo | El plan Spark de Firebase permite 100K verificaciones/día. En la etapa actual del proyecto (tráfico bajo), el riesgo es mínimo. Monitorear cuotas en el panel de Firebase Console antes de escalar. | Activo |
| R05 | **Latencia elevada en la verificación de tokens contra la REST API de Firebase.** Cada autenticación implica un round-trip HTTP a `identitytoolkit.googleapis.com`, lo que puede aumentar el tiempo de respuesta. | Técnico / Rendimiento | Media | Bajo | Bajo | Latencia esperada < 200 ms en condiciones normales. Si se detecta degradación, considerar cache de sesión local con TTL corto. | Activo |
| R06 | **Fallo en la validación de pago del lado del servidor.** El cliente reporta pago exitoso pero el backend no verifica el estado real contra `/v1/payments/{id}`, generando cobros fantasma o pedidos sin cobrar. | Financiero / API | Media | Alto | Alto | `PagoController.php` debe validar el estado del pago (`approved`) directamente contra la API de Mercado Pago antes de confirmar la orden. Registrar logs de cada transacción con su `payment_id`. | Activo |

---

### Riesgos de Seguridad

| ID | Descripción del Riesgo | Categoría | Probabilidad | Impacto | Nivel de Riesgo | Plan de Mitigación | Estado |
|----|------------------------|-----------|-------------|---------|----------------|-------------------|--------|
| R07 | **Exposición de credenciales sensibles en el repositorio.** Las claves de Firebase (`FIREBASE_API_KEY`) y de Mercado Pago (`MP_ACCESS_TOKEN`) en el `.env` podrían ser commiteadas accidentalmente. | Seguridad | Baja | Alto | Medio | El archivo `.env` está incluido en `.gitignore`. El repositorio solo contiene `.env.example` con valores de placeholder. Rotación de claves si se detecta exposición. | Mitigado |
| R08 | **Ataques XSS sobre el frontend.** Inputs de usuario renderizados sin escapar podrían inyectar scripts maliciosos. | Seguridad | Baja | Alto | Medio | Task 9.03 implementó sanitización en `Input.php` para todos los endpoints. El frontend evita uso de `innerHTML` con datos del usuario. | Mitigado |
| R09 | **Ataques CSRF sobre endpoints de mutación.** Requests forjados desde sitios externos podrían ejecutar operaciones en nombre del usuario autenticado. | Seguridad | Baja | Alto | Medio | `Csrf.php` genera y valida tokens en todos los POST/PUT/DELETE. Implementado desde las primeras épicas. | Mitigado |

---

### Riesgos Operacionales

| ID | Descripción del Riesgo | Categoría | Probabilidad | Impacto | Nivel de Riesgo | Plan de Mitigación | Estado |
|----|------------------------|-----------|-------------|---------|----------------|-------------------|--------|
| R10 | **Dependencia total en Google como proveedor de identidad.** Si Google interrumpe el servicio de OAuth o modifica las condiciones de Firebase, el sistema de login queda inutilizable. | Operacional | Baja | Alto | Medio | A largo plazo, contemplar un método de autenticación alternativo (login con email/password propio). En el corto plazo, el SLA de Google hace el riesgo residual aceptable. | Activo |
| R11 | **Pérdida de disponibilidad del servidor de hosting.** El hosting anterior se venció; durante la transición a un nuevo proveedor, el sistema no estará disponible públicamente. | Operacional | Alta | Medio | Alto | Priorizar la contratación del nuevo hosting y dominio. Documentar el proceso de deploy en `readme.md` para facilitar la migración. | Activo |
| R12 | **Inconsistencia entre entornos de desarrollo y producción.** Variables de `.env` distintas entre ambientes pueden causar comportamientos inesperados en producción (ej. `FORCE_HTTPS`, `CHECKOUT_PAYMENTS_ENABLED`). | Operacional | Media | Medio | Medio | Mantener `.env.example` actualizado con todas las variables necesarias. Revisar configuración antes de cada deploy. | Activo |

---

## Resumen de niveles de riesgo

| Nivel | Cantidad | IDs |
|-------|---------|-----|
| Crítico | 0 | — |
| Alto | 3 | R02, R06, R11 |
| Medio | 6 | R01, R03, R07, R08, R09, R10, R12 |
| Bajo | 2 | R04, R05 |

---

## Historial de revisiones

| Versión | Fecha | Cambios | Autor |
|---------|-------|---------|-------|
| 1.0 | Mayo 2026 | Creación inicial de la matriz con foco en integración de APIs (Firebase y Mercado Pago) | Equipo HIGHMIND |
