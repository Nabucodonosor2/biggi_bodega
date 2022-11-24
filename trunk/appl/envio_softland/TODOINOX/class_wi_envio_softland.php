<?php
//////////////////////////////////////////////////////////////////
/////////////////////////// TODOINOX ///////////////////////////
//////////////////////////////////////////////////////////////////

require_once(dirname(__FILE__) . "/../../../../../commonlib/trunk/php/auto_load.php");

class wi_envio_softland extends wi_envio_softland_base{
	function wi_envio_softland($cod_item_menu) {
		parent::wi_envio_softland_base($cod_item_menu);
	}
	function send_venta_iva($handle, $tipo_doc, $i, $cuenta, $monto, $centro_costo) {
		// para TODOINOX no tiene CENTRO COSTO
		parent::send_venta_iva($handle, $tipo_doc, $i, $cuenta, $monto, '');
	}
}
?>