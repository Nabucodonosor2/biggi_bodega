<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

class wi_accion_bitacora extends w_input {
	function wi_accion_bitacora($cod_item_menu) {
		parent::w_input('accion_bitacora', $cod_item_menu);
		$sql = "select COD_ACCION_BITACORA, 
						NOM_ACCION_BITACORA,
						ORDEN
						from ACCION_BITACORA
						where COD_ACCION_BITACORA = {KEY1}
						order by ORDEN";
		$this->dws['dw_accion_bitacora'] = new datawindow($sql);

		// asigna los formatos
		$this->dws['dw_accion_bitacora']->add_control(new edit_text_upper('NOM_ACCION_BITACORA', 80, 100));
		$this->dws['dw_accion_bitacora']->add_control(new edit_num('ORDEN',12,10));		
		
		// asigna los mandatorys
		$this->dws['dw_accion_bitacora']->set_mandatory('NOM_ACCION_BITACORA', 'Descripción');
		$this->dws['dw_accion_bitacora']->set_mandatory('ORDEN', 'Orden');
	}
	function new_record() {
		$this->dws['dw_accion_bitacora']->insert_row();
	}
	function load_record() {
		$cod_accion_bitacora = $this->get_item_wo($this->current_record, 'COD_ACCION_BITACORA');
		$this->dws['dw_accion_bitacora']->retrieve($cod_accion_bitacora);
	}
	function get_key() {
		return $this->dws['dw_accion_bitacora']->get_item(0, 'COD_ACCION_BITACORA');
	}
	function save_record($db) {
		$COD_ACCION_BITACORA 	= $this->get_key();
		$NOM_ACCION_BITACORA 	= $this->dws['dw_accion_bitacora']->get_item(0, 'NOM_ACCION_BITACORA');
		$ORDEN 					= $this->dws['dw_accion_bitacora']->get_item(0, 'ORDEN');

		$COD_ACCION_BITACORA = ($COD_ACCION_BITACORA=='') ? "null" : $COD_ACCION_BITACORA;		
    
		$sp = 'spu_accion_bitacora';
	    if ($this->is_new_record())
	    	$operacion = 'INSERT';
	    else
	    	$operacion = 'UPDATE';
	    
	    $param	= "'$operacion', $COD_ACCION_BITACORA, '$NOM_ACCION_BITACORA', $ORDEN";
		if ($db->EXECUTE_SP($sp, $param)){
			if ($this->is_new_record()) {
				$cod_accion_bitacora = $db->GET_IDENTITY();
				$this->dws['dw_accion_bitacora']->set_item(0, 'COD_ACCION_BITACORA', $cod_accion_bitacora);
			}		
			return true;	
		}
		return false;		
	}		
}
?>