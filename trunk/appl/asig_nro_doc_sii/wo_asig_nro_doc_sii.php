<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");


if (w_output::f_viene_del_menu('asig_nro_doc_sii')) {
	$wo_asig_nro_doc_sii = new wo_asig_nro_doc_sii();
	$wo_asig_nro_doc_sii->retrieve();
}
else {
	$wo = session::get('wo_asig_nro_doc_sii');
	$wo->procesa_event();
}
?>
