<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
//se crea error cuando se filtra por JJ, GTE. VENTA, COMERCIAL. por falta de MB
ini_set('memory_limit', '720M');
ini_set('max_execution_time', 900); //900 seconds = 15 minutes

$cod_usuario = $_REQUEST["cod_usuario"];
$cod_tipo_op = $_REQUEST["cod_tipo_op"];
$cod_cc		 = $_REQUEST["cod_cc"];
$resultado = "";

$K_ESTADO_PARTICIPACION_EMITIDA 	= 1;
$K_ESTADO_PARTICIPACION_CONFIRMADA = 2;

$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

$sql_cod_empresa = "SELECT COD_EMPRESA
							,NOM_USUARIO 
					FROM USUARIO
					WHERE COD_USUARIO = ".$cod_usuario;
$result = $db->build_results($sql_cod_empresa);
$cod_empresa = $result[0]['COD_EMPRESA'];
$nom_usuario = $result[0]['NOM_USUARIO'];

			if ($cod_cc == 0){
				$select_centro_costo = "";	
			}
			else if ($cod_cc == '001'){
				$select_centro_costo = "and NV.COD_EMPRESA NOT IN (SELECT COD_EMPRESA FROM CENTRO_COSTO_EMPRESA WHERE COD_CENTRO_COSTO <> '".$cod_cc."')";	
			}else{
				$select_centro_costo = "and NV.COD_EMPRESA IN (SELECT COD_EMPRESA FROM CENTRO_COSTO_EMPRESA WHERE COD_CENTRO_COSTO = '".$cod_cc."')";
			}
			
$sql_item_crea_desde = "select 'S' SELECCION 
							,null COD_ORDEN_PAGO_PARTICIPACION 
							,null COD_PARTICIPACION 
							,COD_ORDEN_PAGO 
							,convert(nvarchar, FECHA_ORDEN_PAGO, 103) FECHA_ORDEN_PAGO 
							,OP.COD_NOTA_VENTA 
							,convert(nvarchar, FECHA_NOTA_VENTA, 103) FECHA_NOTA_VENTA
							,E.NOM_EMPRESA 
							,OP.TOTAL_NETO TOTAL_NETO_POP 
							,OP.TOTAL_NETO TOTAL_NETO_POP_C 
							,".$K_ESTADO_PARTICIPACION_EMITIDA." COD_ESTADO_PARTICIPACION 
							,COD_TIPO_ORDEN_PAGO
					from ORDEN_PAGO OP, NOTA_VENTA NV, EMPRESA E, CENTRO_COSTO_EMPRESA CCE
					where COD_ORDEN_PAGO not in (select COD_ORDEN_PAGO from PARTICIPACION_ORDEN_PAGO POP, PARTICIPACION P
													where POP.COD_PARTICIPACION = P.COD_PARTICIPACION
														AND COD_ESTADO_PARTICIPACION IN (".$K_ESTADO_PARTICIPACION_EMITIDA.", ".$K_ESTADO_PARTICIPACION_CONFIRMADA."))
					and OP.COD_EMPRESA = ".$cod_empresa."
					and COD_TIPO_ORDEN_PAGO = ".$cod_tipo_op."
					and NV.COD_NOTA_VENTA = OP.COD_NOTA_VENTA
					and E.COD_EMPRESA = NV.COD_EMPRESA
					".$select_centro_costo."
					ORDER BY E.NOM_EMPRESA ASC";

$result = $db->build_results($sql_item_crea_desde);

$sql_nom_centro_costo = "SELECT NOM_CENTRO_COSTO 
						FROM	CENTRO_COSTO 
						WHERE COD_CENTRO_COSTO = ".$cod_cc;
$result_nom_cc = $db->build_results($sql_nom_centro_costo);
$nom_centro_costo = $result_nom_cc[0]['NOM_CENTRO_COSTO'];

$sql_nom_tipo_orden_pago = "SELECT NOM_TIPO_ORDEN_PAGO
							FROM	TIPO_ORDEN_PAGO
							WHERE	COD_TIPO_ORDEN_PAGO = ".$cod_tipo_op;
$result_nom_tipo_op = $db->build_results($sql_nom_tipo_orden_pago);
$nom_tipo_orden_pago = $result_nom_tipo_op[0]['NOM_TIPO_ORDEN_PAGO'];


$resultado = count($result).'|'.$nom_usuario.'|'.$nom_tipo_orden_pago.'|'.$nom_centro_costo.'|';
print $resultado;
?>