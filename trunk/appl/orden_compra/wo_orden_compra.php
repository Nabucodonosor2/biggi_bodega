<?php
include ("class_wo_orden_compra.php");

if (w_output::f_viene_del_menu('orden_compra')) {
	$wo_orden_compra = new wo_orden_compra();
  	$wo_orden_compra->retrieve();
} 
else {
	$wo = session::get('wo_orden_compra');
	$wo->procesa_event();
}
?>