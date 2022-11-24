<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$rut_empresa = $_REQUEST["rut_empresa"];

$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
$sql = "SELECT	DIG_VERIF FROM EMPRESA
		WHERE	RUT = ".$rut_empresa;

$result = $db->build_results($sql);	

$row_count	= $db->count_rows();
if($row_count > 0){
	print urlencode(json_encode($result));
}
else{
	$sql = "SELECT 'NO_EXISTE' DIG_VERIF";
	$result = $db->build_results($sql);	
	print urlencode(json_encode($result));
}
?>