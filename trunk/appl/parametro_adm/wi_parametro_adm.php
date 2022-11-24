<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");


if (isset($_REQUEST['cod_item_menu'])) {
	$w = new wi_parametro_adm();
} else {
	$w = session::get('wi_parametro_adm');
	$w->procesa_event();
}
?>