<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
$temp = new Template_appl('request_crear_desde.htm');	
$entrable = true;

	$sql = "select	'' SELECCION_SOLICITUD
					,'' SELECCION_COTIZACION 
					,'' NRO_COTIZACION";

	
$dw_coti = new datawindow($sql);

$dw_coti->add_control(new edit_radio_button('SELECCION_SOLICITUD', 'SOLICITUD', 'SOLICITUD', 'Solicitud de Cotización', 'SELECCION_OPCION'));
//$control->set_onChange("checked_radio_button(this);");
$dw_coti->add_control(new edit_radio_button('SELECCION_COTIZACION', 'COTIZACION','COTIZACION', 'Cotización', 'SELECCION_OPCION'));
//$control->set_onChange("checked_radio_button(this);");
$dw_coti->add_control(new edit_text('NRO_COTIZACION',20,20));
// $control->set_onChange("f_get_info_seleccion();"); (Ejemplo tomado de ingreso en helen)

$dw_coti->insert_row();

$dw_coti->habilitar($temp, $entrable);
print $temp->toString();
?>