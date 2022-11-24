<?php
require_once(dirname(__FILE__)."/../../../../../commonlib/trunk/php/auto_load.php");

$cod_producto = $_REQUEST['cod_producto'];
$K_BODEGA_TERMINADO = 2;

$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
$sql = "SELECT  dbo.f_bodega_stock_cero('$cod_producto', $K_BODEGA_TERMINADO, getdate()) CANTIDAD_STOCK";  
$result = $db->build_results($sql);

print $result[0]['CANTIDAD_STOCK'];
?>