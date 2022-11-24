<?php
require_once(dirname(__FILE__)."/../../../../../commonlib/trunk/php/auto_load.php");

$cod_producto = $_REQUEST['cod_producto'];
$cod_producto = urldecode($cod_producto);

$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
$sql = "SELECT  PRECIO_VENTA_INTERNO PRECIO
		FROM 	PRODUCTO
		WHERE COD_PRODUCTO = '$cod_producto'";  
$result = $db->build_results($sql);
$precio_interno = number_format($result[0]['PRECIO'], 0, ',', '.');	// da formato al precio

print urlencode($precio_interno);
?>