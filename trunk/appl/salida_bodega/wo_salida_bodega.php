<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
if (w_output::f_viene_del_menu('salida_bodega')) {
	$wo_salida_bodega = new wo_salida_bodega();
	$wo_salida_bodega->retrieve();
}
else {
	$wo = session::get('wo_salida_bodega');
	$wo->procesa_event();
}
?>
