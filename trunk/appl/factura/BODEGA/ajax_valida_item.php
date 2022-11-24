<?php
require_once(dirname(__FILE__)."/../../../../../commonlib/trunk/php/auto_load.php");

$cantidad = $_REQUEST['cantidad']; 
$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

$sql = "SELECT VALOR 
		FROM PARAMETRO
		WHERE COD_PARAMETRO = 29";

$result = $db->build_results($sql);

if($cantidad > $result[0]['VALOR'])	
	print 'ALERTA';
else
	print '';
?>