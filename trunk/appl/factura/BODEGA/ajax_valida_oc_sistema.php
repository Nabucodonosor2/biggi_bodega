<?php
require_once(dirname(__FILE__)."/../../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../../ws_client_biggi/class_client_biggi.php");

$cod_orden_compra	= $_REQUEST["cod_orden_compra"];
$rut				= $_REQUEST["rut"];
$bdName = '';

$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

//obtener la cantidad de la OC
if($rut == '89257000X')
	$bdName = 'TODOINOX';
else if($rut == '91462001X')
	$bdName = 'BIGGI';
else if($rut == '91462001R'){
	$bdName = 'RENTAL';
}		

$sql = "SELECT COD_ORDEN_COMPRA
		FROM $bdName.dbo.ORDEN_COMPRA
		WHERE COD_ORDEN_COMPRA = $cod_orden_compra";

$result = $db->build_results($sql);
	
if(count($result) == 0)
	print 'DIFERENTE';
else	
	print 'IGUAL';
?>