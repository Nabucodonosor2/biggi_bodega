<?php
require_once(dirname(__FILE__)."/../../../../../commonlib/trunk/php/auto_load.php");

if (isset($_POST['b_ok'])) {
	$origen		= $_POST['R_ORIGEN'];
	
	session::set("inf_oc_por_facturar_bodega.ORIGEN", $origen);
	
	$url = "../../../../../commonlib/trunk/php/mantenedor.php?modulo=inf_oc_por_facturar_bodega&cod_item_menu=4097";
	header ('Location:'.$url);
}
else if (isset($_POST['b_cancel'])) {
	base::presentacion();
}
else {
	$temp = new Template_appl('inf_oc_por_facturar_bodega.htm');	
	
	// make_menu
	$menu = session::get('menu_appl');
	$menu->draw($temp);
	
	$sql =   "SELECT '' NADA";
	$dw_param = new datawindow($sql);
	// draw
	$dw_param->retrieve();
	$dw_param->habilitar($temp, true);
	
	print $temp->toString();
}
?>