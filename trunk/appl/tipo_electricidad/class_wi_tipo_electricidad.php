<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

class wi_tipo_electricidad extends w_input {
	function wi_tipo_electricidad($cod_item_menu) {
		parent::w_input('tipo_electricidad', $cod_item_menu);
		$sql = "select COD_TIPO_ELECTRICIDAD, 
						NOM_TIPO_ELECTRICIDAD,					
						ORDEN
						from TIPO_ELECTRICIDAD
						where COD_TIPO_ELECTRICIDAD = {KEY1}
						order by ORDEN";
		$this->dws['dw_tipo_electricidad'] = new datawindow($sql);

		// asigna los formatos
		$this->dws['dw_tipo_electricidad']->add_control(new edit_text_upper('NOM_TIPO_ELECTRICIDAD', 80, 100));
		$this->dws['dw_tipo_electricidad']->add_control(new edit_num('ORDEN',12,10));		
		
		// asigna los mandatorys
		$this->dws['dw_tipo_electricidad']->set_mandatory('NOM_TIPO_ELECTRICIDAD', 'Tipo Electricidad');
		$this->dws['dw_tipo_electricidad']->set_mandatory('ORDEN', 'Orden');
		
	}
	function new_record() {
		$this->dws['dw_tipo_electricidad']->insert_row();
	}
	function load_record() {
		$cod_tipo_electricidad = $this->get_item_wo($this->current_record, 'COD_TIPO_ELECTRICIDAD');
		$this->dws['dw_tipo_electricidad']->retrieve($cod_tipo_electricidad);

	}
	function get_key() {
		return $this->dws['dw_tipo_electricidad']->get_item(0, 'COD_TIPO_ELECTRICIDAD');
	}
	
	function save_record($db) {
		$COD_TIPO_ELECTRICIDAD 	= $this->get_key();
		$NOM_TIPO_ELECTRICIDAD	= $this->dws['dw_tipo_electricidad']->get_item(0, 'NOM_TIPO_ELECTRICIDAD');	
		$ORDEN 					= $this->dws['dw_tipo_electricidad']->get_item(0, 'ORDEN');
		
		$COD_TIPO_ELECTRICIDAD = ($COD_TIPO_ELECTRICIDAD=='') ? "null" : $COD_TIPO_ELECTRICIDAD;		
    
		$sp = 'spu_tipo_electricidad';
	    if ($this->is_new_record())
	    	$operacion = 'INSERT';
	    else
	    	$operacion = 'UPDATE';
	    
	    $param	= "'$operacion', $COD_TIPO_ELECTRICIDAD, '$NOM_TIPO_ELECTRICIDAD', $ORDEN"; 
	    
		if ($db->EXECUTE_SP($sp, $param)){
			if ($this->is_new_record()) {
				$cod_tipo_electricidad = $db->GET_IDENTITY();
				$this->dws['dw_tipo_electricidad']->set_item(0, 'COD_TIPO_ELECTRICIDAD', $cod_tipo_electricidad);
			}
			return true;
		}
		return false;		
				
	}	
	
}
?>