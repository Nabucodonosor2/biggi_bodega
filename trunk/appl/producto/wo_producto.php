<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

if (w_output::f_viene_del_menu('producto')) {
	$wo_producto = new wo_producto();
	$wo_producto->retrieve();
}
else {
	$wo = session::get('wo_producto');
	$wo->procesa_event();
}
?>