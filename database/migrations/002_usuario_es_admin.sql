-- Ejecutar una vez en la base HIGHMIND (phpMyAdmin / CLI).
-- Añade rol de administrador para el panel /admin y la API /api/admin/...

-- Si ya existe la columna (re-ejecución), ignorá el error o comentá esta línea.
ALTER TABLE `usuarios`
  ADD COLUMN `es_admin` tinyint(1) NOT NULL DEFAULT 0 AFTER `nombre`;

-- Promover un usuario a admin (ajustá el id o el email):
-- UPDATE usuarios SET es_admin = 1 WHERE id = 1;
-- UPDATE usuarios SET es_admin = 1 WHERE email = 'tu@email.com';
