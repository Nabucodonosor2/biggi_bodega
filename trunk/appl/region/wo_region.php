<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

if (w_output::f_viene_del_menu('region')) {
	$wo_region = new wo_region();
	$wo_region->retrieve();
}
else {
	$wo = session::get('wo_region');
	$wo->procesa_event();
	
}
?>