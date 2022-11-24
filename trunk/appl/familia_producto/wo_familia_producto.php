<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

if (w_output::f_viene_del_menu('familia_producto')) {
	$wo_familia_producto = new wo_familia_producto();
	$wo_familia_producto->retrieve();
}
else {
	$wo = session::get('wo_familia_producto');
	$wo->procesa_event();
}
?>