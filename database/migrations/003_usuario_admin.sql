-- Usuario admin de desarrollo.
-- Email: admin@admin.com  |  Contraseña en texto plano: Administrador*1234
--
-- IMPORTANTE: en SQL no existe password_hash() de PHP. Si ponés la expresión
-- 'password_hash(...)' entre comillas, MySQL guarda ese texto literal y el
-- login (password_verify en PHP) falla siempre.
-- El valor de `password` debe ser un hash bcrypt/argon2 ya generado (p. ej.
-- con: php -r "echo password_hash('Administrador*1234', PASSWORD_BCRYPT);"

INSERT INTO usuarios (email, nombre, password, es_admin) VALUES (
  'admin@admin.com',
  'Admin',
  '$2y$12$Vm3/a3m1OjWzHdsFVTWOUuqRtCBfcS7l5Ekmdc0rnSXohtJTaa4mS',
  1
)
ON DUPLICATE KEY UPDATE
  nombre = VALUES(nombre),
  password = VALUES(password),
  es_admin = VALUES(es_admin);
