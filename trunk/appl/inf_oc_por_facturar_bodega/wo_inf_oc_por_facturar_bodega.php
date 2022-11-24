<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

if (w_output::f_viene_del_menu('inf_oc_por_facturar_bodega')) 
{
	$wo = new wo_inf_oc_por_facturar_bodega();
	$wo->retrieve();
}
else 
{
	$wo = session::get('wo_inf_oc_por_facturar_bodega');
	$wo->procesa_event();
}
?>