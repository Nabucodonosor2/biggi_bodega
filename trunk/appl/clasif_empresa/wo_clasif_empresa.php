<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

if (w_output::f_viene_del_menu('clasif_empresa')) {
	$wo_clasif_empresa = new wo_clasif_empresa();	
	$wo_clasif_empresa->retrieve();	
}
else {
	$wo = session::get('wo_clasif_empresa');
	$wo->procesa_event();	
}


?>