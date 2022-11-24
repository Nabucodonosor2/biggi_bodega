<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

if (w_output::f_viene_del_menu('instalacion_cotizacion')) {
	$wo_instalacion_cotizacion = new wo_instalacion_cotizacion();
	$wo_instalacion_cotizacion->retrieve();
}
else {
	$wo = session::get('wo_instalacion_cotizacion');
	$wo->procesa_event();
}
?>