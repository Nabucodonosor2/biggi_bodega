<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$cod_producto = $_REQUEST['cod_producto'];
$sql = "select	FOTO_GRANDE
		from 		PRODUCTO
		where 		COD_PRODUCTO = '" . $cod_producto . "'";

$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
$result = $db->build_results($sql);		
header("Content-type: image/jpeg;");
print $result[0]['FOTO_GRANDE'];
?>