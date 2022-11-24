<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

/*
Clase : WI_TIPO_TE
*/
class wi_tipo_te extends w_input {
	function wi_tipo_te($cod_item_menu) {
		parent::w_input('tipo_te', $cod_item_menu);

		$sql = "select COD_TIPO_TE, 
						NOM_TIPO_TE,														 
						ORDEN
						from TIPO_TE
						where COD_TIPO_TE = {KEY1}";
		$this->dws['dw_tipo_te'] = new datawindow($sql);

		// asigna los formatos
		$this->dws['dw_tipo_te']->add_control(new edit_text_upper('NOM_TIPO_TE', 80, 100));		
		$this->dws['dw_tipo_te']->add_control(new edit_num('ORDEN',12,10));	
		
		// asigna los mandatorys		
		$this->dws['dw_tipo_te']->set_mandatory('NOM_TIPO_TE', 'Forma de Pago');
		$this->dws['dw_tipo_te']->set_mandatory('ORDEN', 'Orden');
		
		
	}
	function new_record() {
		$this->dws['dw_tipo_te']->insert_row();
	}
	function load_record() {
		$cod_tipo_te = $this->get_item_wo($this->current_record, 'COD_TIPO_TE');
		$this->dws['dw_tipo_te']->retrieve($cod_tipo_te);
	}
	function get_key() {
		return $this->dws['dw_tipo_te']->get_item(0, 'COD_TIPO_TE');
	}
	
	function save_record($db) {
		$COD_TIPO_TE = $this->get_key();
		$NOM_TIPO_TE = $this->dws['dw_tipo_te']->get_item(0, 'NOM_TIPO_TE');
		$ORDEN = $this->dws['dw_tipo_te']->get_item(0, 'ORDEN');

		$COD_TIPO_TE = ($COD_TIPO_TE=='') ? "null" : $COD_TIPO_TE;		
    
		$sp = 'spu_tipo_te';
	    if ($this->is_new_record())
	    	$operacion = 'INSERT';
	    else
	    	$operacion = 'UPDATE';
	    
	    $param	= "'$operacion', $COD_TIPO_TE, '$NOM_TIPO_TE', $ORDEN";    
		
		if ($db->EXECUTE_SP($sp, $param)){
			if ($this->is_new_record()) {
				$cod_tipo_te = $db->GET_IDENTITY();
				$this->dws['dw_tipo_te']->set_item(0, 'COD_TIPO_TE', $cod_tipo_te);
			}
			return true;
		}
		return false;		
				
	}
	
}
////////////////////////

?>