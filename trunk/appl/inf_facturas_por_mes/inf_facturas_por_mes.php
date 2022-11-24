<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

if (isset($_POST['b_ok'])) {
	$tipo = $_POST['TIPO'];
	$ano = $_POST['ANO_0'];
	$mes_desde = $_POST['MES_DESDE_0'];
	$mes_hasta = $_POST['MES_HASTA_0'];
	if (isset($_POST['CHECK_FA_0']))
		$check_FA = 'S';
	else
		$check_FA = 'N';
	if (isset($_POST['CHECK_NC_0']))
		$check_NC = 'S';
	else
		$check_NC = 'N';
	
	session::set("inf_facturas_por_mes.ANO", $ano);
	session::set("inf_facturas_por_mes.MES_DESDE", $mes_desde);
	session::set("inf_facturas_por_mes.MES_HASTA", $mes_hasta);
	session::set("inf_facturas_por_mes.CHECK_FA", $check_FA);
	session::set("inf_facturas_por_mes.CHECK_NC", $check_NC);
	
	if ($tipo=='R') {
		header ('Location: inf_facturas_por_mes_resumen.php');
	}
	else {
		$url = "../../../../commonlib/trunk/php/mantenedor.php?modulo=inf_facturas_por_mes&cod_item_menu=4015";
		header ('Location:'.$url);
	}
}
else if (isset($_POST['b_cancel'])) {
	base::presentacion();
}
else {
	$temp = new Template_appl('inf_facturas_por_mes.htm');	
	
	// make_menu
	$menu = session::get('menu_appl');
	$menu->draw($temp);
	
	$sql = "select year(getdate()) ANO
				, month(getdate()) MES_DESDE
				, month(getdate()) MES_HASTA
				, 'N' RESUMEN
				, 'D' DETALLE
				, 'S' CHECK_FA
				, 'S' CHECK_NC";
	$dw_param = new datawindow($sql);
	$dw_param->add_control(new edit_ano('ANO'));
	$dw_param->add_control(new edit_mes('MES_DESDE'));
	$dw_param->add_control(new edit_mes('MES_HASTA'));
	$dw_param->add_control(new edit_radio_button('RESUMEN', 'R', 'N', 'Resumen', 'TIPO'));
	$dw_param->add_control(new edit_radio_button('DETALLE', 'D', 'N', 'Detalle', 'TIPO'));
	$dw_param->add_control(new edit_check_box('CHECK_FA', 'S', 'N', 'FA'));
	$dw_param->add_control(new edit_check_box('CHECK_NC', 'S', 'N', 'NC'));
	
	// draw
	$dw_param->retrieve();
	$dw_param->habilitar($temp, true);
	
	print $temp->toString();
}
?>