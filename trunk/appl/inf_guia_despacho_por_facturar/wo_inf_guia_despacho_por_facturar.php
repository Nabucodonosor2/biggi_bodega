<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

if (w_output::f_viene_del_menu('inf_guia_despacho_por_facturar')) {
	$wo = new wo_inf_guia_despacho_por_facturar();
	$wo->retrieve();
}
else {
	$wo = session::get('wo_inf_guia_despacho_por_facturar');
	$wo->procesa_event();
}
?>