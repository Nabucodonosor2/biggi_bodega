<?php
/*
 se volvío a versión anterior, ya que quedó mal implementado cambio al crear las OP esten desmarcadas
 */
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../empresa/class_dw_help_empresa.php");
//se crea error cuando se filtra por JJ, GTE. VENTA, COMERCIAL. por falta de MB
ini_set('memory_limit', '720M');
ini_set('max_execution_time', 900); //900 seconds = 15 minutes

class dw_participacion_orden_pago extends datawindow {
	
	function dw_participacion_orden_pago() {
				
		$sql = "select 'S' SELECCION 
					,COD_ORDEN_PAGO_PARTICIPACION
					,POP.COD_PARTICIPACION
					,OP.COD_ORDEN_PAGO
					,convert(varchar(20), OP.FECHA_ORDEN_PAGO, 103) FECHA_ORDEN_PAGO
					,OP.COD_NOTA_VENTA
					,convert(varchar(20), NV.FECHA_NOTA_VENTA, 103) FECHA_NOTA_VENTA
					,E.NOM_EMPRESA
					,case P.COD_ESTADO_PARTICIPACION
						when 1 /*emitido*/then POP.MONTO_ASIGNADO + dbo.f_op_por_asignar(OP.COD_ORDEN_PAGO)
						else POP.MONTO_ASIGNADO
					 end TOTAL_NETO_POP
					,case P.COD_ESTADO_PARTICIPACION
						when 1 /*emitido*/then POP.MONTO_ASIGNADO + dbo.f_op_por_asignar(OP.COD_ORDEN_PAGO)
						else POP.MONTO_ASIGNADO
					 end TOTAL_NETO_POP_C
					,P.COD_ESTADO_PARTICIPACION
					,OP.COD_TIPO_ORDEN_PAGO
				from PARTICIPACION P, PARTICIPACION_ORDEN_PAGO POP, ORDEN_PAGO OP, NOTA_VENTA NV, EMPRESA E
				where P.COD_PARTICIPACION = {KEY1} and
					POP.COD_PARTICIPACION = P.COD_PARTICIPACION and
					OP.COD_ORDEN_PAGO = POP.COD_ORDEN_PAGO and
					NV.COD_NOTA_VENTA = OP.COD_NOTA_VENTA and
					E.COD_EMPRESA = NV.COD_EMPRESA";
									
		parent::datawindow($sql, 'PARTICIPACION_ORDEN_PAGO', false, false);		
		
		$this->add_control($control = new edit_check_box('SELECCION', 'S', 'N'));
		$control->forzar_js = true;
		$control->set_onChange("asignacion_monto(this);");
		
		$this->add_control(new static_num('TOTAL_NETO_POP'));	
		
		$sql_tipo_op = "SELECT COD_TIPO_ORDEN_PAGO
							,NOM_TIPO_ORDEN_PAGO
						FROM TIPO_ORDEN_PAGO";
										
		$this->add_control(new drop_down_dw('COD_TIPO_ORDEN_PAGO', $sql_tipo_op,125));
		$this->set_entrable('COD_TIPO_ORDEN_PAGO', false);
		
		// computed y accumulate
		$this->set_computed('TOTAL_NETO_POP_C', '[TOTAL_NETO_POP]');
		$this->controls['TOTAL_NETO_POP_C']->type = 'hidden';
		$this->accumulate('TOTAL_NETO_POP_C');
	}

	function insert_row($row=-1) {
		$row = parent::insert_row($row);
		return $row;
	}
	
	function fill_record(&$temp, $record) {	
		parent::fill_record($temp, $record);	
		$COD_ESTADO_PARTICIPACION = $this->get_item(0, 'COD_ESTADO_PARTICIPACION');
		if ($COD_ESTADO_PARTICIPACION != 1){
			$temp->setVar('DISABLE_BUTTON_SELECCION', 'style="display:none"');
			$temp->setVar('DISABLE_BUTTON_TODO', 'style="display:none"');
		}
		else{	
				if ($this->entrable){
					$temp->setVar('DISABLE_BUTTON_SELECCION', '');
					$temp->setVar('DISABLE_BUTTON_TODO', '');
				}
				else{
					$temp->setVar('DISABLE_BUTTON_SELECCION', 'disabled="disabled"');
					$temp->setVar('DISABLE_BUTTON_TODO', 'disabled="disabled"');
				}
		}				
	}
	
	function update($db, $COD_PARTICIPACION, $monto_total)	{				
		$sp = 'spu_participacion_orden_pago';
		$operacion = 'DELETE_ALL';
		$param = "'$operacion', $COD_PARTICIPACION";
					
		if (!$db->EXECUTE_SP($sp, $param)){
			return false;
		}
		
		$suma = 0;
		for ($i = 0; $i < $this->row_count(); $i++){
			$SELECCION = $this->get_item($i, 'SELECCION');
			if ($SELECCION=='N')
				continue;
			$COD_ORDEN_PAGO					= $this->get_item($i, 'COD_ORDEN_PAGO');
			$TOTAL_NETO_POP_C				= $this->get_item($i, 'TOTAL_NETO_POP_C');
			
			if ($suma + $TOTAL_NETO_POP_C > $monto_total)
				$monto_asignado = $monto_total - $suma;
			else
				$monto_asignado = $TOTAL_NETO_POP_C;
			$suma += $monto_asignado;
								
			$operacion = 'INSERT';
			$param = "'$operacion',$COD_PARTICIPACION, $COD_ORDEN_PAGO, $monto_asignado";	
			if (!$db->EXECUTE_SP($sp, $param))
				return false;
		}	
		return true;
	}
}

class dw_pago_participacion extends datawindow {
	function dw_pago_participacion () {
		$sql = "EXEC spdw_faprov_pago {KEY1},'PARTICIPACION'";
		parent::datawindow($sql, 'PARTICIPACION');
		
		$this->add_control(new static_text('PA_COD_FAPROV'));
		$this->add_control(new static_num('PA_TOTAL_NETO'));
		$this->add_control(new static_text('PA_COD_PAGO_FAPROV'));
		$this->add_control(new static_num('PA_MONTO_ASIGNADO'));
	}
}

class wi_participacion extends w_input {
	
	const K_ESTADO_PARTICIPACION_EMITIDA 	= 1;
	const K_ESTADO_PARTICIPACION_CONFIRMADA = 2;
	const K_ESTADO_PARTICIPACION_ANULADA	= 3;
	const K_PARAM_PORC_IVA			= 1;
	const K_PARAM_RET_BH			= 2;
	
	
	function wi_participacion($cod_item_menu){
		
		parent::w_input('participacion', $cod_item_menu);
		$sql = "SELECT COD_PARTICIPACION
				      ,convert(varchar(20), FECHA_PARTICIPACION, 103) FECHA_PARTICIPACION
					  ,P.COD_USUARIO
					  ,U.NOM_USUARIO
				      ,COD_USUARIO_VENDEDOR
					  ,COD_ESTADO_PARTICIPACION
					  ,TIPO_DOCUMENTO
					  ,TIPO_DOCUMENTO TIPO_DOCUMENTO_H
				      ,TOTAL_NETO
				      ,TOTAL_NETO TOTAL_NETO_H
					  ,PORC_IVA
					  ,MONTO_IVA
					  ,MONTO_IVA MONTO_IVA_H
					  ,TOTAL_CON_IVA
					  ,TOTAL_CON_IVA TOTAL_CON_IVA_H
					  ,MOTIVO_ANULA
						----- datos historicos(despliega la fecha en que se cambia el estado_participacion)-----
						,(select TOP 1 convert(varchar(20), LG.FECHA_CAMBIO, 103) +'  '+ convert(varchar(20), LG.FECHA_CAMBIO, 8) FECHA_CAMBIO
							from	LOG_CAMBIO LG, DETALLE_CAMBIO DC
							where	LG.NOM_TABLA = 'PARTICIPACION' and
									LG.KEY_TABLA = convert(varchar, COD_PARTICIPACION) and
									LG.COD_LOG_CAMBIO = DC.COD_LOG_CAMBIO and
									DC.NOM_CAMPO = 'COD_ESTADO_PARTICIPACION' 
									order by LG.FECHA_CAMBIO desc) FECHA_CAMBIO
						----- datos historicos(despliega el usuario que cambia el estado_participacion)-----
						,(select TOP 1 U.NOM_USUARIO
							from	LOG_CAMBIO LG, DETALLE_CAMBIO DC, USUARIO U
							where	LG.NOM_TABLA = 'PARTICIPACION' and
									LG.KEY_TABLA = convert(varchar, COD_PARTICIPACION) and
									LG.COD_LOG_CAMBIO = DC.COD_LOG_CAMBIO and
									LG.COD_USUARIO = U.COD_USUARIO and 
									DC.NOM_CAMPO = 'COD_ESTADO_PARTICIPACION' 
									order by LG.FECHA_CAMBIO desc)USUARIO_CAMBIO
									
						,case COD_ESTADO_PARTICIPACION 
							when 3 then '' 
							else 'none'
						end TR_DISPLAY	
						,dbo.f_get_parametro(1) PORC_IVA_H
						,dbo.f_get_parametro(2) RETENCION_BH_H
						,case TIPO_DOCUMENTO 
							when 'BH' then 'BH' 
							when 'FA' then 'IVA' 
							when 'SUELDO' then '' 
						end LABEL_BH_IVA
						,case TIPO_DOCUMENTO 
							when 'BH' then 'Monto Bruto' 
							when 'FA' then 'Monto Neto' 
							when 'SUELDO' then 'Monto Sueldo' 
						end LABEL_BRUTO_NETO
						,case TIPO_DOCUMENTO 
							when 'BH' then 'Monto Retención' 
							when 'FA' then 'Monto IVA' 
							when 'SUELDO' then '' 
						end LABEL_RETENCION_IVA	
						,case TIPO_DOCUMENTO 
							when 'BH' then 'Total Líquido' 
							when 'FA' then 'Total c/IVA' 
							when 'SUELDO' then '' 
						end LABEL_TOTAL	
						,case TIPO_DOCUMENTO 
							when 'BH' then '' 
							when 'FA' then '' 
							when 'SUELDO' then 'none' 
						end DISPLAY_SUELDO	
						,REFERENCIA	
				FROM PARTICIPACION P, USUARIO U
				where COD_PARTICIPACION = {KEY1}
					and U.COD_USUARIO = P.COD_USUARIO";
				
		$this->dws['dw_participacion'] = new datawindow($sql);
		
		$this->dws['dw_participacion']->add_control(new edit_nro_doc('COD_PARTICIPACION','PARTICIPACION'));
		
		
		$sql_vendedor 			= "select		COD_USUARIO
												,NOM_USUARIO
								from 			USUARIO
								order by		COD_USUARIO";								
		$this->dws['dw_participacion']->add_control(new drop_down_dw('COD_USUARIO_VENDEDOR', $sql_vendedor,125));
		$this->dws['dw_participacion']->set_entrable('COD_USUARIO_VENDEDOR', false);
		
		$sql_estado_participacion 	= "select 	COD_ESTADO_PARTICIPACION
										,NOM_ESTADO_PARTICIPACION
								from 	ESTADO_PARTICIPACION
								where 	COD_ESTADO_PARTICIPACION in (".self::K_ESTADO_PARTICIPACION_EMITIDA.", ".self::K_ESTADO_PARTICIPACION_CONFIRMADA.", ".self::K_ESTADO_PARTICIPACION_ANULADA.")
								order by COD_ESTADO_PARTICIPACION";
		$this->dws['dw_participacion']->add_control(new drop_down_dw('COD_ESTADO_PARTICIPACION', $sql_estado_participacion,125));
		
		
		
		$this->dws['dw_participacion']->add_control(new static_num('TOTAL_NETO'));
		$this->dws['dw_participacion']->add_control(new static_num('PORC_IVA', 1));
		$this->dws['dw_participacion']->add_control(new static_num('MONTO_IVA'));
		$this->dws['dw_participacion']->add_control(new static_num('TOTAL_CON_IVA'));
		
		$this->dws['dw_participacion']->add_control(new edit_text('TOTAL_NETO_H',10,10,'hidden'));
		$this->dws['dw_participacion']->add_control(new edit_text('MONTO_IVA_H',10,10,'hidden'));
		$this->dws['dw_participacion']->add_control(new edit_text('TOTAL_CON_IVA_H',10,10,'hidden'));
		
		$this->dws['dw_participacion']->add_control(new edit_text('TIPO_DOCUMENTO_H',10,10,'hidden'));
		$this->dws['dw_participacion']->add_control(new edit_text('PORC_IVA_H',10,10,'hidden'));
		$this->dws['dw_participacion']->add_control(new edit_text('RETENCION_BH_H',10,10,'hidden'));
	
		$this->dws['dw_participacion']->add_control(new edit_text_upper('REFERENCIA',100,100));

		$this->add_auditoria('COD_ESTADO_PARTICIPACION');
		
		// asigna los mandatorys/
		$this->dws['dw_participacion']->set_mandatory('TIPO_DOCUMENTO', 'Tipo Documento');
		$this->dws['dw_participacion']->set_mandatory('COD_ESTADO_PARTICIPACION', 'Estado');
		
		$this->dws['dw_participacion_orden_pago'] = new dw_participacion_orden_pago();
		//PAGO PARTICIPACION
		$this->dws['dw_pago_participacion'] = new dw_pago_participacion();
	}
	
	function new_record() {
		if (session::is_set('CREA_PARTICIPACION')) {
			$cod_usuario_tipo_op = session::get('CREA_PARTICIPACION');			
			$this->creada_desde($cod_usuario_tipo_op);
			session::un_set('CREA_PARTICIPACION');
			return;
		}		
	}
	
	function load_record() {
		$COD_PARTICIPACION = $this->get_item_wo($this->current_record, 'COD_PARTICIPACION');
		$this->dws['dw_participacion']->retrieve($COD_PARTICIPACION);
		$COD_ESTADO_PARTICIPACION = $this->dws['dw_participacion']->get_item(0, 'COD_ESTADO_PARTICIPACION');
		$tipo_documento = $this->dws['dw_participacion']->get_item(0, 'TIPO_DOCUMENTO');
		$this->dws['dw_pago_participacion']->retrieve($COD_PARTICIPACION);
		$sql_original = $this->dws['dw_participacion_orden_pago']->get_sql();
		if ($tipo_documento=='SUELDO')
			$sql = $sql_original." ORDER BY year(NV.FECHA_NOTA_VENTA) ASC, dbo.f_op_por_asignar(OP.COD_ORDEN_PAGO) asc";
		else 
			$sql = $sql_original." ORDER BY NV.COD_NOTA_VENTA ASC"; 
		$this->dws['dw_participacion_orden_pago']->set_sql($sql);		
		$this->dws['dw_participacion_orden_pago']->retrieve($COD_PARTICIPACION);
		$this->dws['dw_participacion_orden_pago']->set_sql($sql_original);
		
		if ($tipo_documento=='SUELDO') {
			$this->dws['dw_participacion']->remove_control('TOTAL_NETO_H');
			if ($COD_ESTADO_PARTICIPACION == self::K_ESTADO_PARTICIPACION_EMITIDA) {
				$this->dws['dw_participacion']->add_control($control = new edit_num('TOTAL_NETO_H'));
				$control->set_onChange("change_monto_sueldo(this);");
			}
			else
				$this->dws['dw_participacion']->add_control(new static_num('TOTAL_NETO_H'));
		}
			
		$this->b_print_visible 	 = true;
		$this->b_no_save_visible = true;
		$this->b_save_visible 	 = true; 
		$this->b_modify_visible	 = true;
		
		if ($COD_ESTADO_PARTICIPACION == self::K_ESTADO_PARTICIPACION_EMITIDA) {
			$sql = "select 	COD_ESTADO_PARTICIPACION
							,NOM_ESTADO_PARTICIPACION
					from 	ESTADO_PARTICIPACION
					where 	COD_ESTADO_PARTICIPACION in (".self::K_ESTADO_PARTICIPACION_EMITIDA.", ".self::K_ESTADO_PARTICIPACION_CONFIRMADA.", ".self::K_ESTADO_PARTICIPACION_ANULADA.")
					order by COD_ESTADO_PARTICIPACION";
			
			unset($this->dws['dw_participacion']->controls['COD_ESTADO_PARTICIPACION']);
			$this->dws['dw_participacion']->add_control($control = new drop_down_dw('COD_ESTADO_PARTICIPACION',$sql,110));	
			$control->set_onChange("mostrarOcultar_Anula();");
			$this->dws['dw_participacion']->add_control(new edit_text_upper('MOTIVO_ANULA',120, 100));
			
		}
		else if ($COD_ESTADO_PARTICIPACION == self::K_ESTADO_PARTICIPACION_CONFIRMADA){
			
			$sql = "select 	COD_ESTADO_PARTICIPACION
							,NOM_ESTADO_PARTICIPACION
					from 	ESTADO_PARTICIPACION
					where 	COD_ESTADO_PARTICIPACION = ".self::K_ESTADO_PARTICIPACION_CONFIRMADA." or
							COD_ESTADO_PARTICIPACION = ".self::K_ESTADO_PARTICIPACION_ANULADA."
					order by COD_ESTADO_PARTICIPACION";
			
			unset($this->dws['dw_participacion']->controls['COD_ESTADO_PARTICIPACION']);
			$this->dws['dw_participacion']->add_control($control = new drop_down_dw('COD_ESTADO_PARTICIPACION',$sql,110));	
			$control->set_onChange("mostrarOcultar_Anula();");
			$this->dws['dw_participacion']->add_control(new edit_text_upper('MOTIVO_ANULA',120, 100));
			
			$this->dws['dw_participacion_orden_pago']->set_entrable_dw(false);
		}
		else if ($COD_ESTADO_PARTICIPACION == self::K_ESTADO_PARTICIPACION_ANULADA) {
			$this->b_print_visible 	 = false;
			$this->b_no_save_visible = false;
			$this->b_save_visible 	 = false;
			$this->b_modify_visible  = false;
			
			$sql = "select 	COD_ESTADO_PARTICIPACION
							,NOM_ESTADO_PARTICIPACION
					from 	ESTADO_PARTICIPACION
					where	COD_ESTADO_PARTICIPACION = ".self::K_ESTADO_PARTICIPACION_ANULADA."
					order by COD_ESTADO_PARTICIPACION";
			
			unset($this->dws['dw_participacion']->controls['COD_ESTADO_PARTICIPACION']);
			$this->dws['dw_participacion']->add_control(new drop_down_dw('COD_ESTADO_PARTICIPACION',$sql,110));	
		}	
	}

	function goto_record($record) {
		if (!session::is_set("cant_participacion_a_hacer")) 
			parent::goto_record($record);
		else {
			session::un_set("cant_participacion_a_hacer");
			$this->current_record = $record;
			$this->load_record();
			$this->modify_record();
		}
	}
	
	function get_key() {
		return $this->dws['dw_participacion']->get_item(0, 'COD_PARTICIPACION');
	}
	
	function save_record($db) {	
		$COD_PARTICIPACION = $this->get_key();
		$COD_USUARIO = $this->dws['dw_participacion']->get_item(0, 'COD_USUARIO');
		$COD_USUARIO_VENDEDOR = $this->dws['dw_participacion']->get_item(0, 'COD_USUARIO_VENDEDOR');
		$COD_ESTADO_PARTICIPACION = $this->dws['dw_participacion']->get_item(0, 'COD_ESTADO_PARTICIPACION');
		$TIPO_DOCUMENTO = $this->dws['dw_participacion']->get_item(0, 'TIPO_DOCUMENTO');
		$TOTAL_NETO = $this->dws['dw_participacion']->get_item(0, 'TOTAL_NETO_H');
		$PORC_IVA = $this->dws['dw_participacion']->get_item(0, 'PORC_IVA');
		$MONTO_IVA = $this->dws['dw_participacion']->get_item(0, 'MONTO_IVA_H');
		$TOTAL_CON_IVA = $this->dws['dw_participacion']->get_item(0, 'TOTAL_CON_IVA_H');		
		$MOTIVO_ANULA = $this->dws['dw_participacion']->get_item(0, 'MOTIVO_ANULA');
		$REFERENCIA = $this->dws['dw_participacion']->get_item(0, 'REFERENCIA');
		
		$COD_PARTICIPACION = ($COD_PARTICIPACION=='') ? "null" : $COD_PARTICIPACION;		
		$MOTIVO_ANULA = ($MOTIVO_ANULA=='') ? "null" : "'$MOTIVO_ANULA'";	
		$REFERENCIA = ($REFERENCIA=='') ? "null" : "'$REFERENCIA'";	

		if ($TIPO_DOCUMENTO=='SUELDO') {
			$PORC_IVA = 0;
			$MONTO_IVA = 0;
			$TOTAL_CON_IVA = $TOTAL_NETO;
		}
		
		if ($this->is_new_record())
	    	$operacion = 'INSERT';
	    else
	    	$operacion = 'UPDATE';
	    								
		$sp = 'spu_participacion';
	    $param = "'$operacion'
					, $COD_PARTICIPACION
					, $COD_USUARIO
					, $COD_USUARIO_VENDEDOR
					, $COD_ESTADO_PARTICIPACION
					, $TIPO_DOCUMENTO
					, $TOTAL_NETO
					, $PORC_IVA
					, $MONTO_IVA
					, $TOTAL_CON_IVA
					, $MOTIVO_ANULA
					, $REFERENCIA";
		
	    if ($this->is_new_record())
			$cod_estado_anterior = 1;
		else {
			$sql_estado_anterior = "SELECT COD_ESTADO_PARTICIPACION FROM PARTICIPACION WHERE COD_PARTICIPACION = ".$COD_PARTICIPACION;
			$result	= $db->build_results($sql_estado_anterior);
			$cod_estado_anterior = $result[0]['COD_ESTADO_PARTICIPACION'];
		}		
		$cod_estado_actual = $COD_ESTADO_PARTICIPACION;

		if ($db->EXECUTE_SP($sp, $param)){
			if ($this->is_new_record()) {
				$COD_PARTICIPACION = $db->GET_IDENTITY();
				$this->dws['dw_participacion']->set_item(0, 'COD_PARTICIPACION', $COD_PARTICIPACION);		
			}
			
			for ($i=0; $i<$this->dws['dw_participacion_orden_pago']->row_count(); $i++) 
				$this->dws['dw_participacion_orden_pago']->set_item($i, 'COD_PARTICIPACION', $COD_PARTICIPACION);
			
			if (!$this->dws['dw_participacion_orden_pago']->update($db, $COD_PARTICIPACION, $TOTAL_NETO)) 
				return false;
			
			if ($cod_estado_anterior == self::K_ESTADO_PARTICIPACION_EMITIDA  && $cod_estado_actual == self::K_ESTADO_PARTICIPACION_CONFIRMADA ){
				if (!$db->EXECUTE_SP('spu_participacion', "'CONFIRMA', $COD_PARTICIPACION, $COD_USUARIO, $COD_USUARIO_VENDEDOR,$COD_ESTADO_PARTICIPACION,$TIPO_DOCUMENTO,$TOTAL_NETO")) return false;
			}
			return true;			
		}
		return false;					
	}
	
	function creada_desde($cod_usuario_tipo_op) {
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

		$partes = explode("|", $cod_usuario_tipo_op);
		$cod_usuario  = $partes[0];
		$cod_tipo_op  = $partes[1];
		$cod_centro_c = $partes[2];
		$es_sueldo	  = $partes[3];

		$sql_cod_empresa = "SELECT COD_EMPRESA 
							FROM USUARIO
							WHERE COD_USUARIO = ".$cod_usuario;
		$result = $db->build_results($sql_cod_empresa);
		$cod_empresa = $result[0]['COD_EMPRESA'];
			
		if ($es_sueldo=='S') {
			$tipo_participacion = 'SUELDO';
			$this->dws['dw_participacion']->remove_control('TOTAL_NETO');
			$this->dws['dw_participacion']->remove_control('TOTAL_NETO_H');
			$this->dws['dw_participacion']->add_control($control = new edit_num('TOTAL_NETO_H'));
			$control->set_onChange("change_monto_sueldo(this);");
		}
		else {
			$sql_tipo_participacion = "SELECT TIPO_PARTICIPACION 
									FROM EMPRESA
									WHERE COD_EMPRESA = ".$cod_empresa;
			$result = $db->build_results($sql_tipo_participacion);
			$tipo_participacion = $result[0]['TIPO_PARTICIPACION'];
			if ($tipo_participacion == '')		$tipo_participacion = 'FA';
		}

		$sql_crea_desde = "SELECT null COD_PARTICIPACION
								,convert(nvarchar, getdate(), 103) FECHA_PARTICIPACION
								,".$this->cod_usuario." COD_USUARIO
								,NOM_USUARIO
								,".$cod_usuario." COD_USUARIO_VENDEDOR		
								,".self::K_ESTADO_PARTICIPACION_EMITIDA." COD_ESTADO_PARTICIPACION			
								,'".$tipo_participacion."' TIPO_DOCUMENTO					
								,'".$tipo_participacion."' TIPO_DOCUMENTO_H
								,0 TOTAL_NETO
								,0 TOTAL_NETO_H
								,case '".$tipo_participacion."' 
									when 'BH' then dbo.f_get_parametro(2) 
									when 'FA' then dbo.f_get_parametro(1)
									when 'SUELDO' then '0'
								end PORC_IVA
								,0 MONTO_IVA
								,0 MONTO_IVA_H
								,0 TOTAL_CON_IVA
								,0 TOTAL_CON_IVA_H
								,null MOTIVO_ANULA			
								,'none' TR_DISPLAY	
								,dbo.f_get_parametro(1) PORC_IVA_H
								,dbo.f_get_parametro(2) RETENCION_BH_H
								,case '".$tipo_participacion."' 
									when 'BH' then 'BH' 
									when 'FA' then 'IVA' 
									when 'SUELDO' then '' 
								end LABEL_BH_IVA
								,case '".$tipo_participacion."' 
									when 'BH' then 'Monto Bruto' 
									when 'FA' then 'Monto Neto' 
									when 'SUELDO' then 'Monto Sueldo' 
								end LABEL_BRUTO_NETO
								,case '".$tipo_participacion."' 
									when 'BH' then 'Monto Retención' 
									when 'FA' then 'Monto IVA' 
									when 'SUELDO' then '' 
								end LABEL_RETENCION_IVA
								,case '".$tipo_participacion."' 
									when 'BH' then 'Total Líquido' 
									when 'FA' then 'Total c/IVA' 
									when 'SUELDO' then '' 
								end LABEL_TOTAL
								,case '".$tipo_participacion."' 
									when 'BH' then '' 
									when 'FA' then '' 
									when 'SUELDO' then 'none' 
								end DISPLAY_SUELDO	
								,null REFERENCIA	
							FROM USUARIO
							WHERE COD_USUARIO = ".$cod_usuario;
			$result = $db-> build_results($sql_crea_desde);
			$porc_iva = $result[0]['PORC_IVA'];
			
			$sql = $this->dws['dw_participacion']->get_sql();
			$this->dws['dw_participacion']->set_sql($sql_crea_desde);
			$this->dws['dw_participacion']->retrieve($cod_usuario);
			$this->dws['dw_participacion']->set_sql($sql);

			if ($cod_centro_c == 0){
				$select_centro_costo = "";	
			}
			else if ($cod_centro_c == '001'){
				$select_centro_costo = "and NV.COD_EMPRESA NOT IN (SELECT COD_EMPRESA FROM CENTRO_COSTO_EMPRESA WHERE COD_CENTRO_COSTO <> '".$cod_centro_c."')";	
			}else{
				$select_centro_costo = "and NV.COD_EMPRESA IN (SELECT COD_EMPRESA FROM CENTRO_COSTO_EMPRESA WHERE COD_CENTRO_COSTO = '".$cod_centro_c."')";
			}	
						
			$sql_item_crea_desde = "select 'N' SELECCION 
						,null COD_ORDEN_PAGO_PARTICIPACION 
						,null COD_PARTICIPACION 
						,COD_ORDEN_PAGO 
						,convert(nvarchar, FECHA_ORDEN_PAGO, 103) FECHA_ORDEN_PAGO 
						,OP.COD_NOTA_VENTA 
						,convert(nvarchar, FECHA_NOTA_VENTA, 103) FECHA_NOTA_VENTA
						,E.NOM_EMPRESA 
						,dbo.f_op_por_asignar(OP.COD_ORDEN_PAGO) TOTAL_NETO_POP 
						,dbo.f_op_por_asignar(OP.COD_ORDEN_PAGO) TOTAL_NETO_POP_C 
						,".self::K_ESTADO_PARTICIPACION_EMITIDA." COD_ESTADO_PARTICIPACION 
						,COD_TIPO_ORDEN_PAGO 
				from ORDEN_PAGO OP, NOTA_VENTA NV, EMPRESA E
				where dbo.f_op_por_asignar(OP.COD_ORDEN_PAGO) <> 0
				and OP.COD_EMPRESA = ".$cod_empresa."
				and ($cod_tipo_op = 99 or COD_TIPO_ORDEN_PAGO = $cod_tipo_op)
				and NV.COD_NOTA_VENTA = OP.COD_NOTA_VENTA
				and E.COD_EMPRESA = NV.COD_EMPRESA
				".$select_centro_costo;

			if ($es_sueldo=='S') 
				$sql_item_crea_desde .= " ORDER BY year(NV.FECHA_NOTA_VENTA) ASC, dbo.f_op_por_asignar(OP.COD_ORDEN_PAGO) asc";
			else
				$sql_item_crea_desde .= " ORDER BY NV.COD_NOTA_VENTA ASC";
				
			$sql = $this->dws['dw_participacion_orden_pago']->get_sql();
			$this->dws['dw_participacion_orden_pago']->set_sql($sql_item_crea_desde);
			$this->dws['dw_participacion_orden_pago']->retrieve($cod_usuario);
			$this->dws['dw_participacion_orden_pago']->set_sql($sql);
			

			$this->dws['dw_participacion_orden_pago']->set_item(0, 'SUM_TOTAL_NETO_POP_C', 0);
			
			///calcula totales participacion
			$result = $db-> build_results($sql_item_crea_desde);
			$total_neto = 0;
   			for($i=0; $i<count($result); $i++)	
   				$total_neto = $total_neto + $result[$i]['TOTAL_NETO_POP'];	
   			
   			$monto_iva = round($total_neto * $porc_iva/100);
			if ($tipo_participacion == 'BH')
				$total_con_iva = $total_neto - $monto_iva;
			else if ($tipo_participacion == 'FA')
				$total_con_iva = $total_neto + $monto_iva;
			else if ($tipo_participacion == 'SUELDO')
				$total_con_iva = $total_neto;
   			
   			/*
			$this->dws['dw_participacion']->set_item(0, 'TOTAL_NETO', $total_neto);
			$this->dws['dw_participacion']->set_item(0, 'TOTAL_NETO_H', $total_neto);
				
			$this->dws['dw_participacion']->set_item(0, 'MONTO_IVA', $monto_iva);
			$this->dws['dw_participacion']->set_item(0, 'MONTO_IVA_H', $monto_iva);
			
			$this->dws['dw_participacion']->set_item(0, 'TOTAL_CON_IVA', $total_con_iva);
			$this->dws['dw_participacion']->set_item(0, 'TOTAL_CON_IVA_H', $total_con_iva);
			*/
	}	
	
	function print_record() {	
		$cod_participacion	= $this->get_key();
		$sql= "select	P.COD_PARTICIPACION
						,convert(varchar(20), GETDATE(), 103) FECHA
						,convert(varchar(20), P.FECHA_PARTICIPACION, 103) FECHA_PARTICIPACION
						,U.NOM_USUARIO
						,(SELECT NOM_USUARIO FROM USUARIO WHERE COD_USUARIO = P.COD_USUARIO_VENDEDOR) NOM_VENDEDOR
						,P.TIPO_DOCUMENTO
						,P.PORC_IVA
						,P.TOTAL_NETO
						,P.MONTO_IVA
						,P.TOTAL_CON_IVA
						----- datos historicos(despliega la fecha en que se cambia el estado_participacion)-----
						,(select TOP 1 convert(varchar(20), LG.FECHA_CAMBIO, 103) +'  '+ convert(varchar(20), LG.FECHA_CAMBIO, 8) FECHA_CAMBIO
							from	LOG_CAMBIO LG, DETALLE_CAMBIO DC
							where	LG.NOM_TABLA = 'PARTICIPACION' and
									LG.KEY_TABLA = convert(varchar, P.COD_PARTICIPACION) and
									LG.COD_LOG_CAMBIO = DC.COD_LOG_CAMBIO and
									DC.NOM_CAMPO = 'COD_ESTADO_PARTICIPACION' 
									order by LG.FECHA_CAMBIO desc) FECHA_CAMBIO
						----- datos historicos(despliega el usuario que cambia el estado_participacion)-----
						,(select TOP 1 U.NOM_USUARIO
							from	LOG_CAMBIO LG, DETALLE_CAMBIO DC, USUARIO U
							where	LG.NOM_TABLA = 'PARTICIPACION' and
									LG.KEY_TABLA = convert(varchar, P.COD_PARTICIPACION) and
									LG.COD_LOG_CAMBIO = DC.COD_LOG_CAMBIO and
									LG.COD_USUARIO = U.COD_USUARIO and 
									DC.NOM_CAMPO = 'COD_ESTADO_PARTICIPACION' 
									order by LG.FECHA_CAMBIO desc)USUARIO_CAMBIO
						,OP.COD_ORDEN_PAGO
						,convert(varchar(20), OP.FECHA_ORDEN_PAGO, 103) FECHA_ORDEN_PAGO
						,OP.COD_NOTA_VENTA
						,convert(varchar(20), NV.FECHA_NOTA_VENTA, 103) FECHA_NOTA_VENTA
						,E.NOM_EMPRESA
						,POP.MONTO_ASIGNADO TOTAL_NETO_POP
						,case TIPO_DOCUMENTO 
							when 'BH' then 'BH' 
							when 'FA' then 'IVA' 
							when 'SUELDO' then 'IVA' 
						end LABEL_BH_IVA
						,case TIPO_DOCUMENTO 
							when 'BH' then 'Monto Bruto' 
							when 'FA' then 'Monto Neto' 
							when 'SUELDO' then 'Sueldo' 
						end LABEL_BRUTO_NETO
						,case TIPO_DOCUMENTO 
							when 'BH' then 'Monto Retención' 
							when 'FA' then 'Monto IVA' 
							when 'SUELDO' then 'IVA' 
						end LABEL_RETENCION_IVA	
						,case TIPO_DOCUMENTO 
							when 'BH' then 'Total Líquido' 
							when 'FA' then 'Total c/IVA' 
							when 'SUELDO' then 'Total' 
						end LABEL_TOTAL
				from	PARTICIPACION P, PARTICIPACION_ORDEN_PAGO POP, ORDEN_PAGO OP
						, NOTA_VENTA NV, EMPRESA E, USUARIO U
				where	P.COD_PARTICIPACION = $cod_participacion
				AND		U.COD_USUARIO = P.COD_USUARIO
				AND		POP.COD_PARTICIPACION = P.COD_PARTICIPACION
				AND		OP.COD_ORDEN_PAGO = POP.COD_ORDEN_PAGO
				AND		NV.COD_NOTA_VENTA = OP.COD_NOTA_VENTA
				AND		E.COD_EMPRESA = NV.COD_EMPRESA";
				
		$labels = array();
		$labels['strCOD_PARTICIPACION'] = $cod_participacion;
		$rpt = new rpt_print_participacion($sql, $this->root_dir.'appl/participacion/participacion.xml', $labels, "Participacion".$cod_participacion, 0);
		$this->_load_record();
		return true;
	}
}

class rpt_print_participacion extends reporte {
	const K_ESTADO_FAPROV_INGRESADA 		= 1;	

	function rpt_print_participacion($sql, $xml, $labels=array(), $titulo, $con_logo, $vuelve_a_presentacion=false) {
		parent::reporte($sql, $xml, $labels, $titulo, $con_logo, $vuelve_a_presentacion);		
	}

	function modifica_pdf(&$pdf) {
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$result = $db->build_results($this->sql);
		
	}
}
?>