<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

class dw_item_deposito extends datawindow {
	function dw_item_deposito() {
		$sql = "select 'S' SELECCION
						,T.NOM_TIPO_DOC_PAGO
						,convert(varchar, D.FECHA_DOC, 103) FECHA_DOC
						,B.NOM_BANCO 
						,P.NOM_PLAZA
						,D.NRO_DOC
						,D.MONTO_DOC
						,D.COD_INGRESO_PAGO
						,I.COD_DEPOSITO
						,I.COD_ITEM_DEPOSITO
						,I.COD_DOC_INGRESO_PAGO
				from ITEM_DEPOSITO I
					, DOC_INGRESO_PAGO D LEFT OUTER JOIN BANCO B on B.COD_BANCO = D.COD_BANCO
										 LEFT OUTER JOIN PLAZA P on P.COD_PLAZA = D.COD_PLAZA
					, TIPO_DOC_PAGO T
				where I.COD_DEPOSITO = {KEY1}
				  and D.COD_DOC_INGRESO_PAGO = I.COD_DOC_INGRESO_PAGO
				  and T.COD_TIPO_DOC_PAGO = D.COD_TIPO_DOC_PAGO";
		parent::datawindow($sql, 'ITEM_DEPOSITO');
		
		$this->add_control($control = new edit_check_box('SELECCION', 'S', 'N'));
		$control->set_onChange("selecciona_documento(this);");
		$this->add_control(new static_num('MONTO_DOC'));
		
		$this->set_computed('MONTO_SELECCION', "[MONTO_DOC]");
		$this->accumulate('MONTO_SELECCION');
		
	}
	function new_deposito() {
		$sql_original = $this->get_sql();
		$hoy = $this->str2date($this->current_date(), '23:59:59');
		$sql = "select 'S' SELECCION
						,T.NOM_TIPO_DOC_PAGO
						,convert(varchar, D.FECHA_DOC, 103) FECHA_DOC
						,B.NOM_BANCO 
						,P.NOM_PLAZA
						,D.NRO_DOC
						,D.MONTO_DOC
						,D.COD_INGRESO_PAGO
						,null COD_DEPOSITO
						,null COD_ITEM_DEPOSITO
						,D.COD_DOC_INGRESO_PAGO
				from DOC_INGRESO_PAGO D LEFT OUTER JOIN BANCO B on B.COD_BANCO = D.COD_BANCO
										 LEFT OUTER JOIN PLAZA P on P.COD_PLAZA = D.COD_PLAZA
					, TIPO_DOC_PAGO T
					, INGRESO_PAGO I
				where I.COD_ESTADO_INGRESO_PAGO = 2 -- Confirmado
				  and D.COD_INGRESO_PAGO = I.COD_INGRESO_PAGO 
				  and D.COD_TIPO_DOC_PAGO in (1, 2, 3)
				  and D.COD_DOC_INGRESO_PAGO not in (select COD_DOC_INGRESO_PAGO 
													from ITEM_DEPOSITO I, DEPOSITO D 
													where I.COD_DEPOSITO = D.COD_DEPOSITO 
													  and D.COD_ESTADO_DEPOSITO <> 3)
				  and T.COD_TIPO_DOC_PAGO = D.COD_TIPO_DOC_PAGO
				  and D.FECHA_DOC <= $hoy";
			
		$this->set_sql($sql);
		$this->retrieve();		
		$this->set_sql($sql_original);
		for ($i = 0; $i < $this->row_count(); $i++)
			$this->set_status_row($i, K_ROW_NEW_MODIFIED);
	}
	function update($db) {
		$sp = 'spu_item_deposito';
		for ($i = 0; $i < $this->row_count(); $i++){
			$statuts = $this->get_status_row($i);
			if ($statuts == K_ROW_NOT_MODIFIED || $statuts == K_ROW_NEW)
				continue;

			$cod_item_deposito = $this->get_item($i, 'COD_ITEM_DEPOSITO');
			$cod_deposito = $this->get_item($i, 'COD_DEPOSITO');
			$cod_doc_ingreso_pago = $this->get_item($i, 'COD_DOC_INGRESO_PAGO');
			$seleccion = $this->get_item($i, 'SELECCION');
						
			$cod_item_deposito = $cod_item_deposito=='' ? 'null' : $cod_item_deposito;
			
			if ($statuts == K_ROW_NEW_MODIFIED)
				$operacion = 'INSERT';
			elseif ($statuts == K_ROW_MODIFIED)
				$operacion = 'UPDATE';
		
			$param = "'$operacion',$cod_item_deposito, $cod_deposito,$cod_doc_ingreso_pago, '$seleccion'";
			if (!$db->EXECUTE_SP($sp, $param))
				return false;		
		}
		return true;		
	}
}
class wi_deposito extends w_input {
	const K_INGRESADO = 1;
	
	function wi_deposito($cod_item_menu) {
		parent::w_input('deposito', $cod_item_menu);

		$sql = "select D.COD_DEPOSITO
						,D.NRO_DEPOSITO
						,convert(varchar, D.FECHA_DEPOSITO, 103) FECHA_DEPOSITO
						,D.COD_USUARIO
						,U.NOM_USUARIO
						,D.COD_CUENTA_CORRIENTE
						,D.COD_ESTADO_DEPOSITO
				        ,dbo.f_last_mod('NOM_USUARIO', 'DEPOSITO', 'COD_ESTADO_DEPOSITO', D.COD_DEPOSITO) NOM_USUARIO_CAMBIO
						,dbo.f_last_mod('FECHA_CAMBIO', 'DEPOSITO', 'COD_ESTADO_DEPOSITO', D.COD_DEPOSITO) FECHA_CAMBIO
				from DEPOSITO D, USUARIO U
				where D.COD_DEPOSITO = {KEY1}
				  and U.COD_USUARIO = D.COD_USUARIO";
		$this->dws['dw_deposito'] = new datawindow($sql);

		// asigna los formatos
		$this->dws['dw_deposito']->add_control(new edit_num('NRO_DEPOSITO'));	
		$sql = "select COD_CUENTA_CORRIENTE, NOM_CUENTA_CORRIENTE from CUENTA_CORRIENTE order by ORDEN";
		$this->dws['dw_deposito']->add_control(new drop_down_dw('COD_CUENTA_CORRIENTE',$sql));
		$sql = "select COD_ESTADO_DEPOSITO, NOM_ESTADO_DEPOSITO from ESTADO_DEPOSITO order by COD_ESTADO_DEPOSITO";
		$this->dws['dw_deposito']->add_control(new drop_down_dw('COD_ESTADO_DEPOSITO',$sql, 0, '', false));
		
		$this->dws['dw_item_deposito'] = new dw_item_deposito();
		
		//audoria
		$this->add_auditoria('COD_ESTADO_DEPOSITO');
		
		// asigna los mandatorys
		$this->dws['dw_deposito']->set_mandatory('NRO_DEPOSITO', 'Número Depósito');
		$this->dws['dw_deposito']->set_mandatory('COD_CUENTA_CORRIENTE', 'Número de Cuenta');
	}
	function new_record() {
		$this->dws['dw_deposito']->insert_row();
		$this->dws['dw_deposito']->set_item(0, 'FECHA_DEPOSITO', $this->current_date());
		$this->dws['dw_deposito']->set_item(0, 'COD_USUARIO', $this->cod_usuario);
		$this->dws['dw_deposito']->set_item(0, 'NOM_USUARIO', $this->nom_usuario);
		$this->dws['dw_deposito']->set_item(0, 'COD_ESTADO_DEPOSITO', self::K_INGRESADO);
		$this->dws['dw_deposito']->set_entrable('COD_ESTADO_DEPOSITO', false);
		
		$this->dws['dw_item_deposito']->new_deposito();
	}
	function load_record() {
		$cod_deposito = $this->get_item_wo($this->current_record, 'COD_DEPOSITO');
		$this->dws['dw_deposito']->retrieve($cod_deposito);
		$this->dws['dw_deposito']->set_entrable('COD_ESTADO_DEPOSITO', true);
		$this->dws['dw_item_deposito']->retrieve($cod_deposito);
		
		$cod_estado_deposito = $this->dws['dw_deposito']->get_item(0, 'COD_ESTADO_DEPOSITO');
		if ($cod_estado_deposito==1) {	// emitida
			$this->b_modify_visible	 	= true;
			$this->b_save_visible	 	= true;
			$this->b_no_save_visible	= true;
		}
		else if ($cod_estado_deposito==2) { // confirmada
			$this->b_modify_visible	 	= false;
			$this->b_save_visible	 	= false;
			$this->b_no_save_visible	= false;
		}
		else if ($cod_estado_deposito==3) { // anulada
			$this->b_modify_visible	 	= false;
			$this->b_save_visible	 	= false;
			$this->b_no_save_visible	= false;
		}
	}
	function get_key() {
		return $this->dws['dw_deposito']->get_item(0, 'COD_DEPOSITO');
	}
	function save_record($db) {
		$cod_deposito = $this->get_key();
		$nro_deposito = $this->dws['dw_deposito']->get_item(0, 'NRO_DEPOSITO');	
		$cod_usuario = $this->dws['dw_deposito']->get_item(0, 'COD_USUARIO');	
		$cod_cuenta_corriente = $this->dws['dw_deposito']->get_item(0, 'COD_CUENTA_CORRIENTE');	
		$cod_estado_deposito = $this->dws['dw_deposito']->get_item(0, 'COD_ESTADO_DEPOSITO');	
		
		$cod_deposito = ($cod_deposito=='') ? "null" : $cod_deposito;	
		$nro_deposito = ($nro_deposito=='') ? "null" : $nro_deposito;	
		
		$sp = 'spu_deposito';
	    if ($this->is_new_record())
	    	$operacion = 'INSERT';
	    else
	    	$operacion = 'UPDATE';
	    	    	
	    $param	= "'$operacion', $cod_deposito,$cod_usuario,$nro_deposito,$cod_cuenta_corriente,$cod_estado_deposito";    
		if ($db->EXECUTE_SP($sp, $param)){
			if ($this->is_new_record()) {
				$cod_deposito = $db->GET_IDENTITY();
				$this->dws['dw_deposito']->set_item(0, 'COD_DEPOSITO', $cod_deposito);				
			}
			for ($i = 0; $i < $this->dws['dw_item_deposito']->row_count(); $i++)
				$this->dws['dw_item_deposito']->set_item($i, 'COD_DEPOSITO', $cod_deposito);
				
			if (!$this->dws['dw_item_deposito']->update($db))
				return false;
				
			return true;
		}
		return false;						
	}
}
?>