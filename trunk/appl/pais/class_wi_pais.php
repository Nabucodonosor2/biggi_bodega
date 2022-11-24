<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

/*
Clase : WI_PAIS
*/
class wi_pais extends w_input {
	function wi_pais($cod_item_menu) {
		parent::w_input('pais', $cod_item_menu);
		$this->valida_llave = true;		// valida que la PK sea unica

		$sql = "select COD_PAIS, 
						NOM_PAIS														 						
						from PAIS
						where COD_PAIS = {KEY1}";
		$this->dws['dw_pais'] = new datawindow($sql);

		// asigna los formatos		
		$this->dws['dw_pais']->add_control(new edit_text_upper('NOM_PAIS', 80, 100));			
		
		// asigna los mandatorys		
		$this->dws['dw_pais']->set_mandatory('COD_PAIS', 'Código del País');
		$this->dws['dw_pais']->set_mandatory('NOM_PAIS', 'Nombre del País');
		

	}
	function new_record() {	
		$this->dws['dw_pais']->add_control(new edit_num('COD_PAIS',4,4));		
		$this->dws['dw_pais']->insert_row();
	}
	function load_record() {
		$cod_pais = $this->get_item_wo($this->current_record, 'COD_PAIS');
		$this->dws['dw_pais']->retrieve($cod_pais);
	}
	function get_key() {
		return $this->dws['dw_pais']->get_item(0, 'COD_PAIS');
	}
	
	function save_record($db) {
		$COD_PAIS = $this->get_key();
		$NOM_PAIS = $this->dws['dw_pais']->get_item(0, 'NOM_PAIS');		

		$COD_PAIS = ($COD_PAIS=='') ? "null" : $COD_PAIS;		
    
		$sp = 'spu_pais';
	    if ($this->is_new_record())
	    	$operacion = 'INSERT';
	    else
	    	$operacion = 'UPDATE';
	    
	    $param	= "'$operacion', $COD_PAIS, '$NOM_PAIS'"; 
		
			
		if ($db->EXECUTE_SP($sp, $param)){
			if ($this->is_new_record()) {				
				$cod_pais = $this->dws['dw_pais']->get_item(0, 'COD_PAIS');
				$this->dws['dw_pais']->set_item(0, 'COD_PAIS', $cod_pais);
			}
			return true;
		}
		return false;		
				
	}
	
}


?>