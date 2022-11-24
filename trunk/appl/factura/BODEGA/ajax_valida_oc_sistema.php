<?php
require_once(dirname(__FILE__)."/../../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../../ws_client_biggi/class_client_biggi.php");

$cod_orden_compra	= $_REQUEST["cod_orden_compra"];
$rut				= $_REQUEST["rut"];


//obtener la cantidad de la OC
if($rut == '89257000X'){ // 	COMERCIAL TODOINOX LTDA.	

	$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
	$sql = "select SISTEMA, URL_WS, USER_WS,PASSWROD_WS  from PARAMETRO_WS
			where SISTEMA = 'TODOINOX' ";
	$result = $db->build_results($sql);
	
	$user_ws		= $result[0]['USER_WS'];
	$passwrod_ws	= $result[0]['PASSWROD_WS'];
	$url_ws			= $result[0]['URL_WS'];
	
	$biggi = new client_biggi("$user_ws", "$passwrod_ws", "$url_ws");
}
if($rut == '91462001X'){ // COMERCIAL
	$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
	$sql = "select SISTEMA, URL_WS, USER_WS,PASSWROD_WS  from PARAMETRO_WS
			where SISTEMA = 'COMERCIAL' ";
	$result = $db->build_results($sql);
	
	$user_ws		= $result[0]['USER_WS'];
	$passwrod_ws	= $result[0]['PASSWROD_WS'];
	$url_ws			= $result[0]['URL_WS'];

	$biggi = new client_biggi("$user_ws", "$passwrod_ws", "$url_ws");
}
if($rut == '91462001R'){ //RENTAL
	$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
	$sql = "select SISTEMA, URL_WS, USER_WS,PASSWROD_WS  from PARAMETRO_WS
			where SISTEMA = 'RENTAL' ";
	$result = $db->build_results($sql);
	
	$user_ws		= $result[0]['USER_WS'];
	$passwrod_ws	= $result[0]['PASSWROD_WS'];
	$url_ws			= $result[0]['URL_WS'];
	
	$biggi = new client_biggi("$user_ws", "$passwrod_ws", "$url_ws");
}		

$result = $biggi->cli_orden_compra($cod_orden_compra);
	
if($result['ORDEN_COMPRA'][0]['COD_ORDEN_COMPRA'] != $cod_orden_compra)
	print 'DIFERENTE';
else	
	print 'IGUAL';
?>