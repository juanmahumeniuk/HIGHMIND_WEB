-- Elimina la columna password en bases existentes (auth 100% Firebase).
-- Idempotente: solo aplica si la columna existe.

SET @col_exists = (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'usuarios'
    AND COLUMN_NAME = 'password'
);

SET @sql = IF(
  @col_exists > 0,
  'ALTER TABLE usuarios DROP COLUMN password',
  'SELECT 1'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
