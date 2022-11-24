<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

if (w_output::f_viene_del_menu('origen_venta')) {
	$wo_origen_venta = new wo_origen_venta();
	$wo_origen_venta->retrieve();
}
else {
	$wo = session::get('wo_origen_venta');
	$wo->procesa_event();
}
?>