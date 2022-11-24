<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");


if (w_output::f_viene_del_menu('bodega')) {
	$wo_bodega = new wo_bodega();
	$wo_bodega->retrieve();
}
else {
	$wo = session::get('wo_bodega');
	$wo->procesa_event();
}
?>
