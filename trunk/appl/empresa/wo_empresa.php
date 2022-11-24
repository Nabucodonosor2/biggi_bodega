<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

if (w_output::f_viene_del_menu('empresa')) {

	$wo_empresa = new wo_empresa();
	$wo_empresa->retrieve();
} 
else {
	$wo = session::get('wo_empresa');
	$wo->procesa_event();
}
?>