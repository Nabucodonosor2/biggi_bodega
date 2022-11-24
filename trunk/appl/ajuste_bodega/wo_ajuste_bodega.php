<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
if (w_output::f_viene_del_menu('ajuste_bodega')) {
	$wo_ajuste_bodega = new wo_ajuste_bodega();
	$wo_ajuste_bodega->retrieve();
}
else {
	$wo = session::get('wo_ajuste_bodega');
	$wo->procesa_event();
}
?>
