-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 14-05-2026 a las 06:15:22
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `tienda_gamer`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categoria`
--

CREATE TABLE `categoria` (
  `id_categoria` int(11) NOT NULL,
  `nombre_categoria` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `categoria`
--

INSERT INTO `categoria` (`id_categoria`, `nombre_categoria`, `descripcion`) VALUES
(1, 'Laptops Gamer', 'Laptops de alto rendimiento para gaming'),
(2, 'Monitores', 'Monitores gaming con alta tasa de refresco'),
(3, 'Mouse', 'Mouse gaming con alta precisión'),
(4, 'Teclados', 'Teclados mecánicos y membrana'),
(5, 'Consolas', 'Consolas de videojuegos');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_venta`
--

CREATE TABLE `detalle_venta` (
  `id_detalle` int(11) NOT NULL,
  `id_venta` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `detalle_venta`
--

INSERT INTO `detalle_venta` (`id_detalle`, `id_venta`, `id_producto`, `cantidad`, `subtotal`) VALUES
(1, 1, 3, 1, 10500.00),
(2, 2, 3, 1, 10500.00),
(3, 3, 5, 1, 8900.00),
(4, 4, 5, 1, 8900.00),
(5, 4, 4, 1, 235.00),
(6, 4, 3, 1, 10500.00),
(7, 4, 2, 1, 4100.00),
(8, 4, 1, 1, 4200.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `favorito`
--

CREATE TABLE `favorito` (
  `id_favorito` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `fecha` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `favorito`
--

INSERT INTO `favorito` (`id_favorito`, `id_usuario`, `id_producto`, `fecha`) VALUES
(1, 4, 1, '2026-05-13 20:47:49'),
(2, 5, 30, '2026-05-13 23:08:04');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `producto`
--

CREATE TABLE `producto` (
  `id_producto` int(11) NOT NULL,
  `id_categoria` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `marca` varchar(100) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `precio` decimal(10,2) NOT NULL,
  `stock` int(11) DEFAULT 0,
  `imagen` varchar(255) DEFAULT NULL,
  `estado` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `producto`
--

INSERT INTO `producto` (`id_producto`, `id_categoria`, `nombre`, `marca`, `descripcion`, `precio`, `stock`, `imagen`, `estado`) VALUES
(1, 5, 'PlayStation 5 Slim', 'Sony', 'Consola de última generación con gráficos 4K, SSD ultrarrápido y retrocompatibilidad con PS4.', 4200.00, 88, 'https://images.unsplash.com/photo-1607853202273-797f1c22a38e?w=400&q=80', 1),
(2, 5, 'Xbox Series X', 'Microsoft', 'Consola Xbox con Game Pass, 1TB SSD y compatibilidad con miles de títulos.', 4100.00, 89, 'https://images.unsplash.com/photo-1621259182978-fbf93132d53d?w=400&q=80', 1),
(3, 1, 'Laptop Gamer Titan', 'ASUS', 'Laptop gamer con procesador Intel Core i7, RTX 3060 y 16GB RAM para gaming profesional.', 10500.00, 31, 'https://images.unsplash.com/photo-1603302576837-37561b2e2302?w=400&q=80', 1),
(4, 3, 'Mouse Gamer G502', 'Logitech', 'Mouse gaming de alta precisión con sensor HERO 25K y 11 botones programables.', 235.00, 889, 'https://images.unsplash.com/photo-1527864550417-7fd91fc51a46?w=400&q=80', 1),
(5, 2, 'Monitor Gaming 27\"', 'LG', 'Monitor 27 pulgadas Full HD 144Hz con 1ms de respuesta y panel IPS para gaming competitivo.', 8900.00, 62, 'https://images.unsplash.com/photo-1527443224154-c4a573d5b6f8?w=400&q=80', 1),
(6, 1, 'ASUS ROG Strix G15', 'ASUS', 'Laptop gamer con RTX 3070, Intel i7-12700H, 16GB RAM DDR5, 1TB SSD NVMe, pantalla 165Hz FHD.', 8500.00, 15, 'https://images.unsplash.com/photo-1603302576837-37561b2e2302?w=400&q=80', 1),
(7, 1, 'MSI Raider GE76', 'MSI', 'Laptop gaming de alto rendimiento con RTX 3080, i9 12a gen, 32GB RAM, 2TB SSD, pantalla 4K 120Hz.', 12500.00, 8, 'https://images.unsplash.com/photo-1593642632559-0c6d3fc62b89?w=400&q=80', 1),
(8, 1, 'Lenovo Legion 5 Pro', 'Lenovo', 'Laptop gamer AMD Ryzen 7, RTX 3060, 16GB RAM, 512GB SSD, pantalla QHD 165Hz, diseño profesional.', 7200.00, 20, 'https://images.unsplash.com/photo-1525547719571-a2d4ac8945e2?w=400&q=80', 1),
(9, 1, 'Acer Predator Helios 300', 'Acer', 'Laptop gaming con Intel i7, RTX 3060Ti, 16GB RAM, 1TB SSD, refrigeración avanzada AeroBlade.', 6800.00, 12, 'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?w=400&q=80', 1),
(10, 1, 'HP Omen 16', 'HP', 'Laptop gamer con AMD Ryzen 9, RTX 3070, 32GB RAM, 1TB SSD, OMEN Command Center incluido.', 9200.00, 6, 'https://images.unsplash.com/photo-1547394765-185e1e68f34e?w=400&q=80', 1),
(11, 2, 'Samsung Odyssey G7', 'Samsung', 'Monitor curvo 27\" QHLED, 240Hz, 1ms, resolución 2K, compatible G-Sync y FreeSync Premium Pro.', 3200.00, 25, 'https://images.unsplash.com/photo-1527443224154-c4a573d5b6f8?w=400&q=80', 1),
(12, 2, 'ASUS ROG Swift PG27UQ', 'ASUS', 'Monitor 4K HDR 144Hz, G-Sync Ultimate, DisplayHDR 1000, ideal para gaming profesional.', 5800.00, 10, 'https://images.unsplash.com/photo-1585792180666-f7347c490ee2?w=400&q=80', 1),
(13, 2, 'LG UltraGear 27GP850', 'LG', 'Monitor gaming 27\" QHD, 165Hz, 1ms, panel Nano IPS, compatible G-Sync y FreeSync Premium.', 2900.00, 18, 'https://images.unsplash.com/photo-1527443224154-c4a573d5b6f8?w=400&q=80', 1),
(14, 2, 'BenQ MOBIUZ EX2510S', 'BenQ', 'Monitor 25\" FHD 165Hz, IPS, tiempo de respuesta 1ms MPRT, altavoces integrados HDRi.', 1850.00, 30, 'https://images.unsplash.com/photo-1593640408182-31c70c8268f5?w=400&q=80', 1),
(15, 2, 'AOC CQ32G2SE', 'AOC', 'Monitor curvo 32\" QHD 165Hz, VA, 1ms MPRT, AMD FreeSync Premium, diseño sin bordes.', 2400.00, 14, 'https://images.unsplash.com/photo-1585792180666-f7347c490ee2?w=400&q=80', 1),
(16, 3, 'Logitech G Pro X Superlight', 'Logitech', 'Mouse gaming ultraligero 61g, sensor HERO 25K, hasta 25600 DPI, clic progresivo, batería 70h.', 650.00, 40, 'https://images.unsplash.com/photo-1527864550417-7fd91fc51a46?w=400&q=80', 1),
(17, 3, 'Razer DeathAdder V3', 'Razer', 'Mouse ergonómico 59g, sensor Focus Pro 30K, 6 botones programables, Razer Chroma RGB.', 480.00, 35, 'https://images.unsplash.com/photo-1615663245857-ac93bb7c39e7?w=400&q=80', 1),
(18, 3, 'SteelSeries Prime Wireless', 'SteelSeries', 'Mouse inalámbrico gaming, sensor TrueMove Air 18K, 100h batería, switches magnéticos.', 520.00, 22, 'https://images.unsplash.com/photo-1527864550417-7fd91fc51a46?w=400&q=80', 1),
(19, 3, 'Corsair Ironclaw RGB', 'Corsair', 'Mouse gaming para manos grandes, sensor PixArt PMW3391 18K DPI, 10 botones programables.', 310.00, 45, 'https://images.unsplash.com/photo-1615663245857-ac93bb7c39e7?w=400&q=80', 1),
(20, 3, 'HyperX Pulsefire Haste 2', 'HyperX', 'Mouse gaming ultraligero 53g, sensor HyperX 26K DPI, diseño honeycomb, cable paracord.', 280.00, 50, 'https://images.unsplash.com/photo-1527864550417-7fd91fc51a46?w=400&q=80', 1),
(21, 4, 'Corsair K100 RGB', 'Corsair', 'Teclado mecánico gaming OPX optical switches, per-key RGB, rueda iCUE, aluminio premium.', 1200.00, 15, 'https://images.unsplash.com/photo-1541140532154-b024d705b90a?w=400&q=80', 1),
(22, 4, 'Razer BlackWidow V4', 'Razer', 'Teclado mecánico con switches Razer Green clicky, Chroma RGB, reposamuñecas magnético.', 850.00, 20, 'https://images.unsplash.com/photo-1587829741301-dc798b83add3?w=400&q=80', 1),
(23, 4, 'SteelSeries Apex Pro', 'SteelSeries', 'Teclado con switches OmniPoint ajustables 0.1-4mm actuation, OLED display, aluminio.', 1450.00, 8, 'https://images.unsplash.com/photo-1541140532154-b024d705b90a?w=400&q=80', 1),
(24, 4, 'Logitech G915 TKL', 'Logitech', 'Teclado mecánico inalámbrico delgado, GL Clicky switches, LIGHTSPEED, hasta 40h batería.', 1100.00, 12, 'https://images.unsplash.com/photo-1587829741301-dc798b83add3?w=400&q=80', 1),
(25, 4, 'HyperX Alloy Origins Core', 'HyperX', 'Teclado mecánico TKL, HyperX Red switches, RGB per-key, aluminio, cable desmontable.', 420.00, 35, 'https://images.unsplash.com/photo-1541140532154-b024d705b90a?w=400&q=80', 1),
(26, 5, 'PlayStation 5', 'Sony', 'Consola next-gen 4K, SSD ultrarrápido, DualSense haptics, retrocompatible PS4, ray tracing.', 4800.00, 20, 'https://images.unsplash.com/photo-1607853202273-797f1c22a38e?w=400&q=80', 1),
(27, 5, 'Xbox Series X', 'Microsoft', 'Consola 4K 120fps, 1TB SSD NVMe, Quick Resume, Xbox Game Pass compatible, retrocompatible.', 4500.00, 18, 'https://images.unsplash.com/photo-1621259182978-fbf93132d53d?w=400&q=80', 1),
(28, 5, 'Nintendo Switch OLED', 'Nintendo', 'Consola híbrida con pantalla OLED 7\", dock mejorado, 64GB almacenamiento, audio mejorado.', 3200.00, 25, 'https://images.unsplash.com/photo-1578303512597-81e6cc155b3e?w=400&q=80', 1),
(29, 5, 'Steam Deck 512GB', 'Valve', 'PC gaming portátil, AMD APU personalizado, pantalla 7\" 60Hz, SteamOS, acceso a biblioteca Steam.', 5200.00, 10, 'https://images.unsplash.com/photo-1486401899868-0e435ed85128?w=400&q=80', 1),
(30, 5, 'Meta Quest 3', 'Meta', 'Headset VR standalone, resolución 4K+, chipset Snapdragon XR2 Gen 2, passthrough a color.', 3799.54, 8, 'https://images.unsplash.com/photo-1622979135225-d2ba269cf1ac?w=400&q=80', 1),
(31, 4, 'Bases4000', 'Cuper', 'El teclado más cómodo para todo el mundo, especialmente para los ingeniero de sistemas.', 1111111.00, 9, 'img/6a054bc405462.jpg', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `id_usuario` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `correo` varchar(150) NOT NULL,
  `contrasena` varchar(255) NOT NULL,
  `rol` enum('super_admin','admin','cliente') DEFAULT 'cliente',
  `codigo_2fa` varchar(10) DEFAULT NULL,
  `estado_2fa` tinyint(1) DEFAULT 0,
  `fecha_registro` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`id_usuario`, `nombre`, `correo`, `contrasena`, `rol`, `codigo_2fa`, `estado_2fa`, `fecha_registro`) VALUES
(1, 'Administrador', 'admin@tiendagamer.com', '$2y$10$qVD1qLR5djBr7oTwQH9Uze/0Rirb.0sXv7CrfBUSVbcKmtl75Fb2a', 'admin', NULL, 0, '2026-05-12 19:41:19'),
(2, 'Nana', 'keyrafelipez3@gmail.com', '$2y$10$ZlCvp4.hK.TZqNNMISLBfeEJAlXV4N2msjOoFiWSnNwLMEbRHUC1y', 'cliente', NULL, 0, '2026-05-13 12:34:33'),
(3, 'Yuchi', 'Aucur@gmail.com', '$2y$10$eJPCu0n9SB5Mqc8KB6CwEeIB7oo0qCzmNWsOdR.iIRazDftnEG1A2', 'cliente', NULL, 0, '2026-05-13 13:35:22'),
(4, 'Lu Feng', 'LuFeng@gmail.com', '$2y$10$MqfCI90JHyr5poFWWAUjxuqAwvoEg2CQOZF/xaruGBTw.26CaaUOG', 'cliente', NULL, 0, '2026-05-13 17:53:58'),
(5, 'Super Admin', 'superadmin@gamerzone.bo', '$2y$10$n8Lj1X2jaBV4yQTjkIXwfe952bgvZqd3wwPi5rRkPILdziGQ6bae2', 'super_admin', NULL, 0, '2026-05-13 22:10:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `venta`
--

CREATE TABLE `venta` (
  `id_venta` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `fecha` datetime DEFAULT current_timestamp(),
  `total` decimal(10,2) NOT NULL,
  `estado_venta` enum('Pendiente','Pagado','Entregado') DEFAULT 'Pendiente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `venta`
--

INSERT INTO `venta` (`id_venta`, `id_usuario`, `fecha`, `total`, `estado_venta`) VALUES
(1, 4, '2026-05-13 20:41:28', 10500.00, 'Pendiente'),
(2, 4, '2026-05-13 20:44:19', 10500.00, 'Pendiente'),
(3, 4, '2026-05-13 20:48:14', 8900.00, 'Pendiente'),
(4, 4, '2026-05-13 22:59:28', 27935.00, 'Pagado');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `categoria`
--
ALTER TABLE `categoria`
  ADD PRIMARY KEY (`id_categoria`);

--
-- Indices de la tabla `detalle_venta`
--
ALTER TABLE `detalle_venta`
  ADD PRIMARY KEY (`id_detalle`),
  ADD KEY `id_venta` (`id_venta`),
  ADD KEY `id_producto` (`id_producto`);

--
-- Indices de la tabla `favorito`
--
ALTER TABLE `favorito`
  ADD PRIMARY KEY (`id_favorito`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `id_producto` (`id_producto`);

--
-- Indices de la tabla `producto`
--
ALTER TABLE `producto`
  ADD PRIMARY KEY (`id_producto`),
  ADD KEY `id_categoria` (`id_categoria`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `correo` (`correo`);

--
-- Indices de la tabla `venta`
--
ALTER TABLE `venta`
  ADD PRIMARY KEY (`id_venta`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `categoria`
--
ALTER TABLE `categoria`
  MODIFY `id_categoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `detalle_venta`
--
ALTER TABLE `detalle_venta`
  MODIFY `id_detalle` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `favorito`
--
ALTER TABLE `favorito`
  MODIFY `id_favorito` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `producto`
--
ALTER TABLE `producto`
  MODIFY `id_producto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `venta`
--
ALTER TABLE `venta`
  MODIFY `id_venta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `detalle_venta`
--
ALTER TABLE `detalle_venta`
  ADD CONSTRAINT `detalle_venta_ibfk_1` FOREIGN KEY (`id_venta`) REFERENCES `venta` (`id_venta`),
  ADD CONSTRAINT `detalle_venta_ibfk_2` FOREIGN KEY (`id_producto`) REFERENCES `producto` (`id_producto`);

--
-- Filtros para la tabla `favorito`
--
ALTER TABLE `favorito`
  ADD CONSTRAINT `favorito_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`),
  ADD CONSTRAINT `favorito_ibfk_2` FOREIGN KEY (`id_producto`) REFERENCES `producto` (`id_producto`);

--
-- Filtros para la tabla `producto`
--
ALTER TABLE `producto`
  ADD CONSTRAINT `producto_ibfk_1` FOREIGN KEY (`id_categoria`) REFERENCES `categoria` (`id_categoria`);

--
-- Filtros para la tabla `venta`
--
ALTER TABLE `venta`
  ADD CONSTRAINT `venta_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
