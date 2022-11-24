<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
if (w_output::f_viene_del_menu('traspaso_bodega')) {
	$wo_traspaso_bodega = new wo_traspaso_bodega();
	$wo_traspaso_bodega->retrieve();
}
else {
	$wo = session::get('wo_traspaso_bodega');
	$wo->procesa_event();
}
?>
