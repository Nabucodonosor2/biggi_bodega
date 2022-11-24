<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$cod_usuario = $_REQUEST["cod_usuario"];
$cod_tipo_op = $_REQUEST["cod_tipo_op"];
$resultado = "";
$K_ESTADO_EMITIDA 			= 1;
$K_ESTADO_CONFIRMADA		= 2;
$K_CENTRO_COSTO_COMERCIAL	= '001';

$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

$sql_cod_empresa = "SELECT COD_EMPRESA 
					FROM USUARIO
					WHERE COD_USUARIO = ".$cod_usuario;
$result = $db->build_results($sql_cod_empresa);
$cod_empresa = $result[0]['COD_EMPRESA'];

$sql_cant = "select count (*) CANTIDAD
			from ORDEN_PAGO OP, NOTA_VENTA NV, EMPRESA E
			where COD_ORDEN_PAGO not in (select COD_ORDEN_PAGO from PARTICIPACION_ORDEN_PAGO POP, PARTICIPACION P
										where POP.COD_PARTICIPACION = P.COD_PARTICIPACION
										AND COD_ESTADO_PARTICIPACION IN (1,2))
			and OP.COD_EMPRESA = ".$cod_empresa."
			and COD_TIPO_ORDEN_PAGO = ".$cod_tipo_op."
			and NV.COD_NOTA_VENTA = OP.COD_NOTA_VENTA
			and E.COD_EMPRESA = NV.COD_EMPRESA
			and NV.COD_EMPRESA NOT IN (SELECT COD_EMPRESA FROM CENTRO_COSTO_EMPRESA WHERE COD_CENTRO_COSTO <> '".$K_CENTRO_COSTO_COMERCIAL."')";
$result_cant = $db->build_results($sql_cant);
$row_cant = $result_cant[0]['CANTIDAD'];

if($row_cant != 0){
	$sql_centro_costo = "SELECT COD_CENTRO_COSTO
								,NOM_CENTRO_COSTO
						FROM CENTRO_COSTO
						WHERE COD_CENTRO_COSTO = '".$K_CENTRO_COSTO_COMERCIAL."'
						UNION
						SELECT DISTINCT CC.COD_CENTRO_COSTO
								,CC.NOM_CENTRO_COSTO
						FROM ORDEN_PAGO OP, NOTA_VENTA NV, CENTRO_COSTO CC, CENTRO_COSTO_EMPRESA CCE
						WHERE COD_ORDEN_PAGO NOT IN (select COD_ORDEN_PAGO from PARTICIPACION_ORDEN_PAGO POP, PARTICIPACION P
													where POP.COD_PARTICIPACION = P.COD_PARTICIPACION
													AND COD_ESTADO_PARTICIPACION in($K_ESTADO_EMITIDA, $K_ESTADO_CONFIRMADA))
						AND OP.COD_EMPRESA = ".$cod_empresa."
						AND COD_TIPO_ORDEN_PAGO = ".$cod_tipo_op."
						AND NV.COD_NOTA_VENTA = OP.COD_NOTA_VENTA
						AND NV.COD_EMPRESA = CCE.COD_EMPRESA 
						AND CC.COD_CENTRO_COSTO = CCE.COD_CENTRO_COSTO";
}else{
	$sql_centro_costo = "SELECT DISTINCT CC.COD_CENTRO_COSTO
								,CC.NOM_CENTRO_COSTO
						FROM ORDEN_PAGO OP, NOTA_VENTA NV, CENTRO_COSTO CC, CENTRO_COSTO_EMPRESA CCE
						WHERE COD_ORDEN_PAGO NOT IN (select COD_ORDEN_PAGO from PARTICIPACION_ORDEN_PAGO POP, PARTICIPACION P
													where POP.COD_PARTICIPACION = P.COD_PARTICIPACION
													AND COD_ESTADO_PARTICIPACION in($K_ESTADO_EMITIDA, $K_ESTADO_CONFIRMADA))
						AND OP.COD_EMPRESA = ".$cod_empresa."
						AND COD_TIPO_ORDEN_PAGO = ".$cod_tipo_op."
						AND NV.COD_NOTA_VENTA = OP.COD_NOTA_VENTA
						AND NV.COD_EMPRESA = CCE.COD_EMPRESA 
						AND CC.COD_CENTRO_COSTO = CCE.COD_CENTRO_COSTO";
}

$result = $db->build_results($sql_centro_costo);
$row_count = $db->count_rows();	
for($i=0;$i<$row_count;$i++){
	$COD_CENTRO_COSTO = $result[$i]['COD_CENTRO_COSTO'];
	$NOM_CENTRO_COSTO = $result[$i]['NOM_CENTRO_COSTO'];
	$resultado .= $COD_CENTRO_COSTO."|".$NOM_CENTRO_COSTO."*";	
}
print $resultado;
?>