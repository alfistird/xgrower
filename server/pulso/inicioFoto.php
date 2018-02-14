<?php

$param_idEquipo = $_GET["id"];
$param_size = $_GET["size"];
date_default_timezone_set("America/Argentina/Cordoba");
$fecha = date("Y-m-d H:i:s", time());


include "conexion.php";

if ($param_idEquipo != ""){
//TODO agregar validar que el idEquipo sea existente y este activo

//Crear una cantidad "size" de registros vacios en tabla fotos_buffer

	for ($i = 1; $i <= $param_size; $i++) {
	
	$fecha = date("Y-m-d H:i:s", time());
	$consultaInsert = "INSERT INTO `xgrower_db`.`fotos_buffer` ( `idEquipo`,`timestamp`,`numero_paquete`) VALUES ('{$param_idEquipo}','".$fecha."', {$i})";
	mysql_query($consultaInsert, $link);
	
	}

	echo "FOIN,TIME=" .$fecha; //foto iniciada

}

if (!$link) {
	printf("Can't connect to MySQL Server. Errorcode: %s\n", mysqli_connect_error());
	exit;
}
	
?>