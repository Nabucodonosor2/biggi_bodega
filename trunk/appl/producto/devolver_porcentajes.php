<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$K_PARAM_FACTOR_PRE_INT_BAJO =34;	
$K_PARAM_FACTOR_PRE_PUB_ALTO =37;
	

$cod_producto = $_REQUEST['cod_producto'];


$sql_porc = "	SELECT VALOR 
				FROM PARAMETRO 
				WHERE COD_PARAMETRO between ".$K_PARAM_FACTOR_PRE_INT_BAJO."and ".$K_PARAM_FACTOR_PRE_PUB_ALTO;

$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
$result = $db->build_results($sql_porc);
$row_count = $db->count_rows();
If ($row_count == 0) {
	$result  = 0;
}
else{
	$respuesta='';
	for ($i = 0; $i <= $row_count-1; $i++) {		
			$respuesta = $respuesta.$result[$i]['VALOR']."|";
		}
}
print $respuesta;

?>