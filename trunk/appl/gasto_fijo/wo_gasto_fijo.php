<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

if (w_output::f_viene_del_menu('gasto_fijo')) {
	$wo_gasto_fijo = new wo_gasto_fijo();
  	$wo_gasto_fijo->retrieve();
} 
else {
	$wo = session::get('wo_gasto_fijo');
	$wo->procesa_event();
}
?>