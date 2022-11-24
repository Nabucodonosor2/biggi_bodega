<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

if (w_output::f_viene_del_menu('tipo_electricidad')) {
	$wo_tipo_electricidad = new wo_tipo_electricidad();
	$wo_tipo_electricidad->retrieve();
}
else {
	$wo = session::get('wo_tipo_electricidad');
	$wo->procesa_event();
}
?>