<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$cod_producto = $_REQUEST["cod_producto"];

$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
$sql = "SELECT 	COUNT(*) CANT  
		FROM 	PRODUCTO
		WHERE 	COD_PRODUCTO = '$cod_producto'";

$result = $db->build_results($sql);		
print urlencode(json_encode($result));
?>