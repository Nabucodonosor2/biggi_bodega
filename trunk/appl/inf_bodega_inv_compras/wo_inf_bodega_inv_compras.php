<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

if (w_output::f_viene_del_menu('inf_bodega_inv_compras')) {
	$wo = new wo_inf_bodega_inv_compras();
	$wo->retrieve();
}
else {
	$wo = session::get('wo_inf_bodega_inv_compras');
	$wo->procesa_event();
}
?>