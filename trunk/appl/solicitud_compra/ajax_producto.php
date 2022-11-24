<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$cod_producto = urldecode($_REQUEST['cod_producto']);
$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
$sql = "select	COD_PRODUCTO
				,'' CANTIDAD
				,'S' GENERA_COMPRA
				,NOM_PRODUCTO
		from	PRODUCTO P
		where COD_PRODUCTO = '$cod_producto'"; 
$result = $db->build_results($sql);
for($i=0; $i<count($result); $i++) {
	$result[$i]['NOM_PRODUCTO'] = urlencode($result[$i]['NOM_PRODUCTO']);
	$cod_producto = $result[$i]['COD_PRODUCTO'];
	$sql = "SELECT 	E.COD_EMPRESA IT_COD_EMPRESA, 
					E.ALIAS  IT_ALIAS,
					dbo.f_prod_get_precio_costo (PP.COD_PRODUCTO, PP.COD_EMPRESA, getdate()) PRECIO_COMPRA
			FROM PRODUCTO_PROVEEDOR PP, EMPRESA E
			WHERE PP.COD_PRODUCTO = '$cod_producto'
			AND	  PP.ELIMINADO = 'N' 
			AND	  E.COD_EMPRESA = PP.COD_EMPRESA";
			
	$result_emp = $db->build_results($sql);
	for($j=0; $j<count($result_emp); $j++)
		$result_emp[$j]['IT_ALIAS'] = urlencode($result_emp[$j]['IT_ALIAS']);
	
	$result[$i]['COD_EMPRESA'] = $result_emp;
} 
print urlencode(json_encode($result));
?>