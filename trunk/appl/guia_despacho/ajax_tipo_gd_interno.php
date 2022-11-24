<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
$cod_tipo_guia_despacho = $_REQUEST['ve_cod_tipo_guia_despacho'];

if($cod_tipo_guia_despacho <> ""){
	$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
	
	$sql = "SELECT NOM_TIPO_GD_INTERNO_SII
			FROM TIPO_GD_INTERNO_SII TGDS
				,TIPO_GUIA_DESPACHO TGD
			WHERE TGD.COD_TIPO_GUIA_DESPACHO = $cod_tipo_guia_despacho
			AND TGDS.COD_TIPO_GD_INTERNO_SII = TGD.COD_TIPO_GD_INTERNO_SII";
	$result = $db->build_results($sql);		
}		

print $result[0]['NOM_TIPO_GD_INTERNO_SII'];
?>