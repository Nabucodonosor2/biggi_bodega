<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

class wi_accion_cobranza extends w_input {
	function wi_accion_cobranza($cod_item_menu) {
		parent::w_input('accion_cobranza', $cod_item_menu);
		$sql = "SELECT	COD_ACCION_COBRANZA
						,NOM_ACCION_COBRANZA
				FROM	ACCION_COBRANZA
				WHERE COD_ACCION_COBRANZA = {KEY1}
				ORDER BY COD_ACCION_COBRANZA";
		$this->dws['dw_accion_cobranza'] = new datawindow($sql);

		// asigna los formatos
		$this->dws['dw_accion_cobranza']->add_control(new edit_text_upper('NOM_ACCION_COBRANZA', 80, 100));
				
		// asigna los mandatorys		
		$this->dws['dw_accion_cobranza']->set_mandatory('NOM_ACCION_COBRANZA', 'Accion Cobranza');				
	}
	function new_record() {
		$this->dws['dw_accion_cobranza']->insert_row();
	}
	function load_record() {
		$cod_accion_cobranza = $this->get_item_wo($this->current_record, 'COD_ACCION_COBRANZA');
		$this->dws['dw_accion_cobranza']->retrieve($cod_accion_cobranza);
	}
	function get_key() {
		return $this->dws['dw_accion_cobranza']->get_item(0, 'COD_ACCION_COBRANZA');
	}
	
	function save_record($db) {
		$COD_ACCION_COBRANZA 	= $this->get_key();
		$NOM_ACCION_COBRANZA	= $this->dws['dw_accion_cobranza']->get_item(0, 'NOM_ACCION_COBRANZA');	
		
		$COD_ACCION_COBRANZA = ($COD_ACCION_COBRANZA=='') ? "null" : $COD_ACCION_COBRANZA;		
    
		$sp = 'spu_accion_cobranza';
	    if ($this->is_new_record())
	    	$operacion = 'INSERT';
	    else
	    	$operacion = 'UPDATE';
	    
	    $param	= "'$operacion', $COD_ACCION_COBRANZA, '$NOM_ACCION_COBRANZA'"; 
	    
		if ($db->EXECUTE_SP($sp, $param)){
			if ($this->is_new_record()) {
				$cod_accion_cobranza = $db->GET_IDENTITY();
				$this->dws['dw_accion_cobranza']->set_item(0, 'COD_ACCION_COBRANZA', $cod_accion_cobranza);
			}
			return true;
		}
		return false;		
				
	}	
	
}
?>