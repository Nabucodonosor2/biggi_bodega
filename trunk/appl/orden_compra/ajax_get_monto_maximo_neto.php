<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
$cod_empresa = $_REQUEST["cod_empresa"];
$cod_orden_compra = $_REQUEST["cod_orden_compra"];

$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
if($cod_empresa == 0){
	$sql = "SELECT COD_EMPRESA
			FROM ORDEN_COMPRA
			WHERE COD_ORDEN_COMPRA = $cod_orden_compra";
	$result = $db->build_results($sql);
	
	$cod_empresa = $result[0]['COD_EMPRESA'];
}

$sql = "SELECT ISNULL(MONTO_COMPRA_MAX_SIN_AUTORIZAR, 0) MONTO_COMPRA_MAX_SIN_AUTORIZAR
		FROM EMPRESA
		WHERE COD_EMPRESA = $cod_empresa";
			
$result = $db->build_results($sql);
	
print $result[0]['MONTO_COMPRA_MAX_SIN_AUTORIZAR'];
?>