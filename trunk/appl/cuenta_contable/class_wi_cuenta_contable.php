<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

/*
Clase : WI_CUENTA_CONTABLE
*/
class wi_cuenta_contable extends w_input {
	function wi_cuenta_contable($cod_item_menu) {
		parent::w_input('cuenta_contable', $cod_item_menu);

		$sql = "select COD_CUENTA_CONTABLE, 
						NOM_CUENTA_CONTABLE														 						
						from CUENTA_CONTABLE
						where COD_CUENTA_CONTABLE = {KEY1}";
		$this->dws['dw_cuenta_contable'] = new datawindow($sql);

		// asigna los formatos		
		$this->dws['dw_cuenta_contable']->add_control(new edit_text_upper('NOM_CUENTA_CONTABLE', 80, 100));
		
		// asigna los mandatorys
		$this->dws['dw_cuenta_contable']->set_mandatory('COD_CUENTA_CONTABLE', 'Código de Cuenta');
		$this->dws['dw_cuenta_contable']->set_mandatory('NOM_CUENTA_CONTABLE', 'Nombre de Cuenta');
			
		
	}
	function new_record() {	
		$this->dws['dw_cuenta_contable']->add_control(new edit_cuenta_contable('COD_CUENTA_CONTABLE', array(2,2,3)));		
		$this->dws['dw_cuenta_contable']->insert_row();
	}
	function load_record() {
		$cod_cuenta_contable = $this->get_item_wo($this->current_record, 'COD_CUENTA_CONTABLE');
		$this->dws['dw_cuenta_contable']->retrieve($cod_cuenta_contable);
	}
	function get_key() {
		return $this->dws['dw_cuenta_contable']->get_item(0, 'COD_CUENTA_CONTABLE');
	}
	
	function save_record($db) {
		$COD_CUENTA_CONTABLE = $this->get_key();
		$NOM_CUENTA_CONTABLE = $this->dws['dw_cuenta_contable']->get_item(0, 'NOM_CUENTA_CONTABLE');		
		

		$COD_CUENTA_CONTABLE = ($COD_CUENTA_CONTABLE=='') ? "null" : $COD_CUENTA_CONTABLE;		
    
		$sp = 'spu_cuenta_contable';
	    if ($this->is_new_record())
	    	$operacion = 'INSERT';
	    else
	    	$operacion = 'UPDATE';
	    
	    $param	= "'$operacion', $COD_CUENTA_CONTABLE, '$NOM_CUENTA_CONTABLE'";    
		
		if ($db->EXECUTE_SP($sp, $param)){
			if ($this->is_new_record()) {				
				$cod_cuenta_contable = $this->dws['dw_cuenta_contable']->get_item(0, 'COD_CUENTA_CONTABLE');
				$this->dws['dw_cuenta_contable']->set_item(0, 'COD_CUENTA_CONTABLE', $cod_cuenta_contable);
			}
			return true;
		}
		return false;		
				
	}
	
}

?>