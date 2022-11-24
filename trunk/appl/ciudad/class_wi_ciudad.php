<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");


/*
Clase : WI_CIUDAD
*/
class wi_ciudad extends w_input {
	function wi_ciudad($cod_item_menu) {
		parent::w_input('ciudad', $cod_item_menu);
		$this->valida_llave = true;		// valida que la PK sea unica

		$sql = "select COD_CIUDAD,
						COD_REGION,
						COD_PAIS, 
						NOM_CIUDAD																				 						
						from CIUDAD
						where COD_CIUDAD = {KEY1}";	
						
		$sql_region = "select COD_REGION,
						NOM_REGION																				 						
						from REGION
						order by COD_REGION";	
						
		$sql_pais = "select COD_PAIS,
						NOM_PAIS																				 						
						from PAIS
						order by NOM_PAIS";						
	
	
		$this->dws['dw_ciudad'] = new datawindow($sql);
		
		// asigna los formatos						
		$this->dws['dw_ciudad']->add_control(new drop_down_dw('COD_REGION',$sql_region, 280));								
		$this->dws['dw_ciudad']->add_control(new drop_down_dw('COD_PAIS',$sql_pais, 280));						
		$this->dws['dw_ciudad']->add_control(new edit_text_upper('NOM_CIUDAD', 80, 100));
		
		// asigna los mandatorys
		
		//$this->dws['dw_ciudad']->set_mandatory('COD_REGION', 'Nombre de la Región');
		$this->dws['dw_ciudad']->set_mandatory('COD_PAIS', 'Nombre del País');
		$this->dws['dw_ciudad']->set_mandatory('NOM_CIUDAD', 'Nombre de la Ciudad');		
	}	
	function new_record() {	
		$this->dws['dw_ciudad']->add_control(new edit_num('COD_CIUDAD',5,3));			
		$this->dws['dw_ciudad']->insert_row();
		$this->dws['dw_ciudad']->set_item(0, 'COD_PAIS', 56);	// por defecto CHILE		
	}
	function load_record() {
		$cod_ciudad = $this->get_item_wo($this->current_record, 'COD_CIUDAD');
		$this->dws['dw_ciudad']->retrieve($cod_ciudad);
	}
	function get_key() {
		return $this->dws['dw_ciudad']->get_item(0, 'COD_CIUDAD');
	}	
	function save_record($db) {
		$COD_CIUDAD = $this->get_key();
		$COD_REGION = $this->dws['dw_ciudad']->get_item(0, 'COD_REGION');		
		$COD_PAIS = $this->dws['dw_ciudad']->get_item(0, 'COD_PAIS');
		$NOM_CIUDAD = $this->dws['dw_ciudad']->get_item(0, 'NOM_CIUDAD');
		
		$COD_CIUDAD = ($COD_CIUDAD=='') ? "null" : $COD_CIUDAD;
		$COD_REGION = ($COD_REGION=='') ? "null" : $COD_REGION;		
		
		$sp = 'spu_ciudad';
	    if ($this->is_new_record())
	    	$operacion = 'INSERT';
	    else
	    	$operacion = 'UPDATE';
	    
	    $param	= "'$operacion', $COD_CIUDAD, $COD_REGION, $COD_PAIS, '$NOM_CIUDAD'";	    			
			
		if ($db->EXECUTE_SP($sp, $param)){
			if ($this->is_new_record()) {				
				$cod_ciudad = $this->dws['dw_ciudad']->get_item(0, 'COD_CIUDAD');
				$this->dws['dw_ciudad']->set_item(0, 'COD_CIUDAD', $cod_ciudad);			
			}
			return true;
		}
		return false;		
				
	}
}
////////////////////////

?>