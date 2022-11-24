<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$nro_orden_compra = $_REQUEST["nro_orden_compra"];
$cod_empresa = $_REQUEST["cod_empresa"];
$K_ESTADO_ANULADA = 3;

$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

$sql = "SELECT	NV.COD_NOTA_VENTA
				,E.NOM_ESTADO_NOTA_VENTA
				,U.NOM_USUARIO
		FROM NOTA_VENTA NV, USUARIO U, ESTADO_NOTA_VENTA E
		WHERE LTRIM(RTRIM(NRO_ORDEN_COMPRA)) = LTRIM(RTRIM('".$nro_orden_compra."'))
		AND NV.COD_ESTADO_NOTA_VENTA = E.COD_ESTADO_NOTA_VENTA
		AND NV.COD_USUARIO = U.COD_USUARIO
		AND NV.COD_EMPRESA = ".$cod_empresa."
		AND NV.COD_ESTADO_NOTA_VENTA NOT IN(".$K_ESTADO_ANULADA.")
		ORDER BY NV.COD_NOTA_VENTA ASC";

$result = $db->build_results($sql);
$row_count = $db->count_rows();

$COD_NOTA_VENTA = $result[0]['COD_NOTA_VENTA'];
$NOM_ESTADO_NOTA_VENTA = $result[0]['NOM_ESTADO_NOTA_VENTA'];
$NOM_USUARIO = $result[0]['NOM_USUARIO'];
$resultado = $row_count.'|'.$COD_NOTA_VENTA.'|'.$NOM_ESTADO_NOTA_VENTA.'|'.$NOM_USUARIO;	

print $resultado;
?>