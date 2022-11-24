<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");


if (w_output::f_viene_del_menu('mod_arriendo')) {
	$wo_mod_arriendo = new wo_mod_arriendo();
	$wo_mod_arriendo->retrieve();
}
else {
	$wo = session::get('wo_mod_arriendo');
	$wo->procesa_event();
}
?>
