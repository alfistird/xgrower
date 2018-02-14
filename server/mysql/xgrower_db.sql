-- phpMyAdmin SQL Dump
-- version 3.3.10.4
-- http://www.phpmyadmin.net
--
-- Servidor: mysql.xgrower.com
-- Tiempo de generación: 11-09-2015 a las 22:12:54
-- Versión del servidor: 5.1.56
-- Versión de PHP: 5.3.29

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de datos: `xgrower_db`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `alertas`
--

CREATE TABLE IF NOT EXISTS `alertas` (
  `idalertas` int(11) NOT NULL AUTO_INCREMENT,
  `idEquipo` varchar(20) DEFAULT NULL,
  `time_creado` datetime DEFAULT NULL,
  `isLeida` int(11) DEFAULT NULL,
  `tipoAlerta` int(11) DEFAULT NULL,
  PRIMARY KEY (`idalertas`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=303 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `bandeja_salida`
--

CREATE TABLE IF NOT EXISTS `bandeja_salida` (
  `idbandeja_salida` int(11) NOT NULL AUTO_INCREMENT,
  `idEquipo` varchar(20) NOT NULL,
  `mensaje` varchar(255) DEFAULT NULL,
  `estado` int(11) DEFAULT '0',
  `time_creado` datetime DEFAULT NULL,
  `time_enviado` datetime DEFAULT NULL,
  `time_ack` datetime DEFAULT NULL,
  PRIMARY KEY (`idbandeja_salida`,`idEquipo`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=417 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `equipos`
--

CREATE TABLE IF NOT EXISTS `equipos` (
  `idEquipo` varchar(20) NOT NULL,
  `isActivo` int(11) DEFAULT '0',
  `idUsuario` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`idEquipo`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `fotos`
--

CREATE TABLE IF NOT EXISTS `fotos` (
  `idfotos` int(11) NOT NULL AUTO_INCREMENT,
  `id_xgrower` varchar(100) DEFAULT NULL,
  `timestamp` datetime DEFAULT NULL,
  `nombre_archivo` varchar(150) DEFAULT NULL,
  `id_foto_por_xgrower` int(11) DEFAULT NULL,
  PRIMARY KEY (`idfotos`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `fotos_buffer`
--

CREATE TABLE IF NOT EXISTS `fotos_buffer` (
  `idfotos_buffer` int(11) NOT NULL AUTO_INCREMENT,
  `idEquipo` varchar(100) DEFAULT '0',
  `timestamp` datetime DEFAULT '0000-00-00 00:00:00',
  `numero_paquete` smallint(6) DEFAULT '0',
  `estado_paquete` tinyint(4) DEFAULT '0',
  `paquete` blob,
  PRIMARY KEY (`idfotos_buffer`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=56 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pulsos`
--

CREATE TABLE IF NOT EXISTS `pulsos` (
  `idpulsos` int(11) NOT NULL AUTO_INCREMENT,
  `idEquipo` varchar(20) NOT NULL,
  `fecha` datetime DEFAULT NULL,
  `temperatura` int(11) DEFAULT NULL,
  `humedad` int(11) DEFAULT NULL,
  `luminosidad` int(11) DEFAULT NULL,
  `estado_luz` int(11) DEFAULT NULL,
  `estado_ventilacion` int(11) DEFAULT NULL,
  `estado_agua` int(11) DEFAULT NULL,
  `temperatura_custom` int(11) DEFAULT NULL,
  `humedad_custom` int(11) DEFAULT NULL,
  `riegoAutomaticoMinutos` int(11) DEFAULT NULL,
  `riegoManualMinutos` int(11) DEFAULT NULL,
  `isLuzEncendida` int(11) DEFAULT NULL,
  `humedadEnTierra` int(11) DEFAULT NULL,
  PRIMARY KEY (`idpulsos`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1358753 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE IF NOT EXISTS `usuarios` (
  `idUsuario` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(200) DEFAULT NULL,
  `facebook_id` varchar(200) NOT NULL,
  PRIMARY KEY (`idUsuario`),
  UNIQUE KEY `facebook_id_UNIQUE` (`facebook_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=8 ;

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vista_ultimos_bandeja_salida`
--
CREATE TABLE IF NOT EXISTS `vista_ultimos_bandeja_salida` (
`idbandeja_salida` int(11)
,`idEquipo` varchar(20)
,`mensaje` varchar(255)
,`estado` int(11)
,`time_creado` datetime
,`time_enviado` datetime
,`time_ack` datetime
);
-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vista_ultimos_pulsos2000`
--
CREATE TABLE IF NOT EXISTS `vista_ultimos_pulsos2000` (
`idpulsos` int(11)
,`idEquipo` varchar(20)
,`fecha` datetime
,`temperatura` int(11)
,`humedad` int(11)
,`luminosidad` int(11)
,`estado_luz` int(11)
,`estado_ventilacion` int(11)
,`estado_agua` int(11)
,`temperatura_custom` int(11)
,`humedad_custom` int(11)
,`riegoAutomaticoMinutos` int(11)
,`riegoManualMinutos` int(11)
);
-- --------------------------------------------------------

--
-- Estructura para la vista `vista_ultimos_bandeja_salida`
--
DROP TABLE IF EXISTS `vista_ultimos_bandeja_salida`;

CREATE ALGORITHM=UNDEFINED DEFINER=`xgrower_user`@`%` SQL SECURITY DEFINER VIEW `vista_ultimos_bandeja_salida` AS select `bandeja_salida`.`idbandeja_salida` AS `idbandeja_salida`,`bandeja_salida`.`idEquipo` AS `idEquipo`,`bandeja_salida`.`mensaje` AS `mensaje`,`bandeja_salida`.`estado` AS `estado`,`bandeja_salida`.`time_creado` AS `time_creado`,`bandeja_salida`.`time_enviado` AS `time_enviado`,`bandeja_salida`.`time_ack` AS `time_ack` from `bandeja_salida` order by `bandeja_salida`.`idbandeja_salida` desc limit 200;

-- --------------------------------------------------------

--
-- Estructura para la vista `vista_ultimos_pulsos2000`
--
DROP TABLE IF EXISTS `vista_ultimos_pulsos2000`;

CREATE ALGORITHM=UNDEFINED DEFINER=`xgrower_user`@`%` SQL SECURITY DEFINER VIEW `vista_ultimos_pulsos2000` AS select `pulsos`.`idpulsos` AS `idpulsos`,`pulsos`.`idEquipo` AS `idEquipo`,`pulsos`.`fecha` AS `fecha`,`pulsos`.`temperatura` AS `temperatura`,`pulsos`.`humedad` AS `humedad`,`pulsos`.`luminosidad` AS `luminosidad`,`pulsos`.`estado_luz` AS `estado_luz`,`pulsos`.`estado_ventilacion` AS `estado_ventilacion`,`pulsos`.`estado_agua` AS `estado_agua`,`pulsos`.`temperatura_custom` AS `temperatura_custom`,`pulsos`.`humedad_custom` AS `humedad_custom`,`pulsos`.`riegoAutomaticoMinutos` AS `riegoAutomaticoMinutos`,`pulsos`.`riegoManualMinutos` AS `riegoManualMinutos` from `pulsos` order by `pulsos`.`idpulsos` desc limit 2000;
