<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

/*
Clase : WI_COMUNA
*/
class wi_comuna extends w_input {
	function wi_comuna($cod_item_menu) {
		parent::w_input('comuna', $cod_item_menu);
		$this->valida_llave = true;		// valida que la PK sea unica

		$sql = "select COD_COMUNA,
						COD_CIUDAD,
						NOM_COMUNA																				 						
						from COMUNA
						where COD_COMUNA = {KEY1}";	
											
		$sql_ciudad = "select COD_CIUDAD,
						NOM_CIUDAD																				 						
						from CIUDAD";	
											
	
		$this->dws['dw_comuna'] = new datawindow($sql);
		
		// asigna los formatos	
		$this->dws['dw_comuna']->add_control(new drop_down_dw('COD_CIUDAD',$sql_ciudad,180));								
		$this->dws['dw_comuna']->add_control(new edit_text_upper('NOM_COMUNA', 80, 100));
		
		// asigna los mandatorys
		$this->dws['dw_comuna']->set_mandatory('NOM_COMUNA', 'Nombre de Comuna');
		$this->dws['dw_comuna']->set_mandatory('COD_CIUDAD', 'Nombre de Ciudad');
	
	}
	function new_record() {	
		$this->dws['dw_comuna']->add_control(new edit_num('COD_COMUNA'));			
		$this->dws['dw_comuna']->insert_row();
		
	}
	function load_record() {
		$cod_comuna = $this->get_item_wo($this->current_record, 'COD_COMUNA');
		$this->dws['dw_comuna']->retrieve($cod_comuna);
	}
	function get_key() {
		return $this->dws['dw_comuna']->get_item(0, 'COD_COMUNA');
	}
	
	function save_record($db) {
		$COD_COMUNA = $this->get_key();		
		$COD_CIUDAD = $this->dws['dw_comuna']->get_item(0, 'COD_CIUDAD');		
		$NOM_COMUNA = $this->dws['dw_comuna']->get_item(0, 'NOM_COMUNA');		
		
		$COD_COMUNA = ($COD_COMUNA=='') ? "null" : $COD_COMUNA;
		
		$sp = 'spu_comuna';
	    if ($this->is_new_record())
	    	$operacion = 'INSERT';
	    else
	    	$operacion = 'UPDATE';
	    
	    $param	= "'$operacion', $COD_COMUNA, $COD_CIUDAD, '$NOM_COMUNA'";    
    	
			
		if ($db->EXECUTE_SP($sp, $param)){
			if ($this->is_new_record()) {				
				$cod_comuna = $this->dws['dw_comuna']->get_item(0, 'COD_COMUNA');
				$this->dws['dw_comuna']->set_item(0, 'COD_COMUNA', $cod_comuna);			
			}
			return true;
		}
		return false;		
				
	}
	
}

?>