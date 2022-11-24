<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");


if (w_output::f_viene_del_menu('banco')) {
	$wo_banco = new wo_banco();
	$wo_banco->retrieve();
}
else {
	$wo = session::get('wo_banco');
	$wo->procesa_event();
	
}
?>