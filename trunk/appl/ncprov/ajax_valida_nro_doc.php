<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$ve_nro_doc		= $_REQUEST['ve_nro_doc'];
$ve_cod_empresa	= $_REQUEST['ve_cod_empresa'];
$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

$sql="SELECT COUNT(*) COUNT
	  FROM NCPROV
	  WHERE NRO_NCPROV = $ve_nro_doc
	  AND COD_EMPRESA = $ve_cod_empresa
	  AND COD_ESTADO_NCPROV <> 4";
$result = $db->build_results($sql);

print $result[0]['COUNT'];
?>