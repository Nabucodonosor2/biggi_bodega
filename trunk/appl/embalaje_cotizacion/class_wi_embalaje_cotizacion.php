<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

class wi_embalaje_cotizacion extends w_input {
	function wi_embalaje_cotizacion($cod_item_menu) {
		parent::w_input('embalaje_cotizacion', $cod_item_menu);
		$sql = "select COD_EMBALAJE_COTIZACION, 
						NOM_EMBALAJE_COTIZACION,					
						ORDEN
						from EMBALAJE_COTIZACION
						where COD_EMBALAJE_COTIZACION = {KEY1}
						order by ORDEN";
		$this->dws['dw_embalaje_cotizacion'] = new datawindow($sql);

		// asigna los formatos
		$this->dws['dw_embalaje_cotizacion']->add_control(new edit_text_upper('NOM_EMBALAJE_COTIZACION', 80, 100));
		$this->dws['dw_embalaje_cotizacion']->add_control(new edit_num('ORDEN',12,10));	
		
		// asigna los mandatorys		
		$this->dws['dw_embalaje_cotizacion']->set_mandatory('NOM_EMBALAJE_COTIZACION', 'Descripción Embalaje');	
		$this->dws['dw_embalaje_cotizacion']->set_mandatory('ORDEN', 'Orden');	
	}
	function new_record() {
		$this->dws['dw_embalaje_cotizacion']->insert_row();
	}
	function load_record() {
		$cod_embalaje_cotizacion = $this->get_item_wo($this->current_record, 'COD_EMBALAJE_COTIZACION');
		$this->dws['dw_embalaje_cotizacion']->retrieve($cod_embalaje_cotizacion);

	}
	function get_key() {
		return $this->dws['dw_embalaje_cotizacion']->get_item(0, 'COD_EMBALAJE_COTIZACION');
	}
	
	function save_record($db) {
		$COD_EMBALAJE_COTIZACION 	= $this->get_key();
		$NOM_EMBALAJE_COTIZACION	= $this->dws['dw_embalaje_cotizacion']->get_item(0, 'NOM_EMBALAJE_COTIZACION');	
		$ORDEN 						= $this->dws['dw_embalaje_cotizacion']->get_item(0, 'ORDEN');
				
		$COD_EMBALAJE_COTIZACION = ($COD_EMBALAJE_COTIZACION=='') ? "null" : $COD_EMBALAJE_COTIZACION;		
    
		$sp = 'spu_embalaje_cotizacion';
	    if ($this->is_new_record())
	    	$operacion = 'INSERT';
	    else
	    	$operacion = 'UPDATE';
	    
	    $param	= "'$operacion', $COD_EMBALAJE_COTIZACION, '$NOM_EMBALAJE_COTIZACION', $ORDEN";
		
		if ($db->EXECUTE_SP($sp, $param)){
			if ($this->is_new_record()) {
				$cod_embalaje_cotizacion = $db->GET_IDENTITY();
				$this->dws['dw_embalaje_cotizacion']->set_item(0, 'COD_EMBALAJE_COTIZACION', $cod_embalaje_cotizacion);
			}
			return true;
		}
		return false;		
				
	}	
	
}
?>