<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
$cod_usuario_actual = session::get("COD_USUARIO");
$cod_nota_venta = $_REQUEST["cod_nota_venta"];
$porc_dscto_max = $_REQUEST["porc_dscto_max"];

$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
$sql = "SELECT TOP 1 U.COD_USUARIO
					,U.PORC_DESCUENTO_PERMITIDO 
		FROM LOG_CAMBIO L
		     ,USUARIO U
		WHERE NOM_TABLA = 'NOTA_VENTA'
		AND TIPO_CAMBIO = 'U'
		AND U.COD_USUARIO <> $cod_usuario_actual
		AND KEY_TABLA = '$cod_nota_venta'
		AND L.COD_USUARIO = U.COD_USUARIO
		ORDER BY COD_LOG_CAMBIO DESC";

$result = $db->build_results($sql);

if($result[0]['PORC_DESCUENTO_PERMITIDO'] >= $porc_dscto_max){
	print 'PERMITIDO';
}else{
	print '';
}
?>