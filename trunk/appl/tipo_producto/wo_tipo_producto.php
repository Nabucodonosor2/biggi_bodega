<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

if (w_output::f_viene_del_menu('tipo_producto')) {
	$wo_tipo_producto = new wo_tipo_producto();
	$wo_tipo_producto->retrieve();
}
else {
	$wo = session::get('wo_tipo_producto');
	$wo->procesa_event();
	
}
?>