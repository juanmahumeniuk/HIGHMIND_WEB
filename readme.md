# HIGHMIND — Tienda online

Tienda de ropa con catálogo, carrito por usuario, autenticación Firebase (email/contraseña), contacto y checkout con **Mercado Pago** embebido (Card Payment Brick). El backend es **PHP 8** en estilo MVC; el frontend es estático (**HTML, CSS, JS**) bajo `public_html/frontend/`.

---

## Estructura del repositorio

```
├── app/                    # Backend PHP (MVC)
├── database/
│   ├── migrations/         # Esquema y seeds versionados
│   └── patches/            # Correcciones puntuales para BDs existentes
├── docs/
│   ├── changelog/          # Registro de cambios por entrega (CHANGES-9.xx.md)
│   ├── gestion/            # Análisis de riesgos, costo-beneficio
│   ├── pruebas/            # Matrices de pruebas
│   ├── Guia_Tecnica.md
│   └── Manual_Usuario.md
├── public_html/            # Document root (frontend, admin, API)
├── scripts/                # Utilidades de desarrollo (run.sh)
├── tests/e2e/              # Pruebas Playwright (npm run test:e2e)
├── package.json            # Dev: Playwright
├── .env.example
└── run.sh                  # Atajo → scripts/run.sh
```

---

## Arquitectura

| Capa | Ubicación |
|------|-----------|
| Aplicación PHP | [`app/`](app/) — controladores, modelos, núcleo |
| Document root | [`public_html/`](public_html/) — estáticos, `.htaccess` y front controller API |
| API HTTP | `GET/POST …/api/{recurso}` → [`public_html/api/index.php`](public_html/api/index.php) |

Recursos expuestos:

- `productos` — catálogo (JSON)
- `usuarios` — CSRF, verificación de sesión, logout
- `firebase` — verificación de ID token (único punto de creación de sesión)
- `carrito` — ítems del usuario logueado
- `contacto` — mensajes de contacto
- `pagos` — Mercado Pago
- `admin/…` — CRUD administrativo (requiere `es_admin = 1`)

Variables de entorno: copiá [`.env.example`](.env.example) a **`.env`**.

---

## Autenticación (Firebase)

1. Habilitá **Email/Password** en Firebase Console → Authentication → Sign-in method.
2. El frontend usa `signInWithEmailAndPassword` / `createUserWithEmailAndPassword`.
3. El ID token se envía a `POST /api/firebase?action=verify`; el backend crea o vincula el usuario en MySQL y establece la sesión PHP.

**Admin:** creá `admin@admin.com` en Firebase Console. Ejecutá `./run.sh` y confirmá la creación del seed SQL (`es_admin = 1` en MySQL). Iniciá sesión en `/admin/` con las mismas credenciales Firebase.

---

## Base de datos

Migraciones en [`database/migrations/`](database/migrations/):

| Orden | Archivo |
|-------|---------|
| 1 | `000_schema.sql` — esquema base (`firebase_uid`, `es_admin`, sin `password`) |
| 2 | `001_contacto_mensajes.sql` |
| 3 | `003_usuario_admin.sql` — seed admin (opcional) |
| — | `004_drop_password.sql` — solo BDs existentes con columna `password` |

Parches opcionales en [`database/patches/`](database/patches/) para bases ya desplegadas.

`./run.sh` aplica 000 → 001 → 004 (no-op en instalaciones nuevas).

---

## Desarrollo local

Requisitos: **PHP 8+** (`pdo_mysql`, `curl`), **MariaDB/MySQL**.

```bash
./run.sh
# o manualmente:
php -S localhost:8080 -t public_html public_html/router-dev.php
```

Navegá a `http://localhost:8080/frontend/`.

### Pruebas E2E (opcional)

```bash
npm install
npm run test:e2e
```

Los reportes se generan en `playwright-report/` (ignorados por git).

---

## Seguridad (resumen)

- Sesiones PHP con cookie `HttpOnly`, `Secure` bajo HTTPS, `SameSite=Lax`.
- **CSRF** en mutaciones (carrito, contacto, pagos, admin, logout).
- Contraseñas gestionadas **solo por Firebase**; MySQL guarda perfil y permisos.
- PDO con consultas preparadas.

---

## Mercado Pago

- Claves `TEST-…` = sandbox.
- `CHECKOUT_PAYMENTS_ENABLED` desactiva cobros por configuración.

---

## Créditos

Proyecto HIGHMIND. Desarrollo: panITUS.
