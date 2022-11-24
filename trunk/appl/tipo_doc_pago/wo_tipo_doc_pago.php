<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

if (w_output::f_viene_del_menu('tipo_doc_pago')) {
	$wo_tipo_doc_pago = new wo_tipo_doc_pago();
	$wo_tipo_doc_pago->retrieve();
}
else {
	$wo = session::get('wo_tipo_doc_pago');
	$wo->procesa_event();
	
}
?>