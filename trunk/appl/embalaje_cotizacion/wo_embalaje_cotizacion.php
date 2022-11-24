<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

if (w_output::f_viene_del_menu('embalaje_cotizacion')) {
	$wo_embalaje_cotizacion = new wo_embalaje_cotizacion();
	$wo_embalaje_cotizacion->retrieve();
}
else {
	$wo = session::get('wo_embalaje_cotizacion');
	$wo->procesa_event();
}
?>