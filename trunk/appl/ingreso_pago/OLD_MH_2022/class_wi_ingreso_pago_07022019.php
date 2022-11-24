<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../empresa/class_dw_help_empresa.php");
require_once(dirname(__FILE__)."/../ws_client_biggi/class_client_biggi.php");

class dw_ingreso_pago_factura extends datawindow {
	
	function dw_ingreso_pago_factura() {		
		$sql = "SELECT 'S' SELECCION
						,IPF.COD_INGRESO_PAGO_FACTURA
						,F.COD_FACTURA COD_DOC
						,IPF.COD_INGRESO_PAGO
						,F.NRO_FACTURA
						,convert(varchar(20), F.FECHA_FACTURA, 103) FECHA_FACTURA
						,F.REFERENCIA
						,dbo.f_factura_get_saldo(F.COD_FACTURA) + IPF.MONTO_ASIGNADO SALDO
						,dbo.f_factura_get_saldo(F.COD_FACTURA) + IPF.MONTO_ASIGNADO SALDO_C
						,IPF.MONTO_ASIGNADO
						,'none' DISPLAY_CERO
						,0 MONTO_ASIGNADO_C 
				FROM	INGRESO_PAGO_FACTURA IPF, INGRESO_PAGO IP, FACTURA F
				WHERE	IPF.COD_INGRESO_PAGO = {KEY1} AND
						IPF.TIPO_DOC = 'FACTURA' AND
						IPF.COD_DOC = F.COD_FACTURA AND
						IP.COD_INGRESO_PAGO = IPF.COD_INGRESO_PAGO
						order by F.NRO_FACTURA asc";
					
		parent::datawindow($sql, 'INGRESO_PAGO_FACTURA', true, true);	
		
		$this->add_control($control = new edit_check_box('SELECCION', 'S', 'N'));
		$control->set_onChange("asignacion_monto(this, 'INGRESO_PAGO_FACTURA');");
		
		$this->add_control(new static_num('COD_DOC'));
		$this->controls['COD_DOC']->type = 'hidden';

		$this->add_control(new static_num('SALDO'));
		$this->add_control(new edit_precio('SALDO_C',10,10));

		$this->add_control($control = new edit_precio('MONTO_ASIGNADO',10, 10));
		$control->forzar_js = true;	// para que agregue el js aún cuando este hidden el control
		$control->set_onChange("valida_asignacion(get_num_rec_field(this.id), 'INGRESO_PAGO_FACTURA');");
		
		// computed y accumulate de monto_asignado
		$this->set_computed('MONTO_ASIGNADO_C', '[MONTO_ASIGNADO]');
		$this->controls['MONTO_ASIGNADO_C']->type = 'hidden';
		$this->accumulate('MONTO_ASIGNADO_C');
		
		// computed y accumulate de saldo_sin_ingreso_pago
		$this->set_computed('SALDO_C', '[SALDO]');
		$this->controls['SALDO_C']->type = 'hidden';
		$this->accumulate('SALDO_C');
		
		// computed y accumulate de saldo_t
		$this->set_computed('SALDO_T', '[SALDO] - [MONTO_ASIGNADO]');
		$this->accumulate('SALDO_T');
		
		$this->set_first_focus('MONTO_ASIGNADO');
		
		// asigna los mandatorys
		$this->set_mandatory('MONTO_ASIGNADO', 'Monto Asignado');
	
	}
	function insert_row($row=-1) {
		$row = parent::insert_row($row);
		return $row;
	}
	
	function update($db, $COD_INGRESO_PAGO)	{
		$sp = 'spu_ingreso_pago_factura';
		for ($i = 0; $i < $this->row_count(); $i++){
			$statuts = $this->get_status_row($i);
			if ($statuts == K_ROW_NOT_MODIFIED || $statuts == K_ROW_NEW){
				continue;
			}
			$SELECCION = $this->get_item($i, 'SELECCION');
			if ($SELECCION=='N')
				continue;
			$COD_INGRESO_PAGO_FACTURA 	= $this->get_item($i, 'COD_INGRESO_PAGO_FACTURA');
			$COD_INGRESO_PAGO			= $this->get_item($i, 'COD_INGRESO_PAGO');
			$COD_DOC		 			= $this->get_item($i, 'COD_DOC');
			$TIPO_DOC 					= "'FACTURA'";		
			$MONTO_ASIGNADO				= $this->get_item($i, 'MONTO_ASIGNADO');

			$COD_INGRESO_PAGO_FACTURA = ($COD_INGRESO_PAGO_FACTURA =='') ? "null" : $COD_INGRESO_PAGO_FACTURA;
			
			$operacion = 'INSERT';
			$param = "'$operacion',$COD_INGRESO_PAGO_FACTURA, $COD_INGRESO_PAGO, $COD_DOC, $TIPO_DOC, $MONTO_ASIGNADO";	

			if (!$db->EXECUTE_SP($sp, $param))
				return false;				
		}	
		return true;
	}
}

class dw_ingreso_pago_nota_venta extends datawindow {
	
	function dw_ingreso_pago_nota_venta() {		
		$sql = "SELECT 'S' SELECCION_NV
						,IPF.COD_INGRESO_PAGO_FACTURA COD_INGRESO_PAGO_FACTURA_NV
						,NV.COD_NOTA_VENTA COD_DOC_NV
						,IPF.COD_INGRESO_PAGO COD_INGRESO_PAGO_NV
						,convert(varchar(20), NV.FECHA_NOTA_VENTA, 103) FECHA_NOTA_VENTA
						,NV.REFERENCIA REFERENCIA_NV
						,dbo.f_nv_get_saldo(NV.COD_NOTA_VENTA) + IPF.MONTO_ASIGNADO SALDO_NV
						,dbo.f_nv_get_saldo(NV.COD_NOTA_VENTA) + IPF.MONTO_ASIGNADO SALDO_C_NV
						,IPF.MONTO_ASIGNADO MONTO_ASIGNADO_NV
						,'none' DISPLAY_CERO_NV
						,0 MONTO_ASIGNADO_C_NV 
				FROM	INGRESO_PAGO_FACTURA IPF, INGRESO_PAGO IP, NOTA_VENTA NV
				WHERE	IPF.COD_INGRESO_PAGO = {KEY1} AND
						IPF.TIPO_DOC = 'NOTA_VENTA' AND
						IPF.COD_DOC = NV.COD_NOTA_VENTA AND
						IP.COD_INGRESO_PAGO = IPF.COD_INGRESO_PAGO
						order by NV.COD_NOTA_VENTA asc";
		
		parent::datawindow($sql, 'INGRESO_PAGO_NOTA_VENTA', true, true);	
		
		$this->add_control($control = new edit_check_box('SELECCION_NV', 'S', 'N'));
		$control->set_onChange("asignacion_monto(this, 'INGRESO_PAGO_NOTA_VENTA');");
			
		$this->add_control(new static_num('SALDO_NV'));
		$this->add_control(new edit_precio('SALDO_C_NV',10,10));
		
		$this->add_control($control = new edit_precio('MONTO_ASIGNADO_NV',10, 10));
		$control->forzar_js = true;	// para que agregue el js aún cuando este hidden el control
		$control->set_onChange("valida_asignacion(get_num_rec_field(this.id), 'INGRESO_PAGO_NOTA_VENTA');");
		
		// computed y accumulate de monto_asignado
		$this->set_computed('MONTO_ASIGNADO_C_NV', '[MONTO_ASIGNADO_NV]');
		$this->controls['MONTO_ASIGNADO_C_NV']->type = 'hidden';
		$this->accumulate('MONTO_ASIGNADO_C_NV');
		
		// computed y accumulate de saldo_sin_ingreso_pago
		$this->set_computed('SALDO_C_NV', '[SALDO_NV]');
		$this->controls['SALDO_C_NV']->type = 'hidden';
		$this->accumulate('SALDO_C_NV');
		
		
		// computed y accumulate de saldo_t
		$this->set_computed('SALDO_T_NV', '[SALDO_NV] - [MONTO_ASIGNADO_NV]');
		$this->accumulate('SALDO_T_NV');
		
		$this->set_first_focus('MONTO_ASIGNADO_NV');
		
		// asigna los mandatorys
		$this->set_mandatory('MONTO_ASIGNADO_NV', 'Monto Asignado');
	
	}
	function insert_row($row=-1) {
		$row = parent::insert_row($row);
		return $row;
	}
	
	function update($db, $COD_INGRESO_PAGO)	{
		$sp = 'spu_ingreso_pago_factura';	
		for ($i = 0; $i < $this->row_count(); $i++){
			$statuts = $this->get_status_row($i);
			if ($statuts == K_ROW_NOT_MODIFIED || $statuts == K_ROW_NEW){
				continue;
			}
			$SELECCION = $this->get_item($i, 'SELECCION_NV');
			if ($SELECCION=='N')
				continue;
			$COD_INGRESO_PAGO_FACTURA 	= $this->get_item($i, 'COD_INGRESO_PAGO_FACTURA_NV');
			$COD_INGRESO_PAGO			= $this->get_item($i, 'COD_INGRESO_PAGO_NV');
			$COD_DOC		 			= $this->get_item($i, 'COD_DOC_NV');
			$TIPO_DOC 					= "'NOTA_VENTA'";		
			$MONTO_ASIGNADO				= $this->get_item($i, 'MONTO_ASIGNADO_NV');

			$COD_INGRESO_PAGO_FACTURA = ($COD_INGRESO_PAGO_FACTURA =='') ? "null" : $COD_INGRESO_PAGO_FACTURA;
			
			$operacion = 'INSERT';
			$param = "'$operacion',$COD_INGRESO_PAGO_FACTURA, $COD_INGRESO_PAGO, $COD_DOC, $TIPO_DOC, $MONTO_ASIGNADO";	
			
			if (!$db->EXECUTE_SP($sp, $param))
				return false;
			}	
		return true;
	}
}

class dw_doc_ingreso_pago extends datawindow {
	const K_TIPO_DOC_PAGO_EFECTIVO		= 1;
	const K_TIPO_DOC_PAGO_NC			= 7;
	const K_TIPO_DOC_PAGO_POR_DEFINIR	= 8;
	const K_TIPO_DOC_PAGO_ANTICIPO		= 9;
	const K_TIPO_DOC_PAGO_FC			= 11;
	
	function dw_doc_ingreso_pago() {		
		$sql = "SELECT DIP.COD_DOC_INGRESO_PAGO
						,DIP.COD_INGRESO_PAGO
						,DIP.NRO_DOC
						,convert(varchar(20), DIP.FECHA_DOC, 103) FECHA_DOC 
						,DIP.MONTO_DOC
						,DIP.MONTO_DOC 
						,DIP.COD_TIPO_DOC_PAGO
						,TDP.NOM_TIPO_DOC_PAGO
						,DIP.COD_BANCO
						,B.NOM_BANCO
						,DIP.COD_TIPO_DOC_PAGO COD_TIPO_DOC_PAGO_H
						,0 MONTO_DOC_C
						,COD_CHEQUE
						,CASE
							WHEN COD_CHEQUE IS NOT NULL THEN 'background-color: #A9F5E1;'
							ELSE ''
						END COLOR_TR
				FROM 	DOC_INGRESO_PAGO DIP LEFT OUTER JOIN BANCO B ON DIP.COD_BANCO = B.COD_BANCO, TIPO_DOC_PAGO TDP
						
				WHERE 	DIP.COD_INGRESO_PAGO = {KEY1} AND
						DIP.COD_TIPO_DOC_PAGO = TDP.COD_TIPO_DOC_PAGO";
					
					
		parent::datawindow($sql, 'DOC_INGRESO_PAGO', true, true);	
		
		$this->add_control(new edit_text_upper('COD_DOC_INGRESO_PAGO',10, 10, 'hidden'));
		$this->add_control(new edit_text_hidden('COD_CHEQUE'));
		$this->add_control(new edit_text_hidden('COD_BANCO_H'));
		$this->add_control(new edit_num('NRO_DOC',10, 10, 0, true, false, false));
		$this->add_control(new edit_date('FECHA_DOC'));
		$this->add_control($control = new edit_precio('MONTO_DOC',10,10));
		$control->set_onChange("change_monto_doc();");
		
		$this->set_computed('MONTO_DOC_C', '[MONTO_DOC]');
		$this->controls['MONTO_DOC_C']->type = 'hidden';
		$this->accumulate('MONTO_DOC_C');
		
		$sql_tipo_doc = "select	COD_TIPO_DOC_PAGO
									,NOM_TIPO_DOC_PAGO
							from	TIPO_DOC_PAGO
							where COD_TIPO_DOC_PAGO <> ".self::K_TIPO_DOC_PAGO_POR_DEFINIR."
							order by ORDEN asc";
		
		$this->add_control($control = new drop_down_dw('COD_TIPO_DOC_PAGO',$sql_tipo_doc,120));
		$control->set_onChange("valida_tipo_doc_pago(this); valida_asignacion_doc_pago(this)");
		$this->add_control(new edit_text_upper('COD_TIPO_DOC_PAGO_H',10,10,'hidden'));
		
		$sql_banco = " select	COD_BANCO
								,NOM_BANCO
						from	BANCO
						order by COD_BANCO asc";
		$this->add_control(new drop_down_dw('COD_BANCO',$sql_banco,180));
	}
	function insert_row($row=-1) {
		$row = parent::insert_row($row);
		return $row;
	}
	function fill_record(&$temp, $record) {
		$COD_TIPO_DOC_PAGO = $this->get_item($record, 'COD_TIPO_DOC_PAGO');
			
		if ($COD_TIPO_DOC_PAGO==self::K_TIPO_DOC_PAGO_EFECTIVO) {
			$this->controls['NRO_DOC']->type = 'hidden';
			$this->controls['COD_BANCO']->enabled = false;
		}
		else {
			$this->controls['NRO_DOC']->type = 'text';
			$this->controls['COD_BANCO']->enabled = true;
		}
			
		// llama al ancestro
		parent::fill_record($temp, $record);
	}
	function update($db)	{
		$sp = 'spu_doc_ingreso_pago';
		
		for ($i = 0; $i < $this->row_count(); $i++){
				$statuts = $this->get_status_row($i);
				if ($statuts == K_ROW_NOT_MODIFIED || $statuts == K_ROW_NEW)
					continue;
	
					$COD_DOC_INGRESO_PAGO	= $this->get_item($i, 'COD_DOC_INGRESO_PAGO');
					$COD_INGRESO_PAGO 		= $this->get_item($i, 'COD_INGRESO_PAGO');
					$COD_TIPO_DOC_PAGO_H 	= $this->get_item($i, 'COD_TIPO_DOC_PAGO_H');
					$COD_CHEQUE 			= $this->get_item($i, 'COD_CHEQUE');
					
					if ($COD_TIPO_DOC_PAGO_H == self::K_TIPO_DOC_PAGO_ANTICIPO || $COD_TIPO_DOC_PAGO_H == self::K_TIPO_DOC_PAGO_NC || $COD_TIPO_DOC_PAGO_H == self::K_TIPO_DOC_PAGO_FC || $COD_CHEQUE <> '')
						$COD_TIPO_DOC_PAGO = $COD_TIPO_DOC_PAGO_H;
					else
						$COD_TIPO_DOC_PAGO = $this->get_item($i, 'COD_TIPO_DOC_PAGO');
					
					if($COD_CHEQUE <> '')
						$COD_BANCO = $this->get_item($i, 'COD_BANCO_H');
					else
						$COD_BANCO = $this->get_item($i, 'COD_BANCO');
						
					$NRO_DOC	 			= $this->get_item($i, 'NRO_DOC');
					$FECHA_DOC 				= $this->get_item($i, 'FECHA_DOC');
					$MONTO_DOC2 			= $this->get_item($i, 'MONTO_DOC');
					$MONTO_DOC 				= str_replace( ".", "", $MONTO_DOC2);
					
					$COD_DOC_INGRESO_PAGO	= ($COD_DOC_INGRESO_PAGO =='') ? "null" : $COD_DOC_INGRESO_PAGO;
					$COD_BANCO 			 	= ($COD_BANCO =='') ? "null" : $COD_BANCO;
					$NRO_DOC 			 	= ($NRO_DOC =='') ? "null" : $NRO_DOC;
					$MONTO_DOC 			 	= ($MONTO_DOC =='') ? "null" : $MONTO_DOC;
					$COD_CHEQUE 			= ($COD_CHEQUE =='') ? "null" : $COD_CHEQUE;
					
					if ($statuts == K_ROW_NEW_MODIFIED)
						$operacion = 'INSERT';
					else if ($statuts == K_ROW_MODIFIED)
						$operacion = 'UPDATE';		
						
					$param = "'$operacion'
								,$COD_DOC_INGRESO_PAGO
								,$COD_INGRESO_PAGO
								,$COD_TIPO_DOC_PAGO
								,$COD_BANCO
								,$NRO_DOC
								,'$FECHA_DOC'
								,$MONTO_DOC
								,$COD_CHEQUE";
								
					if (!$db->EXECUTE_SP($sp, $param)) 
						return false;
					else {
						if ($statuts == K_ROW_NEW_MODIFIED) {
							$COD_DOC_INGRESO_PAGO = $db->GET_IDENTITY();
							$this->set_item($i, 'COD_DOC_INGRESO_PAGO', $COD_DOC_INGRESO_PAGO);		
						}
					}
				}
		
		for ($i = 0; $i < $this->row_count('delete'); $i++) {
			$statuts = $this->get_status_row($i, 'delete');
			if ($statuts == K_ROW_NEW || $statuts == K_ROW_NEW_MODIFIED)
				continue;
				
			$COD_DOC_INGRESO_PAGO = $this->get_item($i, 'COD_DOC_INGRESO_PAGO', 'delete');
			if (!$db->EXECUTE_SP($sp, "'DELETE', $COD_DOC_INGRESO_PAGO"))
				return false;
		}	
		return true;
	}

}

class dw_ingreso_pago extends dw_help_empresa{
	
	const K_ESTADO_INGRESO_PAGO_EMITIDA		= 1;
	const K_ESTADO_INGRESO_PAGO_CONFIRMADA	= 2;
	const K_ESTADO_INGRESO_PAGO_ANULADA		= 3;
	
	function dw_ingreso_pago($sql) {	
		parent::dw_help_empresa($sql);
		
		$this->add_control(new edit_nro_doc('COD_INGRESO_PAGO','INGRESO_PAGO'));
		
		$this->add_control($control = new edit_precio('OTRO_INGRESO',10, 10));
		$control->set_onChange("ingreso_gasto_abono(this);");
		$this->add_control($control = new edit_precio('OTRO_GASTO',10, 10));
		$control->set_onChange("ingreso_gasto_abono(this);");
		
		$this->add_control($control = new edit_precio('OTRO_ANTICIPO',10, 10));
		$control->set_onChange("ingreso_gasto_abono(this);");
		
		$this->add_control(new edit_text('COD_ESTADO_INGRESO_PAGO',10,10, 'hidden'));
		$this->add_control(new static_text('NOM_ESTADO_INGRESO_PAGO'));
		
		// usuario anulación 
		$sql = "select 	COD_USUARIO
						,NOM_USUARIO
				from USUARIO";
								
		$this->add_control(new drop_down_dw('COD_USUARIO_ANULA',$sql,150));	
		$this->set_entrable('COD_USUARIO_ANULA', false);
		
		$this->add_control(new edit_text_upper('COD_USUARIO_CONFIRMA',10, 10, 'hidden'));	
		
		$this->add_control(new edit_text('USUARIO_CAMBIO',10,10));
		$this->set_entrable('USUARIO_CAMBIO', false);
		
		$this->add_control(new edit_text('FECHA_ANULA',10,10));
		$this->set_entrable('FECHA_ANULA', false);
		
		$this->add_control(new edit_text('FECHA_CAMBIO',10,10));
		$this->set_entrable('FECHA_CAMBIO', false);
		
		// asigna los mandatorys
		$this->set_mandatory('OTRO_INGRESO', 'Otro Ingreso');
		$this->set_mandatory('OTRO_GASTO', 'Otro_gasto');
	}
	function fill_record(&$temp, $record) {	
		parent::fill_record($temp, $record);
		
			$COD_INGRESO_PAGO = $this->get_item(0, 'COD_INGRESO_PAGO');
			
			if ($COD_INGRESO_PAGO !=''){
				$temp->setVar('DISABLE_BUTTON', 'style="display:none"');
			}
			else{	
					if ($this->entrable){
						$temp->setVar('DISABLE_BUTTON', '');
					}
					else{
						$temp->setVar('DISABLE_BUTTON', 'disabled="disabled"');
					}
			}				
	}
	
	
}

class wi_ingreso_pago_base extends w_input {
	const K_ESTADO_INGRESO_PAGO_EMITIDA		= 1;
	const K_ESTADO_INGRESO_PAGO_CONFIRMADA	= 2;
	const K_ESTADO_INGRESO_PAGO_ANULADA		= 3;
	const K_AUTORIZA_CAMBIO_ESTADO			= '992505';
	const K_AUTORIZA_ESTADO_APROBADA		= '992510';
	
	function wi_ingreso_pago_base($cod_item_menu) {
		
		// Marca especial cuando viene desde wo_inf_facturas_por_cobrar
		// debe setearse antes del llamado del parent
		if (session::is_set('DESDE_wo_inf_cheque_fecha')) {
			session::un_set('DESDE_wo_inf_cheque_fecha');
			$this->desde_wo_inf_cheque_fecha = true;
		}
		
		parent::w_input('ingreso_pago', $cod_item_menu);
		
		// valida si el usuario tiene autorizar cambiar el estado de IP
		if ($this->tiene_privilegio_opcion(self::K_AUTORIZA_CAMBIO_ESTADO))
			$autoriza_cambio_estado = 'S';
		else
			$autoriza_cambio_estado = 'N';
		
		$sql="SELECT 	IP.COD_INGRESO_PAGO	
						,convert(varchar(20), IP.FECHA_INGRESO_PAGO, 103) FECHA_INGRESO_PAGO
						,IP.OTRO_INGRESO
						,IP.OTRO_GASTO
						,IP.MOTIVO_ANULA
						,convert(varchar(20), IP.FECHA_ANULA, 103) +'  '+ convert(varchar(20), IP.FECHA_ANULA, 8)FECHA_ANULA
						,IP.COD_USUARIO_ANULA
						,IP.COD_USUARIO
						,IP.COD_EMPRESA
						,IP.COD_ESTADO_INGRESO_PAGO
						,IP.COD_EMPRESA
						,IP.COD_USUARIO_CONFIRMA
						,IP.OTRO_ANTICIPO
						,U.NOM_USUARIO
						,E.RUT
						,E.DIG_VERIF
						,E.NOM_EMPRESA
						,E.ALIAS
						,'".$autoriza_cambio_estado."' AUTORIZA_CAMBIO_ESTADO
						,case IP.COD_ESTADO_INGRESO_PAGO
								when ".self::K_ESTADO_INGRESO_PAGO_ANULADA." then '' 
								else 'none'
							end TR_DISPLAY	
						,EIP.NOM_ESTADO_INGRESO_PAGO
						,(select TOP 1 convert(varchar(20), LG.FECHA_CAMBIO, 103) +'  '+ convert(varchar(20), LG.FECHA_CAMBIO, 8) FECHA_CAMBIO
								from	LOG_CAMBIO LG, DETALLE_CAMBIO DC
								where	LG.NOM_TABLA = 'INGRESO_PAGO' and
										LG.KEY_TABLA = convert(varchar, IP.COD_INGRESO_PAGO) and
										LG.COD_LOG_CAMBIO = DC.COD_LOG_CAMBIO and
										DC.NOM_CAMPO = 'COD_ESTADO_INGRESO_PAGO' 
										order by LG.FECHA_CAMBIO desc) FECHA_CAMBIO
						,(select TOP 1 U.NOM_USUARIO
								from	LOG_CAMBIO LG, DETALLE_CAMBIO DC, USUARIO U
								where	LG.NOM_TABLA = 'INGRESO_PAGO' and
										LG.KEY_TABLA = convert(varchar, IP.COD_INGRESO_PAGO)and
										LG.COD_LOG_CAMBIO = DC.COD_LOG_CAMBIO and
										LG.COD_USUARIO = U.COD_USUARIO and 
										DC.NOM_CAMPO = 'COD_ESTADO_INGRESO_PAGO' 
										order by LG.FECHA_CAMBIO desc)USUARIO_CAMBIO
						,IP.COD_PROYECTO_INGRESO			
						,WS_ORIGEN
						,COD_DOC_ORIGEN
				FROM 	INGRESO_PAGO IP
						left outer join PROYECTO_INGRESO PIN on  IP.COD_PROYECTO_INGRESO = PIN.COD_PROYECTO_INGRESO,
						EMPRESA E, ESTADO_INGRESO_PAGO EIP, USUARIO U
				WHERE 	IP.COD_INGRESO_PAGO = {KEY1} AND
						IP.COD_EMPRESA = E.COD_EMPRESA AND
						IP.COD_ESTADO_INGRESO_PAGO = EIP.COD_ESTADO_INGRESO_PAGO AND
						IP.COD_USUARIO = U.COD_USUARIO";			
			
		// DATAWINDOWS INGRESO_PAGO
		$this->dws['dw_ingreso_pago'] = new dw_ingreso_pago($sql);
		
		$sql	= "select 	 COD_PROYECTO_INGRESO
							,NOM_PROYECTO_INGRESO
					from 	 PROYECTO_INGRESO";
		$this->dws['dw_ingreso_pago']->add_control(new drop_down_dw('COD_PROYECTO_INGRESO',$sql,150));
		//historial de cambio de estado
		$this->add_auditoria('COD_ESTADO_INGRESO_PAGO');
		
		// DATAWINDOWS INGRESO_PAGO_FACTURA
		$this->dws['dw_ingreso_pago_factura'] = new dw_ingreso_pago_factura();
		
		// DATAWINDOWS INGRESO_PAGO_NOTA_VENTA
		$this->dws['dw_ingreso_pago_nota_venta'] = new dw_ingreso_pago_nota_venta();
		
		// DATAWINDOWS doc_ingreso_pago 
		$this->dws['dw_doc_ingreso_pago'] = new dw_doc_ingreso_pago();
		
		//al momento de crear un nuevo registro se iniciara en rut de empresa proveedor
		$this->set_first_focus('RUT');
		
	}
	////////////////////
	// funciones auxiliares para cuando se accede a la FA desde_wo_inf_facturas_por_cobrar
	function load_wo() {
		if ($this->desde_wo_inf_cheque_fecha)
			$this->wo = session::get("wo_inf_cheque_fecha");
		else
			parent::load_wo();
	}
	function get_url_wo() {
		if ($this->desde_wo_inf_cheque_fecha) 
			return $this->root_url.'appl/inf_cheque_fecha/wo_inf_cheque_fecha.php';
		else
			return parent::get_url_wo();
	}
	function new_record() {
		$this->dws['dw_ingreso_pago']->insert_row();
		$this->dws['dw_ingreso_pago']->set_item(0, 'TR_DISPLAY', 'none');
		$this->dws['dw_ingreso_pago']->set_item(0, 'FECHA_INGRESO_PAGO', substr($this->current_date_time(), 0, 16));
		$this->dws['dw_ingreso_pago']->set_item(0, 'COD_USUARIO', $this->cod_usuario);
		$this->dws['dw_ingreso_pago']->set_item(0, 'NOM_USUARIO', $this->nom_usuario);
		$this->dws['dw_ingreso_pago']->set_item(0, 'COD_ESTADO_INGRESO_PAGO', self::K_ESTADO_INGRESO_PAGO_EMITIDA);
		$this->dws['dw_ingreso_pago']->set_item(0, 'NOM_ESTADO_INGRESO_PAGO', 'EMITIDA');
		$this->dws['dw_ingreso_pago']->set_item(0, 'OTRO_GASTO', 0);
		$this->dws['dw_ingreso_pago']->set_item(0, 'OTRO_INGRESO', 0);
		$this->dws['dw_ingreso_pago']->set_item(0, 'OTRO_ANTICIPO', 0);
	}
	
	function load_record() {
		$cod_ingreso_pago = $this->get_item_wo($this->current_record, 'COD_INGRESO_PAGO');
		$this->dws['dw_ingreso_pago']->retrieve($cod_ingreso_pago);
		$cod_empresa = $this->dws['dw_ingreso_pago']->get_item(0, 'COD_EMPRESA');
		$this->dws['dw_ingreso_pago_factura']->retrieve($cod_ingreso_pago);
		$this->dws['dw_ingreso_pago_nota_venta']->retrieve($cod_ingreso_pago);
		$this->dws['dw_doc_ingreso_pago']->retrieve($cod_ingreso_pago); 
		$autoriza_cambio_estado = $this->dws['dw_ingreso_pago']->get_item(0, 'AUTORIZA_CAMBIO_ESTADO');	
		
		$COD_ESTADO_INGRESO_PAGO = $this->dws['dw_ingreso_pago']->get_item(0, 'COD_ESTADO_INGRESO_PAGO');
		
		$this->b_print_visible 	 = true;
		$this->b_no_save_visible = true;
		$this->b_save_visible 	 = true; 
		$this->b_modify_visible	 = true;
		
		//entrable los datos de empresa 
		$this->dws['dw_ingreso_pago']->set_entrable('NOM_EMPRESA'				, true);
		$this->dws['dw_ingreso_pago']->set_entrable('RUT'						, true);
		
		$this->dws['dw_ingreso_pago']->set_entrable('COD_ESTADO_INGRESO_PAGO'	, true);
		$this->dws['dw_ingreso_pago']->set_entrable('NRO_FAPROV'				, true);
		$this->dws['dw_ingreso_pago']->set_entrable('OTRO_INGRESO'				, true);
		$this->dws['dw_ingreso_pago']->set_entrable('OTRO_GASTO'				, true);

		if ($COD_ESTADO_INGRESO_PAGO == self::K_ESTADO_INGRESO_PAGO_EMITIDA) {
			
			$priv = $this->get_privilegio_opcion_usuario(self::K_AUTORIZA_ESTADO_APROBADA, $this->cod_usuario);
			if($priv == 'E'){
				$sql = "select COD_ESTADO_INGRESO_PAGO
							  ,NOM_ESTADO_INGRESO_PAGO
						from ESTADO_INGRESO_PAGO
						order by COD_ESTADO_INGRESO_PAGO";	
			}else{
				$sql = "select 	COD_ESTADO_INGRESO_PAGO
							  ,NOM_ESTADO_INGRESO_PAGO
						from ESTADO_INGRESO_PAGO
						where COD_ESTADO_INGRESO_PAGO <> 2 --confirmada
						order by COD_ESTADO_INGRESO_PAGO";	
			}
			
			unset($this->dws['dw_ingreso_pago']->controls['COD_ESTADO_INGRESO_PAGO']);
			
			$this->dws['dw_ingreso_pago']->add_control($control = new drop_down_dw('COD_ESTADO_INGRESO_PAGO',$sql,110));	
			$control->set_onChange("mostrarOcultar_Anula();");
				
			if($autoriza_cambio_estado == 'S'){
				$this->dws['dw_ingreso_pago']->set_entrable('COD_ESTADO_INGRESO_PAGO'		,true);
				//$this->dws['dw_ingreso_pago']->controls['COD_ESTADO_INGRESO_PAGO']->enabled = true;
			}else{
				$this->dws['dw_ingreso_pago']->set_entrable('COD_ESTADO_INGRESO_PAGO'		,false);
				//$this->dws['dw_ingreso_pago']->controls['COD_ESTADO_INGRESO_PAGO']->enabled = false;
			}
			
			$this->dws['dw_ingreso_pago']->controls['NOM_ESTADO_INGRESO_PAGO']->type = 'hidden';
			$this->dws['dw_ingreso_pago']->add_control(new edit_text_upper('MOTIVO_ANULA',110, 100));
			
			$this->dws['dw_ingreso_pago']->controls['USUARIO_CAMBIO']->type = '';
			$this->dws['dw_ingreso_pago']->controls['FECHA_CAMBIO']->type = '';
			
			$this->dws['dw_ingreso_pago']->controls['COD_USUARIO_ANULA']->type = 'hidden';
			$this->dws['dw_ingreso_pago']->controls['FECHA_ANULA']->type = 'hidden';
			
			$this->dws['dw_ingreso_pago']->set_entrable('NOM_EMPRESA'			,false);
			$this->dws['dw_ingreso_pago']->set_entrable('RUT'					,false);
			$this->dws['dw_ingreso_pago']->set_entrable('ALIAS'					,false);
			$this->dws['dw_ingreso_pago']->set_entrable('COD_EMPRESA'			,false);
						
			$this->dws['dw_ingreso_pago']->set_entrable('OTRO_INGRESO'			,false);
			$this->dws['dw_ingreso_pago']->set_entrable('OTRO_GASTO'			,false);
			$this->dws['dw_ingreso_pago']->set_entrable('OTRO_ANTICIPO'			,false);
			
			//aqui se dejan modificables los datos del tab items
			$this->dws['dw_ingreso_pago_factura']->set_entrable_dw(false);
			//aqui se dejan modificables los datos del tab items
			$this->dws['dw_ingreso_pago_nota_venta']->set_entrable_dw(false);
			
			//aqui se dejan modificables los datos del tab items
			$this->dws['dw_doc_ingreso_pago']->set_entrable_dw(false);
		}
		
		else if ($COD_ESTADO_INGRESO_PAGO == self::K_ESTADO_INGRESO_PAGO_CONFIRMADA){
			
			$sql = "select 	COD_ESTADO_INGRESO_PAGO
							,NOM_ESTADO_INGRESO_PAGO
					from 	ESTADO_INGRESO_PAGO
					where 	COD_ESTADO_INGRESO_PAGO = ".self::K_ESTADO_INGRESO_PAGO_CONFIRMADA." or
							COD_ESTADO_INGRESO_PAGO = ".self::K_ESTADO_INGRESO_PAGO_ANULADA."
							order by COD_ESTADO_INGRESO_PAGO";
			
			unset($this->dws['dw_ingreso_pago']->controls['COD_ESTADO_INGRESO_PAGO']);
			$this->dws['dw_ingreso_pago']->add_control($control = new drop_down_dw('COD_ESTADO_INGRESO_PAGO',$sql,110));	
			$control->set_onChange("mostrarOcultar_Anula();");
			
			if($autoriza_cambio_estado == 'S'){
				$this->dws['dw_ingreso_pago']->set_entrable('COD_ESTADO_INGRESO_PAGO'		,true);
				//$this->dws['dw_ingreso_pago']->controls['COD_ESTADO_INGRESO_PAGO']->enabled = true;
			}else{
				$this->dws['dw_ingreso_pago']->set_entrable('COD_ESTADO_INGRESO_PAGO'		,false);
				//$this->dws['dw_ingreso_pago']->controls['COD_ESTADO_INGRESO_PAGO']->enabled = false;
			}
			
			$this->dws['dw_ingreso_pago']->controls['NOM_ESTADO_INGRESO_PAGO']->type = 'hidden';
			$this->dws['dw_ingreso_pago']->add_control(new edit_text_upper('MOTIVO_ANULA',110, 100));
			
			$this->dws['dw_ingreso_pago']->set_entrable('NOM_EMPRESA'			,false);
			$this->dws['dw_ingreso_pago']->set_entrable('RUT'					,false);
			$this->dws['dw_ingreso_pago']->set_entrable('ALIAS'					,false);
			$this->dws['dw_ingreso_pago']->set_entrable('COD_EMPRESA'			,false);
						
			$this->dws['dw_ingreso_pago']->set_entrable('OTRO_INGRESO'			,false);
			$this->dws['dw_ingreso_pago']->set_entrable('OTRO_GASTO'			,false);
			$this->dws['dw_ingreso_pago']->set_entrable('OTRO_ANTICIPO'			,false);
			
			$this->dws['dw_ingreso_pago']->controls['USUARIO_CAMBIO']->type = '';
			$this->dws['dw_ingreso_pago']->controls['FECHA_CAMBIO']->type = '';
			
			$this->dws['dw_ingreso_pago']->controls['COD_USUARIO_ANULA']->type = 'hidden';
			$this->dws['dw_ingreso_pago']->controls['FECHA_ANULA']->type = 'hidden';

			// aqui se dejan no modificables los datos del tab items
			$this->dws['dw_ingreso_pago_factura']->set_entrable_dw(false);
			
			// aqui se dejan no modificables los datos del tab items
			$this->dws['dw_ingreso_pago_nota_venta']->set_entrable_dw(false);
			
			// aqui se dejan no modificables los datos del tab items
			$this->dws['dw_doc_ingreso_pago']->set_entrable_dw(false);

			
		}
		else if ($COD_ESTADO_INGRESO_PAGO == self::K_ESTADO_INGRESO_PAGO_ANULADA) {
			
			$this->b_print_visible 	 = true;
			$this->b_no_save_visible = false;
			$this->b_save_visible 	 = false;
			$this->b_modify_visible  = false;
			
					
			$this->dws['dw_ingreso_pago']->controls['USUARIO_CAMBIO']->type = 'hidden';
			$this->dws['dw_ingreso_pago']->controls['FECHA_CAMBIO']->type = 'hidden';
			
			$this->dws['dw_ingreso_pago']->controls['COD_USUARIO_ANULA']->type = '';
			$this->dws['dw_ingreso_pago']->controls['FECHA_ANULA']->type = '';
		}
	}
	
	function get_key() {
		return $this->dws['dw_ingreso_pago']->get_item(0, 'COD_INGRESO_PAGO');
	}
	
	function save_record($db) {
		$COD_INGRESO_PAGO	 		= $this->get_key();		
		$FECHA_INGRESO_PAGO			= $this->dws['dw_ingreso_pago']->get_item(0, 'FECHA_INGRESO_PAGO');
		$COD_USUARIO 				= $this->dws['dw_ingreso_pago']->get_item(0, 'COD_USUARIO');
		$COD_EMPRESA				= $this->dws['dw_ingreso_pago']->get_item(0, 'COD_EMPRESA');		
		$OTRO_INGRESO				= $this->dws['dw_ingreso_pago']->get_item(0, 'OTRO_INGRESO');
		$OTRO_GASTO					= $this->dws['dw_ingreso_pago']->get_item(0, 'OTRO_GASTO');
		$COD_ESTADO_INGRESO_PAGO	= $this->dws['dw_ingreso_pago']->get_item(0, 'COD_ESTADO_INGRESO_PAGO');
		$FECHA_ANULA				= $this->dws['dw_ingreso_pago']->get_item(0, 'FECHA_ANULA');
		$COD_PROYECTO_INGRESO		= $this->dws['dw_ingreso_pago']->get_item(0, 'COD_PROYECTO_INGRESO');
		$MOTIVO_ANULA				= $this->dws['dw_ingreso_pago']->get_item(0, 'MOTIVO_ANULA');
		$MOTIVO_ANULA 				= str_replace("'", "''", $MOTIVO_ANULA);
		$COD_USUARIO_ANULA			= $this->dws['dw_ingreso_pago']->get_item(0, 'COD_USUARIO_ANULA');
		$WS_ORIGEN					= $this->dws['dw_ingreso_pago']->get_item(0, 'WS_ORIGEN');
		$COD_DOC_ORIGEN 			= $this->dws['dw_ingreso_pago']->get_item(0, 'COD_DOC_ORIGEN');
		
		if (($MOTIVO_ANULA != '') && ($COD_USUARIO_ANULA == '')) // se anula 
			$COD_USUARIO_ANULA			= $this->cod_usuario;
		else
			$COD_USUARIO_ANULA			= "null";

		$COD_USUARIO_CONFIRMA	= $this->dws['dw_ingreso_pago']->get_item(0, 'COD_USUARIO_CONFIRMA');	

		if (($COD_ESTADO_INGRESO_PAGO == self::K_ESTADO_INGRESO_PAGO_CONFIRMADA) && ($COD_USUARIO_CONFIRMA == ''))// se confirma
			$COD_USUARIO_CONFIRMA		= $this->cod_usuario;
		else
			$COD_USUARIO_CONFIRMA		= "null";
		
		
		$FECHA_ANULA			= ($FECHA_ANULA =='') ? "NULL" : "$FECHA_ANULA";
		$MOTIVO_ANULA			= ($MOTIVO_ANULA =='') ? "NULL" : "'$MOTIVO_ANULA'";
		$COD_USUARIO_ANULA		= ($COD_USUARIO_ANULA =='') ? "NULL" : "$COD_USUARIO_ANULA";	
		$COD_USUARIO_CONFIRMA	= ($COD_USUARIO_CONFIRMA =='') ? "NULL" : "$COD_USUARIO_CONFIRMA";
		$OTRO_INGRESO			= $this->dws['dw_ingreso_pago']->get_item(0, 'OTRO_INGRESO');
		$OTRO_INGRESO			= ($OTRO_INGRESO =='') ? 0 : "$OTRO_INGRESO";	
		$OTRO_GASTO				= $this->dws['dw_ingreso_pago']->get_item(0, 'OTRO_GASTO');
		$OTRO_GASTO				= ($OTRO_GASTO =='') ? 0 : "$OTRO_GASTO";
		$OTRO_ANTICIPO			= $this->dws['dw_ingreso_pago']->get_item(0, 'OTRO_ANTICIPO');
		$OTRO_ANTICIPO			= ($OTRO_ANTICIPO =='') ? 0 : "$OTRO_ANTICIPO";
		$COD_PROYECTO_INGRESO	=($COD_PROYECTO_INGRESO =='')? "null" : $COD_PROYECTO_INGRESO;
		$COD_INGRESO_PAGO 		= ($COD_INGRESO_PAGO =='') ? "null" : $COD_INGRESO_PAGO;	
			
		
		$sp = 'spu_ingreso_pago';
	    if ($this->is_new_record())
	    	$operacion = 'INSERT';
	    else
	    	$operacion = 'UPDATE';
	    
		$param	= "	'$operacion'
					,$COD_INGRESO_PAGO
					,$COD_USUARIO				
					,$COD_EMPRESA				
					,$OTRO_INGRESO
					,$OTRO_GASTO	
					,$COD_ESTADO_INGRESO_PAGO				
					,$COD_USUARIO_ANULA
					,$MOTIVO_ANULA
					,$COD_USUARIO_CONFIRMA
					,$OTRO_ANTICIPO
					,$COD_PROYECTO_INGRESO";

		if ($this->is_new_record())
			$cod_estado_anterior = null;
		else {
			$sql_estado_anterior = "SELECT COD_ESTADO_INGRESO_PAGO FROM INGRESO_PAGO WHERE COD_INGRESO_PAGO = ".$COD_INGRESO_PAGO;
			$result	= $db->build_results($sql_estado_anterior);
			$cod_estado_anterior = $result[0]['COD_ESTADO_INGRESO_PAGO'];
		}
		
		$cod_estado_actual = $COD_ESTADO_INGRESO_PAGO;
		if ($db->EXECUTE_SP($sp, $param)){
			if ($this->is_new_record()) {
				$COD_INGRESO_PAGO = $db->GET_IDENTITY();
				$this->dws['dw_ingreso_pago']->set_item(0, 'COD_INGRESO_PAGO', $COD_INGRESO_PAGO);
			}		
			if ($cod_estado_actual != self::K_ESTADO_INGRESO_PAGO_ANULADA){
				for ($i=0; $i<$this->dws['dw_ingreso_pago_factura']->row_count(); $i++) 
					$this->dws['dw_ingreso_pago_factura']->set_item($i, 'COD_INGRESO_PAGO', $COD_INGRESO_PAGO);
				if (!$this->dws['dw_ingreso_pago_factura']->update($db, $COD_INGRESO_PAGO)) return false;

				for ($i=0; $i<$this->dws['dw_ingreso_pago_nota_venta']->row_count(); $i++) 
					$this->dws['dw_ingreso_pago_nota_venta']->set_item($i, 'COD_INGRESO_PAGO_NV', $COD_INGRESO_PAGO);
				if (!$this->dws['dw_ingreso_pago_nota_venta']->update($db, $COD_INGRESO_PAGO)) return false;
				
				for ($i=0; $i<$this->dws['dw_doc_ingreso_pago']->row_count(); $i++) 
					$this->dws['dw_doc_ingreso_pago']->set_item($i, 'COD_INGRESO_PAGO', $COD_INGRESO_PAGO);
				if (!$this->dws['dw_doc_ingreso_pago']->update($db)) return false;
				
			}
			  
			if ($cod_estado_anterior == self::K_ESTADO_INGRESO_PAGO_EMITIDA && $cod_estado_actual == self::K_ESTADO_INGRESO_PAGO_CONFIRMADA ){
				if (!$db->EXECUTE_SP('spu_ingreso_pago', "'CONFIRMA', $COD_INGRESO_PAGO, $COD_USUARIO, $COD_EMPRESA,$OTRO_INGRESO,$OTRO_GASTO,$COD_ESTADO_INGRESO_PAGO,$COD_USUARIO_ANULA,$MOTIVO_ANULA,$COD_USUARIO_CONFIRMA,$OTRO_ANTICIPO")) return false;
			}
			
			if($cod_estado_actual == self::K_ESTADO_INGRESO_PAGO_ANULADA){
				if($WS_ORIGEN <> '' && $COD_DOC_ORIGEN <> ''){
					$sql = "SELECT SISTEMA
			   					  ,URL_WS
			   					  ,USER_WS
			   					  ,PASSWROD_WS
			   				FROM PARAMETRO_WS
							WHERE SISTEMA = '$WS_ORIGEN'";
					$result = $db->build_results($sql);
					
					$user_ws		= $result[0]['USER_WS'];
					$passwrod_ws	= $result[0]['PASSWROD_WS'];
					$url_ws			= $result[0]['URL_WS'];
			   		 
			   		$biggi = new client_biggi("$user_ws", "$passwrod_ws", "$url_ws");
			   		$biggi->cli_cambio_estado_traspaso($COD_DOC_ORIGEN, 'ANULADO');
					
				}
			}
			
			return true;
		}
		return false;		
	}
	
	function print_record() {
		$cod_ingreso_pago = $this->get_key();
		$sql= "SELECT	--datos ingreso pago
						IP.COD_INGRESO_PAGO
						, SUBSTRING ( CONVERT(char(38),  IP.FECHA_INGRESO_PAGO,121), 12,8) HORA_INGRESO_PAGO
						,convert(varchar(20), IP.FECHA_INGRESO_PAGO, 103) FECHA_INGRESO_PAGO
						--datos usuario y empresa
						,E.RUT
						,E.DIG_VERIF
						,E.NOM_EMPRESA	
						,U.INI_USUARIO
						--datos documento
						,TDP.NOM_TIPO_DOC_PAGO
						,convert(varchar(20), DIP.FECHA_DOC, 103) FECHA_DOC 
						,DIP.NRO_DOC
						,B.NOM_BANCO
						,DIP.MONTO_DOC
						,EIP.NOM_ESTADO_INGRESO_PAGO
				FROM INGRESO_PAGO IP, USUARIO U, EMPRESA E, ESTADO_INGRESO_PAGO EIP, TIPO_DOC_PAGO TDP,
					DOC_INGRESO_PAGO DIP LEFT OUTER JOIN BANCO B ON DIP.COD_BANCO = B.COD_BANCO
				WHERE	IP.COD_INGRESO_PAGO = $cod_ingreso_pago
				AND IP.COD_USUARIO = U.COD_USUARIO
				AND IP.COD_EMPRESA = E.COD_EMPRESA
				AND DIP.COD_INGRESO_PAGO = IP.COD_INGRESO_PAGO
				AND EIP.COD_ESTADO_INGRESO_PAGO = IP.COD_ESTADO_INGRESO_PAGO
				AND TDP.COD_TIPO_DOC_PAGO = DIP.COD_TIPO_DOC_PAGO";
		$labels = array();
		$labels['strCOD_INGRESO_PAGO'] = $cod_ingreso_pago;
		$rpt = new print_ingreso_pago($sql, $this->root_dir.'appl/ingreso_pago/ingreso_pago.xml', $labels, "Ingreso Pago ".$cod_ingreso_pago.".pdf", 'logo');
		$this->_load_record();
		return true;
	}
}
class print_ingreso_pago extends reporte {	
	function print_ingreso_pago($sql, $xml, $labels=array(), $titulo, $con_logo, $vuelve_a_presentacion=false) {
		parent::reporte($sql, $xml, $labels, $titulo, $con_logo, $vuelve_a_presentacion);			
	}
	
	function modifica_pdf(&$pdf) {	
			
			
	        $pdf->SetAutoPageBreak(true);
		
			$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
			$result = $db->build_results($this->sql);
			
			$cod_ingreso_pago	=	$result[0]['COD_INGRESO_PAGO'];
			
			//select para factura
			$sql_factura = "SELECT	F.NRO_FACTURA
									,convert(varchar(20), F.FECHA_FACTURA, 103) FECHA_FACTURA
									,F.REFERENCIA
									,dbo.f_factura_get_saldo(F.COD_FACTURA) + IPF.MONTO_ASIGNADO SALDO
									,IPF.MONTO_ASIGNADO
									,((dbo.f_factura_get_saldo(F.COD_FACTURA) + IPF.MONTO_ASIGNADO) - MONTO_ASIGNADO ) SALDO_T
							FROM	INGRESO_PAGO_FACTURA IPF, INGRESO_PAGO IP, FACTURA F
							WHERE	IPF.COD_INGRESO_PAGO = $cod_ingreso_pago --1014
							AND		IPF.TIPO_DOC = 'FACTURA' 
							AND		IPF.COD_DOC = F.COD_FACTURA 
							AND		IP.COD_INGRESO_PAGO = IPF.COD_INGRESO_PAGO
							ORDER BY	F.COD_FACTURA asc";
			$result_factura = $db->build_results($sql_factura);
			//CANTIDAD DE FACTURA
			$count_factura = count($result_factura);	
			
			
			//select para nota venta
			$sql_nota_venta = "SELECT	NV.COD_NOTA_VENTA COD_DOC_NV
										,convert(varchar(20), NV.FECHA_NOTA_VENTA, 103) FECHA_NOTA_VENTA
										,NV.REFERENCIA REFERENCIA_NV
										,dbo.f_nv_get_saldo(NV.COD_NOTA_VENTA) + IPF.MONTO_ASIGNADO SALDO_NV
										,IPF.MONTO_ASIGNADO MONTO_ASIGNADO_NV
										,((dbo.f_nv_get_saldo(NV.COD_NOTA_VENTA) + IPF.MONTO_ASIGNADO ) - IPF.MONTO_ASIGNADO) SALDO_T_NV
								FROM	INGRESO_PAGO_FACTURA IPF, INGRESO_PAGO IP, NOTA_VENTA NV
								WHERE	IPF.COD_INGRESO_PAGO = $cod_ingreso_pago --1012
								AND		IPF.TIPO_DOC = 'NOTA_VENTA' 
								AND		IPF.COD_DOC = NV.COD_NOTA_VENTA 
								AND		IP.COD_INGRESO_PAGO = IPF.COD_INGRESO_PAGO
								ORDER BY	NV.COD_NOTA_VENTA asc";
			$result_nota_venta = $db->build_results($sql_nota_venta);
			//CANTIDAD DE NOTA_VENTA
			$count_nota_venta = count($result_nota_venta);
			
			$y_ini = $pdf->GetY() + 20;
			$y_fa = $pdf->GetY() + 20;
			
			
		
			
			
			//dibujando los TITULOS  para Doctos.	
			$pdf->SetFont('Arial','B',9);
			$pdf->SetTextColor(4, 22, 114);
			$pdf->SetXY(30,$y_fa + 5);
			$pdf->Cell(385,17,'DOCUMENTOS INGRESO PAGO', 'LTR', '','C');
			
			$pdf->SetXY(30,$y_fa + 20);
			$pdf->Cell(65,15,'Tipo Docto.', 'LTB', '','C');
			$pdf->SetXY(95,$y_fa + 20);
			$pdf->Cell(60,15,'Nro Docto.', 'LTB', '','C');
			$pdf->SetXY(155,$y_fa + 20);
			$pdf->Cell(60,15,'Fecha', 'LTB', '','C');
			$pdf->SetXY(215,$y_fa + 20);
			$pdf->Cell(70,15,'Monto Docto.', 'LTRB', '','C');
			$pdf->SetXY(285,$y_fa + 20);
			$pdf->Cell(65,15,'Saldo Docto.', 'TRB', '','C');
			$pdf->SetXY(350,$y_fa + 20);
			$pdf->Cell(65, 15,'Monto Pago', 'TRB', '','C');

			
			//DETERMINAR SI EL SELECT FACTURA TIENE DATOS
			if($count_factura != 0){

				$pdf->SetFont('Arial','',9);		
					//DIBUJANDO LOS ITEMS DE LA FACTURA		
					$suma_factura = 0;
					$pdf->SetRightMargin(90);
					$pdf->SetLeftMargin(90);
					
					$altura_doc = 0;
					
					for($i=0, $ii = 0; $i<$count_factura; $i++, $ii++){
						$altura_doc = $pdf->gety();
					
						
						if ($altura_doc > 710) {
							$ii = 0;
							$y_fa = 90;
							$pdf->AddPage();
						}
						
						$nro_factura		=	$result_factura[$i]['NRO_FACTURA'];
						$fecha_factura		=	$result_factura[$i]['FECHA_FACTURA'];
						$monto_factura		=	number_format($result_factura[$i]['SALDO'], 0, ',', '.');
						$monto_pago_factura	=	$result_factura[$i]['MONTO_ASIGNADO'];
						$saldo_factura		=	number_format($result_factura[$i]['SALDO_T'], 0, ',', '.');
						$monto_pago_factura		=	$result_factura[$i]['MONTO_ASIGNADO'];
						$suma_factura 			=	$suma_factura + $monto_pago_factura;
						$monto_pago_factura	=	number_format($monto_pago_factura, 0, ',', '.');
						
						$pdf->SetXY(30,$y_fa + 35+(15*$ii));
						$pdf->Cell(65,15,'Factura', 'LTB', '','C');
						
						$pdf->SetXY(95,$y_fa + 35+(15*$ii));
						$pdf->Cell(60,15,$nro_factura, 'LTB', '','C');
						
						$pdf->SetXY(155,$y_fa + 35+(15*$ii));
						$pdf->Cell(60,15,$fecha_factura, 'LTRB', '','C');
						
						$pdf->SetXY(215,$y_fa + 35+(15*$ii));
						$pdf->Cell(70,15,$monto_factura, 'LTB', '','R');
						
						$pdf->SetXY(285,$y_fa + 35+(15*$ii));
						$pdf->Cell(65,15,$saldo_factura, 'LTRB', '','R');
						
						$pdf->SetXY(350,$y_fa + 35+(15*$ii));
						$pdf->Cell(65,15,$monto_pago_factura, 'TRB', '','R');
					}
					
					$suma_factura		=	number_format($suma_factura, 0, ',', '.');
					
					
					$pdf->SetFont('Arial','B',9);
					$pdf->SetXY(285,$y_fa + 35+(15*$ii));
					$pdf->Cell(65,15,'TOTAL  $', 'LTRB', '','R');			
					$pdf->SetXY(350,$y_fa + 35+(15*$ii));
					$pdf->Cell(65,15,$suma_factura, 'TRB', '','R');							
			}
			$en_cual_quedo=$pdf->gety();
			
			//DETERMINAR SI EL SELECT NOTA_VENTA TIENE DATOS
			if($count_nota_venta != 0){
				$pdf->SetRightMargin(90);
				$pdf->SetLeftMargin(90);
				//DIBUJANDO LOS ITEMS DE LA NOTA_VENTA
				$suma_nota_venta = 0;
				
				if($count_factura != 0)
					$y_fa = $en_cual_quedo + 15;
				else
					$y_fa = $en_cual_quedo - 20;
				$pdf->SetFont('Arial','',9);
				
				for($i=0, $ii = 0; $i<$count_nota_venta; $i++, $ii++){
					$altura_doc = $pdf->gety();
					if ($altura_doc > 710) {
							$ii = 0;
							$y_fa = 90;
							$pdf->AddPage();
						}
						
						
					$nro_nv			=	$result_nota_venta[$i]['COD_DOC_NV'];
					$fecha_nv		=	$result_nota_venta[$i]['FECHA_NOTA_VENTA'];
					$monto_nv		=	number_format($result_nota_venta[$i]['SALDO_NV'], 0, ',', '.');
					$monto_pago_nv	=	$result_nota_venta[$i]['MONTO_ASIGNADO_NV'];
					$saldo_nv		=	number_format($result_nota_venta[$i]['SALDO_T_NV'], 0, ',', '.');
					$suma_nota_venta 	=	$suma_nota_venta + $monto_pago_nv;
					$monto_pago_nv	=	number_format($result_nota_venta[$i]['MONTO_ASIGNADO_NV'], 0, ',', '.');
					
					$pdf->SetXY(30,$y_fa + 35+(15*$ii));
					$pdf->Cell(65,15,'Nota Venta', 'LTB', '','C');
					$pdf->SetXY(95,$y_fa + 35+(15*$ii));
					$pdf->Cell(60,15,$nro_nv, 'LTB', '','C');
					$pdf->SetXY(155,$y_fa + 35+(15*$ii));
					$pdf->Cell(60,15,$fecha_nv, 'LTRB', '','C');
					$pdf->SetXY(215,$y_fa + 35+(15*$ii));
					$pdf->Cell(70,15,$monto_nv, 'LTB', '','R');
					$pdf->SetXY(285,$y_fa + 35+(15*$ii));
					$pdf->Cell(65,15,$saldo_nv, 'LTRB', '','R');
					$pdf->SetXY(350,$y_fa + 35+(15*$ii));
					$pdf->Cell(65,15,$monto_pago_nv, 'TRB', '','R');				
				}
				
				$suma_nota_venta		=	number_format($suma_nota_venta, 0, ',', '.');
				
				
				$pdf->SetFont('Arial','B',9);
				$pdf->SetXY(285,$y_fa + 35+(15*$ii));
				$pdf->Cell(65,15,'TOTAL  $', 'LTRB', '','R');
				$pdf->SetXY(350,$y_fa + 35+(15*$ii));
				$pdf->Cell(65,15,$suma_nota_venta, 'TRB', '','R');		
			}else{
				
			}

		// ESTADO INGRESO PAGO
			$sql_estado = "select 		IP.COD_ESTADO_INGRESO_PAGO
						   from 		INGRESO_PAGO IP
						   where	    IP.COD_INGRESO_PAGO = $cod_ingreso_pago
						   ";
			$result_estado= $db->build_results($sql_estado);
			
			if ($result_estado[0]['COD_ESTADO_INGRESO_PAGO']== 3) 
			{
				$pdf->SetTextColor(250, 0, 0);
				$pdf->SetXY(470, 120);
				$pdf->Cell(1,1,'DOCUMENTO ANULADO', '', '','L');
			}

	}
}

// Se separa por K_CLIENTE
$file_name = dirname(__FILE__)."/".K_CLIENTE."/class_wi_ingreso_pago.php";
if (file_exists($file_name)) 
	require_once($file_name);
else {
	class wi_ingreso_pago extends wi_ingreso_pago_base {
		function wi_ingreso_pago($cod_item_menu) {
			parent::wi_ingreso_pago_base($cod_item_menu); 
		}
	}
}
?>