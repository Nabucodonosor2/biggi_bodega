<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");


if (w_output::f_viene_del_menu('mantencion_sw')) {
	$wo_mantencion_sw = new wo_mantencion_sw();
	$wo_mantencion_sw->retrieve();
}
else {
	$wo = session::get('wo_mantencion_sw');
	$wo->procesa_event();
}
?>