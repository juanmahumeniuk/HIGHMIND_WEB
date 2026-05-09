# Matriz de Pruebas Funcionales — HIGHMIND Web

**Proyecto:** HIGHMIND — Tienda online de indumentaria  
**Épica:** 9  
**Tarea:** US-9.05 — Diseño de Matriz de Pruebas Funcionales  
**Tipo de prueba:** Caja negra (Black-box)  
**Fecha:** Mayo 2026  
**Responsable:** Equipo HIGHMIND

---

## Criterios de evaluación

| Estado | Descripción |
|--------|-------------|
| Pendiente | El caso aún no fue ejecutado |
| Aprobado | El resultado obtenido coincide con el esperado |
| Fallido | El resultado obtenido difiere del esperado |
| Bloqueado | No se puede ejecutar por una dependencia externa |

---

## Flujo 1: Registro de usuario

| ID | Descripción | Precondiciones | Pasos | Estímulo | Resultado esperado | Estado |
|----|-------------|----------------|-------|----------|--------------------|--------|
| TC-01 | Registro exitoso con datos válidos | El usuario no tiene cuenta. La BD está disponible. | 1. Ir a la pantalla de registro. 2. Ingresar nombre, email válido y contraseña segura. 3. Confirmar contraseña. 4. Hacer clic en "Registrarse". | POST `/api/usuarios/register` con datos válidos | Se crea el usuario en la BD. Se inicia sesión automáticamente. Se redirige al catálogo. | Pendiente |
| TC-02 | Registro con email ya existente | Existe un usuario con el email `test@highmind.com`. | 1. Ir a la pantalla de registro. 2. Ingresar el email ya registrado. 3. Completar el resto de los campos. 4. Hacer clic en "Registrarse". | POST `/api/usuarios/register` con email duplicado | La API devuelve error `409`. Se muestra mensaje: "El email ya está registrado". No se crea ningún usuario nuevo. | Pendiente |
| TC-03 | Registro con campos obligatorios vacíos | Ninguna. | 1. Ir a la pantalla de registro. 2. Dejar nombre y/o email vacíos. 3. Hacer clic en "Registrarse". | POST `/api/usuarios/register` con campos faltantes | La API devuelve error `400`. Se muestran mensajes de validación por campo faltante. No se crea ningún usuario. | Pendiente |
| TC-04 | Registro con formato de email inválido | Ninguna. | 1. Ir a la pantalla de registro. 2. Ingresar `usuariosinarroba.com` como email. 3. Completar el resto de los campos. 4. Hacer clic en "Registrarse". | POST `/api/usuarios/register` con email malformado | La API devuelve error `400`. Se muestra mensaje: "El email ingresado no es válido". No se crea ningún usuario. | Pendiente |

---

## Flujo 2: Login y Logout

| ID | Descripción | Precondiciones | Pasos | Estímulo | Resultado esperado | Estado |
|----|-------------|----------------|-------|----------|--------------------|--------|
| TC-05 | Login exitoso con credenciales correctas | Existe el usuario `test@highmind.com` con contraseña conocida. | 1. Ir a la pantalla de login. 2. Ingresar email y contraseña correctos. 3. Hacer clic en "Iniciar sesión". | POST `/api/usuarios/login` con credenciales válidas | Se inicia sesión. El ID de sesión se regenera. Se devuelve token CSRF. Se redirige al catálogo. El navbar muestra el nombre del usuario. | Pendiente |
| TC-06 | Login con contraseña incorrecta | Existe el usuario `test@highmind.com`. | 1. Ir a la pantalla de login. 2. Ingresar email correcto y contraseña errónea. 3. Hacer clic en "Iniciar sesión". | POST `/api/usuarios/login` con contraseña inválida | La API devuelve error `401`. Se muestra mensaje: "Credenciales incorrectas". No se inicia sesión. | Pendiente |
| TC-07 | Login con usuario inexistente | Ninguna. | 1. Ir a la pantalla de login. 2. Ingresar un email no registrado. 3. Hacer clic en "Iniciar sesión". | POST `/api/usuarios/login` con email inexistente | La API devuelve error `401`. Se muestra mensaje genérico de error (sin revelar si el email existe). No se inicia sesión. | Pendiente |
| TC-08 | Logout cierra la sesión correctamente | El usuario está logueado. | 1. Hacer clic en "Cerrar sesión" en el navbar. | POST `/api/usuarios/logout` con token CSRF válido | La sesión PHP se destruye. Se redirige al login. Intentar acceder a rutas protegidas redirige al login. | Pendiente |

---

## Flujo 3: Catálogo de productos

| ID | Descripción | Precondiciones | Pasos | Estímulo | Resultado esperado | Estado |
|----|-------------|----------------|-------|----------|--------------------|--------|
| TC-09 | Carga correcta del catálogo | La BD tiene al menos 3 productos con stock > 0. | 1. Ir a la página del catálogo. | GET `/api/productos` | Se renderizan las tarjetas de producto con imagen, nombre, precio y botón "Agregar al carrito". El tiempo de respuesta es < 2 segundos. | Pendiente |
| TC-10 | Producto sin stock muestra indicador visual | Existe un producto con `stock = 0` en la BD. | 1. Ir a la página del catálogo. 2. Localizar el producto sin stock. | GET `/api/productos` (incluye producto sin stock) | El producto se muestra con indicador "Sin stock". El botón "Agregar al carrito" está deshabilitado para ese producto. | Pendiente |
| TC-11 | Visualización del detalle de un producto | La BD tiene al menos un producto disponible. | 1. Ir al catálogo. 2. Hacer clic en una tarjeta de producto. | Apertura del modal de detalle de producto | Se abre el modal con imagen ampliada, descripción completa, precio y selector de cantidad. | Pendiente |

---

## Flujo 4: Carrito de compras

| ID | Descripción | Precondiciones | Pasos | Estímulo | Resultado esperado | Estado |
|----|-------------|----------------|-------|----------|--------------------|--------|
| TC-12 | Agregar producto al carrito | El usuario está logueado. El producto tiene stock disponible. | 1. Ir al catálogo. 2. Hacer clic en "Agregar al carrito" de un producto. | POST `/api/carrito` con `producto_id` y `cantidad = 1` | El ítem se agrega a la BD. El contador del carrito en el navbar se incrementa. Se muestra notificación de éxito. | Pendiente |
| TC-13 | Modificar cantidad de un ítem del carrito | El usuario está logueado. Hay al menos un ítem en el carrito. | 1. Abrir el modal del carrito. 2. Cambiar la cantidad de un ítem a 3. | PUT `/api/carrito/{id}` con `cantidad = 3` | La cantidad se actualiza en la BD. El subtotal del carrito se recalcula correctamente. | Pendiente |
| TC-14 | Eliminar un ítem del carrito | El usuario está logueado. Hay al menos un ítem en el carrito. | 1. Abrir el modal del carrito. 2. Hacer clic en el botón de eliminar de un ítem. | DELETE `/api/carrito/{id}` con token CSRF | El ítem se elimina de la BD. El carrito se actualiza. Si era el último ítem, se muestra el estado "Carrito vacío". | Pendiente |
| TC-15 | Vaciar el carrito completo | El usuario está logueado. El carrito tiene 2 o más ítems. | 1. Abrir el modal del carrito. 2. Hacer clic en "Vaciar carrito". | DELETE `/api/carrito` con token CSRF | Todos los ítems del usuario se eliminan de la BD. El modal muestra "Carrito vacío". El contador del navbar pasa a 0. | Pendiente |
| TC-16 | Intentar agregar al carrito sin estar logueado | El usuario no tiene sesión activa. | 1. Ir al catálogo sin iniciar sesión. 2. Hacer clic en "Agregar al carrito". | POST `/api/carrito` sin sesión activa | La API devuelve error `401`. Se redirige al login o se muestra un mensaje indicando que se debe iniciar sesión. | Pendiente |

---

## Flujo 5: Formulario de contacto

| ID | Descripción | Precondiciones | Pasos | Estímulo | Resultado esperado | Estado |
|----|-------------|----------------|-------|----------|--------------------|--------|
| TC-17 | Envío de mensaje exitoso | La tabla `contacto_mensajes` existe en la BD. | 1. Ir a la página de contacto. 2. Ingresar nombre, email válido y mensaje. 3. Hacer clic en "Enviar". | POST `/api/contacto` con datos válidos y token CSRF | La API devuelve `200`. El mensaje se persiste en la BD. Se muestra confirmación: "Tu mensaje fue enviado correctamente". | Pendiente |
| TC-18 | Envío con campos obligatorios vacíos | Ninguna. | 1. Ir a la página de contacto. 2. Dejar el campo "mensaje" vacío. 3. Hacer clic en "Enviar". | POST `/api/contacto` con campo faltante | La API devuelve error `400`. Se muestra mensaje de validación por campo faltante. No se persiste ningún registro. | Pendiente |
| TC-19 | Envío con formato de email inválido | Ninguna. | 1. Ir a la página de contacto. 2. Ingresar `sinpuntocom@` como email. 3. Completar nombre y mensaje. 4. Hacer clic en "Enviar". | POST `/api/contacto` con email malformado | La API devuelve error `400`. Se muestra mensaje: "El email ingresado no es válido". No se persiste ningún registro. | Pendiente |

---

## Flujo 6: Checkout con Mercado Pago

| ID | Descripción | Precondiciones | Pasos | Estímulo | Resultado esperado | Estado |
|----|-------------|----------------|-------|----------|--------------------|--------|
| TC-20 | Pago aprobado en modo sandbox | El usuario está logueado. El carrito tiene al menos un ítem. `CHECKOUT_PAYMENTS_ENABLED=true`. Credenciales TEST de Mercado Pago configuradas. | 1. Ir al checkout. 2. Completar el formulario con tarjeta de prueba aprobada (ej. `4509 9535 6623 3704`). 3. Hacer clic en "Pagar". | POST `/api/pagos` → Mercado Pago `/v1/payments` | El servidor valida el estado `approved` contra la API de MP. Se muestra pantalla de confirmación de compra. El carrito se vacía. | Pendiente |
| TC-21 | Pago rechazado en modo sandbox | El usuario está logueado. El carrito tiene al menos un ítem. `CHECKOUT_PAYMENTS_ENABLED=true`. Credenciales TEST de Mercado Pago configuradas. | 1. Ir al checkout. 2. Completar el formulario con tarjeta de prueba rechazada. 3. Hacer clic en "Pagar". | POST `/api/pagos` → Mercado Pago `/v1/payments` (estado `rejected`) | El servidor recibe estado `rejected`. Se muestra mensaje de error: "El pago fue rechazado. Verificá los datos de tu tarjeta.". El carrito no se vacía. | Pendiente |
| TC-22 | Checkout bloqueado por configuración | `CHECKOUT_PAYMENTS_ENABLED=false` en `.env`. | 1. Ir a la página de checkout estando logueado. | GET de la página de checkout | Se muestra aviso informando que los pagos están temporalmente deshabilitados. El formulario de pago no se renderiza. | Pendiente |
| TC-23 | Intento de pago sin ítems en el carrito | El usuario está logueado. El carrito está vacío. | 1. Intentar acceder directamente a la URL de checkout. | GET de la página de checkout con carrito vacío | Se redirige al catálogo o se muestra mensaje: "Tu carrito está vacío". No se renderiza el formulario de pago. | Pendiente |

---

## Resumen de cobertura

| Flujo | Casos totales | Casos positivos | Casos negativos |
|-------|:---:|:---:|:---:|
| Registro de usuario | 4 | 1 | 3 |
| Login y Logout | 4 | 2 | 2 |
| Catálogo de productos | 3 | 2 | 1 |
| Carrito de compras | 5 | 3 | 2 |
| Formulario de contacto | 3 | 1 | 2 |
| Checkout Mercado Pago | 4 | 1 | 3 |
| **Total** | **23** | **10** | **13** |

---

## Historial de revisiones

| Versión | Fecha | Cambios | Autor |
|---------|-------|---------|-------|
| 1.0 | Mayo 2026 | Creación inicial de la matriz con 23 casos de prueba para los 6 flujos críticos | Equipo HIGHMIND |
