<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

/*
Clase : WI_FORMA_PAGO
*/
class wi_forma_pago extends w_input {
	function wi_forma_pago($cod_item_menu) {
		parent::w_input('forma_pago', $cod_item_menu);

		$sql = "select COD_FORMA_PAGO, 
						NOM_FORMA_PAGO,														 
						ORDEN, 
						CANTIDAD_DOC
						from FORMA_PAGO
						where COD_FORMA_PAGO = {KEY1}";
		$this->dws['dw_forma_pago'] = new datawindow($sql);

		// asigna los formatos
		$this->dws['dw_forma_pago']->add_control(new edit_text_upper('NOM_FORMA_PAGO', 80, 100));		
		$this->dws['dw_forma_pago']->add_control(new edit_num('ORDEN',12,10));	
		$this->dws['dw_forma_pago']->add_control(new edit_num('CANTIDAD_DOC',12,10));	
	
			
		// asigna los mandatorys		
		$this->dws['dw_forma_pago']->set_mandatory('NOM_FORMA_PAGO', 'Forma de Pago');
		$this->dws['dw_forma_pago']->set_mandatory('ORDEN', 'Orden');
		$this->dws['dw_forma_pago']->set_mandatory('CANTIDAD_DOC', 'Cantidad de Documentos');
		
		
	}
	function new_record() {
		$this->dws['dw_forma_pago']->insert_row();
	}
	function load_record() {
		$cod_forma_pago = $this->get_item_wo($this->current_record, 'COD_FORMA_PAGO');
		$this->dws['dw_forma_pago']->retrieve($cod_forma_pago);
		
		//en la forma de pago OTRO no se puede modificar CANTIDAD_DOC
		if ($cod_forma_pago==1)
			$this->dws['dw_forma_pago']->add_control(new static_num('CANTIDAD_DOC'));
		else
			$this->dws['dw_forma_pago']->add_control(new edit_num('CANTIDAD_DOC',12,10));	
			
	}
	function get_key() {
		return $this->dws['dw_forma_pago']->get_item(0, 'COD_FORMA_PAGO');
	}
	
	function save_record($db) {
		$COD_FORMA_PAGO = $this->get_key();
		$NOM_FORMA_PAGO = $this->dws['dw_forma_pago']->get_item(0, 'NOM_FORMA_PAGO');
		$ORDEN = $this->dws['dw_forma_pago']->get_item(0, 'ORDEN');
		$CANTIDAD_DOC = $this->dws['dw_forma_pago']->get_item(0, 'CANTIDAD_DOC');
		
		$COD_FORMA_PAGO = ($COD_FORMA_PAGO=='') ? "null" : $COD_FORMA_PAGO;		
    
		$sp = 'spu_forma_pago';
	    if ($this->is_new_record())
	    	$operacion = 'INSERT';
	    else
	    	$operacion = 'UPDATE';
	    
	    $param	= "'$operacion', $COD_FORMA_PAGO, '$NOM_FORMA_PAGO', $ORDEN, $CANTIDAD_DOC"; 
    		
		if ($db->EXECUTE_SP($sp, $param)){
			if ($this->is_new_record()) {
				$cod_forma_pago = $db->GET_IDENTITY();
				$this->dws['dw_forma_pago']->set_item(0, 'COD_FORMA_PAGO', $cod_forma_pago);
			}
			return true;
		}
		return false;		
				
	}
	
}
////////////////////////

?>