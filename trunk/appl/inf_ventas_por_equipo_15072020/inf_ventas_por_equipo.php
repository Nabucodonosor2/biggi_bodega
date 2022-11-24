<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

if (isset($_POST['b_ok'])) {
	$ano = $_POST['ANO_0'];
	$mes_desde = $_POST['MES_DESDE_0'];
	$mes_hasta = $_POST['MES_HASTA_0'];
	$cod_producto = $_POST['COD_PRODUCTO_0'];
	$find_exacto = $_POST['FIND_EXACTO_0'];
	
	session::set("inf_ventas_por_equipo.ANO", $ano);
	session::set("inf_ventas_por_equipo.MES_DESDE", $mes_desde);
	session::set("inf_ventas_por_equipo.MES_HASTA", $mes_hasta);
	session::set("inf_ventas_por_equipo.COD_PRODUCTO", $cod_producto);
	session::set("inf_ventas_por_equipo.FIND_EXACTO", $find_exacto);
	
	$url = "../../../../commonlib/trunk/php/mantenedor.php?modulo=inf_ventas_por_equipo&cod_item_menu=4015";
	header ('Location:'.$url);
}
else if (isset($_POST['b_cancel'])) {
	base::presentacion();
}
else {
	$temp = new Template_appl('inf_ventas_por_equipo.htm');	
	
	// make_menu
	$menu = session::get('menu_appl');
	$menu->draw($temp);
	
	$sql = "select year(getdate()) ANO
				, month(getdate()) MES_DESDE
				, month(getdate()) MES_HASTA
				, null COD_PRODUCTO
				, 'N' FIND_EXACTO";
	$dw_param = new datawindow($sql);
	$dw_param->add_control(new edit_ano('ANO'));
	$dw_param->add_control(new edit_mes('MES_DESDE'));
	$dw_param->add_control(new edit_mes('MES_HASTA'));
	$dw_param->add_control(new edit_text_upper('COD_PRODUCTO', 20, 20));
	$dw_param->add_control(new edit_check_box('FIND_EXACTO', 'S', 'N', 'Búsqueda exacta'));
	
	// draw
	$dw_param->retrieve();
	$dw_param->habilitar($temp, true);
	
	print $temp->toString();
}
?>