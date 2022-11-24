<?php
//////////////////////////////////////////////////////////////////
/////////////////////////// BODEGA ///////////////////////////
//////////////////////////////////////////////////////////////////

require_once(dirname(__FILE__) . "/../../../../../commonlib/trunk/php/auto_load.php");

class wi_envio_softland extends wi_envio_softland_base{
	function wi_envio_softland($cod_item_menu) {
		parent::wi_envio_softland_base($cod_item_menu);
		$this->cod_cuenta_otro_ingreso = 1;	//para BODEGA (falta definir ***
		$this->cod_cuenta_otro_gasto = 1;	//para BODEGA (falta definir ***
		$this->cuenta_por_pagar_boleta = 2112046;	//para BODEGA 
		$this->cc_otro_ingreso = '""';				//para BODEGA 
	}
	function send_venta_iva($handle, $tipo_doc, $i, $cuenta, $monto, $centro_costo) {
		// para BODEGA no tiene CENTRO COSTO
		parent::send_venta_iva($handle, $tipo_doc, $i, $cuenta, $monto, '');
	}
}
?>