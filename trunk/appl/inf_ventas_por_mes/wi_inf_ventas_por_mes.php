<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

if (w_input::f_viene_del_output('inf_ventas_por_mes')) {
	$wi = new wi_inf_ventas_por_mes($_REQUEST['cod_item_menu']);
	$rec_no = $_REQUEST['rec_no'];
	$wi->goto_record($rec_no);
}
else {
	$wi = session::get('wi_inf_ventas_por_mes');
	$wi->procesa_event();
}
?>