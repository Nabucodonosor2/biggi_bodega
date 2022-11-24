<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

if (w_output::f_viene_del_menu('proyecto_compra')) {
	$wo_proyecto_compra = new wo_proyecto_compra();
	$wo_proyecto_compra->retrieve();
}
else {	
	$wo = session::get('wo_proyecto_compra');
	$wo->procesa_event();
	
}
?>