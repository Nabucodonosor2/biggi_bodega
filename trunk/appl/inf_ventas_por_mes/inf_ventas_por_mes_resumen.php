<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once("class_static_num_miles.php");

if (isset($_POST['b_print'])) {
	$dw = session::get("inf_ventas_por_mes.dw");
	session::un_set("inf_ventas_por_mes.dw");
	
	$cant_nv = $dw->get_item(0, 'CANT_NV');
	$subtotal = $dw->get_item(0, 'SUBTOTAL')/1000;
	$monto_dscto = $dw->get_item(0, 'MONTO_DSCTO')/1000;
	$porc_dscto = $dw->get_item(0, 'PORC_DSCTO');
	$total_neto = $dw->get_item(0, 'TOTAL_NETO')/1000;
	$monto_dscto_corporativo = $dw->get_item(0, 'MONTO_DSCTO_CORPORATIVO')/1000;
	$porc_dscto_corporativo = $dw->get_item(0, 'PORC_DSCTO_CORPORATIVO');
	$monto_dscto_total = $dw->get_item(0, 'MONTO_DSCTO_TOTAL')/1000;
	$porc_dscto_total = $dw->get_item(0, 'PORC_DSCTO_TOTAL');
	$total_venta = $dw->get_item(0, 'TOTAL_VENTA')/1000;
	$despachado_neto = $dw->get_item(0, 'DESPACHADO_NETO')/1000;
	$cobrado_neto = $dw->get_item(0, 'COBRADO_NETO')/1000;
	$por_cobrar_neto = $dw->get_item(0, 'POR_COBRAR_NETO')/1000;
	
	$sql = "select $cant_nv						CANT_NV
					,$subtotal 					SUBTOTAL
					,$monto_dscto				MONTO_DSCTO
					,$porc_dscto				PORC_DSCTO
					,$total_neto				TOTAL_NETO
					,$monto_dscto_corporativo	MONTO_DSCTO_CORPORATIVO
					,$porc_dscto_corporativo	PORC_DSCTO_CORPORATIVO
					,$monto_dscto_total			MONTO_DSCTO_TOTAL
					,$porc_dscto_total			PORC_DSCTO_TOTAL
					,$total_venta				TOTAL_VENTA
					,$despachado_neto			DESPACHADO_NETO
					,$cobrado_neto				COBRADO_NETO
					,$por_cobrar_neto			POR_COBRAR_NETO";	
					
	$ano = session::get("inf_ventas_por_mes.ANO");
	$mes_desde = session::get("inf_ventas_por_mes.MES_DESDE");
	$mes_hasta = session::get("inf_ventas_por_mes.MES_HASTA");

	$b = new base();
	$mes_desde = $b->nom_mes($mes_desde);
	$mes_hasta = $b->nom_mes($mes_hasta);

	$labels = array();
	$labels['strANO'] = $ano;
	$labels['strMES_DESDE'] = $mes_desde;
	$labels['strMES_HASTA'] = $mes_hasta;
	$rpt = new reporte($sql, dirname(__FILE__).'/inf_ventas_por_mes_resumen.xml', $labels, "Resumen Ventas", true, true);
}
else if (isset($_POST['b_cancel'])) {
	session::un_set("inf_ventas_por_mes.dw");
	base::presentacion();
}
else {
	$temp = new Template_appl('inf_ventas_por_mes_resumen.htm');	
		
	// make_menu
	$menu = session::get('menu_appl');
	$menu->draw($temp);
	
	$ano = session::get("inf_ventas_por_mes.ANO");
	$mes_desde = session::get("inf_ventas_por_mes.MES_DESDE");
	$mes_hasta = session::get("inf_ventas_por_mes.MES_HASTA");
	
	$sql = "exec spdw_inf_ventas_mes_resumen $ano, $mes_desde, $mes_hasta";
	$dw = new datawindow($sql);
	$dw->add_control(new static_num_miles('SUBTOTAL'));
	$dw->add_control(new static_num_miles('MONTO_DSCTO'));
	$dw->add_control(new static_num('PORC_DSCTO', 1));
	$dw->add_control(new static_num_miles('TOTAL_NETO'));
	$dw->add_control(new static_num_miles('MONTO_DSCTO_CORPORATIVO'));
	$dw->add_control(new static_num('PORC_DSCTO_CORPORATIVO', 1));
	$dw->add_control(new static_num_miles('MONTO_DSCTO_TOTAL'));
	$dw->add_control(new static_num('PORC_DSCTO_TOTAL', 1));
	$dw->add_control(new static_num_miles('TOTAL_VENTA'));
	$dw->add_control(new static_num_miles('DESPACHADO_NETO'));
	$dw->add_control(new static_num_miles('COBRADO_NETO'));
	$dw->add_control(new static_num_miles('POR_COBRAR_NETO'));
	session::set("inf_ventas_por_mes.dw", $dw);
	
	// draw
	$dw->retrieve();
	$dw->habilitar($temp, false);
	
	print $temp->toString();
}
?>