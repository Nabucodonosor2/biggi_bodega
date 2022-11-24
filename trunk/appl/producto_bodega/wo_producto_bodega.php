<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

if(w_output::f_viene_del_menu('producto_bodega')){
	$wo_producto_bodega = new wo_producto_bodega();
	$wo_producto_bodega->retrieve();
}else{
	$wo = session::get('wo_producto_bodega');
	$wo->procesa_event();
}
?>