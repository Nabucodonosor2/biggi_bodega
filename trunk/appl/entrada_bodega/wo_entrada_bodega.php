<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
if (w_output::f_viene_del_menu('entrada_bodega')) {
	$wo_entrada_bodega = new wo_entrada_bodega();
	$wo_entrada_bodega->retrieve();
}
else {
	$wo = session::get('wo_entrada_bodega');
	$wo->procesa_event();
}
?>
