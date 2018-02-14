<?php
require_once("facebook.php");
include "conexion.php";

  $config = array(
      'appId' => '293811804131209',
      'secret' => '6a699fcf62446209a483f73e35146cec',
      'fileUpload' => false, // optional
      'allowSignedRequest' => false, // optional, but should be set to false for non-canvas apps
  );

  $facebook = new Facebook($config);
  $user_id = $facebook->getUser();
  $facebook_id= "";
  $idEquipo = "";
    if($user_id) {

      // We have a user ID, so probably a logged in user.
      // If not, we'll get an exception, which we handle below.
      try {

        $user_profile = $facebook->api('/me','GET');
        //echo "XGrower user: " . $user_profile['name'];
        $facebook_id = "". $user_profile['id'];
        //$idEquipo = "porrobot1";
        
      $consultaSelect = "SELECT * FROM usuarios where facebook_id = '".$facebook_id."'";
		$datos = mysql_query($consultaSelect, $link);
		$isExistente = false;
		while ($fila = mysql_fetch_assoc($datos)) {
			$isExistente = true;
		}
		$isGrower = false;
		if($isExistente){
			$consultaSelect = "SELECT equipos.idEquipo FROM equipos, usuarios where equipos.idUsuario = usuarios.idUsuario AND equipos.isActivo = 1 AND usuarios.facebook_id = '".$facebook_id."'";
			$datos = mysql_query($consultaSelect, $link);
		while ($fila = mysql_fetch_assoc($datos)) {
		$idEquipo = "".$fila['idEquipo'];
		$isGrower = true;
		}
		
		}
      
		
        
        $idEquipo = "porrobot1";
        
?> 









<!DOCTYPE html> 
<html> 
<head> 
	<title>xGrower</title> 
	<meta name="viewport" content="width=device-width, initial-scale=1"> 
	<link rel="stylesheet" href="https://code.jquery.com/mobile/1.2.1/jquery.mobile-1.2.1.min.css" />
	<script src="https://code.jquery.com/jquery-1.8.3.min.js"></script>
	<script src="https://code.jquery.com/mobile/1.2.1/jquery.mobile-1.2.1.min.js"></script>
	<script src="facebook.js"></script>
	<script type="text/javascript">
	
   var ultimoEstado;
   var idEquipo = "<?php Print($idEquipo); ?>";
   var isGrower = "<?php Print($isGrower); ?>";
   var fbid = "<?php Print($facebook_id); ?>";
   var horaUltimoEstado;
   var temperatura;
   var humedad;
   var luminosidad;
   var estadoLuz;
   var estadoVentilacion;
   var estadoAguaFinal;
   var temperatura_custom;
   var humedad_custom;
   var humedadEnTierra;

   function gup( name ){
	   name = name.replace(/[\[]/,"\\\[").replace(/[\]]/,"\\\]");  
	   var regexS = "[\\?&]"+name+"=([^&#]*)";  
	   var regex = new RegExp( regexS );  
	   var results = regex.exec( window.location.href ); 
	    if( results == null )    return "";  
	   else    return results[1];}
   
	$( document ).delegate("#estado", "pageinit", function() {
		if(!isGrower){
			location.href = 'nonactive.php?id='+fbid;
		}
		var interval = setInterval(function(){
	        $.mobile.loading('show');
	        clearInterval(interval);
	    },1); 
		 $.getJSON( "estado.php?id="+idEquipo, { } )
		  .done(function( json ) {
		    console.log( "JSON Data: " + json );

		    //{"ultimoEstado":"00,01,00", "horaUltimoEstado":"2013-10-31 19:47:07", "temperatura":"26", "humedad":"35", "luminosidad":"0", "minutosRiegoAutomatico":"0", "minutosRiegoManual":"1", "temperaturaCustom":"0", "humedadCustom":"0", "humedadEnTierra":"0"}
		    //alert(json.ultimoEstado);
		    ultimoEstado = json.ultimoEstado;
		    horaUltimoEstado = json.horaUltimoEstado;
		    temperatura = json.temperatura;
		    humedad = json.humedad;
		    luminosidad = json.luminosidad;
		    temperatura_custom = json.temperaturaCustom;
		    humedadEnTierra = json.humedadEnTierra;
		    humedad_custom = json.humedadCustom;
			
			  $("#fecha").append("<img id='time' src='./imagenes/time32.png' height='16' width='16'/>");
			  $("#fecha").append(" Last status: "+horaUltimoEstado+"");
			//$("#fecha").text("<"+horaUltimoEstado+">");
			
			
			  $("#foto").append("<img id='foto' src='./imagenes/foto.jpg' height='240' width='320' />");
			
			
			  $("#temperatura").append("<img id='temperature' src='./imagenes/temperature32.png' height='16' width='16'/>");
			  $("#temperatura").append(" Temperature: 26 C");
			  
			  $("#humedad").append("<img id='humidity' src='./imagenes/humidity32.png' height='16' width='16'/>");
			  $("#humedad").append(" Humidity: 38%");
			  
			  $("#luminosidad").append("<img id='brightness' src='./imagenes/brightness32.png' height='16' width='16'/>");
			  $("#luminosidad").append(" Brightness: 15%");
			  //$("#humedadEnTierra").text("Humedad en tierra: "+humedadEnTierra);
			  
			  
			  //00,01,02
			  if(ultimoEstado){
				  var estadosArray=ultimoEstado.split(",");
				  actualizarLuz(parseInt(estadosArray[0]));
				  actualizarVentilacion(parseInt(estadosArray[1]));
				  actualizarVentilacionCustoms();
				  actualizarAgua(parseInt(estadosArray[2]));
				  }else{
					  alert("No hay estados previos guardados");
				  }
			  var interval = setInterval(function(){
			        $.mobile.loading('hide');
			        clearInterval(interval);
			    },1);
		  })
		  .fail(function( jqxhr, textStatus, error ) {
		    var err = textStatus + ", " + error;
		    console.log( "Request Failed: " + err );
		});
		  
		});
	function actualizarLuz(a){
		estadoLuz = a;
		if(a == 0){
			$("#luz0").addClass($.mobile.activeBtnClass);
			$("#luz1").removeClass($.mobile.activeBtnClass);
			$("#luz2").removeClass($.mobile.activeBtnClass);
			$("#luz3").removeClass($.mobile.activeBtnClass);
		}else{
			if(a == 1){
				$("#luz1").addClass($.mobile.activeBtnClass);
				$("#luz0").removeClass($.mobile.activeBtnClass);
				$("#luz2").removeClass($.mobile.activeBtnClass);
				$("#luz3").removeClass($.mobile.activeBtnClass);
			}else{
				if(a == 2){
					$("#luz2").addClass($.mobile.activeBtnClass);
					$("#luz1").removeClass($.mobile.activeBtnClass);
					$("#luz0").removeClass($.mobile.activeBtnClass);
					$("#luz3").removeClass($.mobile.activeBtnClass);
					}else{
						if(a == 3){
							$("#luz3").addClass($.mobile.activeBtnClass);
							$("#luz1").removeClass($.mobile.activeBtnClass);
							$("#luz2").removeClass($.mobile.activeBtnClass);
							$("#luz0").removeClass($.mobile.activeBtnClass);
							}else{
								alert("algo malo paso, no deberia haber llegado aca, la luz es: "+a);
						}
				}
		}
	}
	}
	function actualizarVentilacionCustoms(){

		if(temperatura_custom != "" && temperatura_custom != "99"){
			$("#custom-temperatura").val(temperatura_custom);
			}
		if(humedad_custom != "" && humedad_custom != "99"){
			$("#custom-humedad").val(humedad_custom);
			}
		}
	function actualizarVentilacion(a){
		estadoVentilacion = a;
		if(a == 0){
			$("#vent0").addClass($.mobile.activeBtnClass);
			$("#vent1").removeClass($.mobile.activeBtnClass);
			$("#vent2").removeClass($.mobile.activeBtnClass);
		}else{
			if(a == 1){
				$("#vent1").addClass($.mobile.activeBtnClass);
				$("#vent0").removeClass($.mobile.activeBtnClass);
				$("#vent2").removeClass($.mobile.activeBtnClass);
			}else{
				if(a == 2){
					$("#vent2").addClass($.mobile.activeBtnClass);
					$("#vent1").removeClass($.mobile.activeBtnClass);
					$("#vent0").removeClass($.mobile.activeBtnClass);
				}else{
										alert("algo malo paso, no deberia haber llegado aca, la ventilacion es: "+a);
								}
						}
	}
	}

	function actualizarAgua(a){
		estadoAguaFinal = a;
		if(a == 0){
			$("#agua0").addClass($.mobile.activeBtnClass);
			$("#agua1").removeClass($.mobile.activeBtnClass);
			$("#agua2").removeClass($.mobile.activeBtnClass);
		}else{
			if(a == 1){
				$("#agua0").removeClass($.mobile.activeBtnClass);
				$("#agua1").addClass($.mobile.activeBtnClass);
				$("#agua2").removeClass($.mobile.activeBtnClass);
			}else{
				if(a == 2){
					$("#agua0").removeClass($.mobile.activeBtnClass);
					$("#agua1").removeClass($.mobile.activeBtnClass);
					$("#agua2").addClass($.mobile.activeBtnClass);
				}
				}
		}
	}
	
	function validarCustomStuff(){
		var resultado = true;
			if($("#custom-temperatura").val() == ""){
				resultado = false;
				alert("Si queres ventilacion custom, te falto poner la temperatura");
				}else{
					if(!isNumber($("#custom-temperatura").val())){
						resultado = false;
						alert("La temperatura custom tiene que ser un numero de 0 a 99");
						}else{
							if(parseInt($("#custom-temperatura").val()) < 0 || parseInt($("#custom-temperatura").val()) > 99){
								resultado = false;
								alert("La temperatura custom tiene que ser un numero de 0 a 99");
								}else{
									if(parseInt($("#custom-temperatura").val()) < 10){
										temperatura_custom = "0"+parseInt($("#custom-temperatura").val());
										}else{
											temperatura_custom = ""+parseInt($("#custom-temperatura").val());
										}
								}
						}
					}
			if($("#custom-humedad").val() == ""){
				resultado = false;
				alert("Si queres ventilacion custom, te falto poner la humedad");
				}else{
					if(!isNumber($("#custom-humedad").val())){
						resultado = false;
						alert("La humedad custom tiene que ser un numero de 0 a 99");
						}else{
							if(parseInt($("#custom-humedad").val()) < 0 || parseInt($("#custom-humedad").val()) > 99){
								resultado = false;
								alert("La humedad custom tiene que ser un numero de 0 a 99");
								}else{
									if(parseInt($("#custom-humedad").val()) < 10){
										humedad_custom = "0"+parseInt($("#custom-humedad").val());
										}else{
											humedad_custom = ""+parseInt($("#custom-humedad").val());
										}
								}
						}
					}
		
		
		return resultado;
		}
	function isNumber(n) {
		  return !isNaN(parseFloat(n)) && isFinite(n);
		}
	function acomodarEstadosCustoms(){
		if(estadoAguaFinal == 0){
				estadoAguaFinal = "00";
		}else{
			if(estadoAguaFinal == 1){
				estadoAguaFinal = "01";
		}else{
			if(estadoAguaFinal == 2){
				estadoAguaFinal = "02";
		}
			}
		}
				
	}
	function actualizarEstado(){
		if(validarCustomStuff()){
			acomodarEstadosCustoms();
			alert("actualizando a estado: 0"+estadoLuz+",0"+estadoVentilacion+","+estadoAguaFinal+","+temperatura_custom+","+humedad_custom);
			if(idEquipo != ""){
				$.get( "actualizarEstado.php?id="+idEquipo+"&mensaje=0"+estadoLuz+",0"+estadoVentilacion+","+estadoAguaFinal+","+temperatura_custom+","+humedad_custom, function( data ) {
					  alert( "Listo man, esto respondio el server: " + data );
					});
				}else{
					alert("no id o id inactivo");
				}

			
			}
					}
	function desloguearmeFacebookPHP(){
		<?php 
		$logoutUrl = 'https://www.facebook.com/logout.php?next=http://grower.xgrower.com&access_token=' . $facebook->getAccessToken();
		?>
		var logoutURL = '<?php echo $logoutUrl?>';
		window.location.assign(logoutURL);
	}
	</script>
</head> 
<body>
<div id="fb-root"></div>
<div data-role="page" id="estado">
	<div data-role="header" data-theme="b">
	<a href="#" onclick="desloguearmeFacebookPHP()" data-theme="b"  data-mini="true" data-inline="true" data-role="button">Log Out</a>
	<h1>xGrower</h1>
	<a href="http://200.127.49.85:81/video/liveipd.asp" data-theme="b"  data-mini="true" data-inline="true" data-role="button">Settings</a>	
	</div><!-- /header -->

	<div class="margin: 0 auto;" data-role="content">

  
	<h4 id="fecha"></h4>	
	<h5 id="foto"></h5>
	<h5 id="temperatura"></h5>
	<h5 id="humedad"></h5>
	<h5 id="luminosidad"></h5>
	<h5 id="humedadEnTierra"></h5>
	
	<hr>
	<table  border="0" cellpadding="0" cellspacing="0">
  <tr>
    <td><img src="./imagenes/light48.png" alt="light" height="48" width="48" /></td>
    <td><table  border="0" cellpadding="0" cellspacing="0">
      <tr>
        <td><p>&nbsp;&nbsp;Light</p></td>
      </tr>
      <tr>
        <td>
		<div class="margin: 0 auto;" data-role="controlgroup" data-type="horizontal" data-mini="true" id="luz">
			<a onclick="actualizarLuz(0)" id="luz0" data-role="button">OFF</a>
			<a onclick="actualizarLuz(1)" id="luz1" data-role="button">ON</a>
			<a onclick="actualizarLuz(2)" id="luz2" data-role="button">Vegetative</a>
			<a onclick="actualizarLuz(3)" id="luz3" data-role="button">Flowering</a>
			</div>
			</td>
      </tr>
    </table></td>
  </tr>
</table>	

<hr />

<table  border="0" cellpadding="0" cellspacing="0">
  <tr>
    <td><img src="./imagenes/ventilation48.png" alt="ventilation" height="48" width="48" /></td>
    <td><table  border="0" cellpadding="0" cellspacing="0">
      <tr>
        <td><p>&nbsp;&nbsp;Ventilation</p></td>
      </tr>
      <tr>
        <td>
		<div class="margin: 0 auto;" data-role="controlgroup"  data-type="horizontal" data-mini="true" id="ventilacion">
			<a onclick="actualizarVentilacion(0)" id="vent0" data-role="button">OFF</a>
			<a onclick="actualizarVentilacion(1)" id="vent1" data-role="button">ON</a>
			<a onclick="actualizarVentilacion(2)" id="vent2" data-role="button">Custom</a>
			</div>
			</td>
      </tr>
    </table></td>
    <td>
	<table width="90"  border="0" cellspacing="0" cellpadding="0">
      <tr>
	  <div >
        <td>
		
	<table width="0" border="0" cellspacing="0" cellpadding="4">
  <tr>
    <td width="25" align="right">&nbsp;<img src="./imagenes/temperature32.png" alt="temperature" height="16" width="16" /></td>
    <td><input type="text" name="custom-temperatura" id="custom-temperatura" value="" placeholder="Temperature Threshold"/></td>
  </tr>
  </table>

		</td>
      </tr>
      <tr>
        <td>
		
		<table width="0" border="0" cellspacing="0" cellpadding="4">
  <tr>
    <td width="25" align="right"><img src="./imagenes/humidity32.png" alt="humidity" height="16" width="16" /></td>
    <td><input type="text" name="custom-humedad" id="custom-humedad" value="" placeholder="Humidity Threshold"/></td>
  </tr>
		</table>
		
		
		</td>
		</div>
      </tr>
    </table></td>
  </tr>
  </table>


<hr />

<table  border="0" cellpadding="0" cellspacing="0">
  <tr>
    <td><img src="./imagenes/water48.png" alt="ventilation" height="48" width="48" /></td>
    <td><table  border="0" cellpadding="0" cellspacing="0">
      <tr>
        <td><p>&nbsp;&nbsp;Water</p></td>
      </tr>
      <tr>
        <td>
			<div class="margin: 0 auto;" data-role="controlgroup" data-type="horizontal"  data-mini="true" id="aguaAutomaticaDiv">
			<a onclick="actualizarAgua(0)" id="agua0" data-role="button">OFF</a>
			<a onclick="actualizarAgua(1)" id="agua1" data-role="button">ON</a>
			<a onclick="actualizarAgua(2)" id="agua2" data-role="button">Hydroponic</a>
			</div>
			</td>
      </tr>
    </table></td>
  </tr>
</table>	


	
		

</br></br>
		<a class="margin: 0 auto;"  data-role="button" data-theme="e" data-mini="true" onclick="actualizarEstado()">Save changes</a>
	</div><!-- /content -->
</div><!-- /page -->
</body>
</html>
<?php
}

catch(FacebookApiException $e) {
        // If the user is logged out, you can have a 
        // user ID even though the access token is invalid.
        // In this case, we'll get an exception, so we'll
        // just ask the user to login again here.
        //$login_url = $facebook->getLoginUrl();
        $login_url = "login.html"; 
        echo 'Please <a href="' . $login_url . '">login.</a>';
        error_log($e->getType());
        error_log($e->getMessage());
      } 
    } else {

      // No user, print a link for the user to login
      $login_url = $facebook->getLoginUrl();
      echo 'Please <a href="' . $login_url . '">login.</a>';

    }
  ?>
  