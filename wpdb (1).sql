-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 28-05-2024 a las 00:45:54
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
-- Base de datos: `wpdb`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `admins`
--

CREATE TABLE `admins` (
  `usuario_id` int(11) NOT NULL,
  `nombre_usuario` varchar(250) DEFAULT NULL,
  `cedula` varchar(20) DEFAULT NULL,
  `tipo_usuario` enum('SuperAdmin','Admin','Asistente') DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(100) DEFAULT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `nombre_empresa` varchar(100) DEFAULT NULL,
  `empresa_id` int(11) DEFAULT NULL,
  `notas` text DEFAULT NULL,
  `fecha_creacion` timestamp NULL DEFAULT current_timestamp(),
  `ultima_modificacion` varchar(255) DEFAULT NULL,
  `push_noti` varchar(255) DEFAULT NULL,
  `ultimo_login` varchar(250) DEFAULT NULL,
  `ajunstes1` tinyint(1) DEFAULT NULL,
  `ajunstes2` tinyint(1) DEFAULT NULL,
  `ajunstes3` tinyint(1) DEFAULT NULL,
  `ajunstes4` tinyint(1) DEFAULT NULL,
  `ajunstes5` tinyint(1) DEFAULT NULL,
  `ajunstes6` tinyint(1) DEFAULT NULL,
  `ajunstes7` tinyint(1) DEFAULT NULL,
  `ajunstes8` tinyint(1) DEFAULT NULL,
  `ajunstes9` tinyint(1) DEFAULT NULL,
  `ajunstes10` tinyint(1) DEFAULT NULL,
  `ajunstes11` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ajustes`
--

CREATE TABLE `ajustes` (
  `id` int(11) NOT NULL,
  `diasmaximosdepago` varchar(50) DEFAULT NULL,
  `diasdepago` varchar(50) DEFAULT NULL,
  `diasdesuspension` varchar(50) DEFAULT NULL,
  `mensajedepago` text DEFAULT NULL,
  `mensajedeatraso` text DEFAULT NULL,
  `mensajedesuspension` text DEFAULT NULL,
  `limitedeconsulta` varchar(50) DEFAULT NULL,
  `limitedeusuarios` varchar(50) DEFAULT NULL,
  `mensajedelimiteconsulta` text DEFAULT NULL,
  `mensajelimiteusuario` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cargasdata`
--

CREATE TABLE `cargasdata` (
  `carga_id` int(11) NOT NULL,
  `fecha_carga` timestamp NULL DEFAULT current_timestamp(),
  `usuario_id` int(11) DEFAULT NULL,
  `sucursal_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `cliente_id` int(11) NOT NULL,
  `NombreCliente` varchar(255) DEFAULT NULL,
  `cliente_cedula` varchar(20) DEFAULT NULL,
  `pasaporte` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `fechacreacion` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `consultasdiarias`
--

CREATE TABLE `consultasdiarias` (
  `consulta_id` int(11) NOT NULL,
  `fecha_consulta` date DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `cantidad` int(11) DEFAULT NULL,
  `total_usado` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `datoscrediticios`
--

CREATE TABLE `datoscrediticios` (
  `dato_crediticio_id` int(11) NOT NULL,
  `Codigo` varchar(255) DEFAULT NULL,
  `cliente_cedula` varchar(255) DEFAULT NULL,
  `nombrecliente` varchar(255) DEFAULT NULL,
  `empresa_id` int(11) NOT NULL,
  `zona` varchar(255) DEFAULT NULL,
  `sucursal_id` int(11) NOT NULL,
  `prestamos_activos` varchar(255) DEFAULT NULL,
  `legal` varchar(255) DEFAULT NULL,
  `atraso` varchar(255) DEFAULT NULL,
  `notas` varchar(255) DEFAULT NULL,
  `comentarios` varchar(255) DEFAULT NULL,
  `monto_prestamo` varchar(255) DEFAULT NULL,
  `ultima_fecha_pago` varchar(255) DEFAULT NULL,
  `total_adeudado` varchar(255) DEFAULT NULL,
  `monto_cuotas` varchar(255) DEFAULT NULL,
  `frecuencia_pagos` varchar(255) DEFAULT NULL,
  `codigoprestamo` varchar(255) DEFAULT NULL,
  `direccionactual` varchar(255) DEFAULT NULL,
  `numerotelefono1` varchar(255) DEFAULT NULL,
  `numerotelefono2` varchar(255) DEFAULT NULL,
  `valorsolicitado` varchar(255) DEFAULT NULL,
  `fechaaprobacion` varchar(255) DEFAULT NULL,
  `montoaprobado` varchar(255) DEFAULT NULL,
  `fechavencimiento` varchar(255) DEFAULT NULL,
  `montocuotas` varchar(255) DEFAULT NULL,
  `balanceactual` varchar(255) DEFAULT NULL,
  `balanceatraso` varchar(255) DEFAULT NULL,
  `balancependiente` varchar(255) DEFAULT NULL,
  `estatus` varchar(255) DEFAULT NULL,
  `tipo` varchar(255) DEFAULT NULL,
  `ultimopago` varchar(255) DEFAULT NULL,
  `1-30` varchar(255) DEFAULT NULL,
  `31-60` varchar(255) DEFAULT NULL,
  `61-90` varchar(255) DEFAULT NULL,
  `91-120` varchar(255) DEFAULT NULL,
  `121-mas` varchar(255) DEFAULT NULL,
  `fechacreacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empresas`
--

CREATE TABLE `empresas` (
  `empresa_id` int(11) NOT NULL,
  `nombre_empresa` varchar(255) DEFAULT NULL,
  `limite_usuarios` int(11) DEFAULT NULL,
  `limite_consultas` int(11) DEFAULT NULL,
  `nombre_usuario` varchar(250) DEFAULT NULL,
  `cedula` varchar(250) DEFAULT NULL,
  `pasaporte` varchar(250) DEFAULT NULL,
  `tipo_usuario` enum('Empresa','Usuario','Sucursal') DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `encargado` varchar(255) DEFAULT NULL,
  `superadmin_id` int(11) DEFAULT NULL,
  `fecha_pago` varchar(250) DEFAULT NULL,
  `monto_pago` decimal(10,2) DEFAULT NULL,
  `notas` text DEFAULT NULL,
  `fecha_creacion` varchar(250) DEFAULT NULL,
  `ultima_modificacion` varchar(250) DEFAULT NULL,
  `push_noti` varchar(250) DEFAULT NULL,
  `ultima_carga` varchar(250) DEFAULT NULL,
  `opcion1` varchar(250) DEFAULT NULL,
  `opcion2` varchar(250) DEFAULT NULL,
  `opcion3` varchar(250) DEFAULT NULL,
  `ajunstes1` tinyint(1) DEFAULT NULL,
  `ajunstes2` tinyint(1) DEFAULT NULL,
  `ajunstes3` tinyint(1) DEFAULT NULL,
  `ajunstes4` tinyint(1) DEFAULT NULL,
  `ajunstes5` tinyint(1) DEFAULT NULL,
  `ajunstes6` tinyint(1) DEFAULT NULL,
  `ajunstes7` tinyint(1) DEFAULT NULL,
  `ajunstes8` tinyint(1) DEFAULT NULL,
  `ajunstes9` tinyint(1) DEFAULT NULL,
  `ajunstes10` tinyint(1) DEFAULT NULL,
  `ajunstes11` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `gastos`
--

CREATE TABLE `gastos` (
  `ID` int(11) NOT NULL,
  `Fecha` date NOT NULL,
  `Mes` int(11) NOT NULL,
  `Descripcion` varchar(255) NOT NULL,
  `Monto` decimal(10,2) NOT NULL,
  `URL_Factura` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historialconsultas`
--

CREATE TABLE `historialconsultas` (
  `idhistorialconsulta` int(11) NOT NULL,
  `fechaconsulta` varchar(255) DEFAULT NULL,
  `usuarioid` int(11) NOT NULL,
  `usuarioconsulta` varchar(255) DEFAULT NULL,
  `clienteid` int(11) NOT NULL,
  `nombrecliente` varchar(255) DEFAULT NULL,
  `cedulacliente` varchar(255) DEFAULT NULL,
  `idempresa` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ingresos`
--

CREATE TABLE `ingresos` (
  `ID` int(11) NOT NULL,
  `Fecha` date NOT NULL,
  `Mes` int(11) NOT NULL,
  `Descripcion` varchar(255) NOT NULL,
  `Monto` decimal(10,2) NOT NULL,
  `URL_Comprobante` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `loghistory`
--

CREATE TABLE `loghistory` (
  `log_id` int(11) NOT NULL,
  `fecha` timestamp NULL DEFAULT current_timestamp(),
  `usuario_id` int(11) DEFAULT NULL,
  `accion_realizada` text DEFAULT NULL,
  `Detalle` varchar(250) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificaciones`
--

CREATE TABLE `notificaciones` (
  `notificacion_id` int(11) NOT NULL,
  `fecha_notificacion` timestamp NULL DEFAULT current_timestamp(),
  `fecha_fin` date DEFAULT NULL,
  `superadmin_id` int(11) DEFAULT NULL,
  `mensaje` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificacionespagos`
--

CREATE TABLE `notificacionespagos` (
  `id_pago` int(11) NOT NULL,
  `id_cliente` int(11) NOT NULL,
  `fecha_vencimiento` varchar(500) DEFAULT NULL,
  `diasparanotificacion` int(11) DEFAULT NULL,
  `diasparabloquear` int(11) DEFAULT NULL,
  `estado_pago` varchar(20) DEFAULT NULL,
  `mensaje_atraso` mediumtext DEFAULT NULL,
  `mensaje_bloqueado` mediumtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos`
--

CREATE TABLE `pagos` (
  `pago_id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `fecha_pago` date DEFAULT NULL,
  `monto_pago` decimal(10,2) DEFAULT NULL,
  `dato_crediticio_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reporteanual`
--

CREATE TABLE `reporteanual` (
  `ID` int(11) NOT NULL,
  `Anio` int(11) NOT NULL,
  `Total_Ingresos` decimal(10,2) NOT NULL,
  `Total_Gastos` decimal(10,2) NOT NULL,
  `Saldo` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reportemensual`
--

CREATE TABLE `reportemensual` (
  `ID` int(11) NOT NULL,
  `Anio` int(11) NOT NULL,
  `Mes` int(11) NOT NULL,
  `Total_Ingresos` decimal(10,2) NOT NULL,
  `Total_Gastos` decimal(10,2) NOT NULL,
  `Saldo` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sucursales`
--

CREATE TABLE `sucursales` (
  `sucursal_id` int(11) NOT NULL,
  `nombre_sucursal` varchar(255) DEFAULT NULL,
  `direccion_sucursal` varchar(255) DEFAULT NULL,
  `zona_sucursal` varchar(50) DEFAULT NULL,
  `empresa_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`usuario_id`);

--
-- Indices de la tabla `ajustes`
--
ALTER TABLE `ajustes`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `cargasdata`
--
ALTER TABLE `cargasdata`
  ADD PRIMARY KEY (`carga_id`);

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`cliente_id`);

--
-- Indices de la tabla `consultasdiarias`
--
ALTER TABLE `consultasdiarias`
  ADD PRIMARY KEY (`consulta_id`);

--
-- Indices de la tabla `datoscrediticios`
--
ALTER TABLE `datoscrediticios`
  ADD PRIMARY KEY (`dato_crediticio_id`);

--
-- Indices de la tabla `empresas`
--
ALTER TABLE `empresas`
  ADD PRIMARY KEY (`empresa_id`);

--
-- Indices de la tabla `gastos`
--
ALTER TABLE `gastos`
  ADD PRIMARY KEY (`ID`);

--
-- Indices de la tabla `historialconsultas`
--
ALTER TABLE `historialconsultas`
  ADD PRIMARY KEY (`idhistorialconsulta`);

--
-- Indices de la tabla `ingresos`
--
ALTER TABLE `ingresos`
  ADD PRIMARY KEY (`ID`);

--
-- Indices de la tabla `loghistory`
--
ALTER TABLE `loghistory`
  ADD PRIMARY KEY (`log_id`);

--
-- Indices de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD PRIMARY KEY (`notificacion_id`);

--
-- Indices de la tabla `notificacionespagos`
--
ALTER TABLE `notificacionespagos`
  ADD PRIMARY KEY (`id_pago`);

--
-- Indices de la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD PRIMARY KEY (`pago_id`);

--
-- Indices de la tabla `reporteanual`
--
ALTER TABLE `reporteanual`
  ADD PRIMARY KEY (`ID`);

--
-- Indices de la tabla `reportemensual`
--
ALTER TABLE `reportemensual`
  ADD PRIMARY KEY (`ID`);

--
-- Indices de la tabla `sucursales`
--
ALTER TABLE `sucursales`
  ADD PRIMARY KEY (`sucursal_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `admins`
--
ALTER TABLE `admins`
  MODIFY `usuario_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `ajustes`
--
ALTER TABLE `ajustes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `cargasdata`
--
ALTER TABLE `cargasdata`
  MODIFY `carga_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `cliente_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9673;

--
-- AUTO_INCREMENT de la tabla `consultasdiarias`
--
ALTER TABLE `consultasdiarias`
  MODIFY `consulta_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `datoscrediticios`
--
ALTER TABLE `datoscrediticios`
  MODIFY `dato_crediticio_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10500;

--
-- AUTO_INCREMENT de la tabla `empresas`
--
ALTER TABLE `empresas`
  MODIFY `empresa_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de la tabla `gastos`
--
ALTER TABLE `gastos`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `historialconsultas`
--
ALTER TABLE `historialconsultas`
  MODIFY `idhistorialconsulta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT de la tabla `ingresos`
--
ALTER TABLE `ingresos`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `loghistory`
--
ALTER TABLE `loghistory`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  MODIFY `notificacion_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `notificacionespagos`
--
ALTER TABLE `notificacionespagos`
  MODIFY `id_pago` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pagos`
--
ALTER TABLE `pagos`
  MODIFY `pago_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `reporteanual`
--
ALTER TABLE `reporteanual`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `reportemensual`
--
ALTER TABLE `reportemensual`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `sucursales`
--
ALTER TABLE `sucursales`
  MODIFY `sucursal_id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
