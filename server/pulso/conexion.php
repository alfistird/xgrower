<?php
$dbhost="mysql.host.com";  
$dbusuario="username"; 
$dbpassword="password"; 
$db="xgrower_db";        
$link = mysql_connect($dbhost, $dbusuario, $dbpassword);
if (!$link) {
    die('Error Connection : ' . mysql_error());
}
mysql_select_db($db, $link);
//$estado = explode('  ', mysql_stat($link));
//print_r($estado);
//die;
?>

