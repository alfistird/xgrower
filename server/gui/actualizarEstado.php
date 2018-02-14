<?php

$param_idEquipo = $_GET["id"];
$param_mensaje = $_GET["mensaje"];
//agregar esto para saber la fecha
date_default_timezone_set("America/Argentina/Cordoba");
$fecha = date("Y-m-d H:i:s", time());
include "conexion.php";

if ($param_idEquipo == ""){
	echo "sin id no se puede";
} else {
	$consultaSelect = "SELECT * FROM equipos where idEquipo = '".$param_idEquipo."'";
	$datos = mysql_query($consultaSelect, $link);
	$isExistente = false;
	while ($fila = mysql_fetch_assoc($datos)) {
		if($fila['isActivo'] == 1){
			$isExistente = true;
			$consultaSelect = "SELECT * FROM bandeja_salida WHERE idEquipo = '".$param_idEquipo."' AND estado = 1 order by idbandeja_salida desc";
			$datos = mysql_query($consultaSelect, $link);
			$hayMensajeEnEstado1 = false;
			while ($fila = mysql_fetch_assoc($datos)) {
				$hayMensajeEnEstado1 = true;
				break;
			}
			if($hayMensajeEnEstado1){
				$consultaUpdate = "UPDATE bandeja_salida SET estado=2 WHERE idEquipo = '".$fila['idEquipo']."' and estado=1";
				mysql_query($consultaUpdate, $link);
			}
			
		}else{
			echo "ERROR,INACTIVO";
		}
	}
	if(!$isExistente){
		echo "ERROR,INEXSISTENTE";
	}else{
		
		
$consultaInsert = "INSERT INTO `xgrower_db`.`bandeja_salida` ( `idEquipo`,`mensaje`,`estado`,`time_creado`) VALUES ('{$param_idEquipo}','{$param_mensaje}', 1,  '".$fecha."')";
	mysql_query($consultaInsert, $link);

	}
	echo "the damage is done";

}

	
	if (!$link) {
		printf("Can't connect to MySQL Server. Errorcode: %s\n", mysqli_connect_error());
		exit;
	}
	
	

?> 
