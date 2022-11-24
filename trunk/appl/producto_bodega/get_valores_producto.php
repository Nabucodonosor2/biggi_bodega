<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$cod_producto = $_REQUEST['cod_producto'];

$sql_porc = "	select	dbo.f_prod_get_costo_base(COD_PRODUCTO) COSTO_BASE,
						PRECIO_VENTA_INTERNO,
						PRECIO_VENTA_PUBLICO		
				from PRODUCTO where COD_PRODUCTO = '$cod_producto'";

$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
$result = $db->build_results($sql_porc);

print $result[0]['COSTO_BASE']."|".$result[0]['PRECIO_VENTA_INTERNO']."|".$result[0]['PRECIO_VENTA_PUBLICO']."|";

?>