-- Usuario admin de desarrollo (perfil MySQL; la contraseña vive en Firebase Authentication).
-- Crear la cuenta admin@admin.com en Firebase Console con Email/Password antes de iniciar sesión.

INSERT INTO usuarios (email, nombre, es_admin) VALUES (
  'admin@admin.com',
  'Admin',
  1
)
ON DUPLICATE KEY UPDATE
  nombre = VALUES(nombre),
  es_admin = VALUES(es_admin);
