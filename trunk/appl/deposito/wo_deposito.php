<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

if (w_output::f_viene_del_menu('deposito')) {
	$wo = new wo_deposito();
	$wo->retrieve();
}
else {
	$wo = session::get('wo_deposito');
	$wo->procesa_event();	
}
?>