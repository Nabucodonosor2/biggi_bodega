<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$cod_tipo_envio = $_REQUEST['cod_tipo_envio'];

$temp = new Template_appl('dlg_print_envio_softland.htm');
$radio = new edit_radio_button('R_COMPROBANTE', 'S', 'N', 'Comprobantes', 'envio');	
$htm = $radio->draw_entrable('S', 0);
$temp->setVar('R_COMPROBANTE', $htm);

if ($cod_tipo_envio==3 || $cod_tipo_envio==4)	// egreso o ingreso
	$temp->setVar('R_AUXILIAR', "");
else { 
	$radio = new edit_radio_button('R_AUXILIAR', 'S', 'N', 'Auxiliar clientes', 'envio');	
	$htm = $radio->draw_entrable('N', 0);
	$temp->setVar('R_AUXILIAR', $htm);
}

print $temp->toString();
?>