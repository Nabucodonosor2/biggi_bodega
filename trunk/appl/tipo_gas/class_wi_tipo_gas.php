<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

class wi_tipo_gas extends w_input {
	function wi_tipo_gas($cod_item_menu) {
		parent::w_input('tipo_gas', $cod_item_menu);
		$sql = "select COD_TIPO_GAS, 
						NOM_TIPO_GAS,					
						ORDEN
						from TIPO_GAS
						where COD_TIPO_GAS = {KEY1}
						order by ORDEN";
		$this->dws['dw_tipo_gas'] = new datawindow($sql);

		// asigna los formatos
		$this->dws['dw_tipo_gas']->add_control(new edit_text_upper('NOM_TIPO_GAS', 80, 100));
		$this->dws['dw_tipo_gas']->add_control(new edit_num('ORDEN',12,10));
		
		// asigna los mandatorys
		$this->dws['dw_tipo_gas']->set_mandatory('NOM_TIPO_GAS', 'Tipo Gas');
		$this->dws['dw_tipo_gas']->set_mandatory('ORDEN', 'Orden');
				
	}
	function new_record() {
		$this->dws['dw_tipo_gas']->insert_row();
	}
	function load_record() {
		$cod_tipo_gas = $this->get_item_wo($this->current_record, 'COD_TIPO_GAS');
		$this->dws['dw_tipo_gas']->retrieve($cod_tipo_gas);

	}
	function get_key() {
		return $this->dws['dw_tipo_gas']->get_item(0, 'COD_TIPO_GAS');
	}
		
	function save_record($db) {
		$COD_TIPO_GAS 	= $this->get_key();
		$NOM_TIPO_GAS	= $this->dws['dw_tipo_gas']->get_item(0, 'NOM_TIPO_GAS');	
		$ORDEN 					= $this->dws['dw_tipo_gas']->get_item(0, 'ORDEN');
		
		$COD_TIPO_GAS = ($COD_TIPO_GAS=='') ? "null" : $COD_TIPO_GAS;		
    
		$sp = 'spu_tipo_gas';
	    if ($this->is_new_record())
	    	$operacion = 'INSERT';
	    else
	    	$operacion = 'UPDATE';
	    
	    $param	= "'$operacion', $COD_TIPO_GAS, '$NOM_TIPO_GAS', $ORDEN"; 
		
		if ($db->EXECUTE_SP($sp, $param)){
			if ($this->is_new_record()) {
				$cod_tipo_gas = $db->GET_IDENTITY();
				$this->dws['dw_tipo_gas']->set_item(0, 'COD_TIPO_GAS', $cod_tipo_gas);
			}
			return true;
		}
		return false;		
				
	}	
	
}
?>