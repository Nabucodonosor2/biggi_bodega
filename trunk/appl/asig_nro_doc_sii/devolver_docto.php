<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$cod_asig = $_REQUEST['cod_asig'];

$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

$sql = "select convert(nvarchar, getdate(), 103) FECHA_DEVOL
			,NRO_TERMINO - (dbo.f_asig_cant_disponible(COD_ASIG_NRO_DOC_SII) - 1) NRO_INICIO
			,NRO_TERMINO
		from ASIG_NRO_DOC_SII
		where COD_ASIG_NRO_DOC_SII =".$cod_asig;		  

$result = $db->build_results($sql);

$fecha_devol = $result[0]['FECHA_DEVOL'];
$nro_inicio_devol = $result[0]['NRO_INICIO'];
$nro_termino_devol = $result[0]['NRO_TERMINO'];

print $fecha_devol."|".$nro_inicio_devol."|".$nro_termino_devol;
?>
