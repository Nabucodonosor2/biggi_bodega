<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

if (w_output::f_viene_del_menu('grupo')) {
	$wo_grupo = new wo_grupo();
	$wo_grupo->retrieve();
}
else {
	$wo = session::get('wo_grupo');
	$wo->procesa_event();
	
}
?>