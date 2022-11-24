<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

class wi_origen_venta extends w_input {
	function wi_origen_venta($cod_item_menu) {
		parent::w_input('origen_venta', $cod_item_menu);
		$sql = "select COD_ORIGEN_VENTA, 
						NOM_ORIGEN_VENTA,					
						ORDEN
						from ORIGEN_VENTA
						where COD_ORIGEN_VENTA = {KEY1}
						order by ORDEN";
		$this->dws['dw_origen_venta'] = new datawindow($sql);

		// asigna los formatos
		$this->dws['dw_origen_venta']->add_control(new edit_text_upper('NOM_ORIGEN_VENTA', 80, 100));
		$this->dws['dw_origen_venta']->add_control(new edit_num('ORDEN',12,10));		
		
		// asigna los mandatorys		
		$this->dws['dw_origen_venta']->set_mandatory('NOM_ORIGEN_VENTA', 'Descripción Origen');
		$this->dws['dw_origen_venta']->set_mandatory('ORDEN', 'Orden');
		
		
	}
	function new_record() {
		$this->dws['dw_origen_venta']->insert_row();
	}
	function load_record() {
		$cod_origen_venta = $this->get_item_wo($this->current_record, 'COD_ORIGEN_VENTA');
		$this->dws['dw_origen_venta']->retrieve($cod_origen_venta);

	}
	function get_key() {
		return $this->dws['dw_origen_venta']->get_item(0, 'COD_ORIGEN_VENTA');
	}
	
	function save_record($db) {
		$COD_ORIGEN_VENTA 	= $this->get_key();
		$NOM_ORIGEN_VENTA	= $this->dws['dw_origen_venta']->get_item(0, 'NOM_ORIGEN_VENTA');	
		$ORDEN 					= $this->dws['dw_origen_venta']->get_item(0, 'ORDEN');
		
		$COD_ORIGEN_VENTA = ($COD_ORIGEN_VENTA=='') ? "null" : $COD_ORIGEN_VENTA;		
    
		$sp = 'spu_origen_venta';
	    if ($this->is_new_record())
	    	$operacion = 'INSERT';
	    else
	    	$operacion = 'UPDATE';
	    
	    $param	= "'$operacion', $COD_ORIGEN_VENTA, '$NOM_ORIGEN_VENTA', $ORDEN"; 
		
		if ($db->EXECUTE_SP($sp, $param)){
			if ($this->is_new_record()) {
				$cod_origen_venta = $db->GET_IDENTITY();
				$this->dws['dw_origen_venta']->set_item(0, 'COD_ORIGEN_VENTA', $cod_origen_venta);
			}
			return true;
		}
		return false;		
				
	}	
	
}
?>