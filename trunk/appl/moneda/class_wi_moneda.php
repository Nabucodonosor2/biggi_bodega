<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

/*
Clase : WI_MONEDA
*/
class wi_moneda extends w_input {
	function wi_moneda($cod_item_menu) {
		parent::w_input('moneda', $cod_item_menu);

		$this->b_delete_visible = false;
		$this->b_save_visible = false;
		$this->b_no_save_visible = false;
		$this->b_modify_visible = false;
		
		$sql = "select COD_MONEDA, 
						NOM_MONEDA,
						SIMBOLO,									 
						ORDEN
						from MONEDA
						where COD_MONEDA = {KEY1}";
		$this->dws['dw_moneda'] = new datawindow($sql);

		// asigna los formatos
		$this->dws['dw_moneda']->add_control(new edit_text_upper('NOM_MONEDA', 80, 100));		
		$this->dws['dw_moneda']->add_control(new edit_text_upper('SIMBOLO', 80, 100));				
		$this->dws['dw_moneda']->add_control(new edit_num('ORDEN',12,10));	
		
		// asigna los mandatorys		
		$this->dws['dw_moneda']->set_mandatory('NOM_MONEDA', 'Nombre');
		$this->dws['dw_moneda']->set_mandatory('SIMBOLO', 'Símbolo');
		$this->dws['dw_moneda']->set_mandatory('ORDEN', 'Orden');
		
		
	}
	function new_record() {
		$this->dws['dw_moneda']->insert_row();
	}
	function load_record() {
		$cod_moneda = $this->get_item_wo($this->current_record, 'COD_MONEDA');
		$this->dws['dw_moneda']->retrieve($cod_moneda);
	}
	function get_key() {
		return $this->dws['dw_moneda']->get_item(0, 'COD_MONEDA');
	}
	
	function save_record($db) {
		$COD_MONEDA = $this->get_key();
		$NOM_MONEDA = $this->dws['dw_moneda']->get_item(0, 'NOM_MONEDA');
		$SIMBOLO = $this->dws['dw_moneda']->get_item(0, 'SIMBOLO');
		$ORDEN = $this->dws['dw_moneda']->get_item(0, 'ORDEN');

		$COD_MONEDA = ($COD_MONEDA=='') ? "null" : $COD_MONEDA;		
    
		$sp = 'spu_moneda';
	    if ($this->is_new_record())
	    	$operacion = 'INSERT';
	    else
	    	$operacion = 'UPDATE';
	    
	    $param	= "'$operacion', $COD_MONEDA, '$NOM_MONEDA','$SIMBOLO', $ORDEN"; 
		
		if ($db->EXECUTE_SP($sp, $param)){
			if ($this->is_new_record()) {
				$cod_moneda = $db->GET_IDENTITY();
				$this->dws['dw_moneda']->set_item(0, 'COD_MONEDA', $cod_moneda);
			}
			return true;
		}
		return false;		
				
	}
	
}
////////////////////////

?>