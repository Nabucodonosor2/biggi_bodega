<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

if (w_output::f_viene_del_menu('perfil')) {
	$wo_perfil = new wo_perfil();
	$wo_perfil->retrieve();
}
else {
	$wo = session::get('wo_perfil');
	$wo->procesa_event();
	
}
?>