<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

if (w_output::f_viene_del_menu('flete_cotizacion')) {
	$wo_flete_cotizacion = new wo_flete_cotizacion();
	$wo_flete_cotizacion->retrieve();
}
else {
	$wo = session::get('wo_flete_cotizacion');
	$wo->procesa_event();
}
?>