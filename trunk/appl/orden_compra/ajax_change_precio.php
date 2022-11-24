<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$cod_producto = urldecode($_REQUEST["cod_producto"]);
$cod_empresa = $_REQUEST["cod_empresa"];

$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

if ($cod_empresa == '')//se crea la OC a mano y todavía no se ingresa el proveedor
	$sql = "select top (1)  (isnull(PRECIO ,0)) PRECIO
			from		PRODUCTO_PROVEEDOR PP, COSTO_PRODUCTO CP
			where		PP.COD_PRODUCTO	= '$cod_producto'
						and PP.ELIMINADO = 'N'
						and CP.COD_PRODUCTO_PROVEEDOR = PP.COD_PRODUCTO_PROVEEDOR
						and	CP.FECHA_INICIO_VIGENCIA <= getdate()
			order by	CP.FECHA_INICIO_VIGENCIA desc";
else
	$sql = "select top (1)  (isnull(PRECIO ,0)) PRECIO
			from		PRODUCTO_PROVEEDOR PP, COSTO_PRODUCTO CP
			where		PP.COD_PRODUCTO	= '$cod_producto'
						and PP.COD_EMPRESA	= $cod_empresa
						and PP.ELIMINADO = 'N'
						and CP.COD_PRODUCTO_PROVEEDOR = PP.COD_PRODUCTO_PROVEEDOR
						and	CP.FECHA_INICIO_VIGENCIA <= getdate()
			order by	CP.FECHA_INICIO_VIGENCIA desc";
			
$result = $db->build_results($sql);
if (count($result)==0)
	$result[0]['PRECIO'] = 0;
	
print urlencode(json_encode($result));
?>