<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$cod_tipo_doc_sii = $_REQUEST['cod_tipo_doc_sii'];
$nro_inicio = $_REQUEST['nro_inicio'];
$nro_termino = $_REQUEST['nro_termino'];

$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
$sql = "select dbo.f_asig_get_cant_nros_usados(".$cod_tipo_doc_sii.", ".$nro_inicio.", ".$nro_termino.") COUNT";
$result = $db->build_results($sql);
if ($result[0]['COUNT']==0)
	print 'N';
else
	print 'S';
?>