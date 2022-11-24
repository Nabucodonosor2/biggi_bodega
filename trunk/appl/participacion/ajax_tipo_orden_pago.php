<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$cod_usuario = $_REQUEST["cod_usuario"];
$resultado = "";
$K_ESTADO_EMITIDA 			= 1;
$K_ESTADO_CONFIRMADA		= 2;

$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

$sql_cod_empresa = "SELECT COD_EMPRESA 
					FROM USUARIO
					WHERE COD_USUARIO = ".$cod_usuario;
$result = $db->build_results($sql_cod_empresa);
$cod_empresa = $result[0]['COD_EMPRESA'];
	
$sql_tipo_op = "SELECT DISTINCT OP.COD_TIPO_ORDEN_PAGO , T.NOM_TIPO_ORDEN_PAGO
				FROM ORDEN_PAGO OP, TIPO_ORDEN_PAGO T 
				WHERE OP.COD_ORDEN_PAGO NOT IN (SELECT COD_ORDEN_PAGO FROM PARTICIPACION_ORDEN_PAGO POP, PARTICIPACION P
												WHERE POP.COD_PARTICIPACION = P.COD_PARTICIPACION
												AND COD_ESTADO_PARTICIPACION in($K_ESTADO_EMITIDA, $K_ESTADO_CONFIRMADA))
				AND OP.COD_EMPRESA = ".$cod_empresa."
				AND OP.COD_TIPO_ORDEN_PAGO = T.COD_TIPO_ORDEN_PAGO";

$result = $db->build_results($sql_tipo_op);
$row_count = $db->count_rows();	
for($i=0;$i<$row_count;$i++){
	$COD_TIPO_ORDEN_PAGO = $result[$i]['COD_TIPO_ORDEN_PAGO'];
	$NOM_TIPO_ORDEN_PAGO = $result[$i]['NOM_TIPO_ORDEN_PAGO'];
	$resultado .= $COD_TIPO_ORDEN_PAGO."|".$NOM_TIPO_ORDEN_PAGO."*";	
}
print $resultado;
?>