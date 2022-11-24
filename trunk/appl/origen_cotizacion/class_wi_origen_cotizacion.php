<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

class wi_origen_cotizacion extends w_input {
	function wi_origen_cotizacion($cod_item_menu) {
		parent::w_input('origen_cotizacion', $cod_item_menu);
		$sql = "select COD_ORIGEN_COTIZACION, 
						NOM_ORIGEN_COTIZACION,					
						ORDEN
						from ORIGEN_COTIZACION
						where COD_ORIGEN_COTIZACION = {KEY1}
						order by ORDEN";
		$this->dws['dw_origen_cotizacion'] = new datawindow($sql);

		// asigna los formatos
		$this->dws['dw_origen_cotizacion']->add_control(new edit_text_upper('NOM_ORIGEN_COTIZACION', 80, 100));
		$this->dws['dw_origen_cotizacion']->add_control(new edit_num('ORDEN',12,10));			
		
		// asigna los mandatorys		
		$this->dws['dw_origen_cotizacion']->set_mandatory('NOM_ORIGEN_COTIZACION', 'Descripción Origen');
		$this->dws['dw_origen_cotizacion']->set_mandatory('ORDEN', 'Orden');		
		
	}
	function new_record() {
		$this->dws['dw_origen_cotizacion']->insert_row();
	}
	function load_record() {
		$cod_origen_cotizacion = $this->get_item_wo($this->current_record, 'COD_ORIGEN_COTIZACION');
		$this->dws['dw_origen_cotizacion']->retrieve($cod_origen_cotizacion);

	}
	function get_key() {
		return $this->dws['dw_origen_cotizacion']->get_item(0, 'COD_ORIGEN_COTIZACION');
	}
	
	function save_record($db) {
		$COD_ORIGEN_COTIZACION 	= $this->get_key();
		$NOM_ORIGEN_COTIZACION	= $this->dws['dw_origen_cotizacion']->get_item(0, 'NOM_ORIGEN_COTIZACION');	
		$ORDEN 					= $this->dws['dw_origen_cotizacion']->get_item(0, 'ORDEN');
		
		$COD_ORIGEN_COTIZACION = ($COD_ORIGEN_COTIZACION=='') ? "null" : $COD_ORIGEN_COTIZACION;		
    
		$sp = 'spu_origen_cotizacion';
	    if ($this->is_new_record())
	    	$operacion = 'INSERT';
	    else
	    	$operacion = 'UPDATE';
	    
	    $param	= "'$operacion', $COD_ORIGEN_COTIZACION, '$NOM_ORIGEN_COTIZACION', $ORDEN";
     
		if ($db->EXECUTE_SP($sp, $param)){
			if ($this->is_new_record()) {
				$cod_origen_cotizacion = $db->GET_IDENTITY();
				$this->dws['dw_origen_cotizacion']->set_item(0, 'COD_ORIGEN_COTIZACION', $cod_origen_cotizacion);
			}
			return true;
		}
		return false;		
				
	}	
	  
}
?>