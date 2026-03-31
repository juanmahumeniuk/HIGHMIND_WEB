-- Error 1075: AUTO_INCREMENT exige que `id` sea clave primaria (o única).
-- Si ya tenés PRIMARY KEY en `id`, ejecutá solo el segundo ALTER.
--
--   mariadb highmind < dev/patch_carrito_items_autoincrement.sql

ALTER TABLE `carrito_items` ADD PRIMARY KEY (`id`);
ALTER TABLE `carrito_items` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
