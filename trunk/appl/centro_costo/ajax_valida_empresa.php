<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$cod_empresa = $_REQUEST["cod_empresa"];

$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
$sql = "SELECT 	COD_CENTRO_COSTO 
		FROM 	CENTRO_COSTO_EMPRESA
		WHERE 	COD_EMPRESA = $cod_empresa";

$result = $db->build_results($sql);		
$row_count	= $db->count_rows();
if($row_count > 0){
	print urlencode(json_encode($result));
}
else{
	$sql = "SELECT 'NO_EXISTE' COD_CENTRO_COSTO FROM CENTRO_COSTO_EMPRESA";
	$result = $db->build_results($sql);	
	print urlencode(json_encode($result));
}
?>