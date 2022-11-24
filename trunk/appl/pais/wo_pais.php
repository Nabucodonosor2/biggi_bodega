<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

if (w_output::f_viene_del_menu('pais')) {
	$wo_pais = new wo_pais();
	$wo_pais->retrieve();
}
else {
	$wo = session::get('wo_pais');
	$wo->procesa_event();
	
}
?>