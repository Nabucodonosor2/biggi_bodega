<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
/*
Clase : WI_CLASIF_EMPRESA
*/
class wi_clasif_empresa extends w_input {
	function wi_clasif_empresa($cod_item_menu) {
		parent::w_input('clasif_empresa', $cod_item_menu);
		$sql = "select COD_CLASIF_EMPRESA, 
						NOM_CLASIF_EMPRESA,
						ORDEN
						from CLASIF_EMPRESA
						where COD_CLASIF_EMPRESA = {KEY1}";
		$this->dws['dw_clasif_empresa'] = new datawindow($sql);

		// asigna los formatos
		$this->dws['dw_clasif_empresa']->add_control(new edit_text_upper('NOM_CLASIF_EMPRESA', 80, 100));		
		$this->dws['dw_clasif_empresa']->add_control(new edit_num('ORDEN',12,10));
		
		// asigna los mandatorys
		$this->dws['dw_clasif_empresa']->set_mandatory('NOM_CLASIF_EMPRESA', 'Nombre a Clasificación Empresa');
		$this->dws['dw_clasif_empresa']->set_mandatory('ORDEN', 'Orden');
				
	}
	function new_record() {
		$this->dws['dw_clasif_empresa']->insert_row();
	}
	function load_record() {
		$cod_clasif_empresa = $this->get_item_wo($this->current_record, 'COD_CLASIF_EMPRESA');
		$this->dws['dw_clasif_empresa']->retrieve($cod_clasif_empresa);

	}
	function get_key() {
		return $this->dws['dw_clasif_empresa']->get_item(0, 'COD_CLASIF_EMPRESA');
	}
	
	function save_record($db) {
		$COD_CLASIF_EMPRESA = $this->get_key();
		$NOM_CLASIF_EMPRESA = $this->dws['dw_clasif_empresa']->get_item(0, 'NOM_CLASIF_EMPRESA');
		$ORDEN = $this->dws['dw_clasif_empresa']->get_item(0, 'ORDEN');
		
		$COD_CLASIF_EMPRESA = ($COD_CLASIF_EMPRESA=='') ? "null" : $COD_CLASIF_EMPRESA;
				
		$sp = 'spu_clasif_empresa';
	    if ($this->is_new_record())
	    	$operacion = 'INSERT';
	    else
	    	$operacion = 'UPDATE';
	    
	    $param	= "'$operacion', $COD_CLASIF_EMPRESA, '$NOM_CLASIF_EMPRESA', $ORDEN";
	    
		if ($db->EXECUTE_SP($sp, $param)){
			if ($this->is_new_record()) {
				$cod_clasif_empresa = $db->GET_IDENTITY();
				$this->dws['dw_clasif_empresa']->set_item(0, 'COD_CLASIF_EMPRESA', $cod_clasif_empresa);
			}
			return true;
		}
		return false;		
				
	}	
	
}
?>