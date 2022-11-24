<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../ws_client_biggi/class_client_biggi.php");
require_once(dirname(__FILE__)."/../../appl.ini");
$cod_producto	= $_REQUEST["cod_producto"];
$cod_empresa	= $_REQUEST["cod_empresa"];
$cantidad_total	= $_REQUEST["cantidad_total"];
$sistema		= '';

$temp = new Template_appl('dlg_detalle_stock.htm');

if($cod_empresa == 4)
	$sistema = 'TODOINOX';
if($cod_empresa == 1)
	$sistema = 'COMERCIAL'; //planificar si es para comercial o rental

if($sistema <> ''){	
	$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
	$sql = "select SISTEMA, URL_WS, USER_WS,PASSWROD_WS  from PARAMETRO_WS
			where SISTEMA = '$sistema'";
	$result = $db->build_results($sql);
	
	$user_ws		= $result[0]['USER_WS'];
	$passwrod_ws	= $result[0]['PASSWROD_WS'];
	$url_ws			= $result[0]['URL_WS'];
	
	$biggi = new client_biggi("$user_ws", "$passwrod_ws", "$url_ws");
	$result = $biggi->consulta_stock($cod_producto, $sistema);
}

if($result == '')
	$result = 0;

$sql = "SELECT $cantidad_total CANTIDAD_SOLICITADA
			  ,$result CANTIDAD_STOCK
			  ,CASE
			  	WHEN $cantidad_total <= $result THEN 'OK'
			  	ELSE 'Insuficiente'
			  END STATUS";

$dw = new datawindow($sql);
	
$dw->retrieve();
$dw->habilitar($temp, true);
print $temp->toString();
?>