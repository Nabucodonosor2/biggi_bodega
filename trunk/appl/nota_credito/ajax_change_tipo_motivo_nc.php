<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$cod_tipo_nc_interno_sii = $_REQUEST['cod_tipo_nc_interno_sii'];
$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

$sql="SELECT TNIS.COD_MOTIVO_NOTA_CREDITO
			 ,NOM_MOTIVO_NOTA_CREDITO
			 ,TNIS.COD_TIPO_NOTA_CREDITO
			 ,NOM_TIPO_NOTA_CREDITO 
		FROM TIPO_NC_INTERNO_SII TNIS
			 ,MOTIVO_NOTA_CREDITO MNC
			 ,TIPO_NOTA_CREDITO TNC
		WHERE COD_TIPO_NC_INTERNO_SII = $cod_tipo_nc_interno_sii
		AND TNIS.COD_MOTIVO_NOTA_CREDITO = MNC.COD_MOTIVO_NOTA_CREDITO
		AND TNIS.COD_TIPO_NOTA_CREDITO	= TNC.COD_TIPO_NOTA_CREDITO";
$result = $db->build_results($sql);
$result[0]['NOM_MOTIVO_NOTA_CREDITO'] = urlencode($result[0]['NOM_MOTIVO_NOTA_CREDITO']);
$result[0]['NOM_TIPO_NOTA_CREDITO'] = urlencode($result[0]['NOM_TIPO_NOTA_CREDITO']);

print urlencode(json_encode($result));
?>