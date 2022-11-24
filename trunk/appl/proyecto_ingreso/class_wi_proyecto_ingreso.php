<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../common_appl/class_w_cot_nv.php");
require_once(dirname(__FILE__)."/../empresa/class_dw_help_empresa.php");
/*
Clase : wi_proyecto_ingreso
*/
class dw_item_proyecto_ingreso extends datawindow {
	function dw_item_proyecto_ingreso () {		
	
		//todos los campos que se agreguen en el select se deben agregar en función "creada_desde"
		$sql = "SELECT COD_ITEM_PROYECTO_INGRESO
						,TDP.ORDEN
						,IPI.COD_TIPO_DOC_PAGO
						,TDP.NOM_TIPO_DOC_PAGO
						,IPI.COD_CUENTA_CONTABLE
						,IPI.COD_PROYECTO_INGRESO
						,TDP.TIPO_DOCUMENTO
				FROM ITEM_PROYECTO_INGRESO IPI
					LEFT OUTER JOIN CUENTA_CONTABLE CC ON IPI.COD_CUENTA_CONTABLE = CC.COD_CUENTA_CONTABLE
					, PROYECTO_INGRESO PIN, TIPO_DOC_PAGO TDP
				WHERE PIN.COD_PROYECTO_INGRESO = {KEY1}
				AND IPI.COD_PROYECTO_INGRESO = PIN.COD_PROYECTO_INGRESO
				AND IPI.COD_TIPO_DOC_PAGO = TDP.COD_TIPO_DOC_PAGO";
					
					
		parent::datawindow($sql, 'ITEM_PROYECTO_INGRESO', false, false);	
		$this->add_control(new static_text('ORDEN',4, 5));
		$this->add_control(new edit_text_hidden('COD_ITEM_PROYECTO_INGRESO'));
		$this->add_control(new static_text('NOM_TIPO_DOC_PAGO'));
		$this->add_control(new edit_text_hidden('COD_TIPO_DOC_PAGO'));
		$this->add_control(new static_text('TIPO_DOCUMENTO'));
		
		$sql = "select COD_CUENTA_CONTABLE,
					NOM_CUENTA_CONTABLE
				from CUENTA_CONTABLE";
		$this->add_control(new drop_down_dw('COD_CUENTA_CONTABLE', $sql, 120));
	}
	function insert_row($row=-1) {
		$row = parent::insert_row($row);
		$this->set_item($row, 'COD_ITEM_PROYECTO_INGRESO',  - $row - 100);	// Se suma -100 para asegura q que negativo (para el caso 0));
		$this->set_item($row, 'ORDEN', $this->row_count() * 10);
		return $row;
	}
	function update($db){
		$sp = 'spu_item_proyecto_ingreso';
		
		for ($i = 0; $i < $this->row_count(); $i++){
			$statuts = $this->get_status_row($i);
			if ($statuts == K_ROW_NOT_MODIFIED || $statuts == K_ROW_NEW)
				continue;

			$COD_ITEM_PROYECTO_INGRESO 	= $this->get_item($i, 'COD_ITEM_PROYECTO_INGRESO');
			$COD_PROYECTO_INGRESO		= $this->get_item($i, 'COD_PROYECTO_INGRESO');
			$COD_TIPO_DOC_PAGO 			= $this->get_item($i, 'COD_TIPO_DOC_PAGO');
			$COD_CUENTA_CONTABLE 		= $this->get_item($i, 'COD_CUENTA_CONTABLE');
			
			$COD_ITEM_PROYECTO_INGRESO	= ($COD_ITEM_PROYECTO_INGRESO =='') ? "null" : "$COD_ITEM_PROYECTO_INGRESO";			
			
						
			if ($statuts == K_ROW_NEW_MODIFIED)
				$operacion = 'INSERT';
			elseif ($statuts == K_ROW_MODIFIED)
				$operacion = 'UPDATE';
		
			$param = "'$operacion',
						$COD_ITEM_PROYECTO_INGRESO,
						$COD_PROYECTO_INGRESO,
						$COD_TIPO_DOC_PAGO,
						$COD_CUENTA_CONTABLE";
			
			if (!$db->EXECUTE_SP($sp, $param))
				return false;		
		}
		for ($i = 0; $i < $this->row_count('delete'); $i++) {
			$statuts = $this->get_status_row($i, 'delete');			
			if ($statuts == K_ROW_NEW || $statuts == K_ROW_NEW_MODIFIED)
				continue;
			
			$COD_ITEM_PROYECTO_INGRESO = $this->get_item($i, 'COD_ITEM_PROYECTO_INGRESO', 'delete');
			if (!$db->EXECUTE_SP($sp, "'DELETE', $COD_ITEM_COTIZACION")){			
				return false;				
			}			
		}
		return true;
	}
}
class wi_proyecto_ingreso extends w_input {
	function wi_proyecto_ingreso($cod_item_menu) {
		parent::w_input('proyecto_ingreso', $cod_item_menu);
		
		$sql = "SELECT	COD_PROYECTO_INGRESO
						,NOM_PROYECTO_INGRESO
						,ORDEN	ORDEN_PI
				FROM	PROYECTO_INGRESO
				WHERE	COD_PROYECTO_INGRESO = {KEY1}";
		
		$this->dws['dw_proyecto_ingreso'] = new datawindow($sql);

		// asigna los formatos
		$this->dws['dw_proyecto_ingreso']->add_control(new static_text('COD_PROYECTO_INGRESO', 80, 100));
		$this->dws['dw_proyecto_ingreso']->add_control(new edit_text_upper('NOM_PROYECTO_INGRESO', 80, 100));
		$this->dws['dw_proyecto_ingreso']->add_control(new edit_text_upper('ORDEN_PI', 80, 100));			
		
				
		// asigna los mandatorys
		$this->dws['dw_proyecto_ingreso']->set_mandatory('NOM_PROYECTO_INGRESO', 'Nombre Proyecto');
		
		//asigna auditoria
		$this->add_auditoria('COD_PROYECTO_INGRESO');
		$this->add_auditoria('NOM_PROYECTO_INGRESO');
		
		////////////////////
		// tab items
		$this->dws['dw_item_proyecto_ingreso'] = new dw_item_proyecto_ingreso();
	}

	function new_record() {
		$this->dws['dw_proyecto_ingreso']->insert_row();
		
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		
		$sql = "SELECT COD_TIPO_DOC_PAGO
					   ,COD_TIPO_DOC_PAGO COD_TIPO_DOC_PAGO_H
					  ,NOM_TIPO_DOC_PAGO
					  ,TIPO_DOCUMENTO
				FROM TIPO_DOC_PAGO";
		$result = $db->build_results($sql);
		$row_count = $db->count_rows();
		
		for($i=0; $i <count($result); $i++){
			
			$row = $this->dws['dw_item_proyecto_ingreso']->insert_row();
			$this->dws['dw_item_proyecto_ingreso']->set_item($row, 'COD_TIPO_DOC_PAGO', $result[$i]['COD_TIPO_DOC_PAGO']);
			$this->dws['dw_item_proyecto_ingreso']->set_item($row, 'NOM_TIPO_DOC_PAGO', $result[$i]['NOM_TIPO_DOC_PAGO']);
			$this->dws['dw_item_proyecto_ingreso']->set_item($row, 'TIPO_DOCUMENTO', $result[$i]['TIPO_DOCUMENTO']);
		}
	}
	function load_record() {
		$cod_proyecto_ingreso = $this->get_item_wo($this->current_record, 'COD_PROYECTO_INGRESO');
		$this->dws['dw_proyecto_ingreso']->retrieve($cod_proyecto_ingreso);
		$this->dws['dw_item_proyecto_ingreso']->retrieve($cod_proyecto_ingreso);
	}
	function get_key() {	
		return $this->dws['dw_proyecto_ingreso']->get_item(0, 'COD_PROYECTO_INGRESO');
	}
	function save_record($db) {
		$COD_PROYECTO_INGRESO			= $this->get_key();
		$NOM_PROYECTO_INGRESO			= $this->dws['dw_proyecto_ingreso']->get_item(0, 'NOM_PROYECTO_INGRESO');
		$ORDEN							= $this->dws['dw_proyecto_ingreso']->get_item(0, 'ORDEN_PI');
		
		$COD_PROYECTO_INGRESO	= ($COD_PROYECTO_INGRESO=='') ? "null" : $COD_PROYECTO_INGRESO;
		$ORDEN					= ($ORDEN=='') ? "null" : $ORDEN;
		
		$sp = 'spu_proyecto_ingreso';
	    if ($this->is_new_record())
	    	$operacion = 'INSERT';
	    else
	    	$operacion = 'UPDATE';
	    
	    $param	= "'$operacion' 
	    			,$COD_PROYECTO_INGRESO
	    			,'$NOM_PROYECTO_INGRESO'
	    			,$ORDEN";
	    			
	    if ($db->EXECUTE_SP($sp, $param)){
			if ($this->is_new_record()) {
				$COD_PROYECTO_INGRESO = $db->GET_IDENTITY();
				$this->dws['dw_proyecto_ingreso']->set_item(0, 'COD_PROYECTO_INGRESO', $COD_PROYECTO_INGRESO);
			}
			for ($i=0; $i<$this->dws['dw_item_proyecto_ingreso']->row_count(); $i++)
				$this->dws['dw_item_proyecto_ingreso']->set_item($i, 'COD_PROYECTO_INGRESO', $COD_PROYECTO_INGRESO);

			if (!$this->dws['dw_item_proyecto_ingreso']->update($db))
				return false;
			
			return true;	
		}
		return false;
	}
}
?>