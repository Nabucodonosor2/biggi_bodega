<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__) . "/../empresa/class_dw_help_empresa.php");
/*
Clase : wi_centro_costo
*/
class dw_centro_costo_empresa extends dw_help_empresa{
	function dw_centro_costo_empresa(){
		$sql = "SELECT	CCE.COD_CENTRO_COSTO_EMPRESA
						,CCE.COD_CENTRO_COSTO
						,E.COD_EMPRESA
						,E.RUT
						,E.DIG_VERIF
						,E.ALIAS
						,E.NOM_EMPRESA
						,'N' IS_NEW
						,CCE.COD_PROYECTO_COMPRA
				FROM	CENTRO_COSTO_EMPRESA CCE, EMPRESA E
				WHERE	CCE.COD_EMPRESA = E.COD_EMPRESA
				AND		CCE.COD_CENTRO_COSTO = '{KEY1}'	
				ORDER BY	CCE.COD_CENTRO_COSTO_EMPRESA";
/*Para controlar el ingreso de empresas tipo ('C' clientes, 'P' proveedores, 'T' Trabajador o personal)*/
		parent::dw_help_empresa($sql, 'CENTRO_COSTO_EMPRESA', true, true, 'C');

		// Setea el focus en COD_EMPRESA para las nuevas lineas
		$this->set_first_focus('COD_EMPRESA');
		
		$sql = "SELECT COD_CUENTA_COMPRA COD_PROYECTO_COMPRA
					  ,NOM_CUENTA_COMPRA 
				FROM CUENTA_COMPRA
				ORDER BY COD_CUENTA_COMPRA";
		$this->add_control(new drop_down_dw('COD_PROYECTO_COMPRA', $sql, 205));
		
		// deja protected los datos, excepto si la columna es nueva
		$this->set_protect('COD_EMPRESA', "[IS_NEW]=='N'");
		$this->set_protect('NOM_EMPRESA', "[IS_NEW]=='N'");
		$this->set_protect('ALIAS', "[IS_NEW]=='N'");
		$this->set_protect('RUT', "[IS_NEW]=='N'");

		$this->controls['COD_EMPRESA']->size = 10;
		$this->controls['NOM_EMPRESA']->size = 80;
		$this->controls['ALIAS']->size = 45;
	}

	function update($db){
		$sp = 'spu_centro_costo_empresa';

		for ($i = 0; $i < $this->row_count('delete'); $i++) {
			$statuts = $this->get_status_row($i, 'delete');
			if ($statuts == K_ROW_NEW || $statuts == K_ROW_NEW_MODIFIED){
				continue;
			}

			$cod_centro_costo_empresa = $this->get_item($i, 'COD_CENTRO_COSTO_EMPRESA', 'delete');
			if (!$db->EXECUTE_SP($sp, "'DELETE', $cod_centro_costo_empresa")){
				return false;
			}
		}
		for ($i = 0; $i < $this->row_count(); $i++) {
			$statuts = $this->get_status_row($i);
			if ($statuts == K_ROW_NOT_MODIFIED || $statuts == K_ROW_NEW){
				continue;
			}
			$cod_centro_costo_empresa = $this->get_item($i, 'COD_CENTRO_COSTO_EMPRESA');
			$cod_centro_costo = $this->get_item($i, 'COD_CENTRO_COSTO');
			$cod_empresa = $this->get_item($i, 'COD_EMPRESA');
			$cod_proyecto_compra = $this->get_item($i, 'COD_PROYECTO_COMPRA');

			$cod_centro_costo_empresa = ($cod_centro_costo_empresa == '') ? 0 : $cod_centro_costo_empresa;
			$cod_centro_costo = ($cod_centro_costo == '') ? "NULL" : $cod_centro_costo;
			$cod_empresa = ($cod_empresa == '') ? 0 : $cod_empresa;
			$cod_proyecto_compra = ($cod_proyecto_compra == '') ? "NULL" : $cod_proyecto_compra;

			if ($statuts == K_ROW_NEW_MODIFIED){
				$operacion = 'INSERT';
			}
			elseif ($statuts == K_ROW_MODIFIED){
				$operacion = 'UPDATE';
			}
			$param = "'$operacion', $cod_centro_costo_empresa, $cod_centro_costo, $cod_empresa, $cod_proyecto_compra";

			if (!$db->EXECUTE_SP($sp, $param)){
				return false;
			}
		}
		return true;
	}
}

class wi_centro_costo extends w_input {
	function wi_centro_costo($cod_item_menu) {
		parent::w_input('centro_costo', $cod_item_menu);
		//valida que la PK sea unica
		$this->valida_llave = true;

		$sql = "SELECT	COD_CENTRO_COSTO
						,COD_CENTRO_COSTO COD_CENTRO_COSTO_H
						,NOM_CENTRO_COSTO
						,COD_CUENTA_CONTABLE_VENTAS
						,COD_CUENTA_CONTABLE_IVA
						,COD_CUENTA_CONTABLE_POR_COBRAR
				FROM CENTRO_COSTO
				WHERE	COD_CENTRO_COSTO = '{KEY1}'
				ORDER BY COD_CENTRO_COSTO ASC";
		$this->dws['dw_centro_costo'] = new datawindow($sql);
		$this->dws['dw_centro_costo_empresa'] = new dw_centro_costo_empresa();

		$this->set_first_focus('COD_CENTRO_COSTO');
		// asigna los formatos
		$this->dws['dw_centro_costo']->add_control(new edit_text('COD_CENTRO_COSTO_H',10, 10, 'hidden'));
		$this->dws['dw_centro_costo']->add_control(new edit_text_upper('NOM_CENTRO_COSTO', 80, 100));			
		
		//COD_CUENTA_CONTABLE_COMPRA
		$sql_contable_ventas = "SELECT	COD_CUENTA_CONTABLE, 
	    								NOM_CUENTA_CONTABLE
								FROM	CUENTA_CONTABLE
								ORDER BY	COD_CUENTA_CONTABLE";
		$this->dws['dw_centro_costo']->add_control(new drop_down_dw('COD_CUENTA_CONTABLE_VENTAS', $sql_contable_ventas, 150));
		
		//COD_CUENTA_CONTABLE_IVA
		$sql_contable_iva = "SELECT	COD_CUENTA_CONTABLE,
									NOM_CUENTA_CONTABLE
							 FROM	CUENTA_CONTABLE
							 ORDER BY	COD_CUENTA_CONTABLE";
		$this->dws['dw_centro_costo']->add_control(new drop_down_dw('COD_CUENTA_CONTABLE_IVA', $sql_contable_iva, 150));
				
		//COD_CUENTA_CONTABLE_POR_PAGAR
		$sql_contable_cobrar = 	"SELECT	COD_CUENTA_CONTABLE,
										NOM_CUENTA_CONTABLE
								FROM	CUENTA_CONTABLE
								ORDER BY	COD_CUENTA_CONTABLE";
		$this->dws['dw_centro_costo']->add_control(new drop_down_dw('COD_CUENTA_CONTABLE_POR_COBRAR', $sql_contable_cobrar, 150));
	
		// asigna los mandatorys
		$this->dws['dw_centro_costo']->set_mandatory('COD_CENTRO_COSTO', 'Código Centro Costo');
		$this->dws['dw_centro_costo']->set_mandatory('NOM_CENTRO_COSTO', 'Centro Costo');
		$this->dws['dw_centro_costo']->set_mandatory('COD_CUENTA_CONTABLE_VENTAS', 'Contable Ventas');
		$this->dws['dw_centro_costo']->set_mandatory('COD_CUENTA_CONTABLE_IVA', 'Contable Iva');
		$this->dws['dw_centro_costo']->set_mandatory('COD_CUENTA_CONTABLE_POR_COBRAR', 'Contable por Cobrar');
		
		//asigna auditoria
		$this->add_auditoria('COD_CENTRO_COSTO');
		$this->add_auditoria('NOM_CENTRO_COSTO');
		$this->add_auditoria('COD_CUENTA_CONTABLE_VENTAS');
		$this->add_auditoria('COD_CUENTA_CONTABLE_IVA');
		$this->add_auditoria('COD_CUENTA_CONTABLE_POR_COBRAR');
	}

	function new_record() {
		$this->dws['dw_centro_costo']->insert_row();
		$this->dws['dw_centro_costo']->add_control(new edit_text_upper('COD_CENTRO_COSTO', 30, 40));	
	}

	function load_record() {
		$cod_centro_costo = $this->get_item_wo($this->current_record, 'COD_CENTRO_COSTO');
		$this->dws['dw_centro_costo']->retrieve($cod_centro_costo);
		$this->dws['dw_centro_costo_empresa']->retrieve($cod_centro_costo);
	}

	function get_key() {
		$COD_CENTRO_COSTO = $this->dws['dw_centro_costo']->get_item(0, 'COD_CENTRO_COSTO');
		return "'".$COD_CENTRO_COSTO."'";
		
	}

	function save_record($db) {
		$COD_CENTRO_COSTO			= $this->get_key();
		$NOM_CENTRO_COSTO			= $this->dws['dw_centro_costo']->get_item(0, 'NOM_CENTRO_COSTO');
		$COD_CUENTA_CONTABLE_VENTAS = $this->dws['dw_centro_costo']->get_item(0, 'COD_CUENTA_CONTABLE_VENTAS');
		$COD_CUENTA_CONTABLE_IVA	= $this->dws['dw_centro_costo']->get_item(0, 'COD_CUENTA_CONTABLE_IVA');
		$COD_CUENTA_CONTABLE_POR_COBRAR = $this->dws['dw_centro_costo']->get_item(0, 'COD_CUENTA_CONTABLE_POR_COBRAR');

		$COD_CENTRO_COSTO = ($COD_CENTRO_COSTO=='') ? "null" : $COD_CENTRO_COSTO;
		$COD_CUENTA_CONTABLE_VENTAS = ($COD_CUENTA_CONTABLE_VENTAS=='') ? "null" : $COD_CUENTA_CONTABLE_VENTAS;
		$COD_CUENTA_CONTABLE_IVA = ($COD_CUENTA_CONTABLE_IVA=='') ? "null" : $COD_CUENTA_CONTABLE_IVA;
		$COD_CUENTA_CONTABLE_POR_COBRAR = ($COD_CUENTA_CONTABLE_POR_COBRAR=='') ? "null" : $COD_CUENTA_CONTABLE_POR_COBRAR;

		$sp = 'spu_centro_costo';
	    if ($this->is_new_record())
	    	$operacion = 'INSERT';
	    else
	    	$operacion = 'UPDATE';

	    $param	= "'$operacion' ,$COD_CENTRO_COSTO ,'$NOM_CENTRO_COSTO' ,$COD_CUENTA_CONTABLE_VENTAS ,$COD_CUENTA_CONTABLE_IVA ,$COD_CUENTA_CONTABLE_POR_COBRAR";
	
	    if ($db->EXECUTE_SP($sp, $param)){
	    	for ($i = 0; $i < $this->dws['dw_centro_costo_empresa']->row_count(); $i++){
				$this->dws['dw_centro_costo_empresa']->set_item($i, 'COD_CENTRO_COSTO', $COD_CENTRO_COSTO);
			}
	    	
	    	if (!$this->dws['dw_centro_costo_empresa']->update($db))			
				return false;
	    	
			return true;
		}
		return false;
	}
}
////////////////////////
?>