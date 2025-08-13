-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 13-08-2025 a las 02:03:25
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
-- Base de datos: `servicio_social`
--

DELIMITER $$
--
-- Funciones
--
CREATE DEFINER=`root`@`localhost` FUNCTION `RecalcularHoras` (`_estudiante_id` INT) RETURNS DECIMAL(7,2) DETERMINISTIC BEGIN
    DECLARE total_horas DECIMAL(7,2);
    SELECT COALESCE(SUM(horas_acumuladas), 0) INTO total_horas
    FROM registroshoras
    WHERE estudiante_id = _estudiante_id AND estado = 'aprobado';
    UPDATE estudiantes
    SET horas_completadas = total_horas
    WHERE estudiante_id = _estudiante_id;
    RETURN total_horas;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `administradores`
--

CREATE TABLE `administradores` (
  `admin_id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `nombre` varchar(50) NOT NULL,
  `apellido_paterno` varchar(50) DEFAULT NULL,
  `apellido_materno` varchar(50) DEFAULT NULL,
  `telefono` varchar(15) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `administradores`
--

INSERT INTO `administradores` (`admin_id`, `usuario_id`, `nombre`, `apellido_paterno`, `apellido_materno`, `telefono`, `activo`) VALUES
(1, 1, 'Rogelio ', 'Lucas ', 'Cristobal', '5512345678', 1),
(2, 15, 'Itzel', 'Serrano', 'Espinoza', '5639221849', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `documentos_servicio`
--

CREATE TABLE `documentos_servicio` (
  `documento_id` int(11) NOT NULL,
  `solicitud_id` int(11) NOT NULL,
  `tipo_documento_id` int(11) NOT NULL,
  `nombre_archivo` varchar(255) NOT NULL,
  `ruta_archivo` varchar(255) NOT NULL,
  `tipo_archivo` varchar(255) NOT NULL,
  `fecha_subida` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_validacion` datetime DEFAULT NULL,
  `validado_por` int(11) DEFAULT NULL COMMENT 'ID del usuario de vinculación que validó',
  `estado` enum('pendiente','aprobada','rechazada') DEFAULT 'pendiente',
  `observaciones` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `documentos_servicio`
--

INSERT INTO `documentos_servicio` (`documento_id`, `solicitud_id`, `tipo_documento_id`, `nombre_archivo`, `ruta_archivo`, `tipo_archivo`, `fecha_subida`, `fecha_validacion`, `validado_por`, `estado`, `observaciones`) VALUES
(1, 4, 1, 'Date y scp.pdf', '../../uploads/documentos_servicio/doc_4_689b6f055fff4.pdf', 'application/pdf', '2025-08-12 16:42:45', '2025-08-12 10:45:00', 14, 'rechazada', ''),
(2, 4, 6, 'estacionamiento.pdf', '../../uploads/documentos_servicio/doc_4_689b6f0d4f46c.pdf', 'application/pdf', '2025-08-12 16:42:53', '2025-08-12 10:45:02', 14, 'rechazada', ''),
(3, 4, 2, 'Carta de Presentación.pdf.pdf', '../../uploads/documentos_servicio/doc_4_689b6f156d93e.pdf', 'application/pdf', '2025-08-12 16:43:01', NULL, NULL, 'pendiente', ''),
(4, 4, 3, 'Cuadro Comparativo de Metodologías Ágiles.pdf', '../../uploads/documentos_servicio/doc_4_689b6f1e79c50.pdf', 'application/pdf', '2025-08-12 16:43:10', '2025-08-12 10:45:07', 14, 'aprobada', ''),
(5, 4, 5, 'Listas de verificación (1).pdf', '../../uploads/documentos_servicio/doc_4_689b6f2545c1b.pdf', 'application/pdf', '2025-08-12 16:43:17', '2025-08-12 10:45:10', 14, 'rechazada', ''),
(6, 5, 1, 'Formato de Pago Universal with Primefaces.pdf', '../../uploads/documentos_servicio/doc_5_689b700d879ec.pdf', 'application/pdf', '2025-08-12 16:47:09', '2025-08-12 10:48:20', 14, 'aprobada', ''),
(7, 5, 6, 'Aspectos básicos de Microsoft Azure Descripción de los conceptos de nube-Lucas Cristobal Rogelio.pdf', '../../uploads/documentos_servicio/doc_5_689b702c34f5e.pdf', 'application/pdf', '2025-08-12 16:47:40', '2025-08-12 10:48:23', 14, 'aprobada', ''),
(8, 5, 2, 'Carta de Presentación.pdf.pdf', '../../uploads/documentos_servicio/doc_5_689b717af29f2.pdf', 'application/pdf', '2025-08-12 16:53:14', '2025-08-12 10:53:26', 14, 'rechazada', ''),
(9, 5, 3, '788-1589-1-SM.pdf', '../../uploads/documentos_servicio/doc_5_689b72fd29e24.pdf', 'application/pdf', '2025-08-12 16:59:41', '2025-08-12 10:59:54', 14, 'rechazada', '');

--
-- Disparadores `documentos_servicio`
--
DELIMITER $$
CREATE TRIGGER `after_documento_update` AFTER UPDATE ON `documentos_servicio` FOR EACH ROW BEGIN
    IF NEW.tipo_documento_id = 1 THEN 
        UPDATE solicitudes 
        SET estado_carta_presentacion = NEW.estado
        WHERE solicitud_id = NEW.solicitud_id;
    ELSEIF NEW.tipo_documento_id = 6 THEN 
        UPDATE solicitudes 
        SET estado_carta_aceptacion = NEW.estado
        WHERE solicitud_id = NEW.solicitud_id;
    ELSEIF NEW.tipo_documento_id = 4 THEN 
        UPDATE solicitudes 
        SET estado_carta_termino = NEW.estado
        WHERE solicitud_id = NEW.solicitud_id;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `entidades_receptoras`
--

CREATE TABLE `entidades_receptoras` (
  `entidad_id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `tipo_entidad` enum('Federal','Estatal','Municipal','O.N.G.','I.E.','I.P.') NOT NULL,
  `unidad_administrativa` varchar(100) NOT NULL,
  `domicilio` text NOT NULL,
  `municipio` varchar(50) NOT NULL,
  `telefono` varchar(15) NOT NULL,
  `funcionario_responsable` varchar(100) NOT NULL,
  `cargo_funcionario` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `entidades_receptoras`
--

INSERT INTO `entidades_receptoras` (`entidad_id`, `nombre`, `tipo_entidad`, `unidad_administrativa`, `domicilio`, `municipio`, `telefono`, `funcionario_responsable`, `cargo_funcionario`) VALUES
(1, 'Universidad Politécnica de Texcoco', 'I.E.', 'Direccion del plantel', 'Carretera Federal Texcoco-Lechería Km. 36.5, San Joaquín Coapango', 'Texcoco', '5559521000', 'Israel Flores Lopez', 'Director'),
(2, 'Universidad Politécnica de Texcoco', 'I.E.', 'SISTEMAS', 'Carretera Federal Texcoco-Lechería Km. 36.5, San Joaquín Coapango', 'Texcoco', '5559521000', 'EDURNET', 'JEFA');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estudiantes`
--

CREATE TABLE `estudiantes` (
  `estudiante_id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `matricula` varchar(20) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `apellido_paterno` varchar(50) DEFAULT NULL,
  `apellido_materno` varchar(50) DEFAULT NULL,
  `carrera` varchar(50) DEFAULT NULL,
  `cuatrimestre` int(11) DEFAULT NULL,
  `telefono` varchar(15) DEFAULT NULL,
  `curp` varchar(18) DEFAULT NULL,
  `edad` int(11) DEFAULT NULL,
  `facebook` varchar(100) DEFAULT NULL,
  `porcentaje_creditos` decimal(5,2) DEFAULT NULL,
  `promedio` decimal(3,2) DEFAULT NULL,
  `domicilio` text DEFAULT NULL,
  `sexo` enum('Masculino','Femenino','Otro') DEFAULT NULL,
  `horas_requeridas` int(11) DEFAULT 480,
  `horas_completadas` decimal(5,2) DEFAULT 0.00,
  `horas_restantes` decimal(5,2) GENERATED ALWAYS AS (`horas_requeridas` - `horas_completadas`) STORED,
  `activo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `estudiantes`
--

INSERT INTO `estudiantes` (`estudiante_id`, `usuario_id`, `matricula`, `nombre`, `apellido_paterno`, `apellido_materno`, `carrera`, `cuatrimestre`, `telefono`, `curp`, `edad`, `facebook`, `porcentaje_creditos`, `promedio`, `domicilio`, `sexo`, `horas_requeridas`, `horas_completadas`, `activo`) VALUES
(13, 18, '221550147', 'Rogelio', 'Lucas', 'Cristobal', 'Ingeniería en Sistemas Electrónicos', 7, '5638221849', 'LUCR011009HMCCRGA5', 18, 'Lucas Rogelio', 60.00, 8.50, 'Numero', 'Masculino', 480, 16.00, 1),
(14, 19, '221550139', 'Tania Itzel', 'Serrano', 'Espinoza', 'Ingeniería en Sistemas Electrónicos', 7, '5638221784', 'LUCR011009HMCCRGA5', 21, 'Lucas Rogelio', 60.00, 8.50, 'Numero', 'Femenino', 480, 0.00, 1),
(15, 20, '221550149', 'Roman', 'Gomez', 'Gomez', 'Ingeniería en Mecatrónica', 7, '5638221784', 'LUCR011009HMCCRGA5', NULL, 'Lucas Rogelio', 60.00, 7.00, 'Numero', 'Masculino', 480, 0.00, 1),
(17, 22, '221550146', 'Rodrigo', 'Lopez', 'Lopez', 'Licenciatura en Administración', 7, '5638221784', 'LUCR011009HMCCRGA5', NULL, 'Lucas Rogelio', 60.00, 8.00, 'Numero', 'Femenino', 480, 0.00, 1),
(18, 23, '221550145', 'PRUEBA', 'Lopez', 'Lopez', 'Ingeniería en Sistemas Electrónicos', 7, '5638221784', 'LUCR011009HMCCRGA5', NULL, 'Lucas Rogelio', 60.00, 8.00, 'Numero', 'Masculino', 480, 0.00, 1),
(27, 32, '221550144', 'Jorge', 'Serrano', 'Espinoza', 'Licenciatura en Administración', 9, '5638221784', '0', 17, '0', 60.00, 8.00, 'Numero', 'Masculino', 480, 0.00, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estudiantes_responsables`
--

CREATE TABLE `estudiantes_responsables` (
  `id` int(11) NOT NULL,
  `estudiante_id` int(11) NOT NULL,
  `responsable_id` int(11) NOT NULL,
  `fecha_asignacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `estudiantes_responsables`
--

INSERT INTO `estudiantes_responsables` (`id`, `estudiante_id`, `responsable_id`, `fecha_asignacion`) VALUES
(21, 15, 1, '2025-07-25 18:17:08'),
(26, 17, 2, '2025-07-30 03:11:54'),
(30, 14, 1, '2025-07-30 04:26:27'),
(31, 13, 2, '2025-07-30 04:45:36');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `periodos_registro`
--

CREATE TABLE `periodos_registro` (
  `periodo_id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL COMMENT 'Ej: Marzo - Septiembre 2025',
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `estado` enum('activo','inactivo') NOT NULL DEFAULT 'inactivo',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `periodos_registro`
--

INSERT INTO `periodos_registro` (`periodo_id`, `nombre`, `fecha_inicio`, `fecha_fin`, `estado`, `fecha_creacion`) VALUES
(1, 'Período Heredado (Datos Antiguos)', '2025-01-01', '2025-01-02', 'inactivo', '2025-07-31 14:59:15'),
(2, 'Marzo - Septiembre 2025', '2025-08-01', '2025-12-31', 'inactivo', '2025-07-31 15:09:57'),
(3, 'Julio - Noviembre 2025', '2025-07-31', '2025-12-31', 'inactivo', '2025-07-31 16:15:36');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `programas`
--

CREATE TABLE `programas` (
  `programa_id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `programas`
--

INSERT INTO `programas` (`programa_id`, `nombre`, `descripcion`) VALUES
(1, 'Educación, arte, cultura y deporte', NULL),
(2, 'Alimentación y Nutrición', NULL),
(3, 'Asistencia y seguridad social', NULL),
(5, 'Grupos vulnerables con capacidades diferentes, infantes y tercera edad', NULL),
(6, 'Apoyo a proyectos productivos', NULL),
(7, 'Salud', NULL),
(8, 'Empleo y capacitación para el trabajo', NULL),
(9, 'Medio ambiente', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `recuperacion`
--

CREATE TABLE `recuperacion` (
  `id` int(11) NOT NULL,
  `correo` varchar(255) NOT NULL,
  `token` varchar(100) NOT NULL,
  `expira` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `registroshoras`
--

CREATE TABLE `registroshoras` (
  `registro_id` int(11) NOT NULL,
  `estudiante_id` int(11) NOT NULL,
  `responsable_id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `hora_entrada` datetime NOT NULL,
  `hora_salida` datetime DEFAULT NULL,
  `horas_acumuladas` decimal(5,2) DEFAULT NULL,
  `estado` enum('pendiente','aprobado','rechazado') DEFAULT 'pendiente',
  `fecha_validacion` datetime DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `registroshoras`
--

INSERT INTO `registroshoras` (`registro_id`, `estudiante_id`, `responsable_id`, `fecha`, `hora_entrada`, `hora_salida`, `horas_acumuladas`, `estado`, `fecha_validacion`, `observaciones`, `fecha_registro`) VALUES
(231, 13, 2, '2025-08-06', '2025-08-06 08:02:32', '2025-08-06 21:42:37', 8.00, 'aprobado', NULL, NULL, '2025-08-06 14:02:32'),
(0, 14, 1, '2025-08-12', '2025-08-12 18:01:13', NULL, NULL, 'pendiente', NULL, NULL, '2025-08-13 00:01:13');

--
-- Disparadores `registroshoras`
--
DELIMITER $$
CREATE TRIGGER `after_registroshoras_delete` AFTER DELETE ON `registroshoras` FOR EACH ROW BEGIN
    IF OLD.estado = 'aprobado' THEN
        SELECT RecalcularHoras(OLD.estudiante_id) INTO @dummy;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `after_registroshoras_insert` AFTER INSERT ON `registroshoras` FOR EACH ROW BEGIN
    IF NEW.estado = 'aprobado' THEN
        SELECT RecalcularHoras(NEW.estudiante_id) INTO @dummy;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `after_registroshoras_update` AFTER UPDATE ON `registroshoras` FOR EACH ROW BEGIN
    IF (OLD.estado <> NEW.estado) OR (NEW.estado = 'aprobado' AND OLD.horas_acumuladas <> NEW.horas_acumuladas) THEN
        SELECT RecalcularHoras(NEW.estudiante_id) INTO @dummy;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `responsables`
--

CREATE TABLE `responsables` (
  `responsable_id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `nombre` varchar(50) NOT NULL,
  `apellido_paterno` varchar(50) DEFAULT NULL,
  `apellido_materno` varchar(50) DEFAULT NULL,
  `cargo` varchar(50) DEFAULT NULL,
  `departamento` varchar(50) DEFAULT NULL,
  `telefono` varchar(15) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `responsables`
--

INSERT INTO `responsables` (`responsable_id`, `usuario_id`, `nombre`, `apellido_paterno`, `apellido_materno`, `cargo`, `departamento`, `telefono`, `activo`) VALUES
(1, 2, 'María', 'Robles', 'Gomez', 'Coordinador', 'Servicio Social', '5511223344', 1),
(2, 3, 'Carlos', 'Gomez', 'Gomez', 'Supervisor', 'Recursos Humanos', '5522334455', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `rol_id` int(11) NOT NULL,
  `nombre_rol` varchar(20) NOT NULL,
  `descripcion` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`rol_id`, `nombre_rol`, `descripcion`) VALUES
(1, 'admin', 'Administrador del sistema con todos los permisos'),
(2, 'encargado', 'Responsable de validar horas de servicio social'),
(3, 'estudiante', 'Estudiante que realiza servicio social'),
(4, 'vinculacion', 'Usuario encargado de la vinculación de estudiantes');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `solicitudes`
--

CREATE TABLE `solicitudes` (
  `solicitud_id` int(11) NOT NULL,
  `estudiante_id` int(11) NOT NULL,
  `entidad_id` int(11) NOT NULL,
  `programa_id` int(11) NOT NULL,
  `periodo_id` int(11) NOT NULL,
  `funcionario_responsable` varchar(255) NOT NULL DEFAULT '',
  `cargo_funcionario` varchar(255) NOT NULL DEFAULT '',
  `fecha_solicitud` date NOT NULL,
  `actividades` text NOT NULL,
  `horario_lv_inicio` time DEFAULT NULL,
  `horario_lv_fin` time DEFAULT NULL,
  `horario_sd_inicio` time DEFAULT NULL,
  `horario_sd_fin` time DEFAULT NULL,
  `periodo_inicio` date NOT NULL,
  `periodo_fin` date NOT NULL,
  `horas_requeridas` int(11) NOT NULL DEFAULT 480,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `estado` enum('pendiente','aprobada','rechazada') NOT NULL DEFAULT 'pendiente',
  `fecha_aprobacion` datetime DEFAULT NULL,
  `aprobado_por` int(11) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `estado_carta_aceptacion` enum('Pendiente','Aprobada','Rechazada','Generada') DEFAULT 'Pendiente',
  `estado_carta_presentacion` enum('Pendiente','Aprobada','Rechazada') NOT NULL DEFAULT 'Pendiente',
  `estado_carta_termino` enum('Pendiente','Aprobada','Rechazada') NOT NULL DEFAULT 'Pendiente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `solicitudes`
--

INSERT INTO `solicitudes` (`solicitud_id`, `estudiante_id`, `entidad_id`, `programa_id`, `periodo_id`, `funcionario_responsable`, `cargo_funcionario`, `fecha_solicitud`, `actividades`, `horario_lv_inicio`, `horario_lv_fin`, `horario_sd_inicio`, `horario_sd_fin`, `periodo_inicio`, `periodo_fin`, `horas_requeridas`, `fecha_registro`, `estado`, `fecha_aprobacion`, `aprobado_por`, `observaciones`, `estado_carta_aceptacion`, `estado_carta_presentacion`, `estado_carta_termino`) VALUES
(4, 13, 1, 1, 1, 'Responsable', 'cargo', '2025-07-22', 'actividad', '23:44:00', '16:44:00', NULL, NULL, '2025-07-22', '2025-12-22', 480, '2025-07-31 14:59:16', 'pendiente', NULL, NULL, NULL, 'Rechazada', 'Rechazada', 'Pendiente'),
(5, 14, 1, 1, 1, 'Responsable', 'cargo', '2025-07-22', 'apoyo en direccion y apoyo tecnico ', '23:47:00', '16:47:00', NULL, NULL, '2025-07-22', '2025-12-22', 480, '2025-07-31 14:59:16', 'pendiente', NULL, NULL, NULL, 'Aprobada', 'Aprobada', 'Pendiente'),
(6, 15, 1, 1, 1, 'Responsable', 'cargo', '2025-07-22', 'ac', '23:49:00', '16:49:00', NULL, NULL, '2025-07-22', '2025-12-22', 480, '2025-07-31 14:59:16', 'pendiente', NULL, NULL, NULL, 'Pendiente', 'Pendiente', 'Pendiente'),
(7, 17, 1, 1, 1, 'Responsable', 'cargo', '2025-07-22', 'ac', '23:51:00', '16:51:00', NULL, NULL, '2025-07-22', '2025-12-22', 480, '2025-07-31 14:59:16', 'pendiente', NULL, NULL, NULL, 'Pendiente', 'Pendiente', 'Pendiente'),
(8, 18, 1, 3, 1, 'Responsable', 'cargo', '2025-07-25', 'ac', '10:03:00', '03:03:00', NULL, NULL, '2025-07-25', '2025-12-25', 480, '2025-07-31 14:59:16', 'pendiente', NULL, NULL, NULL, 'Pendiente', 'Pendiente', 'Pendiente'),
(15, 27, 1, 7, 3, 'responsable', 'cargo', '2025-07-31', 'ac', '11:48:00', '01:48:00', '11:48:00', '00:48:00', '2025-07-31', '2025-11-30', 480, '2025-07-31 17:49:10', 'aprobada', NULL, NULL, NULL, 'Pendiente', 'Pendiente', 'Pendiente');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipos_documentos`
--

CREATE TABLE `tipos_documentos` (
  `tipo_documento_id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `requerido` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tipos_documentos`
--

INSERT INTO `tipos_documentos` (`tipo_documento_id`, `nombre`, `descripcion`, `requerido`) VALUES
(1, 'Carta de Presentación', 'Documento oficial que presenta al estudiante en la entidad receptora', 1),
(2, 'Primer Informe', 'Reporte de actividades del primer periodo', 1),
(3, 'Segundo Informe', 'Reporte de actividades del segundo periodo', 1),
(4, 'Carta de Termino', 'Documento que acredita la conclusión del servicio social', 1),
(5, 'Comprobante de Pago', 'Documento que comprueba el pago por el servicio social', 1),
(6, 'Carta de Aceptación', 'Documento que acredita que la entidad receptora aceptó al estudiante', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `usuario_id` int(11) NOT NULL,
  `correo` varchar(100) NOT NULL,
  `contrasena` varchar(255) NOT NULL,
  `rol_id` int(11) NOT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `ultimo_login` datetime DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `tipo_usuario` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`usuario_id`, `correo`, `contrasena`, `rol_id`, `fecha_registro`, `ultimo_login`, `activo`, `tipo_usuario`) VALUES
(1, 'admin@uptex.edu.mx', '$2y$10$T1mT.W/AmisCxKw3yGofQe0PADnBrAZ08O4tAzcb854vz9jLugrKK', 1, '2025-07-31 14:59:16', '2025-08-11 20:55:59', 1, 'admin'),
(2, 'encargado1@uptex.edu.mx', '$2y$10$QFCxUwfjYItlwZLUod7rIeD7BJC6jNAAMdNv8Egxq6Y/I8sSlgHp.', 2, '2025-07-31 14:59:16', '2025-08-04 19:39:18', 1, 'encargado'),
(3, 'encargado2@uptex.edu.mx', '$2y$10$v.3x2NAxCtXzO8DG3QYCgus65XNuSFF3dsKkMhx2QggY7xsJ9TZRa', 2, '2025-07-31 14:59:16', '2025-07-31 11:38:27', 1, 'encargado'),
(14, 'vinculacion@uptex.edu.mx', '$2y$12$jqhyU4MY7/n4XmePIXhKXOFp0iRsXp.UDSPwj74AyBCHUEyn5F4wC', 4, '2025-07-31 14:59:16', '2025-08-12 17:44:17', 1, 'vinculacion'),
(15, '221550110@uptex.edu.mx', '$2y$10$r3lxCRPrLHHq3eCFDW9Ome/GDoFPy..VsY5tqHr6Pn1uITmhB2TZm', 1, '2025-07-31 14:59:16', '2025-07-29 21:25:22', 1, 'admin'),
(18, '221550147@uptex.edu.mx', '$2y$10$4KqzsL0/dpgLouCZUit9reGOszG7h1jT.ZTTFfMgL140hsyBElUoa', 3, '2025-07-31 14:59:16', '2025-08-12 17:52:26', 1, 'estudiante'),
(19, '221550139@uptex.edu.mx', '$2y$10$T89ADFHIUgaWDgPKKNOD/upmFSWEkCTLz8kknmKI0YfIXHidxwHDu', 3, '2025-07-31 14:59:16', '2025-08-12 17:57:41', 1, 'estudiante'),
(20, '221550148@uptex.edu.mx', '$2y$10$VYHkzdis6lQhfkw2xq3rrewaPsvb3dCHGzhjBQdX2yGm4iakegVDK', 3, '2025-07-31 14:59:16', '2025-07-31 10:11:49', 1, 'estudiante'),
(22, '221550146@uptex.edu.mx', '$2y$10$1WG1wjUIIqlw4dSMN5ZXt.PjOgaxrrjAYxp4P4X3JkpCp5KoPZmEu', 3, '2025-07-31 14:59:16', NULL, 1, 'estudiante'),
(23, '221550145@uptex.edu.mx', '$2y$10$or73JQR59DHFWDCElikvveANt44K10oAN284BLdmsxMDjBiX1Ruum', 3, '2025-07-31 14:59:16', NULL, 1, 'estudiante'),
(32, '221550144@uptex.edu.mx', '$2y$10$64LMegwHzTdkke8s741le.9PVuDC8TG99q/qlrAiLLSYoyQU1AMzy', 3, '2025-07-31 17:49:10', '2025-07-31 11:49:23', 1, 'estudiante');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vinculacion`
--

CREATE TABLE `vinculacion` (
  `vinculacion_id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `nombre` varchar(50) NOT NULL,
  `apellido_paterno` varchar(50) DEFAULT NULL,
  `apellido_materno` varchar(50) DEFAULT NULL,
  `telefono` varchar(15) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `vinculacion`
--

INSERT INTO `vinculacion` (`vinculacion_id`, `usuario_id`, `nombre`, `apellido_paterno`, `apellido_materno`, `telefono`, `activo`) VALUES
(1, 14, 'Roman', 'Valdez', 'Tores', '56382217849', 1);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `documentos_servicio`
--
ALTER TABLE `documentos_servicio`
  ADD PRIMARY KEY (`documento_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `documentos_servicio`
--
ALTER TABLE `documentos_servicio`
  MODIFY `documento_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
