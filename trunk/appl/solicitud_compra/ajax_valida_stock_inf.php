<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../ws_client_biggi/class_client_biggi.php");
$cod_producto	= $_REQUEST['cod_producto'];
$cod_empresa	= $_REQUEST['cod_empresa'];
$cantidad		= $_REQUEST['cantidad'];
$sistema		= '';
$cod_usuario	= session::get("COD_USUARIO");

if($cod_empresa == 4)
	$sistema = 'TODOINOX';

$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

$sql = "SELECT A.AUTORIZA_MENU
        FROM   AUTORIZA_MENU A, USUARIO U
        WHERE  U.COD_USUARIO = ".$cod_usuario." AND
               A.COD_PERFIL = U.COD_PERFIL AND 
               A.COD_ITEM_MENU = '991705'";
$result = $db->build_results($sql);
if (count($result)==0)
	$autoriza = 'N';
else
	$autoriza = $result[0]['AUTORIZA_MENU'];

$sql = "select SISTEMA
			,URL_WS
			,USER_WS
			,PASSWROD_WS
		from PARAMETRO_WS
		where SISTEMA = '$sistema'";
$result = $db->build_results($sql);

$user_ws		= $result[0]['USER_WS'];
$passwrod_ws	= $result[0]['PASSWROD_WS'];
$url_ws			= $result[0]['URL_WS'];

$biggi = new client_biggi("$user_ws", "$passwrod_ws", "$url_ws");
$result = $biggi->consulta_stock($cod_producto, $sistema);

if($cantidad > $result[0]['STOCK'] && $result[0]['MANEJA_INVENTARIO'] == 'S')
	print "Producto: $cod_producto"." Cantidad solicitada: ".$cantidad." Cantidad en stock: ".$result[0]['STOCK']."\n\n|$autoriza";
else
	print "OK";
?>