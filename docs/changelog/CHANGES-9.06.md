# Cambios — Limpieza y auth Firebase unificada

## Resumen

- Eliminada copia duplicada `repo/` y `repo.zip`.
- Esquema MySQL consolidado: `firebase_uid` + `es_admin` en `000_schema.sql`; columna `password` eliminada.
- Auth unificada: email/contraseña vía Firebase SDK en sitio y panel admin; sesión PHP solo vía `POST /api/firebase verify`.
- Código muerto eliminado: login local, `Usuario::crear`, `hashPassword`, modal cambio de clave admin, duplicados JS.

## Archivos nuevos

| Archivo | Descripción |
|---------|-------------|
| `app/Core/PostCsrfGuard.php` | Trait CSRF compartido |
| `public_html/frontend/js/auth.js` | Login/logout Firebase compartido |
| `database/migrations/004_drop_password.sql` | Drop columna password en BDs legacy |

## Archivos eliminados

- `repo/` (copia completa del proyecto)
- `database/migrations/002_usuario_es_admin.sql`
- `database/migrations/002_add_firebase_uid.sql`

## Cambios principales

- `FirebaseClient::signUp()` — creación de usuarios admin en Firebase REST.
- `Usuario::adminCrear()` — inserta `(firebase_uid, email, nombre, es_admin)`.
- `login.html` — formulario email/contraseña + registro.
- `admin.js` — gate usa `firebaseSignIn`; comparte `config.js` y `auth.js`.

## Setup admin

1. Firebase Console → Authentication → Email/Password habilitado.
2. Crear usuario `admin@admin.com` en Firebase.
3. `./run.sh` → seed MySQL con `es_admin = 1`.
