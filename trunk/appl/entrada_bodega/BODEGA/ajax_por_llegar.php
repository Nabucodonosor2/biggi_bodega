<?php
require_once(dirname(__FILE__) . "/../../../../../commonlib/trunk/php/auto_load.php");


$cod_orden_compra = $_REQUEST['cod_orden_compra'];

$sql = "SELECT dbo.f_oc_por_llegar(COD_ITEM_ORDEN_COMPRA) CANTIDAD
		FROM ITEM_ORDEN_COMPRA
		WHERE COD_ORDEN_COMPRA = $cod_orden_compra";

$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
$result = $db->build_results($sql);

$respuesta = $result[0]['CANTIDAD'];
print urlencode($respuesta);
?>