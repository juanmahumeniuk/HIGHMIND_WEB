# CHANGES — Tarea 9.05: Diseño de Matriz de Pruebas Funcionales

**Épica:** 9  
**Rama:** `9.05`  
**Fecha:** Mayo 2026  
**Equipo:** HIGHMIND

---

## Objetivo

Diseñar la documentación de pruebas funcionales del proyecto mediante una **matriz de casos de prueba de caja negra (Black-box)** que detalle pasos, estímulos y resultados esperados para los flujos críticos de usuario, cubriendo tanto escenarios exitosos como casos de error.

---

## Archivos creados

### `docs/pruebas/matriz-pruebas-funcionales.md`

Matriz de 23 casos de prueba distribuidos en 6 flujos críticos del sistema:

#### Flujo 1 — Registro de usuario (TC-01 a TC-04)
- `TC-01` — Registro exitoso con datos válidos
- `TC-02` — Registro con email ya existente (error 409)
- `TC-03` — Registro con campos obligatorios vacíos (error 400)
- `TC-04` — Registro con formato de email inválido (error 400)

#### Flujo 2 — Login y Logout (TC-05 a TC-08)
- `TC-05` — Login exitoso con credenciales correctas
- `TC-06` — Login con contraseña incorrecta (error 401)
- `TC-07` — Login con usuario inexistente (error 401, mensaje genérico)
- `TC-08` — Logout destruye sesión y redirige correctamente

#### Flujo 3 — Catálogo de productos (TC-09 a TC-11)
- `TC-09` — Carga correcta del catálogo con todos los productos
- `TC-10` — Producto sin stock muestra indicador visual y deshabilita el botón
- `TC-11` — Visualización del detalle de producto en modal

#### Flujo 4 — Carrito de compras (TC-12 a TC-16)
- `TC-12` — Agregar producto al carrito (usuario logueado)
- `TC-13` — Modificar cantidad de un ítem y recalcular subtotal
- `TC-14` — Eliminar un ítem individual del carrito
- `TC-15` — Vaciar el carrito completo
- `TC-16` — Intento de agregar al carrito sin sesión activa (error 401)

#### Flujo 5 — Formulario de contacto (TC-17 a TC-19)
- `TC-17` — Envío exitoso de mensaje con persistencia en BD
- `TC-18` — Envío con campos obligatorios vacíos (error 400)
- `TC-19` — Envío con formato de email inválido (error 400)

#### Flujo 6 — Checkout con Mercado Pago (TC-20 a TC-23)
- `TC-20` — Pago aprobado en modo sandbox (validación contra API de MP)
- `TC-21` — Pago rechazado en modo sandbox
- `TC-22` — Checkout bloqueado por `CHECKOUT_PAYMENTS_ENABLED=false`
- `TC-23` — Intento de acceso al checkout con carrito vacío

---

## Distribución de casos

| Flujo | Casos totales | Positivos | Negativos |
|-------|:---:|:---:|:---:|
| Registro | 4 | 1 | 3 |
| Login / Logout | 4 | 2 | 2 |
| Catálogo | 3 | 2 | 1 |
| Carrito | 5 | 3 | 2 |
| Contacto | 3 | 1 | 2 |
| Checkout MP | 4 | 1 | 3 |
| **Total** | **23** | **10** | **13** |

---

## Notas

- Todos los casos tienen estado inicial **Pendiente** — la ejecución de las pruebas queda fuera del alcance de esta tarea.
- Los casos del flujo de Checkout (TC-20 y TC-21) requieren credenciales TEST de Mercado Pago y el entorno configurado con `CHECKOUT_PAYMENTS_ENABLED=true`.
- El caso TC-07 (usuario inexistente) valida que el mensaje de error sea genérico para no revelar si un email está registrado en el sistema (práctica de seguridad).
- Los casos TC-03, TC-04, TC-18 y TC-19 verifican el correcto funcionamiento de `Input.php` (`app/Core/Input.php`) en la sanitización y validación de entradas.
