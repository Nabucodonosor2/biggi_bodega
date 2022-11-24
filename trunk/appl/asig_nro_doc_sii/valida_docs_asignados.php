<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$tipo_doc = $_REQUEST['cod_tipo_doc_sii'];
$ini = $_REQUEST['nro_inicio'];
$fin = $_REQUEST['nro_termino'];

$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

$sql = "select count(*) RESULT 
		from asig_nro_doc_sii 
		where $ini > = nro_inicio and $ini < isnull(nro_inicio_devol, nro_termino + 1)";
$result = $db->build_results($sql);
$nro_ini = $result[0]['RESULT'];

$sql = "select count(*) RESULT 
		from asig_nro_doc_sii 
		where $fin > = nro_inicio and $fin < isnull(nro_inicio_devol, nro_termino + 1)";
$result = $db->build_results($sql);
$nro_fin = $result[0]['RESULT'];
		
$total_nro = $nro_ini + $nro_fin;
		
if ($total_nro == 0)
	print 'N';
else
	print 'S';

?>