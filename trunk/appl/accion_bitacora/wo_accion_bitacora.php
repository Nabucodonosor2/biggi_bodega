<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

if (w_output::f_viene_del_menu('accion_bitacora')) {
	$wo_accion_bitacora = new wo_accion_bitacora();
	$wo_accion_bitacora->retrieve();
}
else {
	$wo = session::get('wo_accion_bitacora');
	$wo->procesa_event();
}
?>