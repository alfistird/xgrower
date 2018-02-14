<?php

$param_facebookid = $_GET["id"];
include "conexion.php";

$consultaSelect = "SELECT * FROM usuarios where facebook_id = '".$param_facebookid."'";
		$datos = mysql_query($consultaSelect, $link);
		$isExistente = false;
		while ($fila = mysql_fetch_assoc($datos)) {
			$isExistente = true;
		}
		$isGrower = false;
		if($isExistente){
			$consultaSelect = "SELECT equipos.idEquipo FROM equipos, usuarios where equipos.idUsuario = usuarios.idUsuario AND equipos.isActivo = 1 AND usuarios.facebook_id = '".$param_facebookid."'";
			$datos = mysql_query($consultaSelect, $link);
			while ($fila = mysql_fetch_assoc($datos)) {
				$idEquipo = "".$fila['idEquipo'];
				$isGrower = true;
			}
			
		}
		
		if($isGrower){
			echo "vos no deberias estar aca, esta todo bien.";
		}else{
			//echo "registrar equipo";
			?>
			<!DOCTYPE html> 
<html> 
			<head>
			<title>XGrower</title> 
	<meta name="viewport" content="width=device-width, initial-scale=1"> 
	<script src="https://code.jquery.com/jquery-1.8.3.min.js"></script>
	<script src="facebook.js"></script>
	<script type="text/javascript">
			var fbid = "<?php Print($param_facebookid); ?>";
			function validar(){
				if($("#email").val() == ""){
					alert("mail vacio");
					return false;
				}
				if($("#porrobotid").val() == ""){
					alert("porrobotid vacio");
					return false;
				}
				return true;
				}
			function registrar(){
				if(validar()){
					alert("listo para hacer la llamada aca");
					$.post( "register.php", { email: $("#email").val(), porrobotid: $("#porrobotid").val(), facebookid: fbid} )
					  .done(function( data ) {
					    alert( "Data Loaded: " + data );
					    location.href = 'index.php';
					  });
					
					}
			}
			</script>
			</head>
			<body>
			<div data-role="page" id="nonactive">
			<div class="margin: 0 auto;" data-role="content">
			Bienvenido. Registra tu XGrower:<br/>
				<input type="text" value="email" name="email" id="email">
				<input type="text" value="porrobotid" name="porrobotid" id="porrobotid">
				<input type="button" value="Registrar" onclick="registrar();">
				</div>
				</div>
			</body>
			</html>
			
			<?php 
		}
?>