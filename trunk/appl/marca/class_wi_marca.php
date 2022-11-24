<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

/*
Clase : WI_MARCA
*/
class wi_marca extends w_input {
	function wi_marca($cod_item_menu) {
		parent::w_input('marca', $cod_item_menu);

		$sql = "select COD_MARCA, 
						NOM_MARCA,
						ORDEN														 						
						from MARCA
						where COD_MARCA = {KEY1}
						order by ORDEN";
		$this->dws['dw_marca'] = new datawindow($sql);

		// asigna los formatos
		$this->dws['dw_marca']->add_control(new edit_text_upper('NOM_MARCA', 80, 100));	
		$this->dws['dw_marca']->add_control(new edit_num('ORDEN',12,10));
		
		// asigna los mandatorys		
		$this->dws['dw_marca']->set_mandatory('NOM_MARCA', 'Marca');				
		$this->dws['dw_marca']->set_mandatory('ORDEN', 'Orden');
	}
	function new_record() {
		$this->dws['dw_marca']->insert_row();
	}
	function load_record() {
		$cod_marca = $this->get_item_wo($this->current_record, 'COD_MARCA');
		$this->dws['dw_marca']->retrieve($cod_marca);
	}
	function get_key() {
		return $this->dws['dw_marca']->get_item(0, 'COD_MARCA');
	}
	
	function save_record($db) {
		$COD_MARCA = $this->get_key();
		$NOM_MARCA = $this->dws['dw_marca']->get_item(0, 'NOM_MARCA');		
		$ORDEN = $this->dws['dw_marca']->get_item(0, 'ORDEN');
		
		$COD_MARCA = ($COD_MARCA=='') ? "null" : $COD_MARCA;		
    
		$sp = 'spu_marca ';
	    if ($this->is_new_record())
	    	$operacion = 'INSERT';
	    else
	    	$operacion = 'UPDATE';
	    
	    $param	= "'$operacion', $COD_MARCA, '$NOM_MARCA', $ORDEN"; 
		
			
		if ($db->EXECUTE_SP($sp, $param)){
			if ($this->is_new_record()) {
				$cod_marca = $db->GET_IDENTITY();
				$this->dws['dw_marca']->set_item(0, 'COD_MARCA', $cod_marca);
			}
			return true;
		}
		return false;		
				
	}
	
}
////////////////////////

?>