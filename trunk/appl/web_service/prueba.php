<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$cod_nota_venta = $_REQUEST['cod_nota_venta'];
$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
$sql = "select COD_PRODUCTO
					,NOM_PRODUCTO
					,COD_ITEM_NOTA_VENTA
			from ITEM_NOTA_VENTA
			where COD_NOTA_VENTA = $cod_nota_venta";
$result = $db->build_results($sql);
print_r($result);
?>