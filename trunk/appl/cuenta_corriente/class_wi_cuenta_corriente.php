<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../empresa/class_dw_help_empresa.php");

class wi_cuenta_corriente extends w_input {
	function wi_cuenta_corriente($cod_item_menu) {
		parent::w_input('cuenta_corriente', $cod_item_menu);

		$sql = "select 		COD_CUENTA_CORRIENTE,
							NOM_CUENTA_CORRIENTE, 
							NRO_CUENTA_CORRIENTE,
							COD_CUENTA_CONTABLE,
							ORDEN																				 						
				from 		CUENTA_CORRIENTE
				where 		COD_CUENTA_CORRIENTE = {KEY1}
				order by 	ORDEN";
						
		$sql_cuenta_contable = "select 		COD_CUENTA_CONTABLE,
											NOM_CUENTA_CONTABLE
								from 		CUENTA_CONTABLE
								ORDER BY	COD_CUENTA_CONTABLE";	
						
						
		$this->dws['dw_cuenta_corriente'] = new datawindow($sql);

		// asigna los formatos
		$this->dws['dw_cuenta_corriente']->add_control(new edit_text_upper('NOM_CUENTA_CORRIENTE', 80, 80));	
		$this->dws['dw_cuenta_corriente']->add_control(new edit_text_upper('NRO_CUENTA_CORRIENTE', 80, 80));
		$this->dws['dw_cuenta_corriente']->add_control(new drop_down_dw('COD_CUENTA_CONTABLE',$sql_cuenta_contable, 180));
		$this->dws['dw_cuenta_corriente']->add_control(new edit_num('ORDEN',12,10));				
		
		// asigna los mandatorys
		$this->dws['dw_cuenta_corriente']->set_mandatory('NOM_CUENTA_CORRIENTE', 'Nombre de Cuenta');
		$this->dws['dw_cuenta_corriente']->set_mandatory('NRO_CUENTA_CORRIENTE', 'Número de Cuenta');
		$this->dws['dw_cuenta_corriente']->set_mandatory('ORDEN', 'Orden');		
	}
	function new_record() {
		$this->dws['dw_cuenta_corriente']->insert_row();
	}
	function load_record() {
		$cod_cuenta_corriente = $this->get_item_wo($this->current_record, 'COD_CUENTA_CORRIENTE');
		$this->dws['dw_cuenta_corriente']->retrieve($cod_cuenta_corriente);
	}
	function get_key() {
		return $this->dws['dw_cuenta_corriente']->get_item(0, 'COD_CUENTA_CORRIENTE');
	}
	
	function save_record($db) {
		$COD_CUENTA_CORRIENTE = $this->get_key();
		$NOM_CUENTA_CORRIENTE = $this->dws['dw_cuenta_corriente']->get_item(0, 'NOM_CUENTA_CORRIENTE');	
		$NRO_CUENTA_CORRIENTE = $this->dws['dw_cuenta_corriente']->get_item(0, 'NRO_CUENTA_CORRIENTE');	
		$COD_CUENTA_CONTABLE = $this->dws['dw_cuenta_corriente']->get_item(0, 'COD_CUENTA_CONTABLE');	
		
		
		$ORDEN = $this->dws['dw_cuenta_corriente']->get_item(0, 'ORDEN');
		
		
		$COD_CUENTA_CORRIENTE = ($COD_CUENTA_CORRIENTE=='') ? "null" : $COD_CUENTA_CORRIENTE;	
		
		$sp = 'spu_cuenta_corriente';
	    if ($this->is_new_record())
	    	$operacion = 'INSERT';
	    else
	    	$operacion = 'UPDATE';
	    
	    $param	= "'$operacion'
	    			, $COD_CUENTA_CORRIENTE
	    			,'$NOM_CUENTA_CORRIENTE'
	    			,$NRO_CUENTA_CORRIENTE
	    			,$COD_CUENTA_CONTABLE
	    			,$ORDEN";

		if ($db->EXECUTE_SP($sp, $param)){
			if ($this->is_new_record()) {
				$COD_CUENTA_CORRIENTE = $db->GET_IDENTITY();
				$this->dws['dw_cuenta_corriente']->set_item(0, 'COD_CUENTA_CORRIENTE', $COD_CUENTA_CORRIENTE);				
			}
				
			return true;
		}
		return false;						
	}
}
?>