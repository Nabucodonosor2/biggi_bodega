<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

//$cod_empresa = $_REQUEST['cod_empresa'];
$cod_doc = $_REQUEST['cod_doc'];
$tipo_doc = $_REQUEST['tipo_doc'];

if (session::is_set('wi_guia_recepcion'))
	$wi = session::get('wi_guia_recepcion');
else	
	$wi = session::get('wi_guia_recepcion_arriendo');

$sql_original = $wi->dws['dw_item_guia_recepcion']->get_sql();

$sql = "exec spdw_gr_load_item '$tipo_doc',  $cod_doc";

$wi->dws['dw_item_guia_recepcion']->set_sql($sql);
$wi->dws['dw_item_guia_recepcion']->make_tabla_htm("wi_guia_recepcion.htm");	//$wi->nom_template
$wi->dws['dw_item_guia_recepcion']->set_sql($sql_original);
$wi->save_SESSION();
?>