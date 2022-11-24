<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$K_PRIV_OC_BACKCHARGE = '991515';
$cod_usuario = session::get("COD_USUARIO");	// viene del login
		
$cod_nv = $_REQUEST['cod_nv'];
$kl_estado_cerrada = 2;
$kl_estado_anulada = 3;
 
$sql = "SELECT COD_ESTADO_NOTA_VENTA 
		FROM NOTA_VENTA 
		WHERE COD_NOTA_VENTA = $cod_nv ";

$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
$result = $db->build_results($sql);
$row_count = $db->count_rows(); 
if ($row_count == 1){
	if ($result[0]['COD_ESTADO_NOTA_VENTA'] == $kl_estado_cerrada) {
		if (w_base::tiene_privilegio_opcion_usuario($K_PRIV_OC_BACKCHARGE, $cod_usuario))
			$respuesta = 'CERRADA_PUEDE';
		else
			$respuesta = 'CERRADA';
	}
	else if ($result[0]['COD_ESTADO_NOTA_VENTA'] == $kl_estado_anulada)
		$respuesta = 'ANULADA';
	else
		$respuesta = 'SI';
}
else
	$respuesta = 'NO';
		
print $respuesta;
?>