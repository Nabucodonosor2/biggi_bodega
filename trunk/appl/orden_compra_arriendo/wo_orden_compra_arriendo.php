<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

if (w_output::f_viene_del_menu('orden_compra_arriendo')) {
	$wo = new wo_orden_compra_arriendo();
  	$wo->retrieve();
} 
else {
	$wo = session::get('wo_orden_compra_arriendo');
	$wo->procesa_event();
}
?>