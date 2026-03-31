-- Ejecutar una vez en la base HIGHMIND (phpMyAdmin / CLI).
CREATE TABLE IF NOT EXISTS `contacto_mensajes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(120) NOT NULL,
  `email` varchar(80) NOT NULL,
  `mensaje` text NOT NULL,
  `creado` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
