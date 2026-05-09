# HIGHMIND — Tienda online

Tienda de ropa con catálogo, carrito por usuario, autenticación, contacto y checkout con **Mercado Pago** embebido (Card Payment Brick). El backend es **PHP 8** en estilo MVC; el frontend es estático (**HTML, CSS, JS**) bajo `public_html/frontend/`. Pensado para despliegue en hosting compartido (Apache + MySQL/MariaDB), con la lógica de aplicación fuera del document root. aaa

---

## Arquitectura

| Capa | Ubicación |
|------|-----------|
| Aplicación PHP | [`app/`](app/) — controladores, modelos, núcleo (`Env`, `Database`, `Router`, `Session`, `Csrf`, `Input`, etc.) |
| Document root | [`public_html/`](public_html/) — estáticos, `.htaccess` y **front controller** de la API |
| API HTTP | `GET/POST …/api/{recurso}` → [`public_html/api/index.php`](public_html/api/index.php) → [`app/Core/Router.php`](app/Core/Router.php) |

Recursos expuestos por la API (segmento inicial del path):

- `productos` — catálogo (JSON)
- `usuarios` — registro, login, logout, sesión, CSRF
- `carrito` — ítems del usuario logueado
- `contacto` — envío de mensaje (persistencia en BD si existe la tabla)
- `pagos` — configuración Mercado Pago y creación de pagos vía API `/v1/payments`

Variables de entorno: copiá [`.env.example`](.env.example) a **`.env`** (no versionado) y completá credenciales.

---

## Características

- **Catálogo** cargado desde MySQL con PDO y consultas preparadas.
- **Carrito** asociado al usuario; subtotal, cantidades y vaciado desde el modal.
- **Registro / login** con sesión PHP (cookie `HttpOnly`, `Secure` bajo HTTPS, `SameSite`), regeneración de ID al iniciar sesión y tokens **CSRF** en operaciones que mutan estado.
- **Contacto** con POST a la API, sanitización de entradas y tabla opcional `contacto_mensajes` ([`database/migrations/001_contacto_mensajes.sql`](database/migrations/001_contacto_mensajes.sql)).
- **Pagos** con SDK Mercado Pago (brick de tarjeta) y servidor que cobra con token; modo prueba detectable en UI; candado **`CHECKOUT_PAYMENTS_ENABLED`** para desactivar cobros por configuración.
- **Frontend responsive**: navbar con menú móvil, modales de producto y carrito, estilos en [`public_html/frontend/css/estilo.css`](public_html/frontend/css/estilo.css).

---

## Estructura del repositorio

```
Highmind_Web/
├── app/                          # PHP (fuera del document root en producción ideal)
│   ├── bootstrap.php
│   ├── Controllers/
│   ├── Core/
│   └── Models/
├── database/
│   └── migrations/               # SQL incremental (ej. contacto)
├── public_html/                  # Document root del virtual host
│   ├── api/
│   │   └── index.php             # Front controller API
│   ├── frontend/                 # HTML, CSS, JS, img
│   ├── index.html                # Redirección típica a frontend/
│   ├── router-dev.php            # Router para php -S (desarrollo)
│   └── .htaccess                 # Rewrite /api → api/index.php
├── .env.example                  # Plantilla de configuración
├── readme.md
└── …                             # dumps SQL de referencia / import inicial
```

En producción, la carpeta `app/` debe quedar **fuera** de la carpeta servida por Apache si el hosting lo permite; el `bootstrap` ya asume `app` como raíz MVC y carga `.env` desde el directorio padre de `app/`.

---

## Configuración (`.env`)

- **Base de datos:** `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS`, `DB_CHARSET` (opcional `DB_UNIX_SOCKET`).
- **HTTPS:** `FORCE_HTTPS` — en bootstrap se puede forzar redirección a HTTPS.
- **Pagos:** `CHECKOUT_PAYMENTS_ENABLED` (`true` / `false` / `off`, etc.); `MP_PUBLIC_KEY`, `MP_ACCESS_TOKEN`; opcional `MP_API_BASE`.
- Ver comentarios en [`.env.example`](.env.example).

---

## Desarrollo local

Requisitos: **PHP 8+** con extensiones habituales (`pdo_mysql`, `curl` para Mercado Pago), **MariaDB/MySQL**.

1. Creá la base e importá el esquema/datos (por ejemplo dumps del repo o los que uses en tu entorno).
2. Ejecutá migraciones pendientes (ej. `database/migrations/001_contacto_mensajes.sql`) si usás contacto en servidor.
3. Copiá `.env.example` → `.env` y ajustá `DB_*` y, si probás pagos, credenciales **TEST** de Mercado Pago.
4. Servidor embebido PHP (desde la raíz del repo):

```bash
php -S localhost:8080 -t public_html public_html/router-dev.php
```

Navegá a `http://localhost:8080/frontend/` (o el `index` que redirija). Las peticiones a `/api/...` las enruta `router-dev.php`.

Con **Apache**, el document root apunta a `public_html/`; las reglas de [`.htaccess`](public_html/.htaccess) envían `/api` al front controller.

---

## Seguridad (resumen)

- Sesiones endurecidas; **CSRF** en login, registro, logout, carrito mutante, contacto y creación de pagos.
- Entradas normalizadas con [`app/Core/Input.php`](app/Core/Input.php); salidas sensibles en JS sin `innerHTML` inseguro donde aplica.
- Contraseñas con `password_hash` (Argon2id si está disponible, si no bcrypt).
- Consultas SQL con **PDO prepared statements** en modelos.

---

## Mercado Pago

- Claves **`TEST-…`** = sandbox (la UI puede mostrar aviso de modo prueba).
- Producción: credenciales de producción y revisión de `CHECKOUT_PAYMENTS_ENABLED` según política del sitio.

---

## Créditos

Proyecto HIGHMIND. Desarrollo: panITUS. Interfaz inspirada en una estética tipo Material / oscura.
