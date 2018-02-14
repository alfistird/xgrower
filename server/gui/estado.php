<?php

$param_idEquipo = $_GET["id"];
$ultimoEstado;
$horaUltimoEstado;
$temperatura;
$humedad;
$luminosidad;
$estadoAgua;
$minutosRiegoAutomatico;
$minutosRiegoManual;
$temperaturaCustom;
$humedadCustom;
$humedadEnTierra;
include "conexion.php";

if ($param_idEquipo == ""){
	
} else {
	$consultaSelect = "SELECT * FROM equipos where idEquipo = '".$param_idEquipo."'";
	$datos = mysql_query($consultaSelect, $link);
	$isExistente = false;
	while ($fila = mysql_fetch_assoc($datos)) {
		if($fila['isActivo'] == 1){
			$isExistente = true;
				
		}else{
			$param_idEquipo = "";
			echo '{"error" : "ERROR,INACTIVO"}';
		}
	}
	if(!$isExistente){
		echo '{"error" : "ERROR,INEXSISTENTE"}';
	}else{
		//si existe voy a devolver el ultimo estado
		$consultaSelect = "SELECT * FROM pulsos where idEquipo = '".$param_idEquipo."' order by idpulsos desc";
		$datos = mysql_query($consultaSelect, $link);
		while ($fila = mysql_fetch_assoc($datos)) {
			if($fila['estado_agua']<10){
				$ultimoEstado = "0" . $fila['estado_luz'] . ",0". $fila['estado_ventilacion'] . ",0". $fila['estado_agua'] . "";
			}else{
				$ultimoEstado = "0" . $fila['estado_luz'] . ",0". $fila['estado_ventilacion'] . ",". $fila['estado_agua'] . "";
			}
			$horaUltimoEstado = "".$fila['fecha'] ."";
			$temperatura = "".$fila['temperatura'] ."";
			$humedad = "".$fila['humedad'] ."";
			$luminosidad = "".$fila['luminosidad'] ."";
			$minutosRiegoAutomatico = "".$fila['riegoAutomaticoMinutos'] ."";
			$minutosRiegoManual = "".$fila['riegoManualMinutos'] ."";
			$temperaturaCustom = "".$fila['temperatura_custom'] ."";
			$humedadCustom = "".$fila['humedad_custom'] ."";
			$humedadEnTierra = "".$fila['humedadEnTierra'] ."";
			break;
		}
		echo '{"ultimoEstado":"'.$ultimoEstado.'", "horaUltimoEstado":"'.$horaUltimoEstado.'", "temperatura":"'.$temperatura.'", "humedad":"'.$humedad.'", "luminosidad":"'.$luminosidad.'", "minutosRiegoAutomatico":"'.$minutosRiegoAutomatico.'", "minutosRiegoManual":"'.$minutosRiegoManual.'", "temperaturaCustom":"'.$temperaturaCustom.'", "humedadCustom":"'.$humedadCustom.'", "humedadEnTierra":"'.$humedadEnTierra.'"}';
	}
}

	
	if (!$link) {
		printf("Can't connect to MySQL Server. Errorcode: %s\n", mysqli_connect_error());
		exit;
	}
	
	

?> 