<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

if (w_output::f_viene_del_menu('inf_por_despachar')) {
	$wo = new wo_inf_por_despachar();
	$wo->retrieve();
}
else {
	$wo = session::get('wo_inf_por_despachar');
	$wo->procesa_event();
}
?>