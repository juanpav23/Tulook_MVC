-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 12-02-2026 a las 13:58:55
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
-- Base de datos: `tulook`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `advertencias_usuario`
--

CREATE TABLE `advertencias_usuario` (
  `ID_Advertencia` int(11) NOT NULL,
  `ID_Usuario` int(11) NOT NULL,
  `ID_Admin` int(11) DEFAULT NULL,
  `Motivo` varchar(100) NOT NULL,
  `Descripcion` text DEFAULT NULL,
  `Tipo` enum('Leve','Moderada','Grave') DEFAULT 'Leve',
  `Fecha_Advertencia` timestamp NOT NULL DEFAULT current_timestamp(),
  `Estado` enum('Activa','Revisada','Expirada') DEFAULT 'Activa'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `articulo`
--

CREATE TABLE `articulo` (
  `ID_Articulo` int(11) NOT NULL,
  `N_Articulo` varchar(45) NOT NULL,
  `Foto` varchar(300) NOT NULL,
  `ID_Categoria` int(11) NOT NULL,
  `ID_SubCategoria` int(11) NOT NULL,
  `ID_Genero` int(11) NOT NULL,
  `ID_Precio` int(11) NOT NULL,
  `Activo` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `articulo`
--

INSERT INTO `articulo` (`ID_Articulo`, `N_Articulo`, `Foto`, `ID_Categoria`, `ID_SubCategoria`, `ID_Genero`, `ID_Precio`, `Activo`) VALUES
(5, 'Anillos', 'ImgProducto/Accesorios/Hombre/Anillo/1768679813_696be9852910c.png', 2, 17, 1, 1, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `atributo_valor`
--

CREATE TABLE `atributo_valor` (
  `ID_AtributoValor` int(11) NOT NULL,
  `ID_TipoAtributo` int(11) NOT NULL,
  `Valor` varchar(50) NOT NULL,
  `Orden` int(11) DEFAULT 0,
  `Activo` tinyint(4) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `atributo_valor`
--

INSERT INTO `atributo_valor` (`ID_AtributoValor`, `ID_TipoAtributo`, `Valor`, `Orden`, `Activo`) VALUES
(1, 1, 'XS', 1, 1),
(2, 1, 'S', 2, 1),
(3, 1, 'M', 3, 1),
(4, 1, 'L', 4, 1),
(5, 1, 'XL', 5, 1),
(6, 1, 'XXL', 6, 1),
(7, 1, '28', 7, 1),
(8, 1, '30', 8, 1),
(9, 1, '32', 9, 1),
(10, 1, '34', 10, 1),
(11, 1, '36', 11, 1),
(12, 1, '38', 12, 1),
(13, 1, '40', 13, 1),
(14, 1, '42', 14, 1),
(16, 1, 'Única', 15, 1),
(20, 3, '19', 1, 1),
(21, 3, '20', 2, 1),
(22, 3, '28', 3, 1),
(23, 3, '30', 4, 1),
(24, 3, '32', 5, 1),
(25, 3, '34', 6, 1),
(26, 3, '36', 7, 1),
(27, 3, 'Ajuste Estándar', 8, 1),
(28, 3, 'Correa Corta', 9, 1),
(29, 3, 'Correa Larga', 10, 1),
(30, 4, '30 ml', 1, 1),
(31, 4, '50 ml', 2, 1),
(32, 4, '75 ml', 3, 1),
(33, 4, '100 ml', 4, 1),
(34, 4, '150 ml', 5, 1),
(35, 5, 'Pequeño', 1, 1),
(36, 5, 'Mediano', 2, 1),
(37, 5, 'Grande', 3, 1),
(38, 5, 'Extra Grande', 4, 1),
(40, 3, '13', 11, 1),
(41, 5, 'Mini', 5, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categoria`
--

CREATE TABLE `categoria` (
  `ID_Categoria` int(11) NOT NULL,
  `N_Categoria` varchar(45) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `categoria`
--

INSERT INTO `categoria` (`ID_Categoria`, `N_Categoria`) VALUES
(1, 'Ropa'),
(2, 'Accesorios'),
(3, 'Calzado');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categoria_subcategoria`
--

CREATE TABLE `categoria_subcategoria` (
  `ID_Categoria` int(11) NOT NULL,
  `ID_SubCategoria` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categoria_subcategoria`
--

INSERT INTO `categoria_subcategoria` (`ID_Categoria`, `ID_SubCategoria`) VALUES
(1, 1),
(1, 2),
(1, 3),
(1, 4),
(2, 1),
(2, 2),
(2, 3),
(2, 4),
(3, 3),
(3, 4);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `color`
--

CREATE TABLE `color` (
  `ID_Color` int(11) NOT NULL,
  `N_Color` varchar(45) NOT NULL,
  `CodigoHex` varchar(45) NOT NULL,
  `Activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `color`
--

INSERT INTO `color` (`ID_Color`, `N_Color`, `CodigoHex`, `Activo`) VALUES
(1, 'Blanco', '#FFFFFF', 1),
(2, 'Negro', '#000000', 1),
(4, 'Azul Oscuro', '#00008B', 1),
(5, 'Azul Claro', '#ADD8E6', 1),
(6, 'Rojo', '#FF0000', 1),
(7, 'Verde', '#008000', 1),
(8, 'Verde Claro', '#00ff00', 1),
(12, 'Morado', '#800080', 1),
(19, 'Borgoña', '#800020', 1),
(21, 'Camello', '#C19A6B', 1),
(22, 'Caqui', '#C3B091', 1),
(23, 'Dorado', '#FFD700', 1),
(24, 'Gris', '#808080', 1),
(25, 'Gris Oscuro', '#505050', 1),
(26, 'Habana', '#A67B5B', 1),
(27, 'Marron', '#8B4513', 1),
(28, 'Naranja', '#FFA500', 1),
(31, 'Plateado', '#C0C0C0', 1),
(34, 'Rosado', '#FFC0CB', 1),
(40, 'Azul Atómico', '#0033FF', 1),
(41, 'Marrón Dorado', '#B8860B', 1),
(42, 'Negro Dorado', '#3B2F2F', 1),
(43, 'Negro Plateado', '#4B4B4B', 1),
(44, 'Azul Marino', '#000080', 1),
(45, 'Rojo Fuego', '#B22222', 1),
(46, 'Rojo Vino', '#8B0000', 1),
(47, 'Coñac', '#9A463D', 1),
(48, 'Menta', '#98FF98', 1),
(49, 'Fucsia', '#FF00FF', 1),
(50, 'Marrón Gris', '#7B6A58', 1),
(51, 'Oro', '#DAA520', 1),
(52, 'Violeta', '#EE82EE', 1),
(53, 'Púrpura', '#9B30FF', 1),
(54, 'Melocotón', '#FFDAB9', 1),
(55, 'Multicolorido', '#CCCCCC', 1),
(56, 'Perlaenegrecia', '#E8E8E8', 1),
(57, 'Lila', '#C8A2C8', 1),
(58, 'Naranjado', '#FFA500', 1),
(59, 'Beige', '#F5F5DC', 1),
(62, 'Comex Melifluo', '#88B9DD', 1),
(64, 'Amarrillo', '#FFFF00', 1),
(66, 'Azul', '#0000FF', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `descuento`
--

CREATE TABLE `descuento` (
  `ID_Descuento` int(11) NOT NULL,
  `Codigo` varchar(50) DEFAULT NULL,
  `ID_Articulo` int(11) DEFAULT NULL,
  `ID_Categoria` int(11) DEFAULT NULL,
  `Tipo` enum('Porcentaje','ValorFijo') DEFAULT 'Porcentaje',
  `Valor` decimal(10,2) NOT NULL,
  `Monto_Minimo` decimal(10,2) NOT NULL,
  `Usos_Globales` int(11) DEFAULT 0,
  `Max_Usos_Global` int(11) NOT NULL,
  `Max_Usos_Usuario` int(11) NOT NULL,
  `FechaInicio` datetime DEFAULT NULL,
  `FechaFin` datetime DEFAULT NULL,
  `Activo` tinyint(1) DEFAULT 1,
  `ID_Producto` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `descuento`
--

INSERT INTO `descuento` (`ID_Descuento`, `Codigo`, `ID_Articulo`, `ID_Categoria`, `Tipo`, `Valor`, `Monto_Minimo`, `Usos_Globales`, `Max_Usos_Global`, `Max_Usos_Usuario`, `FechaInicio`, `FechaFin`, `Activo`, `ID_Producto`) VALUES
(1, 'RWEWEWW_2025', NULL, 1, 'Porcentaje', 5.00, 600.00, 9, 30, 6, '2026-01-13 00:00:00', '2026-02-12 23:59:00', 1, NULL),
(2, 'AUTO-1AAE6F3A', NULL, 1, 'Porcentaje', 5.00, 0.00, 30, 30, 6, '2026-01-13 19:09:27', '2026-02-12 23:59:00', 1, NULL),
(3, 'RWEWEWWW_2025', NULL, 1, 'Porcentaje', 5.00, 20.00, 0, 44, 2, '2026-01-13 00:00:00', '2026-02-12 23:59:00', 1, NULL),
(4, 'DSDSDSSDSDWS_2025', NULL, 1, 'Porcentaje', 5.00, 50000.00, 4, 20, 4, '2026-01-14 00:00:00', '2026-02-13 23:59:00', 1, NULL),
(5, '9C47B4F3', NULL, 1, 'ValorFijo', 100000.00, 1000.00, 1, 1, 1, '2026-01-15 00:00:00', '2026-02-14 23:59:00', 1, NULL),
(6, 'RWEWEWW_2025DD', NULL, 2, 'Porcentaje', 20.00, 5000.00, 0, 4, 4, '2026-01-15 00:00:00', '2026-02-14 23:59:00', 1, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `descuento_usuario`
--

CREATE TABLE `descuento_usuario` (
  `ID_DescuentoUsuario` int(11) NOT NULL,
  `ID_Descuento` int(11) NOT NULL,
  `ID_Usuario` int(11) NOT NULL,
  `Usos` int(11) DEFAULT 0,
  `Fecha_Ultimo_Uso` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `descuento_usuario`
--

INSERT INTO `descuento_usuario` (`ID_DescuentoUsuario`, `ID_Descuento`, `ID_Usuario`, `Usos`, `Fecha_Ultimo_Uso`) VALUES
(1, 2, 73, 0, NULL),
(2, 1, 73, 6, '2026-01-14 16:18:57'),
(3, 3, 73, 0, NULL),
(4, 2, 1, 30, '2026-01-14 05:04:12'),
(10, 4, 73, 4, '2026-01-14 20:56:20'),
(11, 5, 73, 1, '2026-01-15 17:46:23'),
(12, 4, 1, 0, NULL),
(13, 6, 1, 0, NULL),
(14, 5, 1, 0, NULL),
(15, 1, 1, 0, NULL),
(16, 3, 1, 0, NULL),
(17, 6, 73, 0, NULL),
(18, 4, 82, 0, NULL),
(19, 6, 82, 0, NULL),
(20, 5, 82, 0, NULL),
(21, 1, 82, 0, NULL),
(22, 3, 82, 0, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `direccion`
--

CREATE TABLE `direccion` (
  `ID_Direccion` int(11) NOT NULL,
  `ID_Usuario` int(11) NOT NULL,
  `Direccion` text NOT NULL,
  `Ciudad` varchar(100) DEFAULT NULL,
  `Departamento` varchar(100) DEFAULT NULL,
  `CodigoPostal` varchar(20) DEFAULT NULL,
  `Predeterminada` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `direccion`
--

INSERT INTO `direccion` (`ID_Direccion`, `ID_Usuario`, `Direccion`, `Ciudad`, `Departamento`, `CodigoPostal`, `Predeterminada`) VALUES
(1, 73, 'CRA 5#4-155', 'Puerto Boyacá', 'Boyacá', '155201', 1),
(2, 73, 'CRA 5#4-15', 'Puerto Boyacá', 'Boyacá', '155201', 0),
(3, 1, 'CRA 5#4-15', 'Puerto Boyacá', 'Boyacá', '155201', 1),
(4, 73, 'CRA 5#4-1532', 'medellin', 'antioquia', '155232', 1),
(5, 73, 'CRA 5#4-15', 'Puerto Boyacá', 'Boyacá', '155201', 1),
(6, 82, 'CRA 5#4-15', 'Puerto Boyacá', 'Boyacá', '155201', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `factura`
--

CREATE TABLE `factura` (
  `ID_Factura` int(11) NOT NULL,
  `ID_Usuario` int(11) NOT NULL,
  `ID_Direccion` int(11) DEFAULT NULL,
  `Fecha_Factura` datetime NOT NULL,
  `Monto_Total` decimal(10,2) NOT NULL,
  `Subtotal` decimal(10,2) DEFAULT 0.00,
  `IVA` decimal(10,2) DEFAULT 0.00,
  `Direccion_Envio` text DEFAULT NULL,
  `Estado` enum('Emitido','Confirmado','Preparando','Enviado','Entregado','Retrasado','Devuelto','Anulado') DEFAULT 'Emitido',
  `ID_Metodo_Pago` int(11) NOT NULL,
  `Usuario_Confirmacion` int(11) DEFAULT NULL,
  `Fecha_Confirmacion` datetime DEFAULT NULL,
  `Usuario_Anulacion` int(11) DEFAULT NULL,
  `Fecha_Anulacion` datetime DEFAULT NULL,
  `Codigo_Acceso` varchar(12) DEFAULT NULL,
  `Fecha_Envio` datetime DEFAULT NULL,
  `Fecha_Estimada_Entrega` date DEFAULT NULL,
  `Fecha_Entrega` datetime DEFAULT NULL,
  `Numero_Guia` varchar(100) DEFAULT NULL,
  `Transportadora` varchar(100) DEFAULT NULL,
  `Notas_Envio` text DEFAULT NULL,
  `Motivo_Anulacion` text DEFAULT NULL,
  `Usuario_Envio` int(11) DEFAULT NULL,
  `Usuario_Entrega` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `factura`
--

INSERT INTO `factura` (`ID_Factura`, `ID_Usuario`, `ID_Direccion`, `Fecha_Factura`, `Monto_Total`, `Subtotal`, `IVA`, `Direccion_Envio`, `Estado`, `ID_Metodo_Pago`, `Usuario_Confirmacion`, `Fecha_Confirmacion`, `Usuario_Anulacion`, `Fecha_Anulacion`, `Codigo_Acceso`, `Fecha_Envio`, `Fecha_Estimada_Entrega`, `Fecha_Entrega`, `Numero_Guia`, `Transportadora`, `Notas_Envio`, `Motivo_Anulacion`, `Usuario_Envio`, `Usuario_Entrega`) VALUES
(1, 73, 1, '2026-01-13 19:09:27', 36414000.00, 30600000.00, 5814000.00, NULL, 'Preparando', 2, NULL, NULL, NULL, NULL, '1444D269', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2, 73, 2, '2026-01-13 14:38:14', 36414000.00, 30600000.00, 5814000.00, NULL, 'Anulado', 2, NULL, NULL, 73, '2026-01-15 11:31:09', 'AFF593BD', NULL, NULL, NULL, NULL, NULL, NULL, 'GO GO GO', NULL, NULL),
(3, 73, 1, '2026-01-13 14:40:06', 36414000.00, 30600000.00, 5814000.00, NULL, 'Entregado', 3, NULL, NULL, NULL, NULL, '9C47B4F3', '2026-01-15 11:34:39', NULL, '2026-01-27 18:20:26', 'TLL-20260115-3-3315', 'Interrapidisimo', 'Se entregó a transportadora, tiempo estimado 3 días\r\nSe entregó a transportadora, tiempo estimado 3 días\r\nSe entregó a transportadora, tiempo estimado 3 días\r\nSe entregó a transportadora, tiempo estimado 3 días\r\nSe entregó a transportadora, tiempo estimado 3 días\r\nSe entregó a transportadora, tiempo estimado 3 días\r\nSe entregó a transportadora, tiempo estimado 3 días\r\nSe entregó a transportadora, tiempo estimado 3 días', NULL, 73, 1),
(4, 73, 4, '2026-01-13 14:43:07', 36414000.00, 30600000.00, 5814000.00, NULL, 'Entregado', 1, NULL, NULL, NULL, NULL, 'CE7B908B', '2026-01-25 16:41:44', NULL, '2026-01-25 16:42:09', NULL, NULL, NULL, NULL, 1, 1),
(5, 73, 4, '2026-01-13 15:06:16', 36414000.00, 30600000.00, 5814000.00, NULL, 'Enviado', 2, NULL, NULL, NULL, NULL, '86BFCBD1', '2026-01-26 21:22:24', NULL, NULL, 'TLL-20260127-5-1984', 'Servientrega', 'Se entregó a transportadora, tiempo estimado 3 días.\r\nCliente notificado por correo electrónico.\r\nSeguro incluido en el envío.', NULL, 1, NULL),
(6, 73, 4, '2026-01-13 15:42:59', 36414000.00, 30600000.00, 5814000.00, NULL, 'Enviado', 2, NULL, NULL, NULL, NULL, '2B7E731E', '2026-01-26 21:48:13', NULL, NULL, 'TLL-20260127-6-6201', 'Servientrega', 'Se entregó a transportadora, tiempo estimado 3 días.\r\nCliente notificado por correo electrónico.\r\nSeguro incluido en el envío.', NULL, 1, NULL),
(7, 73, 4, '2026-01-13 15:54:23', 34593300.00, 29070000.00, 5523300.00, NULL, 'Preparando', 1, NULL, NULL, NULL, NULL, '8FB32933', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(8, 73, 4, '2026-01-13 16:02:08', 72828000.00, 61200000.00, 11628000.00, NULL, 'Retrasado', 1, NULL, NULL, NULL, NULL, '6CA61AF7', '2026-01-26 18:59:51', '2026-01-31', NULL, 'TLL-20260127-8-7452', 'Servientrega', 'dwdw', NULL, 1, NULL),
(9, 73, 4, '2026-01-13 16:08:48', 36414000.00, 30600000.00, 5814000.00, NULL, 'Preparando', 2, NULL, NULL, NULL, NULL, '7888C27B', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(10, 73, 4, '2026-01-13 18:55:11', 71007300.00, 59670000.00, 11337300.00, NULL, 'Preparando', 1, NULL, NULL, NULL, NULL, 'FC442317', '2026-01-26 20:38:07', NULL, NULL, 'TLL-20260127-10-5885', 'Servientrega', 'Producto llegó dañado durante el transporte.', NULL, 1, NULL),
(11, 73, 4, '2026-01-13 19:44:59', 72828000.00, 61200000.00, 11628000.00, NULL, 'Preparando', 2, NULL, NULL, NULL, NULL, '665125FF', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(12, 73, 4, '2026-01-13 19:45:46', 34593300.00, 29070000.00, 5523300.00, NULL, 'Preparando', 1, NULL, NULL, NULL, NULL, '05E8D5AA', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(13, 73, 4, '2026-01-13 20:20:54', 34593300.00, 29070000.00, 5523300.00, NULL, 'Preparando', 2, NULL, NULL, NULL, NULL, 'EAD0968A', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(14, 73, 4, '2026-01-13 20:33:53', 34593300.00, 29070000.00, 5523300.00, NULL, 'Anulado', 1, NULL, NULL, 1, '2026-01-28 19:04:28', 'BDDAB84E', NULL, NULL, NULL, NULL, NULL, NULL, 'Cliente solicitó la cancelación de su pedido.', NULL, NULL),
(15, 73, 4, '2026-01-13 22:20:48', 69186600.00, 58140000.00, 11046600.00, NULL, 'Enviado', 1, NULL, NULL, NULL, NULL, '29BC6F6C', '2026-02-02 13:03:10', '2026-02-28', NULL, 'TLL-20260202-15-3883', 'Interrapidisimo', 'Se entregó a transportadora, tiempo estimado 3 días.\r\nCliente notificado por correo electrónico.\r\nSeguro incluido en el envío.', NULL, 1, NULL),
(16, 73, 4, '2026-01-13 22:27:48', 34593300.00, 29070000.00, 5523300.00, NULL, 'Confirmado', 2, NULL, NULL, NULL, NULL, 'BB6A05CB', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(17, 73, 4, '2026-01-13 22:37:33', 34593300.00, 29070000.00, 5523300.00, NULL, 'Confirmado', 1, NULL, NULL, NULL, NULL, '80093CA0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(18, 73, 4, '2026-01-13 22:44:01', 34593300.00, 29070000.00, 5523300.00, NULL, 'Confirmado', 2, NULL, NULL, NULL, NULL, '0CD045C6', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(19, 73, 4, '2026-01-13 23:02:59', 34593300.00, 29070000.00, 5523300.00, NULL, 'Confirmado', 1, NULL, NULL, NULL, NULL, '825ACEA2', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(20, 73, 4, '2026-01-13 23:15:49', 34593300.00, 29070000.00, 5523300.00, NULL, 'Confirmado', 1, NULL, NULL, NULL, NULL, '35BD9C5E', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(21, 73, 4, '2026-01-13 23:17:49', 34593300.00, 29070000.00, 5523300.00, NULL, 'Confirmado', 1, NULL, NULL, NULL, NULL, 'EF9115B6', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(22, 73, 4, '2026-01-13 23:18:20', 34593300.00, 29070000.00, 5523300.00, NULL, 'Confirmado', 1, NULL, NULL, NULL, NULL, '7709E020', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(23, 73, 4, '2026-01-14 10:17:44', 71007300.00, 59670000.00, 11337300.00, NULL, 'Confirmado', 2, NULL, NULL, NULL, NULL, '5CEECECB', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(24, 73, 4, '2026-01-14 10:18:33', 34593300.00, 29070000.00, 5523300.00, NULL, 'Confirmado', 1, NULL, NULL, NULL, NULL, '0EDDDD7F', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(25, 73, 4, '2026-01-14 10:18:57', 34593300.00, 29070000.00, 5523300.00, NULL, 'Confirmado', 1, NULL, NULL, NULL, NULL, '663306A3', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(26, 73, 4, '2026-01-14 10:29:43', 99999999.99, 87210000.00, 16569900.00, NULL, 'Confirmado', 2, NULL, NULL, NULL, NULL, 'AF6CC4A9', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(27, 73, 4, '2026-01-14 13:17:32', 34593300.00, 29070000.00, 5523300.00, NULL, 'Confirmado', 1, NULL, NULL, NULL, NULL, '13339650', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(28, 73, 4, '2026-01-14 14:44:33', 99999999.99, 99999999.99, 27616500.00, NULL, 'Confirmado', 2, NULL, NULL, NULL, NULL, 'ED462150', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(29, 73, 4, '2026-01-14 14:56:20', 34593300.00, 29070000.00, 5523300.00, NULL, 'Confirmado', 1, NULL, NULL, NULL, NULL, '1D847FBE', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(30, 73, 4, '2026-01-14 14:57:00', 99999999.99, 99999999.99, 58140000.00, NULL, 'Confirmado', 1, NULL, NULL, NULL, NULL, 'A9B758D8', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(31, 73, 4, '2026-01-14 15:15:36', 36414.00, 30600.00, 5814.00, NULL, 'Confirmado', 2, NULL, NULL, NULL, NULL, 'C260416F', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(32, 73, 4, '2026-01-14 15:22:51', 36414.00, 30600.00, 5814.00, NULL, 'Confirmado', 2, NULL, NULL, NULL, NULL, '5141D0D3', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(33, 73, 4, '2026-01-14 15:32:49', 36414000.00, 30600000.00, 5814000.00, NULL, 'Confirmado', 2, NULL, NULL, NULL, NULL, '32F2E4E9', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(34, 73, 4, '2026-01-14 15:34:15', 99999999.99, 99999999.99, 23256000.00, NULL, 'Confirmado', 1, NULL, NULL, NULL, NULL, '8D6A63B0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(35, 73, 4, '2026-01-14 15:54:19', 36414.00, 30600.00, 5814.00, NULL, 'Confirmado', 1, NULL, NULL, NULL, NULL, '9B835285', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(36, 73, 4, '2026-01-14 15:54:41', 36414.00, 30600.00, 5814.00, NULL, 'Confirmado', 1, NULL, NULL, NULL, NULL, '2212B19C', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(37, 73, 4, '2026-01-14 16:10:54', 364140.00, 306000.00, 58140.00, NULL, 'Confirmado', 1, NULL, NULL, NULL, NULL, 'B0894D7B', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(38, 73, 4, '2026-01-14 16:17:29', 1820700.00, 1530000.00, 290700.00, NULL, 'Retrasado', 1, NULL, NULL, NULL, NULL, '8A653E2D', '2026-01-14 18:04:07', '2026-01-30', NULL, '', 'Interrapidisimo', 'jaja', NULL, 73, NULL),
(39, 73, 4, '2026-01-14 18:05:20', 364140.00, 306000.00, 58140.00, NULL, 'Confirmado', 2, NULL, NULL, NULL, NULL, '4B412471', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(40, 73, 4, '2026-01-14 18:25:18', 364140.00, 306000.00, 58140.00, NULL, 'Confirmado', 1, NULL, NULL, NULL, NULL, '89248D51', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(41, 73, 4, '2026-01-14 18:56:48', 364140.00, 306000.00, 58140.00, NULL, 'Confirmado', 1, NULL, NULL, NULL, NULL, '332AC80A', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(42, 73, 4, '2026-01-14 19:04:03', 364140.00, 306000.00, 58140.00, NULL, 'Confirmado', 2, NULL, NULL, NULL, NULL, 'E5C151C4', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(43, 73, 4, '2026-01-14 19:10:48', 364140.00, 306000.00, 58140.00, NULL, 'Confirmado', 1, NULL, NULL, NULL, NULL, '135A3F61', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(44, 73, 4, '2026-01-14 19:15:14', 364140.00, 306000.00, 58140.00, NULL, 'Confirmado', 1, NULL, NULL, NULL, NULL, 'ECA9289A', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(45, 73, 4, '2026-01-14 19:19:46', 364140.00, 306000.00, 58140.00, NULL, 'Confirmado', 1, NULL, NULL, NULL, NULL, '4231E655', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(46, 73, 4, '2026-01-14 19:20:29', 364140.00, 306000.00, 58140.00, NULL, 'Confirmado', 1, NULL, NULL, NULL, NULL, '6782CA79', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(47, 73, 4, '2026-01-14 19:22:29', 364140.00, 306000.00, 58140.00, NULL, 'Confirmado', 1, NULL, NULL, NULL, NULL, '2C632D74', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(48, 73, 4, '2026-01-14 19:25:17', 364140.00, 306000.00, 58140.00, NULL, 'Confirmado', 1, NULL, NULL, NULL, NULL, '6EE59504', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(49, 73, 4, '2026-01-14 19:30:20', 364140.00, 306000.00, 58140.00, NULL, 'Confirmado', 1, NULL, NULL, NULL, NULL, 'BD92549B', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(50, 73, 4, '2026-01-14 19:32:59', 364140.00, 306000.00, 58140.00, NULL, 'Entregado', 1, NULL, NULL, NULL, NULL, '5057D821', '2026-01-15 11:24:24', NULL, '2026-01-15 11:29:02', 'TLL-20260115-50-2116', 'Servientrega', 'Se entregó a transportadora, tiempo estimado 3 días\r\nCliente notificado por correo\r\nSeguro incluido', NULL, 73, 73),
(51, 73, 2, '2026-01-15 11:40:51', 364140.00, 306000.00, 58140.00, NULL, 'Confirmado', 2, NULL, NULL, NULL, NULL, '0B556261', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(52, 73, 4, '2026-01-15 11:41:46', 364140.00, 306000.00, 58140.00, NULL, 'Confirmado', 2, NULL, NULL, NULL, NULL, '3912A62A', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(53, 73, 4, '2026-01-15 11:46:23', 12257000.00, 10300000.00, 1957000.00, NULL, 'Confirmado', 2, NULL, NULL, NULL, NULL, '8C2A5E6A', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(54, 73, 4, '2026-01-15 12:12:00', 364140.00, 306000.00, 58140.00, NULL, 'Confirmado', 1, NULL, NULL, NULL, NULL, '22C8DEE0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(55, 1, 3, '2026-01-15 12:35:07', 364140.00, 306000.00, 58140.00, NULL, 'Confirmado', 1, NULL, NULL, NULL, NULL, 'A44FB965', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(56, 1, 3, '2026-01-15 17:20:19', 285600.00, 240000.00, 45600.00, NULL, 'Confirmado', 2, NULL, NULL, NULL, NULL, '19BABD88', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(57, 1, 3, '2026-01-25 14:16:16', 357000.00, 300000.00, 57000.00, NULL, 'Entregado', 1, NULL, NULL, NULL, NULL, '48D614AC', '2026-01-26 18:54:02', NULL, '2026-01-27 18:20:49', 'TLL-20260127-57-1516', 'TCC', '', NULL, 1, 1),
(58, 1, 3, '2026-01-25 18:53:28', 357000.00, 300000.00, 57000.00, NULL, 'Anulado', 1, NULL, NULL, 1, '2026-01-25 18:55:20', 'C58E35E7', NULL, NULL, NULL, NULL, NULL, NULL, 'Cliente solicitó la cancelación de su pedido.', NULL, NULL),
(59, 1, 3, '2026-01-25 18:55:55', 357000.00, 300000.00, 57000.00, NULL, 'Preparando', 1, NULL, NULL, NULL, NULL, '0DAE16FB', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(60, 1, 3, '2026-01-26 16:39:05', 357000.00, 300000.00, 57000.00, NULL, 'Anulado', 1, NULL, NULL, 1, '2026-01-26 16:41:03', '4F039121', NULL, NULL, NULL, NULL, NULL, NULL, 'Cliente solicitó la cancelación de su pedido.', NULL, NULL),
(61, 1, 3, '2026-01-26 16:41:22', 357000.00, 300000.00, 57000.00, NULL, 'Anulado', 1, NULL, NULL, 1, '2026-01-26 17:50:55', '3AE07568', '2026-01-26 17:50:02', NULL, NULL, 'TLL-20260126-61-8916', '', 'Cliente no aceptó el producto al momento de la entrega.', 'Cliente solicitó la cancelación de su pedido.', 1, NULL),
(62, 1, 3, '2026-01-26 17:51:15', 357000.00, 300000.00, 57000.00, NULL, 'Anulado', 1, NULL, NULL, 1, '2026-01-26 21:31:24', '3C23A788', '2026-01-26 18:11:08', NULL, NULL, 'TLL-20260127-62-5950', 'Servientrega', 'Cliente no aceptó el producto al momento de la entrega.', 'Cliente solicitó la cancelación de su pedido.', 1, NULL),
(63, 1, 3, '2026-01-27 23:31:19', 1428000.00, 1200000.00, 228000.00, NULL, 'Confirmado', 1, NULL, NULL, NULL, NULL, 'DEF856AB', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(64, 1, 3, '2026-01-28 18:28:35', 3570000.00, 3000000.00, 570000.00, NULL, 'Anulado', 1, NULL, NULL, 1, '2026-01-28 18:30:11', 'BACDBD63', NULL, NULL, NULL, NULL, NULL, NULL, 'Por que si', NULL, NULL),
(65, 1, 3, '2026-01-28 18:37:58', 3570000.00, 3000000.00, 570000.00, NULL, 'Anulado', 1, NULL, NULL, 1, '2026-01-28 18:42:39', '96FD2E6F', NULL, NULL, NULL, NULL, NULL, NULL, 'Cliente solicitó la cancelación de su pedido.', NULL, NULL),
(66, 1, 3, '2026-01-28 18:43:20', 3570000.00, 3000000.00, 570000.00, NULL, 'Anulado', 1, NULL, NULL, 1, '2026-01-28 18:44:17', 'ED577BD4', NULL, NULL, NULL, NULL, NULL, NULL, 'Cliente solicitó la cancelación de su pedido.', NULL, NULL),
(67, 1, 3, '2026-01-28 18:44:46', 3570000.00, 3000000.00, 570000.00, NULL, 'Enviado', 1, NULL, NULL, NULL, NULL, '4F18986C', '2026-01-28 21:18:47', '2026-02-12', NULL, 'TLL-20260129-67-8800', 'Servientrega', 'Se entregó a transportadora, tiempo estimado 3 días.\r\nCliente notificado por correo electrónico.\r\nSeguro incluido en el envío.', NULL, 1, NULL),
(68, 1, 3, '2026-02-02 12:55:39', 3570000.00, 3000000.00, 570000.00, NULL, 'Anulado', 1, NULL, NULL, 1, '2026-02-02 12:58:14', '89C00D9B', NULL, NULL, NULL, NULL, NULL, NULL, 'Cliente solicitó la cancelación de su pedido.', NULL, NULL),
(69, 73, 4, '2026-02-04 13:42:17', 357000.00, 300000.00, 57000.00, NULL, 'Confirmado', 1, NULL, NULL, NULL, NULL, '51E4C3F0', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(70, 73, 4, '2026-02-04 15:21:59', 357000.00, 300000.00, 57000.00, NULL, 'Confirmado', 1, NULL, NULL, NULL, NULL, 'D5D035A7', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(71, 73, 5, '2026-02-06 12:34:58', 714000.00, 600000.00, 114000.00, NULL, 'Confirmado', 1, NULL, NULL, NULL, NULL, '55AAAE32', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(72, 82, 6, '2026-02-06 13:12:24', 357000.00, 300000.00, 57000.00, NULL, 'Confirmado', 1, NULL, NULL, NULL, NULL, 'F4689E05', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(73, 73, 5, '2026-02-06 13:12:30', 357000.00, 300000.00, 57000.00, NULL, 'Confirmado', 1, NULL, NULL, NULL, NULL, '9AD38045', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(74, 1, 3, '2026-02-10 21:47:16', 714000.00, 600000.00, 114000.00, NULL, 'Confirmado', 1, NULL, NULL, NULL, NULL, 'A976D685', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(75, 73, 5, '2026-02-11 13:33:05', 357000.00, 300000.00, 57000.00, NULL, 'Confirmado', 1, NULL, NULL, NULL, NULL, '233834F5', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `factura_producto`
--

CREATE TABLE `factura_producto` (
  `ID_FacturaProducto` int(11) NOT NULL,
  `ID_Factura` int(11) NOT NULL,
  `ID_Producto` int(11) DEFAULT NULL,
  `Cantidad` int(11) DEFAULT NULL,
  `Precio_Unitario` decimal(10,2) NOT NULL DEFAULT 0.00,
  `Subtotal` decimal(10,2) NOT NULL,
  `Descuento_Aplicado` decimal(10,2) NOT NULL DEFAULT 0.00,
  `ID_Descuento` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `factura_producto`
--

INSERT INTO `factura_producto` (`ID_FacturaProducto`, `ID_Factura`, `ID_Producto`, `Cantidad`, `Precio_Unitario`, `Subtotal`, `Descuento_Aplicado`, `ID_Descuento`) VALUES
(1, 1, 1, 1, 30600000.00, 30600000.00, 0.00, NULL),
(2, 2, 1, 1, 30600000.00, 30600000.00, 0.00, NULL),
(3, 3, 1, 1, 30600000.00, 30600000.00, 0.00, NULL),
(4, 4, 1, 1, 30600000.00, 30600000.00, 0.00, NULL),
(5, 5, 1, 1, 30600000.00, 30600000.00, 0.00, NULL),
(6, 6, 1, 1, 30600000.00, 30600000.00, 0.00, NULL),
(7, 7, 1, 1, 29070000.00, 29070000.00, 5.00, NULL),
(8, 8, 1, 2, 30600000.00, 61200000.00, 0.00, NULL),
(9, 9, 1, 1, 30600000.00, 30600000.00, 0.00, NULL),
(10, 10, 1, 1, 30600000.00, 30600000.00, 0.00, NULL),
(11, 10, 1, 1, 29070000.00, 29070000.00, 5.00, NULL),
(12, 11, 1, 2, 30600000.00, 61200000.00, 0.00, NULL),
(13, 12, 1, 1, 29070000.00, 29070000.00, 5.00, NULL),
(14, 13, 1, 1, 29070000.00, 29070000.00, 5.00, NULL),
(15, 14, 1, 1, 29070000.00, 29070000.00, 5.00, NULL),
(16, 15, 1, 2, 29070000.00, 58140000.00, 3060000.00, 1),
(17, 16, 1, 1, 29070000.00, 29070000.00, 1530000.00, 1),
(18, 17, 1, 1, 29070000.00, 29070000.00, 1530000.00, 1),
(19, 18, 1, 1, 29070000.00, 29070000.00, 1530000.00, 1),
(20, 19, 1, 1, 29070000.00, 29070000.00, 1530000.00, 1),
(21, 20, 1, 1, 29070000.00, 29070000.00, 1530000.00, 1),
(22, 21, 1, 1, 29070000.00, 29070000.00, 1530000.00, 1),
(23, 22, 1, 1, 29070000.00, 29070000.00, 1530000.00, 1),
(24, 23, 1, 1, 30600000.00, 30600000.00, 0.00, NULL),
(25, 23, 1, 1, 29070000.00, 29070000.00, 1530000.00, 1),
(26, 24, 1, 1, 29070000.00, 29070000.00, 1530000.00, 1),
(27, 25, 1, 1, 29070000.00, 29070000.00, 1530000.00, 1),
(28, 26, 1, 3, 29070000.00, 87210000.00, 4590000.00, 4),
(29, 27, 1, 1, 29070000.00, 29070000.00, 1530000.00, 4),
(30, 28, 1, 5, 29070000.00, 99999999.99, 7650000.00, 4),
(31, 29, 1, 1, 29070000.00, 29070000.00, 1530000.00, 4),
(32, 30, 1, 10, 30600000.00, 99999999.99, 0.00, NULL),
(33, 31, 1, 1, 30600.00, 30600.00, 0.00, NULL),
(34, 32, 1, 1, 30600.00, 30600.00, 0.00, NULL),
(35, 33, 1, 1, 30600000.00, 30600000.00, 0.00, NULL),
(36, 34, 1, 4, 30600000.00, 99999999.99, 0.00, NULL),
(37, 35, 1, 1, 30600.00, 30600.00, 0.00, NULL),
(38, 36, 1, 1, 30600.00, 30600.00, 0.00, NULL),
(39, 37, 1, 1, 306.00, 306.00, 0.00, NULL),
(40, 38, 1, 5, 306000.00, 1530000.00, 0.00, NULL),
(41, 39, 1, 1, 306000.00, 306000.00, 0.00, NULL),
(42, 40, 1, 1, 306000.00, 306000.00, 0.00, NULL),
(43, 41, 1, 1, 306000.00, 306000.00, 0.00, NULL),
(44, 42, 1, 1, 306000.00, 306000.00, 0.00, NULL),
(45, 43, 1, 1, 306000.00, 306000.00, 0.00, NULL),
(46, 44, 1, 1, 306000.00, 306000.00, 0.00, NULL),
(47, 45, 1, 1, 306000.00, 306000.00, 0.00, NULL),
(48, 46, 1, 1, 306000.00, 306000.00, 0.00, NULL),
(49, 47, 1, 1, 306000.00, 306000.00, 0.00, NULL),
(50, 48, 1, 1, 306000.00, 306000.00, 0.00, NULL),
(51, 49, 1, 1, 306000.00, 306000.00, 0.00, NULL),
(52, 50, 1, 1, 306000.00, 306000.00, 0.00, NULL),
(53, 51, 1, 1, 306000.00, 306000.00, 0.00, NULL),
(54, 52, 1, 1, 306000.00, 306000.00, 0.00, NULL),
(55, 53, 1, 50, 206000.00, 10300000.00, 5000000.00, 5),
(56, 54, 1, 1, 306000.00, 306000.00, 0.00, NULL),
(57, 55, 1, 1, 306000.00, 306000.00, 0.00, NULL),
(58, 56, 2, 2, 120000.00, 240000.00, 0.00, NULL),
(59, 57, 7, 1, 300000.00, 300000.00, 0.00, NULL),
(60, 58, 7, 1, 300000.00, 300000.00, 0.00, NULL),
(61, 59, 7, 1, 300000.00, 300000.00, 0.00, NULL),
(62, 60, 7, 1, 300000.00, 300000.00, 0.00, NULL),
(63, 61, 7, 1, 300000.00, 300000.00, 0.00, NULL),
(64, 62, 7, 1, 300000.00, 300000.00, 0.00, NULL),
(65, 63, 7, 4, 300000.00, 1200000.00, 0.00, NULL),
(66, 64, 7, 10, 300000.00, 3000000.00, 0.00, NULL),
(67, 65, 7, 10, 300000.00, 3000000.00, 0.00, NULL),
(68, 66, 7, 10, 300000.00, 3000000.00, 0.00, NULL),
(69, 67, 7, 10, 300000.00, 3000000.00, 0.00, NULL),
(70, 68, 7, 10, 300000.00, 3000000.00, 0.00, NULL),
(71, 69, 7, 1, 300000.00, 300000.00, 0.00, NULL),
(72, 70, 7, 1, 300000.00, 300000.00, 0.00, NULL),
(73, 71, 7, 2, 300000.00, 600000.00, 0.00, NULL),
(74, 72, 7, 1, 300000.00, 300000.00, 0.00, NULL),
(75, 73, 7, 1, 300000.00, 300000.00, 0.00, NULL),
(76, 74, 7, 2, 300000.00, 600000.00, 0.00, NULL),
(77, 75, 7, 1, 300000.00, 300000.00, 0.00, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `favorito`
--

CREATE TABLE `favorito` (
  `ID_Favorito` int(11) NOT NULL,
  `ID_Usuario` int(11) NOT NULL,
  `ID_Articulo` int(11) DEFAULT NULL,
  `Fecha` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `genero`
--

CREATE TABLE `genero` (
  `ID_Genero` int(11) NOT NULL,
  `N_Genero` varchar(45) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `genero`
--

INSERT INTO `genero` (`ID_Genero`, `N_Genero`) VALUES
(1, 'Hombre'),
(2, 'Mujer'),
(3, 'Niños');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `metodo_pago`
--

CREATE TABLE `metodo_pago` (
  `ID_Metodo_Pago` int(11) NOT NULL,
  `T_Pago` varchar(45) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `metodo_pago`
--

INSERT INTO `metodo_pago` (`ID_Metodo_Pago`, `T_Pago`) VALUES
(1, 'Tarjeta'),
(2, 'Efectivo'),
(3, 'PSE');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `palabras_bloqueadas`
--

CREATE TABLE `palabras_bloqueadas` (
  `ID_Palabra` int(11) NOT NULL,
  `Palabra` varchar(100) NOT NULL,
  `Gravedad` enum('Leve','Media','Grave') DEFAULT 'Media',
  `Activo` tinyint(1) DEFAULT 1,
  `Fecha_Creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NULL DEFAULT NULL,
  `used` tinyint(4) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedido_seguimiento`
--

CREATE TABLE `pedido_seguimiento` (
  `ID_Seguimiento` int(11) NOT NULL,
  `ID_Factura` int(11) NOT NULL,
  `Estado` varchar(50) NOT NULL,
  `Descripcion` text DEFAULT NULL,
  `ID_Usuario` int(11) DEFAULT NULL,
  `Fecha` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pedido_seguimiento`
--

INSERT INTO `pedido_seguimiento` (`ID_Seguimiento`, `ID_Factura`, `Estado`, `Descripcion`, `ID_Usuario`, `Fecha`) VALUES
(1, 38, 'Preparando', 'Estamos preparando tu pedido', 73, '2026-01-14 23:02:54'),
(2, 38, 'Enviado', 'Envío registrado: Ya se envio le seguiremos actualizando los estados', 73, '2026-01-14 23:04:07'),
(3, 50, 'Preparando', '', 73, '2026-01-15 16:23:54'),
(4, 50, 'Enviado', 'Envío registrado: Se entregó a transportadora, tiempo estimado 3 días\r\nCliente notificado por correo\r\nSeguro incluido', 73, '2026-01-15 16:24:24'),
(5, 50, 'Entregado', 'see+¿dwwecwf', 73, '2026-01-15 16:29:02'),
(6, 2, 'Anulado', 'No vendemos Negros', 73, '2026-01-15 16:31:09'),
(7, 3, 'Preparando', '', 73, '2026-01-15 16:34:13'),
(8, 3, 'Enviado', 'Envío registrado: Se entregó a transportadora, tiempo estimado 3 días\r\nSe entregó a transportadora, tiempo estimado 3 días\r\nSe entregó a transportadora, tiempo estimado 3 días\r\nSe entregó a transportadora, tiempo estimado 3 días\r\nSe entregó a transportadora, tiempo estimado 3 días\r\nSe entregó a transportadora, tiempo estimado 3 días\r\nSe entregó a transportadora, tiempo estimado 3 días\r\nSe entregó a transportadora, tiempo estimado 3 días', 73, '2026-01-15 16:34:39'),
(9, 4, 'Preparando', '', 1, '2026-01-25 21:41:04'),
(10, 4, 'Enviado', '', 1, '2026-01-25 21:41:44'),
(11, 4, 'Entregado', '', 1, '2026-01-25 21:42:09'),
(12, 5, 'Preparando', '', 1, '2026-01-25 21:45:02'),
(13, 6, 'Preparando', '', 1, '2026-01-25 21:46:13'),
(14, 6, 'Enviado', 'Envío registrado', 1, '2026-01-25 21:46:38'),
(15, 58, 'Anulado', 'Cliente solicitó la cancelación de su pedido.', 1, '2026-01-25 23:55:20'),
(16, 59, 'Preparando', 'Producto en proceso de preparación y empaquetado en nuestras oficinas. Se realiza verificación de calidad antes del envío.', 1, '2026-01-25 23:58:00'),
(17, 60, 'Anulado', 'Cliente solicitó la cancelación de su pedido.', 1, '2026-01-26 21:41:03'),
(18, 61, 'Preparando', 'Producto en proceso de preparación y empaquetado en nuestras oficinas. Se realiza verificación de calidad antes del envío.', 1, '2026-01-26 21:54:44'),
(19, 61, 'Enviado', 'Envío registrado', 1, '2026-01-26 22:50:02'),
(20, 61, 'Devuelto', 'Cliente no aceptó el producto al momento de la entrega.', 1, '2026-01-26 22:50:32'),
(21, 61, 'Anulado', 'Cliente solicitó la cancelación de su pedido.', 1, '2026-01-26 22:50:55'),
(22, 62, 'Preparando', 'Producto en proceso de preparación y empaquetado en nuestras oficinas. Se realiza verificación de calidad antes del envío.', 1, '2026-01-26 22:52:04'),
(23, 62, 'Enviado', 'Envío registrado', 1, '2026-01-26 23:11:08'),
(24, 7, 'Preparando', 'Producto en proceso de preparación y empaquetado en nuestras oficinas. Se realiza verificación de calidad antes del envío.', 1, '2026-01-26 23:32:47'),
(25, 57, 'Preparando', 'Producto en proceso de preparación y empaquetado en nuestras oficinas. Se realiza verificación de calidad antes del envío.', 1, '2026-01-26 23:34:39'),
(26, 57, 'Enviado', 'Envío registrado', 1, '2026-01-26 23:54:02'),
(27, 8, 'Preparando', 'Producto en proceso de preparación y empaquetado en nuestras oficinas. Se realiza verificación de calidad antes del envío.', 1, '2026-01-26 23:54:23'),
(28, 8, 'Enviado', 'Envío registrado', 1, '2026-01-26 23:59:51'),
(29, 9, 'Preparando', 'Producto en proceso de preparación y empaquetado en nuestras oficinas. Se realiza verificación de calidad antes del envío.', 1, '2026-01-27 00:00:09'),
(30, 10, 'Preparando', 'Producto en proceso de preparación y empaquetado en nuestras oficinas. Se realiza verificación de calidad antes del envío.', 1, '2026-01-27 01:37:44'),
(31, 10, 'Enviado', 'Envío registrado', 1, '2026-01-27 01:38:07'),
(32, 1, 'Preparando', 'Producto en proceso de preparación y empaquetado en nuestras oficinas. Se realiza verificación de calidad antes del envío.', 1, '2026-01-27 01:38:24'),
(33, 11, 'Preparando', 'Producto en proceso de preparación y empaquetado en nuestras oficinas. Se realiza verificación de calidad antes del envío.', 1, '2026-01-27 02:03:17'),
(34, 5, 'Enviado', 'Envío registrado: Se entregó a transportadora, tiempo estimado 3 días.\r\nCliente notificado por correo electrónico.\r\nSeguro incluido en el envío.', 1, '2026-01-27 02:22:24'),
(35, 62, 'Retrasado', 'Retraso por condiciones climáticas adversas que afectan el transporte.', 1, '2026-01-27 02:26:52'),
(36, 62, 'Devuelto', 'Cliente no aceptó el producto al momento de la entrega.', 1, '2026-01-27 02:28:07'),
(37, 62, 'Anulado', 'Cliente solicitó la cancelación de su pedido.', 1, '2026-01-27 02:31:24'),
(38, 6, 'Devuelto', 'Producto llegó dañado durante el transporte.', 1, '2026-01-27 02:32:49'),
(39, 6, 'Preparando', 'Producto devuelto en proceso de re-preparación. Se verifica el estado del producto y se realiza nuevo empaque para reenvío.', 1, '2026-01-27 02:42:58'),
(40, 6, 'Enviado', 'Envío registrado: Se entregó a transportadora, tiempo estimado 3 días.\r\nCliente notificado por correo electrónico.\r\nSeguro incluido en el envío.', 1, '2026-01-27 02:48:13'),
(41, 38, 'Enviado', 'Fecha estimada de entrega actualizada a 31/01/2026. Motivo: porque quiero', 1, '2026-01-27 03:32:30'),
(42, 38, 'Retrasado', 'jaja', 1, '2026-01-27 03:33:02'),
(43, 3, 'Entregado', 'Producto entregado satisfactoriamente. Firma recibida y sin novedades en la entrega.', 1, '2026-01-27 23:20:26'),
(44, 57, 'Entregado', 'Producto entregado satisfactoriamente. Firma recibida y sin novedades en la entrega.', 1, '2026-01-27 23:20:49'),
(45, 12, 'Preparando', 'Producto en proceso de preparación y empaquetado en nuestras oficinas. Se realiza verificación de calidad antes del envío.', 1, '2026-01-27 23:39:02'),
(46, 8, 'Retrasado', 'dwdw', 1, '2026-01-28 01:08:37'),
(47, 13, 'Preparando', 'Producto en proceso de preparación y empaquetado en nuestras oficinas. Se realiza verificación de calidad antes del envío.', 1, '2026-01-28 04:04:27'),
(48, 10, 'Devuelto', 'Producto llegó dañado durante el transporte.', 1, '2026-01-28 04:07:03'),
(49, 10, 'Preparando', 'Producto devuelto en proceso de re-preparación. Se verifica el estado del producto y se realiza nuevo empaque para reenvío.', 1, '2026-01-28 04:07:52'),
(50, 64, 'Anulado', 'Por que si', 1, '2026-01-28 23:30:11'),
(51, 64, 'Anulado', 'Stock devuelto por anulación de pedido: 10 unidades de \'Anillos\'. Stock anterior: 40, Stock nuevo: 50', 1, '2026-01-28 23:30:11'),
(52, 65, 'Anulado', 'Cliente solicitó la cancelación de su pedido.', 1, '2026-01-28 23:42:39'),
(53, 65, 'Anulado', 'Stock devuelto por anulación de pedido: 10 unidades de \'Anillos\'. Stock anterior: 40, Stock nuevo: 50', 1, '2026-01-28 23:42:39'),
(54, 66, 'Anulado', 'Cliente solicitó la cancelación de su pedido.', 1, '2026-01-28 23:44:17'),
(55, 66, 'Anulado', 'Stock devuelto por anulación de pedido: 10 unidades de \'Anillos\'. Stock anterior: 40, Stock nuevo: 50', 1, '2026-01-28 23:44:17'),
(56, 67, 'Preparando', 'Producto en proceso de preparación y empaquetado en nuestras oficinas. Se realiza verificación de calidad antes del envío.', 1, '2026-01-28 23:45:31'),
(57, 14, 'Anulado', 'Cliente solicitó la cancelación de su pedido.', 1, '2026-01-29 00:04:28'),
(58, 67, 'Enviado', 'Envío registrado: Se entregó a transportadora, tiempo estimado 3 días.\r\nCliente notificado por correo electrónico.\r\nSeguro incluido en el envío. - Entrega estimada: 12/02/2026', 1, '2026-01-29 02:18:47'),
(59, 68, 'Anulado', 'Cliente solicitó la cancelación de su pedido.', 1, '2026-02-02 17:58:14'),
(60, 68, 'Anulado', 'Stock devuelto por anulación de pedido: 10 unidades de \'Anillos\'. Stock anterior: 30, Stock nuevo: 40', 1, '2026-02-02 17:58:14'),
(61, 15, 'Preparando', 'Producto en proceso de preparación y empaquetado en nuestras oficinas. Se realiza verificación de calidad antes del envío.', 1, '2026-02-02 17:59:11'),
(62, 15, 'Enviado', 'Envío registrado: Se entregó a transportadora, tiempo estimado 3 días.\r\nCliente notificado por correo electrónico.\r\nSeguro incluido en el envío. - Entrega estimada: 28/02/2026', 1, '2026-02-02 18:03:10'),
(63, 67, 'Enviado', 'Fecha estimada de entrega actualizada a 12/02/2026', 1, '2026-02-02 18:40:50');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `permiso`
--

CREATE TABLE `permiso` (
  `ID_Permiso` int(11) NOT NULL,
  `N_Permiso` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `permiso`
--

INSERT INTO `permiso` (`ID_Permiso`, `N_Permiso`) VALUES
(1, 'Desactivar Producto'),
(2, 'Agregar Producto'),
(3, 'Eliminar Producto'),
(4, 'Editar Precio'),
(5, 'Gestionar Roles'),
(6, 'Comprar Producto');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `precio`
--

CREATE TABLE `precio` (
  `ID_precio` int(11) NOT NULL,
  `Valor` decimal(10,2) NOT NULL,
  `FechaAct` datetime NOT NULL,
  `Activo` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `precio`
--

INSERT INTO `precio` (`ID_precio`, `Valor`, `FechaAct`, `Activo`) VALUES
(1, 300000.00, '2026-01-17 12:28:35', 1),
(5, 1000000.00, '2026-01-17 10:56:25', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `producto`
--

CREATE TABLE `producto` (
  `ID_Producto` int(11) NOT NULL,
  `ID_Articulo` int(11) NOT NULL,
  `Nombre_Producto` varchar(255) DEFAULT NULL,
  `Foto` varchar(300) NOT NULL,
  `Porcentaje` decimal(5,2) NOT NULL,
  `Cantidad` int(11) NOT NULL DEFAULT 0,
  `Activo` tinyint(1) NOT NULL DEFAULT 0,
  `ID_Atributo1` int(11) DEFAULT NULL,
  `ID_Atributo2` int(11) DEFAULT NULL,
  `ID_Atributo3` int(11) DEFAULT NULL,
  `ValorAtributo1` varchar(50) DEFAULT NULL,
  `ValorAtributo2` varchar(50) DEFAULT NULL,
  `ValorAtributo3` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `producto`
--

INSERT INTO `producto` (`ID_Producto`, `ID_Articulo`, `Nombre_Producto`, `Foto`, `Porcentaje`, `Cantidad`, `Activo`, `ID_Atributo1`, `ID_Atributo2`, `ID_Atributo3`, `ValorAtributo1`, `ValorAtributo2`, `ValorAtributo3`) VALUES
(7, 5, 'Anillo', 'ImgProducto/Accesorios/Hombre/Anillo/1768679845_696be9a59f232.png', 0.00, 31, 1, 3, 2, NULL, '19', 'Amarrillo', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `resena`
--

CREATE TABLE `resena` (
  `ID_Resena` int(11) NOT NULL,
  `ID_Usuario` int(11) NOT NULL,
  `ID_Articulo` int(11) NOT NULL,
  `ID_Producto` int(11) NOT NULL,
  `ID_Factura` int(11) DEFAULT NULL,
  `Calificacion` tinyint(1) NOT NULL CHECK (`Calificacion` between 1 and 5),
  `Titulo` varchar(100) DEFAULT NULL,
  `Comentario` text NOT NULL,
  `Fecha_Resena` timestamp NOT NULL DEFAULT current_timestamp(),
  `Fecha_Actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `Activo` tinyint(1) DEFAULT 1,
  `Reportado` tinyint(1) DEFAULT 0,
  `Util_Positivo` int(11) DEFAULT 0,
  `Util_Negativo` int(11) DEFAULT 0,
  `Modificado` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `resena`
--

INSERT INTO `resena` (`ID_Resena`, `ID_Usuario`, `ID_Articulo`, `ID_Producto`, `ID_Factura`, `Calificacion`, `Titulo`, `Comentario`, `Fecha_Resena`, `Fecha_Actualizacion`, `Activo`, `Reportado`, `Util_Positivo`, `Util_Negativo`, `Modificado`) VALUES
(1, 73, 5, 7, NULL, 4, 'buen comentario', 'Este reporte se elabora para registrar el proceso de implementación y verificación de la funcionalidad de reseñas en la tienda online Tulook, con el propósito de evaluar su correcto funcionamiento y extraer lecciones aprendidas durante las pruebas realizadas.s', '2026-02-11 19:55:54', '2026-02-12 00:05:46', 1, 0, 1, 0, 1),
(2, 82, 5, 7, NULL, 4, 'buen comentario', 'hola amigos', '2026-02-11 21:41:50', '2026-02-12 00:40:45', 1, 0, 0, 1, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `resena_foto`
--

CREATE TABLE `resena_foto` (
  `ID_Foto` int(11) NOT NULL,
  `ID_Resena` int(11) NOT NULL,
  `Foto` varchar(300) NOT NULL,
  `Activo` tinyint(1) DEFAULT 1,
  `Orden` int(11) DEFAULT 0,
  `Fecha_Subida` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `resena_foto`
--

INSERT INTO `resena_foto` (`ID_Foto`, `ID_Resena`, `Foto`, `Activo`, `Orden`, `Fecha_Subida`) VALUES
(1, 1, 'uploads/resenas/resena_698ce0e9bde78.png', 0, 0, '2026-02-11 20:04:57'),
(2, 1, 'uploads/resenas/resena_698ce103d7c88.jpeg', 0, 0, '2026-02-11 20:05:23'),
(3, 1, 'uploads/resenas/resena_698d19774320d.jpeg', 0, 0, '2026-02-12 00:06:15'),
(4, 1, 'uploads/resenas/resena_698d219b80091.png', 0, 0, '2026-02-12 00:40:59');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `resena_reporte`
--

CREATE TABLE `resena_reporte` (
  `ID_Reporte` int(11) NOT NULL,
  `ID_Resena` int(11) NOT NULL,
  `ID_Usuario` int(11) NOT NULL,
  `Motivo` varchar(100) NOT NULL,
  `Descripcion` text DEFAULT NULL,
  `Fecha_Reporte` timestamp NOT NULL DEFAULT current_timestamp(),
  `Estado` enum('Pendiente','Revisado','Descartado') DEFAULT 'Pendiente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `resena_reporte`
--

INSERT INTO `resena_reporte` (`ID_Reporte`, `ID_Resena`, `ID_Usuario`, `Motivo`, `Descripcion`, `Fecha_Reporte`, `Estado`) VALUES
(1, 1, 82, 'Spam', 'malo', '2026-02-11 20:05:56', 'Revisado');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `resena_respuesta`
--

CREATE TABLE `resena_respuesta` (
  `ID_Respuesta` int(11) NOT NULL,
  `ID_Resena` int(11) NOT NULL,
  `ID_Usuario` int(11) NOT NULL,
  `Respuesta` text NOT NULL,
  `Fecha_Respuesta` timestamp NOT NULL DEFAULT current_timestamp(),
  `Activo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `resena_respuesta`
--

INSERT INTO `resena_respuesta` (`ID_Respuesta`, `ID_Resena`, `ID_Usuario`, `Respuesta`, `Fecha_Respuesta`, `Activo`) VALUES
(1, 2, 73, 'hola', '2026-02-11 22:03:19', 1),
(2, 1, 73, 'hola', '2026-02-12 00:04:15', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `resena_voto`
--

CREATE TABLE `resena_voto` (
  `ID_Voto` int(11) NOT NULL,
  `ID_Resena` int(11) NOT NULL,
  `ID_Usuario` int(11) NOT NULL,
  `Es_Positivo` tinyint(1) NOT NULL,
  `Fecha_Voto` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `resena_voto`
--

INSERT INTO `resena_voto` (`ID_Voto`, `ID_Resena`, `ID_Usuario`, `Es_Positivo`, `Fecha_Voto`) VALUES
(1, 1, 73, 1, '2026-02-11 21:40:34'),
(2, 2, 73, 0, '2026-02-12 00:40:45');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rol`
--

CREATE TABLE `rol` (
  `ID_Rol` int(11) NOT NULL,
  `Roles` varchar(45) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `rol`
--

INSERT INTO `rol` (`ID_Rol`, `Roles`) VALUES
(1, 'Administrador'),
(2, 'Editor'),
(3, 'Cliente');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rol_permiso`
--

CREATE TABLE `rol_permiso` (
  `ID_Rol` int(11) NOT NULL,
  `ID_Permiso` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `rol_permiso`
--

INSERT INTO `rol_permiso` (`ID_Rol`, `ID_Permiso`) VALUES
(1, 1),
(1, 2),
(1, 3),
(1, 4),
(1, 5),
(1, 6),
(2, 1),
(2, 4),
(2, 6),
(3, 6);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `subcategoria`
--

CREATE TABLE `subcategoria` (
  `ID_SubCategoria` int(11) NOT NULL,
  `SubCategoria` varchar(50) NOT NULL,
  `ID_Categoria` int(11) DEFAULT NULL,
  `AtributosRequeridos` varchar(100) DEFAULT '1,2' COMMENT 'IDs de tipo_atributo separados por coma'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `subcategoria`
--

INSERT INTO `subcategoria` (`ID_SubCategoria`, `SubCategoria`, `ID_Categoria`, `AtributosRequeridos`) VALUES
(1, 'Jeans', 1, '1,2'),
(2, 'Pantaloneta', 1, '1,2'),
(3, 'Camiseta', 1, '1,2'),
(4, 'Camisa', 1, '1,2'),
(5, 'Sudadera', 1, '1,2'),
(6, 'Boxer', 1, '1,2'),
(7, 'Lenceria', 1, '1,2'),
(8, 'Gorras', 2, '2'),
(9, 'Sombreros', 2, '1,2'),
(10, 'Relojes', 2, '2'),
(11, 'Perfumes', 2, '4'),
(12, 'Gafas', 2, '2'),
(13, 'Morrales', 2, '5,2'),
(14, 'Billeteras', 2, '5,2'),
(15, 'Cinturon', 2, '3,2'),
(16, 'Llaveros', 2, '2'),
(17, 'Anillo', 2, '3,2'),
(18, 'Sandalias', 3, '1,2'),
(19, 'Botas', 3, '1,2'),
(20, 'chanclas', 3, '1,2'),
(21, 'crocs', 3, '1,2'),
(22, 'Tenis', 3, '1,2');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_atributo`
--

CREATE TABLE `tipo_atributo` (
  `ID_TipoAtributo` int(11) NOT NULL,
  `Nombre` varchar(50) NOT NULL,
  `Descripcion` varchar(255) DEFAULT NULL,
  `Activo` tinyint(4) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tipo_atributo`
--

INSERT INTO `tipo_atributo` (`ID_TipoAtributo`, `Nombre`, `Descripcion`, `Activo`) VALUES
(1, 'Talla', 'Para ropa, calzado y Sombreros', 1),
(2, 'Color', 'Color del producto', 1),
(3, 'Medida', 'Para anillos y cinturones', 1),
(4, 'Volumen', 'Para perfumes', 1),
(5, 'Tamaño', 'Para morrales y billeteras', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_documento`
--

CREATE TABLE `tipo_documento` (
  `ID_TD` int(11) NOT NULL,
  `Documento` varchar(45) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `tipo_documento`
--

INSERT INTO `tipo_documento` (`ID_TD`, `Documento`) VALUES
(1, 'Cédula de Ciudadanía'),
(2, 'Tarjeta de Identidad'),
(3, 'Cédula de Extranjería');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `ID_Usuario` int(11) NOT NULL,
  `Nombre` varchar(50) NOT NULL,
  `Apellido` varchar(50) NOT NULL,
  `ID_Rol` int(11) NOT NULL DEFAULT 3,
  `ID_TD` int(11) NOT NULL DEFAULT 1,
  `N_Documento` int(11) NOT NULL,
  `Correo` varchar(50) NOT NULL,
  `Celular` varchar(50) NOT NULL,
  `Contrasena` varchar(255) NOT NULL DEFAULT '',
  `token_recuperacion` varchar(255) DEFAULT NULL,
  `token_expira` datetime DEFAULT NULL,
  `Activo` tinyint(1) NOT NULL DEFAULT 1,
  `Num_Advertencias` int(11) DEFAULT 0,
  `Motivo_Desactivacion` text DEFAULT NULL,
  `Fecha_Desactivacion` datetime DEFAULT NULL,
  `ID_Admin_Desactiva` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`ID_Usuario`, `Nombre`, `Apellido`, `ID_Rol`, `ID_TD`, `N_Documento`, `Correo`, `Celular`, `Contrasena`, `token_recuperacion`, `token_expira`, `Activo`, `Num_Advertencias`, `Motivo_Desactivacion`, `Fecha_Desactivacion`, `ID_Admin_Desactiva`) VALUES
(1, 'Luis', 'Vega', 1, 1, 1038105095, 'leoescorp2552@gmail.com', '3134454668', '$2y$10$tKW7iiFCwCE7uSMwoQ4/Pef1fazEQIKLTIxCrfIFC4xq78ZGrizZK', NULL, NULL, 1, 0, NULL, NULL, NULL),
(73, 'juan', 'pablo', 1, 1, 1105467236, 'kiusmila9@gmail.com', '3107928675', '$2y$10$AxiPLKpvszPDs6zbtGZJ/eTf/dCksakHF6LVmbOP.9TO.7mKaoKQK', NULL, NULL, 1, 0, NULL, NULL, NULL),
(77, 'Carlos santiago', 'Gómez', 2, 1, 1038105094, 'leo@gmail.com', '3134404640', '$2y$10$TOb5K33ES40KEBPYNK4Hput2UeEs3kgK1a9QqS/maZU/69TDaNWNW', NULL, NULL, 1, 0, NULL, NULL, NULL),
(78, 'Daniel Steven', 'Suarez Londoño', 1, 1, 1057094074, 'daniydiegocraft@gmail.com', '3122901524', '$2y$10$elmMO.JvDyA6bDbM2eL6x.U0CXjpfKHfPenh/cwxupNfbX8sKnNWm', NULL, NULL, 1, 0, NULL, NULL, NULL),
(79, 'Carlos santiago', 'Gómez', 2, 1, 1038105080, 'leoescorp25@gmail.com', '3145626858', '$2y$10$pHAoWJ.CgBL7av9d72JMk.SXNo6aWJCtE7dnQKJAjpXyB.9nxca1a', NULL, NULL, 1, 0, NULL, NULL, NULL),
(80, 'Luis Fernando', 'Acosta Vega', 2, 1, 2038105090, 'eoescorp2552@gmail.com', '3134404696', '$2y$10$.uAdq68Ef5f8ANC6E1t9puRlhKCoF7tdKpbX./YgCgFWWdWT5XXei', NULL, NULL, 1, 0, NULL, NULL, NULL),
(81, 'Luis Fernando', 'Acosta Vega', 2, 1, 1038105090, 'leoescorp@gmail.com', '3134404667', '$2y$10$tPlOiAMssOKa8.y6wye9G.yqA9Nh34ZunAYrnUjgvB7zHQbGWGGBS', NULL, NULL, 1, 0, NULL, NULL, NULL),
(82, 'Juan', 'Ciervo', 3, 1, 1105462589, 'ciervoj72@gmail.com', '3202184958', '$2y$10$eFb1uCF6fyPa7Wr2HdsSF.n9OD5UnUX65oLpSHUXrmD/ClP12Mraa', NULL, NULL, 1, 0, 'Porque estas inactivo por mas de una semana.', '2026-02-06 19:06:04', 73);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `advertencias_usuario`
--
ALTER TABLE `advertencias_usuario`
  ADD PRIMARY KEY (`ID_Advertencia`),
  ADD KEY `ID_Usuario` (`ID_Usuario`),
  ADD KEY `ID_Admin` (`ID_Admin`);

--
-- Indices de la tabla `articulo`
--
ALTER TABLE `articulo`
  ADD PRIMARY KEY (`ID_Articulo`),
  ADD KEY `Fk_Genero_idx` (`ID_Genero`),
  ADD KEY `Fk_Categoria_idx` (`ID_Categoria`),
  ADD KEY `Fk_SubCategoria_idx` (`ID_SubCategoria`),
  ADD KEY `Fk_Precio_idx` (`ID_Precio`);

--
-- Indices de la tabla `atributo_valor`
--
ALTER TABLE `atributo_valor`
  ADD PRIMARY KEY (`ID_AtributoValor`),
  ADD KEY `ID_TipoAtributo` (`ID_TipoAtributo`);

--
-- Indices de la tabla `categoria`
--
ALTER TABLE `categoria`
  ADD PRIMARY KEY (`ID_Categoria`);

--
-- Indices de la tabla `categoria_subcategoria`
--
ALTER TABLE `categoria_subcategoria`
  ADD PRIMARY KEY (`ID_Categoria`,`ID_SubCategoria`),
  ADD KEY `fk_subcat` (`ID_SubCategoria`);

--
-- Indices de la tabla `color`
--
ALTER TABLE `color`
  ADD PRIMARY KEY (`ID_Color`);

--
-- Indices de la tabla `descuento`
--
ALTER TABLE `descuento`
  ADD PRIMARY KEY (`ID_Descuento`),
  ADD UNIQUE KEY `Codigo` (`Codigo`),
  ADD KEY `ID_Articulo` (`ID_Articulo`),
  ADD KEY `ID_Categoria` (`ID_Categoria`);

--
-- Indices de la tabla `descuento_usuario`
--
ALTER TABLE `descuento_usuario`
  ADD PRIMARY KEY (`ID_DescuentoUsuario`),
  ADD UNIQUE KEY `ID_Descuento` (`ID_Descuento`,`ID_Usuario`),
  ADD KEY `ID_Usuario` (`ID_Usuario`);

--
-- Indices de la tabla `direccion`
--
ALTER TABLE `direccion`
  ADD PRIMARY KEY (`ID_Direccion`),
  ADD KEY `ID_Usuario` (`ID_Usuario`);

--
-- Indices de la tabla `factura`
--
ALTER TABLE `factura`
  ADD PRIMARY KEY (`ID_Factura`),
  ADD UNIQUE KEY `CodigoAcceso` (`Codigo_Acceso`),
  ADD KEY `Fk_Metodo_Pago_idx` (`ID_Metodo_Pago`),
  ADD KEY `Fk_Usuario_idx` (`ID_Usuario`),
  ADD KEY `UsuarioConfirmacion` (`Usuario_Confirmacion`),
  ADD KEY `UsuarioAnulacion` (`Usuario_Anulacion`),
  ADD KEY `Usuario_Envio` (`Usuario_Envio`),
  ADD KEY `Usuario_Entrega` (`Usuario_Entrega`),
  ADD KEY `idx_factura_estado` (`Estado`),
  ADD KEY `idx_factura_fecha` (`Fecha_Factura`),
  ADD KEY `idx_factura_usuario` (`ID_Usuario`),
  ADD KEY `idx_factura_envio` (`Fecha_Envio`),
  ADD KEY `idx_factura_entrega` (`Fecha_Entrega`),
  ADD KEY `idx_factura_fecha_estimada` (`Fecha_Estimada_Entrega`);

--
-- Indices de la tabla `factura_producto`
--
ALTER TABLE `factura_producto`
  ADD PRIMARY KEY (`ID_FacturaProducto`),
  ADD KEY `Fk_Producto_idx` (`ID_Producto`),
  ADD KEY `Fk_FP_Factura_idx` (`ID_Factura`),
  ADD KEY `fk_factura_producto_descuento` (`ID_Descuento`);

--
-- Indices de la tabla `favorito`
--
ALTER TABLE `favorito`
  ADD PRIMARY KEY (`ID_Favorito`);

--
-- Indices de la tabla `genero`
--
ALTER TABLE `genero`
  ADD PRIMARY KEY (`ID_Genero`);

--
-- Indices de la tabla `metodo_pago`
--
ALTER TABLE `metodo_pago`
  ADD PRIMARY KEY (`ID_Metodo_Pago`);

--
-- Indices de la tabla `palabras_bloqueadas`
--
ALTER TABLE `palabras_bloqueadas`
  ADD PRIMARY KEY (`ID_Palabra`),
  ADD UNIQUE KEY `Palabra` (`Palabra`);

--
-- Indices de la tabla `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_token` (`token`),
  ADD KEY `idx_email` (`email`);

--
-- Indices de la tabla `pedido_seguimiento`
--
ALTER TABLE `pedido_seguimiento`
  ADD PRIMARY KEY (`ID_Seguimiento`),
  ADD KEY `ID_Usuario` (`ID_Usuario`),
  ADD KEY `idx_seguimiento_factura` (`ID_Factura`);

--
-- Indices de la tabla `permiso`
--
ALTER TABLE `permiso`
  ADD PRIMARY KEY (`ID_Permiso`);

--
-- Indices de la tabla `precio`
--
ALTER TABLE `precio`
  ADD PRIMARY KEY (`ID_precio`);

--
-- Indices de la tabla `producto`
--
ALTER TABLE `producto`
  ADD PRIMARY KEY (`ID_Producto`),
  ADD KEY `Fk_Producto_Ropa_idx` (`ID_Articulo`),
  ADD KEY `Fk_Producto_Precio_idx` (`Porcentaje`),
  ADD KEY `idx_atributo1` (`ID_Atributo1`),
  ADD KEY `idx_atributo2` (`ID_Atributo2`),
  ADD KEY `idx_atributo3` (`ID_Atributo3`);

--
-- Indices de la tabla `resena`
--
ALTER TABLE `resena`
  ADD PRIMARY KEY (`ID_Resena`),
  ADD UNIQUE KEY `unique_resena_usuario_producto` (`ID_Usuario`,`ID_Producto`),
  ADD KEY `ID_Articulo` (`ID_Articulo`),
  ADD KEY `ID_Producto` (`ID_Producto`),
  ADD KEY `ID_Factura` (`ID_Factura`);

--
-- Indices de la tabla `resena_foto`
--
ALTER TABLE `resena_foto`
  ADD PRIMARY KEY (`ID_Foto`),
  ADD KEY `ID_Resena` (`ID_Resena`);

--
-- Indices de la tabla `resena_reporte`
--
ALTER TABLE `resena_reporte`
  ADD PRIMARY KEY (`ID_Reporte`),
  ADD KEY `ID_Resena` (`ID_Resena`),
  ADD KEY `ID_Usuario` (`ID_Usuario`);

--
-- Indices de la tabla `resena_respuesta`
--
ALTER TABLE `resena_respuesta`
  ADD PRIMARY KEY (`ID_Respuesta`),
  ADD KEY `ID_Resena` (`ID_Resena`),
  ADD KEY `ID_Usuario` (`ID_Usuario`);

--
-- Indices de la tabla `resena_voto`
--
ALTER TABLE `resena_voto`
  ADD PRIMARY KEY (`ID_Voto`),
  ADD UNIQUE KEY `unique_voto` (`ID_Resena`,`ID_Usuario`),
  ADD KEY `ID_Usuario` (`ID_Usuario`);

--
-- Indices de la tabla `rol`
--
ALTER TABLE `rol`
  ADD PRIMARY KEY (`ID_Rol`);

--
-- Indices de la tabla `rol_permiso`
--
ALTER TABLE `rol_permiso`
  ADD PRIMARY KEY (`ID_Rol`,`ID_Permiso`),
  ADD KEY `IdPermiso` (`ID_Permiso`);

--
-- Indices de la tabla `subcategoria`
--
ALTER TABLE `subcategoria`
  ADD PRIMARY KEY (`ID_SubCategoria`);

--
-- Indices de la tabla `tipo_atributo`
--
ALTER TABLE `tipo_atributo`
  ADD PRIMARY KEY (`ID_TipoAtributo`);

--
-- Indices de la tabla `tipo_documento`
--
ALTER TABLE `tipo_documento`
  ADD PRIMARY KEY (`ID_TD`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`ID_Usuario`),
  ADD UNIQUE KEY `ID_Usuario_UNIQUE` (`ID_Usuario`),
  ADD UNIQUE KEY `N_Documento_UNIQUE` (`N_Documento`),
  ADD UNIQUE KEY `Correo_UNIQUE` (`Correo`),
  ADD UNIQUE KEY `Celular_UNIQUE` (`Celular`),
  ADD KEY `FK.TD_idx` (`ID_TD`),
  ADD KEY `Fk_Rol_idx` (`ID_Rol`),
  ADD KEY `fk_usuario_admin_desactiva` (`ID_Admin_Desactiva`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `advertencias_usuario`
--
ALTER TABLE `advertencias_usuario`
  MODIFY `ID_Advertencia` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `articulo`
--
ALTER TABLE `articulo`
  MODIFY `ID_Articulo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `atributo_valor`
--
ALTER TABLE `atributo_valor`
  MODIFY `ID_AtributoValor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT de la tabla `categoria`
--
ALTER TABLE `categoria`
  MODIFY `ID_Categoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `color`
--
ALTER TABLE `color`
  MODIFY `ID_Color` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- AUTO_INCREMENT de la tabla `descuento`
--
ALTER TABLE `descuento`
  MODIFY `ID_Descuento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `descuento_usuario`
--
ALTER TABLE `descuento_usuario`
  MODIFY `ID_DescuentoUsuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT de la tabla `direccion`
--
ALTER TABLE `direccion`
  MODIFY `ID_Direccion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `factura`
--
ALTER TABLE `factura`
  MODIFY `ID_Factura` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=76;

--
-- AUTO_INCREMENT de la tabla `factura_producto`
--
ALTER TABLE `factura_producto`
  MODIFY `ID_FacturaProducto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;

--
-- AUTO_INCREMENT de la tabla `favorito`
--
ALTER TABLE `favorito`
  MODIFY `ID_Favorito` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT de la tabla `metodo_pago`
--
ALTER TABLE `metodo_pago`
  MODIFY `ID_Metodo_Pago` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `palabras_bloqueadas`
--
ALTER TABLE `palabras_bloqueadas`
  MODIFY `ID_Palabra` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `pedido_seguimiento`
--
ALTER TABLE `pedido_seguimiento`
  MODIFY `ID_Seguimiento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT de la tabla `permiso`
--
ALTER TABLE `permiso`
  MODIFY `ID_Permiso` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `precio`
--
ALTER TABLE `precio`
  MODIFY `ID_precio` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `producto`
--
ALTER TABLE `producto`
  MODIFY `ID_Producto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `resena`
--
ALTER TABLE `resena`
  MODIFY `ID_Resena` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `resena_foto`
--
ALTER TABLE `resena_foto`
  MODIFY `ID_Foto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `resena_reporte`
--
ALTER TABLE `resena_reporte`
  MODIFY `ID_Reporte` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `resena_respuesta`
--
ALTER TABLE `resena_respuesta`
  MODIFY `ID_Respuesta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `resena_voto`
--
ALTER TABLE `resena_voto`
  MODIFY `ID_Voto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `rol`
--
ALTER TABLE `rol`
  MODIFY `ID_Rol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `subcategoria`
--
ALTER TABLE `subcategoria`
  MODIFY `ID_SubCategoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT de la tabla `tipo_atributo`
--
ALTER TABLE `tipo_atributo`
  MODIFY `ID_TipoAtributo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `tipo_documento`
--
ALTER TABLE `tipo_documento`
  MODIFY `ID_TD` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `ID_Usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=83;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `advertencias_usuario`
--
ALTER TABLE `advertencias_usuario`
  ADD CONSTRAINT `advertencias_usuario_ibfk_1` FOREIGN KEY (`ID_Usuario`) REFERENCES `usuario` (`ID_Usuario`),
  ADD CONSTRAINT `advertencias_usuario_ibfk_2` FOREIGN KEY (`ID_Admin`) REFERENCES `usuario` (`ID_Usuario`);

--
-- Filtros para la tabla `articulo`
--
ALTER TABLE `articulo`
  ADD CONSTRAINT `Fk_Categoria` FOREIGN KEY (`ID_Categoria`) REFERENCES `categoria` (`ID_Categoria`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `Fk_Genero` FOREIGN KEY (`ID_Genero`) REFERENCES `genero` (`ID_Genero`),
  ADD CONSTRAINT `Fk_Precio` FOREIGN KEY (`ID_Precio`) REFERENCES `precio` (`ID_precio`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `Fk_SubCategoria` FOREIGN KEY (`ID_SubCategoria`) REFERENCES `subcategoria` (`ID_SubCategoria`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `atributo_valor`
--
ALTER TABLE `atributo_valor`
  ADD CONSTRAINT `atributo_valor_ibfk_1` FOREIGN KEY (`ID_TipoAtributo`) REFERENCES `tipo_atributo` (`ID_TipoAtributo`);

--
-- Filtros para la tabla `descuento_usuario`
--
ALTER TABLE `descuento_usuario`
  ADD CONSTRAINT `descuento_usuario_ibfk_1` FOREIGN KEY (`ID_Descuento`) REFERENCES `descuento` (`ID_Descuento`),
  ADD CONSTRAINT `descuento_usuario_ibfk_2` FOREIGN KEY (`ID_Usuario`) REFERENCES `usuario` (`ID_Usuario`);

--
-- Filtros para la tabla `factura`
--
ALTER TABLE `factura`
  ADD CONSTRAINT `factura_ibfk_1` FOREIGN KEY (`Usuario_Envio`) REFERENCES `usuario` (`ID_Usuario`),
  ADD CONSTRAINT `factura_ibfk_2` FOREIGN KEY (`Usuario_Entrega`) REFERENCES `usuario` (`ID_Usuario`);

--
-- Filtros para la tabla `factura_producto`
--
ALTER TABLE `factura_producto`
  ADD CONSTRAINT `fk_factura_producto_descuento` FOREIGN KEY (`ID_Descuento`) REFERENCES `descuento` (`ID_Descuento`);

--
-- Filtros para la tabla `pedido_seguimiento`
--
ALTER TABLE `pedido_seguimiento`
  ADD CONSTRAINT `pedido_seguimiento_ibfk_1` FOREIGN KEY (`ID_Factura`) REFERENCES `factura` (`ID_Factura`),
  ADD CONSTRAINT `pedido_seguimiento_ibfk_2` FOREIGN KEY (`ID_Usuario`) REFERENCES `usuario` (`ID_Usuario`);

--
-- Filtros para la tabla `producto`
--
ALTER TABLE `producto`
  ADD CONSTRAINT `Fk_Articulo` FOREIGN KEY (`ID_Articulo`) REFERENCES `articulo` (`ID_Articulo`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `resena`
--
ALTER TABLE `resena`
  ADD CONSTRAINT `resena_ibfk_1` FOREIGN KEY (`ID_Usuario`) REFERENCES `usuario` (`ID_Usuario`),
  ADD CONSTRAINT `resena_ibfk_2` FOREIGN KEY (`ID_Articulo`) REFERENCES `articulo` (`ID_Articulo`),
  ADD CONSTRAINT `resena_ibfk_3` FOREIGN KEY (`ID_Producto`) REFERENCES `producto` (`ID_Producto`),
  ADD CONSTRAINT `resena_ibfk_4` FOREIGN KEY (`ID_Factura`) REFERENCES `factura` (`ID_Factura`);

--
-- Filtros para la tabla `resena_foto`
--
ALTER TABLE `resena_foto`
  ADD CONSTRAINT `resena_foto_ibfk_1` FOREIGN KEY (`ID_Resena`) REFERENCES `resena` (`ID_Resena`) ON DELETE CASCADE;

--
-- Filtros para la tabla `resena_reporte`
--
ALTER TABLE `resena_reporte`
  ADD CONSTRAINT `resena_reporte_ibfk_1` FOREIGN KEY (`ID_Resena`) REFERENCES `resena` (`ID_Resena`) ON DELETE CASCADE,
  ADD CONSTRAINT `resena_reporte_ibfk_2` FOREIGN KEY (`ID_Usuario`) REFERENCES `usuario` (`ID_Usuario`);

--
-- Filtros para la tabla `resena_respuesta`
--
ALTER TABLE `resena_respuesta`
  ADD CONSTRAINT `resena_respuesta_ibfk_1` FOREIGN KEY (`ID_Resena`) REFERENCES `resena` (`ID_Resena`) ON DELETE CASCADE,
  ADD CONSTRAINT `resena_respuesta_ibfk_2` FOREIGN KEY (`ID_Usuario`) REFERENCES `usuario` (`ID_Usuario`);

--
-- Filtros para la tabla `resena_voto`
--
ALTER TABLE `resena_voto`
  ADD CONSTRAINT `resena_voto_ibfk_1` FOREIGN KEY (`ID_Resena`) REFERENCES `resena` (`ID_Resena`) ON DELETE CASCADE,
  ADD CONSTRAINT `resena_voto_ibfk_2` FOREIGN KEY (`ID_Usuario`) REFERENCES `usuario` (`ID_Usuario`);

--
-- Filtros para la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD CONSTRAINT `FK_Usuario_Rol` FOREIGN KEY (`ID_Rol`) REFERENCES `rol` (`ID_Rol`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `FK_Usuario_TD` FOREIGN KEY (`ID_TD`) REFERENCES `tipo_documento` (`ID_TD`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_usuario_admin_desactiva` FOREIGN KEY (`ID_Admin_Desactiva`) REFERENCES `usuario` (`ID_Usuario`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
