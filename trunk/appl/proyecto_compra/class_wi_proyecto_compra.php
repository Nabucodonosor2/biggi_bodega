<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
/*
Clase : wi_proyecto_compra
*/
class wi_proyecto_compra extends w_input {
	function wi_proyecto_compra($cod_item_menu) {
		parent::w_input('proyecto_compra', $cod_item_menu);

		$sql = "SELECT	COD_CUENTA_COMPRA
						,NOM_CUENTA_COMPRA
						,COD_CUENTA_CONTABLE_COMPRA
						,COD_CUENTA_CONTABLE_IVA
						,COD_CUENTA_CONTABLE_POR_PAGAR
						,COD_CENTRO_COSTO
				FROM	CUENTA_COMPRA
				WHERE	COD_CUENTA_COMPRA = {KEY1}
				ORDER BY COD_CUENTA_COMPRA ASC";
		$this->dws['dw_proyecto_compra'] = new datawindow($sql);

		// asigna los formatos
		$this->dws['dw_proyecto_compra']->add_control(new edit_text_upper('NOM_CUENTA_COMPRA', 80, 100));			
		
		//COD_CUENTA_CONTABLE_COMPRA
		$sql_contable_compra = "SELECT	COD_CUENTA_CONTABLE, 
	    								NOM_CUENTA_CONTABLE
								FROM	CUENTA_CONTABLE
								ORDER BY	COD_CUENTA_CONTABLE";
		$this->dws['dw_proyecto_compra']->add_control(new drop_down_dw('COD_CUENTA_CONTABLE_COMPRA',$sql_contable_compra,150));
		
		//COD_CUENTA_CONTABLE_IVA
		$sql_contable_iva = "SELECT	COD_CUENTA_CONTABLE,
									NOM_CUENTA_CONTABLE
							 FROM	CUENTA_CONTABLE
							 ORDER BY	COD_CUENTA_CONTABLE";
		$this->dws['dw_proyecto_compra']->add_control(new drop_down_dw('COD_CUENTA_CONTABLE_IVA',$sql_contable_iva,150));
				
		//COD_CUENTA_CONTABLE_POR_PAGAR
		$sql_contable_pagar = 	"SELECT	COD_CUENTA_CONTABLE,
										NOM_CUENTA_CONTABLE
								FROM	CUENTA_CONTABLE
								ORDER BY	COD_CUENTA_CONTABLE";
		$this->dws['dw_proyecto_compra']->add_control(new drop_down_dw('COD_CUENTA_CONTABLE_POR_PAGAR',$sql_contable_pagar,150));
		
		//COD_CENTRO_COSTO
		$sql_centro_costo	 = 	"SELECT	COD_CENTRO_COSTO,
										NOM_CENTRO_COSTO
								FROM	CENTRO_COSTO
								ORDER BY	COD_CENTRO_COSTO";
		$this->dws['dw_proyecto_compra']->add_control(new drop_down_dw('COD_CENTRO_COSTO',$sql_centro_costo,150));
				
		// asigna los mandatorys
		$this->dws['dw_proyecto_compra']->set_mandatory('NOM_CUENTA_COMPRA', 'Cuenta Compra');
		$this->dws['dw_proyecto_compra']->set_mandatory('COD_CUENTA_CONTABLE_COMPRA', 'Contable Compra');
		$this->dws['dw_proyecto_compra']->set_mandatory('COD_CUENTA_CONTABLE_IVA', 'Contable Iva');
		$this->dws['dw_proyecto_compra']->set_mandatory('COD_CUENTA_CONTABLE_POR_PAGAR', 'Contable por Pagar');
	}

	function new_record() {
		$this->dws['dw_proyecto_compra']->insert_row();
	}

	function load_record() {
		$cod_cuenta_compra = $this->get_item_wo($this->current_record, 'COD_CUENTA_COMPRA');
		$this->dws['dw_proyecto_compra']->retrieve($cod_cuenta_compra);
	}

	function get_key() {
		return $this->dws['dw_proyecto_compra']->get_item(0, 'COD_CUENTA_COMPRA');
	}

	function save_record($db) {
		$COD_CUENTA_COMPRA			= $this->get_key();
		$NOM_CUENTA_COMPRA			= $this->dws['dw_proyecto_compra']->get_item(0, 'NOM_CUENTA_COMPRA');
		$COD_CUENTA_CONTABLE_COMPRA = $this->dws['dw_proyecto_compra']->get_item(0, 'COD_CUENTA_CONTABLE_COMPRA');
		$COD_CUENTA_CONTABLE_IVA	= $this->dws['dw_proyecto_compra']->get_item(0, 'COD_CUENTA_CONTABLE_IVA');
		$COD_CUENTA_CONTABLE_POR_PAGAR = $this->dws['dw_proyecto_compra']->get_item(0, 'COD_CUENTA_CONTABLE_POR_PAGAR');
		$COD_CENTRO_COSTO			= $this->dws['dw_proyecto_compra']->get_item(0, 'COD_CENTRO_COSTO');

		$COD_CUENTA_COMPRA = ($COD_CUENTA_COMPRA=='') ? "null" : $COD_CUENTA_COMPRA;
		$COD_CUENTA_CONTABLE_COMPRA = ($COD_CUENTA_CONTABLE_COMPRA=='') ? "null" : $COD_CUENTA_CONTABLE_COMPRA;
		$COD_CUENTA_CONTABLE_IVA = ($COD_CUENTA_CONTABLE_IVA=='') ? "null" : $COD_CUENTA_CONTABLE_IVA;
		$COD_CUENTA_CONTABLE_POR_PAGAR = ($COD_CUENTA_CONTABLE_POR_PAGAR=='') ? "null" : $COD_CUENTA_CONTABLE_POR_PAGAR;
    	$COD_CENTRO_COSTO = ($COD_CENTRO_COSTO=='') ? "null" : "'$COD_CENTRO_COSTO'";
    	
		$sp = 'spu_proyecto_compra';
	    if ($this->is_new_record())
	    	$operacion = 'INSERT';
	    else
	    	$operacion = 'UPDATE';
	    
	    $param	= "'$operacion' ,$COD_CUENTA_COMPRA ,'$NOM_CUENTA_COMPRA' ,$COD_CUENTA_CONTABLE_COMPRA ,$COD_CUENTA_CONTABLE_IVA ,$COD_CUENTA_CONTABLE_POR_PAGAR , $COD_CENTRO_COSTO";
	    if ($db->EXECUTE_SP($sp, $param)){
			if ($this->is_new_record()) {
				$COD_CUENTA_COMPRA = $db->GET_IDENTITY();
				$this->dws['dw_proyecto_compra']->set_item(0, 'COD_CUENTA_COMPRA', $COD_CUENTA_COMPRA);
			}
			return true;
		}
		return false;
	}
}
////////////////////////
?>