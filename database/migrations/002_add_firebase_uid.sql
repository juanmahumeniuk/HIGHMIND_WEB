-- Agrega soporte para autenticación via Firebase Authentication.
-- firebase_uid: identificador único del usuario en Firebase (ej. "abc123xyz")
-- password: se vuelve nullable porque usuarios Firebase no tienen password local.

ALTER TABLE usuarios
    ADD COLUMN firebase_uid VARCHAR(128) NULL DEFAULT NULL UNIQUE AFTER email,
    MODIFY COLUMN password VARCHAR(255) NULL DEFAULT NULL;
