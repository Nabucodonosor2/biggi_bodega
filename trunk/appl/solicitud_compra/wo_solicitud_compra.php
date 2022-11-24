<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

if (w_output::f_viene_del_menu('solicitud_compra')) {
	$wo = new wo_solicitud_compra();
	$wo->retrieve();
}
else {
	$wo = session::get('wo_solicitud_compra');
	$wo->procesa_event();
}
?>