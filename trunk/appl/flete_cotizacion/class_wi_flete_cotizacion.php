<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

class wi_flete_cotizacion extends w_input {
	function wi_flete_cotizacion($cod_item_menu) {
		parent::w_input('flete_cotizacion', $cod_item_menu);
		$sql = "select COD_FLETE_COTIZACION, 
						NOM_FLETE_COTIZACION,					
						ORDEN
						from FLETE_COTIZACION
						where COD_FLETE_COTIZACION = {KEY1}
						order by ORDEN";
		$this->dws['dw_flete_cotizacion'] = new datawindow($sql);

		// asigna los formatos
		$this->dws['dw_flete_cotizacion']->add_control(new edit_text_upper('NOM_FLETE_COTIZACION', 80, 100));
		$this->dws['dw_flete_cotizacion']->add_control(new edit_num('ORDEN',12,10));		
		
		// asigna los mandatorys		
		$this->dws['dw_flete_cotizacion']->set_mandatory('NOM_FLETE_COTIZACION', 'Descripción Flete');
		$this->dws['dw_flete_cotizacion']->set_mandatory('ORDEN', 'Orden');
	}
	function new_record() {
		$this->dws['dw_flete_cotizacion']->insert_row();
	}
	function load_record() {
		$cod_flete_cotizacion = $this->get_item_wo($this->current_record, 'COD_FLETE_COTIZACION');
		$this->dws['dw_flete_cotizacion']->retrieve($cod_flete_cotizacion);

	}
	function get_key() {
		return $this->dws['dw_flete_cotizacion']->get_item(0, 'COD_FLETE_COTIZACION');
	}
	
	function save_record($db) {
		$COD_FLETE_COTIZACION 	= $this->get_key();
		$NOM_FLETE_COTIZACION	= $this->dws['dw_flete_cotizacion']->get_item(0, 'NOM_FLETE_COTIZACION');	
		$ORDEN 					= $this->dws['dw_flete_cotizacion']->get_item(0, 'ORDEN');
		
		$COD_FLETE_COTIZACION = ($COD_FLETE_COTIZACION=='') ? "null" : $COD_FLETE_COTIZACION;		
    
		$sp = 'spu_flete_cotizacion';
	    if ($this->is_new_record())
	    	$operacion = 'INSERT';
	    else
	    	$operacion = 'UPDATE';
	    
	    $param	= "'$operacion', $COD_FLETE_COTIZACION, '$NOM_FLETE_COTIZACION', $ORDEN";		
		
		if ($db->EXECUTE_SP($sp, $param)){
			if ($this->is_new_record()) {
				$cod_flete_cotizacion = $db->GET_IDENTITY();
				$this->dws['dw_flete_cotizacion']->set_item(0, 'COD_FLETE_COTIZACION', $cod_flete_cotizacion);
			}
			return true;
		}
		return false;		
				
	}	
	
}
?>