<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$rut_empresa = $_REQUEST["vl_rut"];
$f_inicial = $_REQUEST["f_inicio"];
$f_termino = $_REQUEST["f_termino"];

$res = explode('/', $f_inicial);
if (strlen($res[2])==2)
	$res[2] = '20'.$res[2];
$f_inicial = sprintf("{ts '$res[2]-$res[1]-$res[0] 00:00:00.000'}");

$res = explode('/', $f_termino);
if (strlen($res[2])==2)
	$res[2] = '20'.$res[2];
$f_termino = sprintf("{ts '$res[2]-$res[1]-$res[0] 23:59:59.000'}");

$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
$sql = "EXEC spr_facturas_por_cliente $rut_empresa, $f_inicial, $f_termino";
$result = $db->build_results($sql);
	
$row_count	= $db->count_rows();
if($row_count > 0){
	$sql = "SELECT '1' COUNT";
	$result = $db->build_results($sql);	
	print urlencode(json_encode($result));
}
else{
	$sql = "SELECT '0' COUNT";
	$result = $db->build_results($sql);	
	print urlencode(json_encode($result));
}
?>