<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

if (w_output::f_viene_del_menu('accion_cobranza')) {
	$wo_accion_cobranza = new wo_accion_cobranza();
	$wo_accion_cobranza->retrieve();
}
else {
	$wo = session::get('wo_accion_cobranza');
	$wo->procesa_event();
}
?>