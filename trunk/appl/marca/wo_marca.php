<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

if (w_output::f_viene_del_menu('marca')) {
	$wo_marca = new wo_marca();
	$wo_marca->retrieve();
}
else {
	$wo = session::get('wo_marca');
	$wo->procesa_event();
	
}
?>