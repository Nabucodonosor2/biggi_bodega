<?php
require_once(dirname(__FILE__)."/../../../../../commonlib/trunk/php/auto_load.php");

class wi_ingreso_pago extends wi_ingreso_pago_base{
	function wi_ingreso_pago($cod_item_menu){
		parent::wi_ingreso_pago_base($cod_item_menu); 
   	}
	function new_record() {
		parent::new_record();
		$this->dws['dw_ingreso_pago']->set_item(0, 'COD_PROYECTO_INGRESO', 2);	//DEPOSITOS COMERCIAL
	}
}
?>