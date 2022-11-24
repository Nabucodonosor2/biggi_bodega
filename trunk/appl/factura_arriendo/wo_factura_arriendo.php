<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

if (w_output::f_viene_del_menu('factura_arriendo')) {
	$wo_factura = new wo_factura_arriendo();
	$wo_factura->retrieve();
} 
else {
  $wo = session::get('wo_factura_arriendo');
  $wo->procesa_event();
}
?>