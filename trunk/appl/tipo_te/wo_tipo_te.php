<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

if (w_output::f_viene_del_menu('tipo_te')) {
	$wo_tipo_te = new wo_tipo_te();
	$wo_tipo_te->retrieve();
}
else {	
	$wo = session::get('wo_tipo_te');
	$wo->procesa_event();
	
}
?>