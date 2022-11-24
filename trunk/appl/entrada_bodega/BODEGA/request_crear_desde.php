<?php
require_once(dirname(__FILE__) . "/../../../../../commonlib/trunk/php/auto_load.php");

$temp = new Template_appl('request_crear_desde.htm');	
$entrable = true;

$sql = "select   '' COD_ORDEN_COMPRA
				,'' NRO_FACTURA_PROVEEDOR
				,'' FECHA_FACTURA_PROVEEDOR
				,'S' FA_NORMAL_PROVEEDOR
				,'N' FA_EXENTA_PROVEEDOR";	

$dw_request = new datawindow($sql);
$dw_request->add_control($control = new edit_num('COD_ORDEN_COMPRA'));
$control->set_onChange("f_get_info_seleccion();");
$dw_request->add_control(new edit_num('NRO_FACTURA_PROVEEDOR'));
$dw_request->add_control(new edit_date('FECHA_FACTURA_PROVEEDOR'));
$dw_request->add_control(new edit_radio_button('FA_NORMAL_PROVEEDOR', 'S', 'N', 'Factura Afecta', 'RADIO'));
$dw_request->add_control(new edit_radio_button('FA_EXENTA_PROVEEDOR', 'S', 'N', 'Factura Exenta', 'RADIO'));

$dw_request->insert_row();
$dw_request->habilitar($temp, $entrable);
print $temp->toString();
?>