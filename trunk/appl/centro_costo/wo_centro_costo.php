<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

if (w_output::f_viene_del_menu('centro_costo')) {
	$wo_centro_costo = new wo_centro_costo();
	$wo_centro_costo->retrieve();
}
else {	
	$wo = session::get('wo_centro_costo');
	$wo->procesa_event();
	
}
?>