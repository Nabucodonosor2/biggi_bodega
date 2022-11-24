<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

if (w_output::f_viene_del_menu('origen_cotizacion')) {
	$wo_origen_cotizacion = new wo_origen_cotizacion();
	$wo_origen_cotizacion->retrieve();
}
else {
	$wo = session::get('wo_origen_cotizacion');
	$wo->procesa_event();
}
?>