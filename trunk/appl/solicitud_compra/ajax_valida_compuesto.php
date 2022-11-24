<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$cod_producto = urldecode($_REQUEST['cod_producto']);
$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
$sql = "SELECT COUNT(*) COUNT
		FROM PRODUCTO_PROVEEDOR
		WHERE COD_PRODUCTO = '$cod_producto'
		and ELIMINADO = 'N'"; 
$result = $db->build_results($sql);

print $result[0]['COUNT'];
?>