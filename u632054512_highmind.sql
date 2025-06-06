-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 05-06-2025 a las 23:26:35
-- Versión del servidor: 10.11.10-MariaDB
-- Versión de PHP: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `u632054512_highmind`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `carrito_items`
--

CREATE TABLE `carrito_items` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL DEFAULT 1,
  `agregado` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `carrito_items`
--

INSERT INTO `carrito_items` (`id`, `usuario_id`, `producto_id`, `cantidad`, `agregado`) VALUES
(18, 2, 15, 1, '2025-06-05 12:04:20');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(120) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio` decimal(12,2) NOT NULL,
  `img` varchar(200) NOT NULL,
  `stock` int(11) DEFAULT 0,
  `activo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `nombre`, `descripcion`, `precio`, `img`, `stock`, `activo`) VALUES
(1, 'Buzo Total Black - Unisex', 'Buzo negro liso, unisex, calidad premium. Ideal para cualquier ocasión.', 32000.00, 'img/producto (1).jpeg', 20, 1),
(2, 'Remera Blanca Boxy Fit', 'Remera blanca, corte boxy fit, algodón de alta calidad.', 15000.00, 'img/producto (2).jpeg', 30, 1),
(3, 'Buzo Negro - Smile', 'Buzo negro con detalle bordado Smile. Estilo urbano.', 33000.00, 'img/producto (3).jpeg', 18, 1),
(4, 'Buzo Negro Oversize - HIGHMIND', 'Buzo oversize negro con logo HIGHMIND, abrigado y cómodo.', 35000.00, 'img/producto (4).jpeg', 16, 1),
(5, 'Buzo Negro Oversize - HIGHMIND (Variante)', 'Otra variante del buzo oversize HIGHMIND, corte relajado.', 35000.00, 'img/producto (5).jpeg', 12, 1),
(6, 'Conjunto Jogging', 'Conjunto jogging completo, tela premium, cómodo y moderno.', 45000.00, 'img/producto (6).jpeg', 10, 1),
(7, 'Campera Negra - HIGHMIND', 'Campera negra con detalles HIGHMIND, ideal para el invierno.', 55000.00, 'img/producto (7).jpeg', 7, 1),
(8, 'Buzo Negro - Smile (Variante)', 'Otra versión del buzo Smile, tela premium.', 33000.00, 'img/producto (8).jpeg', 14, 1),
(9, 'Shorts Negros', 'Shorts deportivos negros, frescos y cómodos.', 14000.00, 'img/producto (9).jpeg', 21, 1),
(10, 'Gorra - HIGHMIND', 'Gorra negra con logo bordado HIGHMIND.', 11000.00, 'img/producto (10).jpeg', 33, 1),
(11, 'Buzo - Frase', 'Buzo negro con frase motivacional. Diseño original.', 32000.00, 'img/producto (11).jpeg', 11, 1),
(12, 'Conjunto - Frase', 'Conjunto completo con detalles de frase estampada.', 46000.00, 'img/producto (12).jpeg', 9, 1),
(13, 'Remera Negra Básica', 'Remera negra básica, corte clásico.', 14000.00, 'img/producto (13).jpeg', 25, 1),
(14, 'Pantalón Jogger', 'Pantalón jogger unisex, súper cómodo.', 21000.00, 'img/producto (14).jpeg', 18, 1),
(15, 'Hoodie Blackout', 'Hoodie oversize ultra black, súper abrigado.', 38000.00, 'img/producto (15).jpeg', 10, 1),
(16, 'Remera Logo Minimalista', 'Remera con logo minimalista HIGHMIND. Algodón.', 15000.00, 'img/producto (16).jpeg', 23, 1),
(17, 'Canguro Negro', 'Canguro negro básico, práctico y urbano.', 30000.00, 'img/producto (17).jpeg', 16, 1),
(18, 'Remera Oversize Blanca', 'Remera oversize color blanco, tela suave.', 15000.00, 'img/producto (18).jpeg', 14, 1),
(19, 'Shorts Urban', 'Shorts estilo urbano, ideales para verano.', 14500.00, 'img/producto (19).jpeg', 16, 1),
(20, 'Remera Negra Fit', 'Remera negra fit, para un look ajustado.', 14500.00, 'img/producto (20).jpeg', 18, 1),
(21, 'Remera Blanca Frase', 'Remera blanca con frase estampada.', 16000.00, 'img/producto (21).jpeg', 15, 1),
(22, 'Canguro Oversize Negro', 'Canguro negro oversize, corte moderno.', 32000.00, 'img/producto (22).jpeg', 8, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `email` varchar(80) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nombre` varchar(60) NOT NULL,
  `creado` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `email`, `password`, `nombre`, `creado`) VALUES
(1, 'juanmanuel.nextop@gmail.com', '$2y$10$Fama3HW5EwZI/FLi4OUqc.K3zUg8RH4rXikUcRXe/6BzDM1b6GBea', 'Juan Manuel', '2025-06-03 00:42:56'),
(2, 'jmanuelhumeniuk@gmail.com', '$2y$10$989o/CwoHv3.hsYiIEw3mehu5qogRl9X8HcNgvRzRbAZtyAfQZy5O', 'Juan Manuel Humeniuk', '2025-06-03 02:09:54');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `carrito_items`
--
ALTER TABLE `carrito_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario_id` (`usuario_id`,`producto_id`),
  ADD KEY `producto_id` (`producto_id`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `carrito_items`
--
ALTER TABLE `carrito_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `carrito_items`
--
ALTER TABLE `carrito_items`
  ADD CONSTRAINT `carrito_items_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `carrito_items_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
