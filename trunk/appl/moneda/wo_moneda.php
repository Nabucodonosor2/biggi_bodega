<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

if (w_output::f_viene_del_menu('moneda')) {
	$wo_moneda = new wo_moneda();
	
	// desabilitar botones de		
	$wo_moneda->b_add_visible  = false;
	$wo_moneda->b_find_visible = false;
	$wo_moneda->b_export_visible = false;
	
	$wo_moneda->retrieve();
}
else {
	$wo = session::get('wo_moneda');
	
	$wo->procesa_event();
	
	// desabilitar botones de		
	$wo->b_add_visible = false;
	
}
?>