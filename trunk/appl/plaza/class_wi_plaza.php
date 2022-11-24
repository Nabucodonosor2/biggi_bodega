<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

/*
Clase : WI_PLAZA
*/
class wi_plaza extends w_input {
	function wi_plaza($cod_item_menu) {
		parent::w_input('plaza', $cod_item_menu);
		$this->valida_llave = true;		// valida que la PK sea unica

		$sql = "select 		COD_PLAZA, 
							NOM_PLAZA														 						
				from 		PLAZA
				where 		COD_PLAZA = {KEY1}
				order by 	COD_PLAZA";
		$this->dws['dw_plaza'] = new datawindow($sql);

		// asigna los formatos				
		$this->dws['dw_plaza']->add_control(new edit_text_upper('NOM_PLAZA', 80, 100));			
		
		// asigna los mandatorys
		$this->dws['dw_plaza']->set_mandatory('COD_PLAZA', 'Código de Plaza');	
		$this->dws['dw_plaza']->set_mandatory('NOM_PLAZA', 'Nombre de Plaza');	
		
	}
	function new_record() {
		$this->dws['dw_plaza']->insert_row();
		$this->dws['dw_plaza']->add_control(new edit_num('COD_PLAZA', 12, 10));	
	}
	function load_record() {
		$cod_plaza = $this->get_item_wo($this->current_record, 'COD_PLAZA');
		$this->dws['dw_plaza']->retrieve($cod_plaza);
	}
	function get_key() {
		return $this->dws['dw_plaza']->get_item(0, 'COD_PLAZA');
	}
	
	
	function save_record($db) {
		$COD_PLAZA = $this->get_key();
		$NOM_PLAZA = $this->dws['dw_plaza']->get_item(0, 'NOM_PLAZA');	
		
		$COD_PLAZA = ($COD_PLAZA=='') ? "null" : $COD_PLAZA;		
    
		$sp = 'spu_plaza';
	    if ($this->is_new_record())
	    	$operacion = 'INSERT';
	    else
	    	$operacion = 'UPDATE';
	    
	    $param	= "'$operacion', $COD_PLAZA, '$NOM_PLAZA'"; 	
			
		if ($db->EXECUTE_SP($sp, $param)){
			if ($this->is_new_record()) {
				$COD_PLAZA = $this->get_key();
				$this->dws['dw_plaza']->set_item(0, 'COD_PLAZA', $COD_PLAZA);
			}
			return true;
		}
		return false;
				
	}
	
}
////////////////////////

?>