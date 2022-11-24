<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

if (w_output::f_viene_del_menu('ciudad')) {
	$wo_ciudad = new wo_ciudad();
	$wo_ciudad->retrieve();
}
else {
	$wo = session::get('wo_ciudad');
	$wo->procesa_event();	
}
?>