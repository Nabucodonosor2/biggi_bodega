<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$nro_doc = $_REQUEST['nro_doc'];
$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

$sql="SELECT COUNT(*) COUNT
	  FROM NCPROV
	  WHERE NRO_NCPROV = $nro_doc
	  AND COD_ESTADO_NCPROV <> 4";
$result = $db->build_results($sql);

print $result[0]['COUNT'];
?>