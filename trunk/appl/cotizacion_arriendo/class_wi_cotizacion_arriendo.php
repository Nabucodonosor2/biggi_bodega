<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../common_appl/class_w_cot_nv.php");
require_once(dirname(__FILE__)."/../empresa/class_dw_help_empresa.php");

class wi_cotizacion_arriendo extends w_input {

	function wi_cotizacion_arriendo($cod_item_menu) {
		parent::w_input('cotizacion_arriendo', $cod_item_menu);

		
		
		$sql = "SELECT COD_COTIZACION
					, COD_USUARIO
					, REFERENCIA
				FROM COTIZACION
				WHERE cod_cotizacion={KEY1}";
		
		$this->dws['dw_arriendo_cotizacion'] = new datawindow($sql);
		$this->dws['dw_arriendo_cotizacion']->add_control(new static_text('COD_COTIZACION'));
		$this->dws['dw_arriendo_cotizacion']->add_control(new static_text('COD_USUARIO'));
		$this->dws['dw_arriendo_cotizacion']->add_control(new static_text('REFERENCIA'));
		
		
		
	}

	function load_record() {
		$COD_COTIZACION = $this->get_item_wo($this->current_record, 'COD_COTIZACION');
		$this->dws['dw_arriendo_cotizacion']->retrieve($COD_COTIZACION);
	}

	function get_key() {
		return $this->dws['dw_arriendo_cotizacion']->get_item(0, 'COD_COTIZACION');
	}

}
?>