<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

define("K_MODULO", 'instalacion_cotizacion');
class wi_instalacion_cotizacion extends w_input {
	function wi_instalacion_cotizacion($cod_item_menu) {
		parent::w_input('instalacion_cotizacion', $cod_item_menu);
		$sql = "select COD_INSTALACION_COTIZACION, 
						NOM_INSTALACION_COTIZACION,					
						ORDEN
						from INSTALACION_COTIZACION
						where COD_INSTALACION_COTIZACION = {KEY1}
						order by ORDEN";
		$this->dws['dw_instalacion_cotizacion'] = new datawindow($sql);

		// asigna los formatos
		$this->dws['dw_instalacion_cotizacion']->add_control(new edit_text_upper('NOM_INSTALACION_COTIZACION', 80, 100));
		$this->dws['dw_instalacion_cotizacion']->add_control(new edit_num('ORDEN',12,10));
		
		// asigna los mandatorys		
		$this->dws['dw_instalacion_cotizacion']->set_mandatory('NOM_INSTALACION_COTIZACION', 'Descripción Instalación');
		$this->dws['dw_instalacion_cotizacion']->set_mandatory('ORDEN', 'Orden');	
		
				
	}
	function new_record() {
		$this->dws['dw_instalacion_cotizacion']->insert_row();
	}
	function load_record() {
		$cod_instalacion_cotizacion = $this->get_item_wo($this->current_record, 'COD_INSTALACION_COTIZACION');
		$this->dws['dw_instalacion_cotizacion']->retrieve($cod_instalacion_cotizacion);

	}
	function get_key() {
		return $this->dws['dw_instalacion_cotizacion']->get_item(0, 'COD_INSTALACION_COTIZACION');
	}
	
	function save_record($db) {
		$COD_INSTALACION_COTIZACION 	= $this->get_key();
		$NOM_INSTALACION_COTIZACION		= $this->dws['dw_instalacion_cotizacion']->get_item(0, 'NOM_INSTALACION_COTIZACION');	
		$ORDEN 							= $this->dws['dw_instalacion_cotizacion']->get_item(0, 'ORDEN');
		
		$COD_INSTALACION_COTIZACION = ($COD_INSTALACION_COTIZACION=='') ? "null" : $COD_INSTALACION_COTIZACION;		
    
		$sp = 'spu_instalacion_cotizacion';
	    if ($this->is_new_record())
	    	$operacion = 'INSERT';
	    else
	    	$operacion = 'UPDATE';
	    
	    $param	= "'$operacion', $COD_INSTALACION_COTIZACION, '$NOM_INSTALACION_COTIZACION', $ORDEN";   
    
		if ($db->EXECUTE_SP($sp, $param)){
			if ($this->is_new_record()) {
				$cod_instalacion_cotizacion = $db->GET_IDENTITY();
				$this->dws['dw_instalacion_cotizacion']->set_item(0, 'COD_INSTALACION_COTIZACION', $cod_instalacion_cotizacion);
			}
			return true;
		}
		return false;		
				
	}	
	
}
?>