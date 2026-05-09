# Cambios — Rama 9.01

## Tarea: Refinamiento de autenticación y sesiones

Reemplaza el sistema de login/registro local por autenticación tercerizada con **Firebase Authentication (Google Sign-In)** y finaliza el flujo de redirecciones post-login con persistencia del carrito entre tienda y checkout.

---

## Archivos nuevos

| Archivo | Descripción |
|---------|-------------|
| `app/Core/FirebaseClient.php` | Verifica ID tokens contra la REST API de Firebase sin librerías externas |
| `app/Controllers/FirebaseController.php` | Recibe el token del frontend, valida CSRF, busca o crea el usuario en BD y establece la sesión PHP |
| `database/migrations/002_add_firebase_uid.sql` | Agrega columna `firebase_uid` a la tabla `usuarios` y vuelve `password` nullable |

## Archivos modificados

| Archivo | Qué cambió |
|---------|------------|
| `app/Controllers/UsuarioController.php` | Se eliminaron las acciones `login` y `register` (ahora las maneja Firebase); se mantienen `csrf`, `check` y `logout` |
| `app/Core/Router.php` | Se agregó la ruta `/api/firebase` |
| `app/Models/Usuario.php` | Se agregaron métodos: `buscarPorFirebaseUid`, `crearDesdeFirebase`, `vincularFirebaseUid` |
| `public_html/frontend/login.html` | Reemplaza formularios de login/registro por botón "Ingresar con Google" + SDK Firebase v9 compat |
| `public_html/frontend/js/login.js` | Flujo: popup Google → ID token → POST al backend → sesión PHP → redirect |
| `public_html/frontend/js/config.js` | Se agrega `window.FIREBASE_CONFIG` con las credenciales públicas del proyecto Firebase |
| `public_html/frontend/css/estilo.css` | Estilos para el nuevo botón de login y subtítulo |
| `.env.example` | Se reemplazaron variables de Auth0 por `FIREBASE_API_KEY` y `FIREBASE_PROJECT_ID` |

---

## Flujo de autenticación

```
1. Usuario hace click en "Ingresar con Google"
2. Firebase SDK abre un popup de Google
3. El usuario se autentica con su cuenta Google
4. Firebase devuelve un ID token al frontend
5. El frontend hace POST /api/firebase?action=verify con {id_token, csrf_token}
6. El backend verifica el token contra Firebase REST API
7. El backend busca o crea el usuario en la BD por firebase_uid
8. El backend establece $_SESSION[usuario_id / nombre / email]
9. El frontend redirige a postLoginRedirect (o index.html por defecto)
```

## Flujo carrito → login → checkout (preservado)

```
1. Usuario sin sesión intenta "Finalizar compra"
2. carrito.js guarda postLoginRedirect + openCartOnLoad en sessionStorage
3. Redirige a login.html
4. Usuario se loguea con Google
5. login.js lee postLoginRedirect y redirige a tienda.html
6. carrito.js detecta openCartOnLoad y abre el carrito automáticamente
7. Usuario completa el checkout con sesión activa ✓
```

---

## Migración de base de datos requerida

```sql
ALTER TABLE usuarios
    ADD COLUMN firebase_uid VARCHAR(128) NULL DEFAULT NULL UNIQUE AFTER email,
    MODIFY COLUMN password VARCHAR(255) NULL DEFAULT NULL;
```

## Variables de entorno requeridas

Agregar al `.env`:
```
FIREBASE_API_KEY=tu-api-key
FIREBASE_PROJECT_ID=tu-project-id
```

---

## Cómo testear localmente

```bash
php -S localhost:8000 -t public_html public_html/router-dev.php
```

| # | Acción | Resultado esperado |
|---|--------|--------------------|
| 1 | Ir a `/frontend/login.html` | Ver botón "Ingresar con Google" |
| 2 | Click en el botón | Se abre popup de Google |
| 3 | Loguearse con cuenta Google | Sesión activa, redirige a `index.html` |
| 4 | Ver navbar en cualquier página | Muestra "Nombre (Cerrar sesión)" |
| 5 | Intentar checkout sin sesión | Redirige a login |
| 6 | Loguearse desde ese redirect | Vuelve a tienda con carrito abierto |
| 7 | Click en "Cerrar sesión" | Cierra sesión PHP + Firebase |
| 8 | Repetir login con la misma cuenta | No duplica el usuario en BD |
