<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

if (w_output::f_viene_del_menu('inf_facturas_por_cobrar')) {
	$wo = new wo_inf_facturas_por_cobrar();
	$wo->retrieve();
}
else {
	$wo = session::get('wo_inf_facturas_por_cobrar');
	$wo->procesa_event();
}
?>