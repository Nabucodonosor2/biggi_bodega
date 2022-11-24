<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

if (w_output::f_viene_del_menu('usuario')) 
{
	$wo_usuario = new wo_usuario();
	$wo_usuario->retrieve();
	
}
else {
	$wo = session::get('wo_usuario');
	$wo->procesa_event();
	
}
?>