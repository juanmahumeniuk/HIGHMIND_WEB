# Guía Técnica - Highmind Web

## Arquitectura del Sistema

Patrón **MVC** en PHP puro. Punto de entrada API: [`public_html/api/index.php`](public_html/api/index.php) → `Core\Router` o `AdminController`.

---

## Core y Seguridad

- **`Input.php`**: sanitización de entradas POST.
- **`Csrf.php`**: tokens anti-CSRF en mutaciones.
- **`Controller/AbstractController.php`**: sesión, validación de método, CSRF en POST, respuestas JSON (`jsonOk`, `jsonError`, `jsonBody`).
- **`Controller/AuthenticatedController.php`**: extiende lo anterior con `requireAuth()` para endpoints que exigen sesión.
- **`Database.php`**: PDO singleton.
- **`FirebaseClient.php`**: verificación de ID tokens y creación de usuarios vía REST Identity Toolkit.

---

## Capa Model

- **`BaseModel.php`**: acceso PDO centralizado (`fetchOne`, `fetchAll`, `execute`, `insert`, `exists`).
- **`Contracts/AdminListable.php`**, **`Contracts/AdminReadable.php`**: contratos para listado y lectura en panel admin.
- Modelos concretos: `Producto`, `Usuario`, `Carrito`, `ContactoMensaje`.

---

## Capa Admin

- **`AdminController.php`**: enrutador; exige `AdminAuth::require()` y delega por entidad.
- **`Controllers/Admin/AbstractAdminHandler.php`**: plantilla REST (GET list/item, POST create/update/delete).
- Handlers: `ProductoAdminHandler`, `UsuarioAdminHandler`, `CarritoAdminHandler`, `ContactoAdminHandler`.
- **`Services/ProductImageUploader.php`**: subida y validación de imágenes de producto.

---

## Endpoints de la API

### Firebase (`FirebaseController.php`)

| Acción | Método | Parámetros | Descripción |
| --- | --- | --- | --- |
| `verify` | POST | `id_token`, `csrf_token` | Verifica token Firebase, crea/vincula usuario en MySQL, establece sesión PHP. |

### Usuarios (`UsuarioController.php`)

| Acción | Método | Parámetros | Descripción |
| --- | --- | --- | --- |
| `csrf` | GET | — | Token CSRF. |
| `check` | GET | — | Estado de sesión (datos desde MySQL). |
| `logout` | POST | `csrf_token` | Cierra sesión PHP. |

### Productos (`ProductoController.php`)

| Acción | Método | Descripción |
| --- | --- | --- |
| GET | — | Catálogo activo en JSON. |

### Carrito (`CarritoController.php`)

*Requiere sesión. POST requiere `csrf_token`.*

| Acción | Método | Parámetros |
| --- | --- | --- |
| `get` | GET | — |
| `add` | POST | `id`, `qty`, `csrf_token` |
| `update` | POST | `id`, `qty`, `csrf_token` |
| `remove` | POST | `id`, `csrf_token` |
| `clear` | POST | `csrf_token` |

### Pagos (`PagoController.php`)

| Acción | Método | Descripción |
| --- | --- | --- |
| `config` | GET | Clave pública Mercado Pago. |
| `create` | POST | Crea pago con token de tarjeta. |

### Contacto (`ContactoController.php`)

| Acción | Método | Parámetros |
| --- | --- | --- |
| POST | — | `nombre`, `email`, `mensaje`, `csrf_token` |

### Admin (`/api/admin/{recurso}`)

CRUD de productos, usuarios, carrito_items, contacto_mensajes. Requiere sesión con `es_admin = 1`.

Creación de usuarios: el backend llama `FirebaseClient::signUp` y guarda `firebase_uid` en MySQL.

---

## Frontend JS

Orden de carga recomendado: `config.js` → `api-client.js` → `dom.js` → `auth.js` → script de página → `navbar.js`.

| Archivo | Rol |
| --- | --- |
| `config.js` | Firebase config, `apiUrl`, CSRF, `safeImgSrc` |
| `api-client.js` | `apiGet`, `apiPost`, `apiPostFormData`, `apiPostCart`, `fetchCarrito`, badge carrito |
| `dom.js` | `el`, `setFeedback`, `formatPrecio`, `frontendAssetUrl` |
| `auth.js` | Login/logout Firebase, `getSession()`, navbar sesión |
| `script.js` | Catálogo, contacto |
| `carrito.js` | Modal carrito, Mercado Pago |
| `login.js` | Formularios login/registro en `login.html` |
| `admin/admin.js` | Panel admin CRUD |

---

## Estructura del repositorio

| Ruta | Contenido |
| --- | --- |
| `app/` | Controladores, modelos, core, servicios |
| `database/migrations/` | Esquema SQL versionado |
| `database/patches/` | Scripts one-off para BDs existentes |
| `docs/changelog/` | Historial de cambios (`CHANGES-9.xx.md`) |
| `docs/gestion/`, `docs/pruebas/` | Documentación académica / QA |
| `public_html/` | Sitio, admin y front controller API |
| `scripts/run.sh` | Servidor de desarrollo + reset MySQL |
| `tests/e2e/` | Specs Playwright |
