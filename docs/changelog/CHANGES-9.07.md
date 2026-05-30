# Cambios — Refactor bases por capa y frontend reutilizable

## Resumen

- Backend: clases base `AbstractController`, `AuthenticatedController`, `BaseModel` e interfaces admin; panel admin dividido en handlers por entidad.
- Frontend: módulos compartidos `api-client.js` y `dom.js`; `auth.js` expone `getSession()`.
- Sin cambios en contratos HTTP ni respuestas JSON visibles (`ok`, `msg`, `items`, `item`).

## Archivos nuevos

| Archivo | Descripción |
|---------|-------------|
| `app/Core/Controller/AbstractController.php` | Sesión, CSRF, helpers JSON, `jsonBody()` |
| `app/Core/Controller/AuthenticatedController.php` | `requireAuth()` para carrito y pagos |
| `app/Models/BaseModel.php` | Helpers PDO (`fetchOne`, `fetchAll`, `execute`, …) |
| `app/Models/Contracts/AdminListable.php` | Contrato `adminListar()` |
| `app/Models/Contracts/AdminReadable.php` | Contrato `adminObtener(int $id)` |
| `app/Services/ProductImageUploader.php` | Upload de imágenes de producto (admin) |
| `app/Controllers/Admin/AbstractAdminHandler.php` | Plantilla REST admin |
| `app/Controllers/Admin/ProductoAdminHandler.php` | CRUD productos |
| `app/Controllers/Admin/UsuarioAdminHandler.php` | CRUD usuarios |
| `app/Controllers/Admin/CarritoAdminHandler.php` | Gestión carrito admin |
| `app/Controllers/Admin/ContactoAdminHandler.php` | Mensajes de contacto |
| `public_html/frontend/js/api-client.js` | `apiGet`, `apiPost`, `apiPostCart`, `fetchCarrito` |
| `public_html/frontend/js/dom.js` | `el`, `setFeedback`, `formatPrecio`, `frontendAssetUrl` |

## Archivos eliminados

- `app/Core/PostCsrfGuard.php` — lógica absorbida por `AbstractController`

## Cambios principales

- `AdminController.php` reducido a enrutador (~35 líneas) hacia handlers por entidad.
- Modelos `Producto`, `Usuario`, `Carrito`, `ContactoMensaje` extienden `BaseModel`; `adminListarTodos()` → `adminListar()` en Producto/Carrito.
- Controladores públicos migrados a bases; `Router` usa `jsonError()` para 404.
- HTML: orden de scripts `config → api-client → dom → auth → [script|login|carrito|admin] → navbar`.
- `admin.js`: usa módulos compartidos; helpers `buildToolbar()` y `confirmPost()`.

## Verificación

- `php -l` en todos los archivos bajo `app/` — OK.
- `Database::pdo()` solo en `BaseModel`.
- Contratos API sin cambios.
