<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

if (w_output::f_viene_del_menu('tipo_gas')) {
	$wo_tipo_gas = new wo_tipo_gas();
	$wo_tipo_gas->retrieve();
}
else {
	$wo = session::get('wo_tipo_gas');
	$wo->procesa_event();
}
?>