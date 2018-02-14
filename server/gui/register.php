<?php

$param_email = $_POST["email"];
$param_idEquipo = $_POST["porrobotid"];
$param_facebookId = $_POST["facebookid"];
include "conexion.php";

if ($param_idEquipo == ""){
	
} else {
	$consultaSelect = "SELECT * FROM equipos where idEquipo = '".$param_idEquipo."'";
	$datos = mysql_query($consultaSelect, $link);
	$isExistente = false;
	while ($fila = mysql_fetch_assoc($datos)) {
			$isExistente = true;
	}
	if($isExistente){
		echo '{"error" : "ERROR,EXSISTENTE, EQUIPO YA REGISTRADO."}';
	}else{
		//check if usuario already exist
		
		$consultaSelect = "SELECT * FROM usuarios where email = '".$param_email."'";
		$datos = mysql_query($consultaSelect, $link);
		$isUser = false;
		while ($fila = mysql_fetch_assoc($datos)) {
				$isUser = true;
		}
		if(!$isUser){
			$consultaInsert = "INSERT INTO `xgrower_db`.`usuarios` ( `email`,`facebook_id` ) VALUES ('{$param_email}','{$param_facebookId}')";
			mysql_query($consultaInsert, $link);
		}
		
		$consultaSelect = "SELECT * FROM usuarios where email = '".$param_email."'";
		$datos = mysql_query($consultaSelect, $link);
		$isExistente = false;
		$idUser = "";
		while ($fila = mysql_fetch_assoc($datos)) {
				$isExistente = true;
				$idUser = "".$fila['idUsuario'];
		}
		
		if(!$isExistente){
			echo 'hubo un problema guardando el nuevo usuario. consulte con la empresa proveedora XGrower.';
		}else{
			$consultaInsert = "INSERT INTO `xgrower_db`.`equipos` ( `idEquipo`,`isActivo`,`idUsuario` ) VALUES ('{$param_idEquipo}', 1, '".$idUser."')";
			mysql_query($consultaInsert, $link);
			echo 'DONE';
		}
	}
}

	
	if (!$link) {
		printf("Can't connect to MySQL Server. Errorcode: %s\n", mysqli_connect_error());
		exit;
	}
	
	

?> 