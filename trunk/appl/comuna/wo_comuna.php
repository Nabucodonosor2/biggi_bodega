<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

if (w_output::f_viene_del_menu('comuna')) {
	$wo_comuna = new wo_comuna();
	$wo_comuna->retrieve();
}
else {
	$wo = session::get('wo_comuna');
	$wo->procesa_event();
	
}
?>