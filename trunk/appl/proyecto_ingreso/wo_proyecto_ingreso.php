<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

if (w_output::f_viene_del_menu('proyecto_ingreso')) {
	$wo_proyecto_ingreso = new wo_proyecto_ingreso();
	$wo_proyecto_ingreso->retrieve();
}
else {	
	$wo = session::get('wo_proyecto_ingreso');
	$wo->procesa_event();
	
}
?>