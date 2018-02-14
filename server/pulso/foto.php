<?php

$param_idEquipo = $_GET["id"];
$param_temperatura = $_GET["te"];
$param_humedad = $_GET["hu"];
$param_luminosidad = $_GET["lu"];
$param_estadoLuz = $_GET["el"];
$param_estadoVentiladores = $_GET["ev"];
$param_estadoAgua = $_GET["ea"];
$param_estadoCambio = $_GET["ca"];
$param_temperaturaCustom = $_GET["ct"];
$param_humedadCustom = $_GET["ch"];
$param_isLuzEncendida = $_GET["il"];
date_default_timezone_set("America/Argentina/Cordoba");
$fecha = date("Y-m-d H:i:s", time());
$email = "";


include "conexion.php";

if ($param_idEquipo == ""){
$consultaSelect = "SELECT * FROM pulsos ORDER BY `idpulsos` DESC
    LIMIT 20";
	$datos = mysql_query($consultaSelect, $link);
	
	while ($fila = mysql_fetch_assoc($datos)) {
    echo "pulsoId: " . $fila['idpulsos'] . "<br>equipoId: ".$fila['idEquipo'] . "<br>fecha: ".$fila['fecha'] ."<br>temperatura :".$fila['temperatura'] . "<br>humedad: ".$fila['humedad'] . "<br>luminosidad: ".$fila['luminosidad'] ."<br>estado luz: ".$fila['estado_luz'] ."<br>estado ventilacion: ".$fila['estado_ventilacion'] ."<br>temperatura custom: ".$fila['temperatura_custom'] ."<br>humedad custom: ".$fila['humedad_custom'] ."<br>riego automatico minutos: ".$fila['riegoAutomaticoMinutos'] ."<br>riego manual minutos: ".$fila['riegoManualMinutos'] ."<br>humedad en tierra: ".$fila['humedadEnTierra'] ."    <br><br>";
}

	
} else {
	$consultaSelect = "SELECT * FROM equipos where idEquipo = '".$param_idEquipo."'";
	$datos = mysql_query($consultaSelect, $link);
	$isExistente = false;
	while ($fila = mysql_fetch_assoc($datos)) {
		if($fila['isActivo'] == 1){
			$isExistente = true;
			//
			$email = $fila['email'];
			if($param_estadoCambio == "1"){
				$consultaUpdate = "UPDATE bandeja_salida SET estado=0, time_ack='".$fecha."' WHERE idEquipo = '".$fila['idEquipo']."' and estado = 3";
				mysql_query($consultaUpdate, $link);
			}
			//
			
			$consultaSelect = "SELECT * FROM bandeja_salida WHERE idEquipo = '".$param_idEquipo."' AND estado = 1";
			$datos = mysql_query($consultaSelect, $link);
			$mensaje = "";
			while ($fila = mysql_fetch_assoc($datos)) {
				$mensaje = $mensaje. "," . $fila['mensaje'];
				$consultaUpdate = "UPDATE bandeja_salida SET estado=3, time_enviado='".$fecha."'  WHERE idEquipo = '".$fila['idEquipo']."' and estado = 1";
			mysql_query($consultaUpdate, $link);		
			}
			if($mensaje == ""){
				echo "BIEN,TIME=" .$fecha;
			}else{
				echo "CAMBIO" .$mensaje;
			}
		}else{
			echo "ERROR,INACTIVO";
		}
	}
	if(!$isExistente){
		echo "ERROR,INEXSISTENTE";
	}else{
		if($param_temperatura == ""){
			$param_temperatura = 0;
		}
		if($param_humedad == ""){
			$param_humedad = 0;
		}
		if($param_luminosidad == ""){
			$param_luminosidad = 0;
		}
		if($param_estadoLuz == ""){
			$param_estadoLuz = 0;
		}
		if($param_estadoVentiladores == ""){
			$param_estadoVentiladores = 0;
		}
		if($param_estadoAgua == ""){
			$param_estadoAgua = 0;
		}
		if($param_temperaturaCustom == ""){
			$param_temperaturaCustom = 0;
		}
		if($param_humedadCustom == ""){
			$param_humedadCustom = 0;
		}
	if($param_isLuzEncendida == ""){
			$param_isLuzEncendida = 99;
		}
		
		
$consultaInsert = "INSERT INTO `xgrower_db`.`pulsos` ( `idEquipo`,`fecha`,`temperatura`,`humedad`,`luminosidad`,`estado_luz`,`estado_ventilacion`,`estado_agua`,`temperatura_custom`,`humedad_custom`,`isLuzEncendida` ) VALUES ('{$param_idEquipo}','".$fecha."', {$param_temperatura}, {$param_humedad},{$param_luminosidad}, {$param_estadoLuz}, {$param_estadoVentiladores}, {$param_estadoAgua}, {$param_temperaturaCustom}, {$param_humedadCustom}, {$param_isLuzEncendida})";
	mysql_query($consultaInsert, $link);
	if(intval($param_luminosidad) < 70 && intval($param_isLuzEncendida) == 1){
		//
	$consultaSelect = "SELECT * FROM `xgrower_db`.`alertas` WHERE idEquipo = '".$param_idEquipo."'";
	$datos = mysql_query($consultaSelect, $link);
	$yaHayUna = false;
	while ($fila = mysql_fetch_assoc($datos)) {
		if(intval($fila['tipoAlerta']) == 0 && intval($fila['isLeida']) == 0){
			$yaHayUna = true;
			break;
		}else{
			
		}
	}
	if(!$yaHayUna){
		$consultaInsert = "INSERT INTO `xgrower_db`.`alertas` ( `idEquipo`,`time_creado`,`isLeida`, `tipoAlerta`) VALUES ('{$param_idEquipo}','".$fecha."', 0, 0)";
		mysql_query($consultaInsert, $link);
		$to      = $email;
$subject = 'the subject';
$message = 'La luz no esta andando y deberia';
$headers = 'From: webmaster@example.com' . "\r\n" .
    'Reply-To: webmaster@example.com' . "\r\n" .
    'X-Mailer: PHP/' . phpversion();

mail($to, $subject, $message, $headers);
	}
	//
	}else{
		if(intval($param_luminosidad) > 70 && intval($param_isLuzEncendida) == 0){
			
			//
	$consultaSelect = "SELECT * FROM `xgrower_db`.`alertas` WHERE idEquipo = '".$param_idEquipo."'";
	$datos = mysql_query($consultaSelect, $link);
	$yaHayUna = false;
	while ($fila = mysql_fetch_assoc($datos)) {
		if(intval($fila['tipoAlerta']) == 1 && intval($fila['isLeida']) == 0){
			$yaHayUna = true;
			break;
		}else{
			
		}
	}
	if(!$yaHayUna){
		$consultaInsert = "INSERT INTO `xgrower_db`.`alertas` ( `idEquipo`,`time_creado`,`isLeida`, `tipoAlerta`) VALUES ('{$param_idEquipo}','".$fecha."', 0, 1)";
		mysql_query($consultaInsert, $link);
		$to      = $email;
$subject = 'the subject';
$message = 'Mucha luminosidad, y deberia estar todo apagado';
$headers = 'From: webmaster@example.com' . "\r\n" .
    'Reply-To: webmaster@example.com' . "\r\n" .
    'X-Mailer: PHP/' . phpversion();

mail($to, $subject, $message, $headers);
	}
	//
		}
	}
	}

}
	if (!$link) {
		printf("Can't connect to MySQL Server. Errorcode: %s\n", mysqli_connect_error());
		exit;
	}?>