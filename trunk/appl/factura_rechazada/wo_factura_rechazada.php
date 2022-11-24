<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

if(w_output::f_viene_del_menu('factura_rechazada')) {
	$wo_factura_rechazada = new wo_factura_rechazada();
	$wo_factura_rechazada->retrieve();
}else{
  $wo = session::get('wo_factura_rechazada');
  $wo->procesa_event();
}
?>