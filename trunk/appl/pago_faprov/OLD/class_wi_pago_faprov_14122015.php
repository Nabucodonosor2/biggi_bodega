<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../empresa/class_dw_help_empresa.php");
require_once(dirname(__FILE__)."/".K_CLIENTE."/rpt_pago_proveedor.php");
ini_set('max_execution_time', 900); //900 seconds = 15 minutes

class dw_pago_faprov_faprov extends datawindow {
	function dw_pago_faprov_faprov() {		
		$sql = "SELECT 'S' SELECCION
						,PFF.COD_PAGO_FAPROV_FAPROV
						,PFF.COD_PAGO_FAPROV
						,PFF.COD_FAPROV
						,F.NRO_FAPROV
						,convert(varchar(20), F.FECHA_FAPROV, 103) FECHA_FAPROV
						,F.TOTAL_CON_IVA TOTAL_CON_IVA_FA
						,F.TOTAL_CON_IVA TOTAL_CON_IVA_FA_H
						,dbo.f_pago_faprov_get_monto_ncprov(F.COD_FAPROV)MONTO_NCPROV
						,dbo.f_pago_faprov_get_monto_ncprov(F.COD_FAPROV)MONTO_NCPROV_H	
						,dbo.f_pago_faprov_get_por_asignar(F.COD_FAPROV) + PFF.MONTO_ASIGNADO SALDO_SIN_PAGO_FAPROV
						,dbo.f_pago_faprov_get_por_asignar(F.COD_FAPROV) + PFF.MONTO_ASIGNADO SALDO_SIN_PAGO_FAPROV_H 
						,PFF.MONTO_ASIGNADO
						,dbo.f_pago_faprov_get_pago_ant(F.COD_FAPROV) PAGO_ANTERIOR
						,CC.NOM_CUENTA_CORRIENTE
						,CC.COD_CUENTA_CORRIENTE COD_CUENTA_CORRIENTE_H
						,'' DISPLAY_TR
				FROM 	PAGO_FAPROV_FAPROV PFF
						, FAPROV F left outer join CUENTA_COMPRA c on c.COD_CUENTA_COMPRA = f.COD_CUENTA_COMPRA
								   left outer join CUENTA_CORRIENTE cc on c.COD_CUENTA_CORRIENTE = cc.COD_CUENTA_CORRIENTE
						, PAGO_FAPROV PF
				WHERE 	PFF.COD_PAGO_FAPROV = {KEY1} AND
						F.COD_FAPROV = PFF.COD_FAPROV AND
						PF.COD_PAGO_FAPROV = PFF.COD_PAGO_FAPROV
				ORDER BY F.NRO_FAPROV";
					
					
		parent::datawindow($sql, 'PAGO_FAPROV_FAPROV', true, true);	
		
		$this->add_control($control = new edit_check_box('SELECCION', 'S', 'N'));
		$control->set_onChange("asignacion_monto(this);");
		
		$this->add_control(new static_num('TOTAL_CON_IVA_FA'));
		$this->add_control(new static_num('MONTO_NCPROV'));
		$this->add_control(new static_num('PAGO_ANTERIOR'));
		$this->add_control(new edit_precio('PAGO_ANTERIOR_H',10,10));
		$this->controls['PAGO_ANTERIOR_H']->type = 'hidden';
		
		$this->add_control(new static_num('SALDO_SIN_PAGO_FAPROV'));
		$this->add_control(new edit_precio('SALDO_SIN_PAGO_FAPROV_H',10,10));
		$this->controls['SALDO_SIN_PAGO_FAPROV_H']->type = 'hidden';
		
		$this->add_control(new edit_text('COD_CUENTA_CORRIENTE_H',10,10,'hidden'));
		$this->add_control(new edit_text('MONTO_NCPROV_H',10,10,'hidden'));
		
		$this->add_control($control = new edit_precio('MONTO_ASIGNADO',10, 10));
		$control->forzar_js = true;	// para que agregue el js aún cuando este hidden el control
		$control->set_onChange("valida_asignacion(get_num_rec_field(this.id));");
		/*
		$this->set_computed('MONTO_ASIGNADO_C', '[MONTO_ASIGNADO]');
		$this->controls['MONTO_ASIGNADO_C']->type = 'hidden';
		
		$this->accumulate('MONTO_ASIGNADO_C', 'copia_suma_a_monto_doc();');
		*/
		// asigna los mandatorys
		$this->set_mandatory('MONTO_ASIGNADO', 'Monto Asignado');
	
	}
	function insert_row($row=-1) {
		$row = parent::insert_row($row);
		return $row;
	}
	function update($db, $COD_PAGO_FAPROV)	{
		$sp = 'spu_pago_faprov_faprov';
		$operacion = 'DELETE_ALL';
		$param = "'$operacion',null, $COD_PAGO_FAPROV";			
		if (!$db->EXECUTE_SP($sp, $param)){
			return false;
		}	
		
		for ($i = 0; $i < $this->row_count(); $i++){
			$statuts = $this->get_status_row($i);
			if ($statuts == K_ROW_NOT_MODIFIED || $statuts == K_ROW_NEW){
			//	continue;
			}
			$SELECCION = $this->get_item($i, 'SELECCION');
			if ($SELECCION=='N')
				continue;
				
			$COD_PAGO_FAPROV_FAPROV 	= $this->get_item($i, 'COD_PAGO_FAPROV_FAPROV');
			$COD_PAGO_FAPROV			= $this->get_item($i, 'COD_PAGO_FAPROV');
			$COD_FAPROV		 			= $this->get_item($i, 'COD_FAPROV');
			$MONTO_ASIGNADO				= $this->get_item($i, 'MONTO_ASIGNADO');
	
			$COD_PAGO_FAPROV_FAPROV = ($COD_PAGO_FAPROV_FAPROV =='') ? "null" : $COD_PAGO_FAPROV_FAPROV;
			
			$operacion = 'INSERT';
			$param = "'$operacion',$COD_PAGO_FAPROV_FAPROV, $COD_PAGO_FAPROV, $COD_FAPROV, $MONTO_ASIGNADO";		
			
			if (!$db->EXECUTE_SP($sp, $param))
				return false;
		
		}	
		return true;
	}

}


class dw_pago_faprov extends dw_help_empresa{
	
	const K_ESTADO_PAGO_FAPROV_EMITIDA 		= 1;
	const K_ESTADO_PAGO_FAPROV_IMPRESA		= 2;
	const K_ESTADO_PAGO_FAPROV_ANULADA		= 3;
	
	const K_TIPO_PAGO_FAPROV_CHEQUE			= 1;
	const K_TIPO_PAGO_FAPROV_TRANSFERENCIA	= 2;
	
	
	
	function dw_pago_faprov(){
		$sql = "SELECT PF.COD_PAGO_FAPROV
						,convert(varchar(20), PF.FECHA_PAGO_FAPROV, 103) FECHA_PAGO_FAPROV
						,PF.COD_USUARIO
						,PF.COD_EMPRESA
						,PF.COD_EMPRESA COD_EMPRESA_H
						,PF.NRO_DOCUMENTO
						,convert(varchar(20), PF.FECHA_DOCUMENTO, 103) FECHA_DOCUMENTO
						,PF.MONTO_DOCUMENTO
						,PF.MONTO_DOCUMENTO MONTO_DOCUMENTO_H
						,PF.MONTO_DOCUMENTO MONTO_DOCUMENTO_S
						,PF.PAGUESE_A
						,convert(varchar(20), PF.FECHA_ANULA, 103) +'  '+ convert(varchar(20), PF.FECHA_ANULA, 8)FECHA_ANULA
						,PF.MOTIVO_ANULA
						,PF.COD_USUARIO_ANULA
						,PF.ES_NOMINATIVO
						,PF.ES_CRUZADO
						,EPF.NOM_ESTADO_PAGO_FAPROV
						,PF.COD_ESTADO_PAGO_FAPROV
						----- datos historicos(despliega la fecha en que se cambia el estado_faprov)-----
						,(select TOP 1 convert(varchar(20), LG.FECHA_CAMBIO, 103) +'  '+ convert(varchar(20), LG.FECHA_CAMBIO, 8) FECHA_CAMBIO
							from	LOG_CAMBIO LG, DETALLE_CAMBIO DC
							where	LG.NOM_TABLA = 'PAGO_FAPROV' and
									LG.KEY_TABLA = convert(varchar, PF.COD_PAGO_FAPROV) and
									LG.COD_LOG_CAMBIO = DC.COD_LOG_CAMBIO and
									DC.NOM_CAMPO = 'COD_ESTADO_PAGO_FAPROV' 
									order by LG.FECHA_CAMBIO desc) FECHA_CAMBIO
						----- datos historicos(despliega el usuario que cambia el estado_faprov)-----
						,(select TOP 1 U.NOM_USUARIO
							from	LOG_CAMBIO LG, DETALLE_CAMBIO DC, USUARIO U
							where	LG.NOM_TABLA = 'PAGO_FAPROV' and
									LG.KEY_TABLA = convert(varchar, PF.COD_PAGO_FAPROV) and
									LG.COD_LOG_CAMBIO = DC.COD_LOG_CAMBIO and
									LG.COD_USUARIO = U.COD_USUARIO and 
									DC.NOM_CAMPO = 'COD_ESTADO_PAGO_FAPROV' 
									order by LG.FECHA_CAMBIO desc)USUARIO_CAMBIO
						,case PF.COD_ESTADO_PAGO_FAPROV
							when ".self::K_ESTADO_PAGO_FAPROV_ANULADA." then '' 
							else 'none'
						end TR_DISPLAY	
						,case PF.COD_TIPO_PAGO_FAPROV
							when ".self::K_TIPO_PAGO_FAPROV_CHEQUE." then '' 
							else 'none'
						end TD_DISPLAY		
						,E.RUT
						,E.DIG_VERIF
						,E.NOM_EMPRESA
						,E.ALIAS
						,PF.COD_TIPO_PAGO_FAPROV
						,TPF.NOM_TIPO_PAGO_FAPROV
						,PF.COD_CUENTA_CORRIENTE
						,PF.COD_CUENTA_CORRIENTE COD_CUENTA_CORRIENTE_HI	
						,CC.NOM_CUENTA_CORRIENTE
						,U.NOM_USUARIO
						,dbo.f_monto_nc_prov(PF.COD_PAGO_FAPROV) MONTO_NC
						,dbo.f_monto_nc_prov(PF.COD_PAGO_FAPROV) MONTO_NC_H
						,dbo.f_ncprov_string(PF.COD_PAGO_FAPROV) COD_NCPROV_S
						,0 TOTAL_MONTO_NC
						,0 TOTAL_MONTO_NC_H
				FROM	PAGO_FAPROV PF
						, EMPRESA E, TIPO_PAGO_FAPROV TPF,
						USUARIO U, CUENTA_CORRIENTE CC, ESTADO_PAGO_FAPROV EPF
				WHERE	COD_PAGO_FAPROV = {KEY1} AND
						E.COD_EMPRESA = PF.COD_EMPRESA AND
						U.COD_USUARIO = PF.COD_USUARIO AND 
						CC.COD_CUENTA_CORRIENTE = PF.COD_CUENTA_CORRIENTE AND
						TPF.COD_TIPO_PAGO_FAPROV = PF.COD_TIPO_PAGO_FAPROV AND
						EPF.COD_ESTADO_PAGO_FAPROV = PF.COD_ESTADO_PAGO_FAPROV";

		
		// DATAWINDOWS HELP_EMPRESA
		parent::dw_help_empresa($sql, '', false, false, 'P');	// El último parametro indica que solo acepta proveedores
		
		// DATOS GENERALES
		$this->add_control(new edit_nro_doc('COD_PAGO_FAPROV','PAGO_FAPROV'));
		
		
		$sql_tipo_pago_faprov   	= "	select		COD_TIPO_PAGO_FAPROV
													,NOM_TIPO_PAGO_FAPROV
									from 			TIPO_PAGO_FAPROV
									order by		COD_TIPO_PAGO_FAPROV";								
		$this->add_control($control = new drop_down_dw('COD_TIPO_PAGO_FAPROV', $sql_tipo_pago_faprov,125));
		$control->set_onChange("mostrarOcultar_tipo_doc();");
		
		$sql_cuenta_corriente 		= "	select		COD_CUENTA_CORRIENTE
													,NOM_CUENTA_CORRIENTE
									from 			CUENTA_CORRIENTE
									order by		COD_CUENTA_CORRIENTE";								
		$this->add_control($control = new drop_down_dw('COD_CUENTA_CORRIENTE', $sql_cuenta_corriente,125));
		
		$this->add_control(new edit_check_box('ES_NOMINATIVO', 'S', 'N', 'Nominativo'));
		$this->add_control(new edit_check_box('ES_CRUZADO', 'S', 'N', 'Cruzado'));
		
		$this->add_control(new edit_text('COD_ESTADO_PAGO_FAPROV',10,10, 'hidden'));
		$this->add_control(new edit_text('COD_EMPRESA_H',10,10, 'hidden'));
		$this->add_control(new edit_text('COD_CUENTA_CORRIENTE_HI',10,10, 'hidden'));
		
		$this->add_control(new static_text('NOM_ESTADO_PAGO_FAPROV'));

		$this->add_control(new edit_num('NRO_DOCUMENTO',10, 10, 0, true, false, false));
		$this->add_control(new edit_text_upper('PAGUESE_A',35,50));
		
		$this->add_control(new edit_date('FECHA_DOCUMENTO'));
		
		$this->add_control(new static_num('MONTO_DOCUMENTO'));
		$this->add_control(new static_num('MONTO_NC'));
		$this->add_control(new static_num('MONTO_DOCUMENTO_S'));
		$this->add_control(new static_num('TOTAL_MONTO_NC'));
		$this->add_control(new edit_text_hidden('MONTO_NC_H'));
		$this->add_control(new edit_text_hidden('MONTO_DOCUMENTO_H'));
		$this->add_control(new edit_text_hidden('COD_NCPROV_S'));
		$this->add_control(new edit_text_hidden('TOTAL_MONTO_NC_H'));
				
		// usuario anulación
		$sql = "select 	COD_USUARIO
						,NOM_USUARIO
				from USUARIO";
		$this->add_control(new drop_down_dw('COD_USUARIO_ANULA',$sql,150));	
		$this->set_entrable('COD_USUARIO_ANULA', false);
		
		$this->add_control(new edit_text('USUARIO_CAMBIO',10,10));
		$this->set_entrable('USUARIO_CAMBIO', false);
		
		$this->add_control(new edit_text('FECHA_ANULA',10,10));
		$this->set_entrable('FECHA_ANULA', false);
		
		$this->add_control(new edit_text('FECHA_CAMBIO',10,10));
		$this->set_entrable('FECHA_CAMBIO', false);
		
		$this->set_entrable('RUT', false);
		$this->set_entrable('COD_EMPRESA', false);
		$this->set_entrable('NOM_EMPRESA', false);
		$this->set_entrable('ALIAS', false);
		$this->set_entrable('COD_CUENTA_CORRIENTE', false);
		
		
		// asigna los mandatorys/
		$this->set_mandatory('COD_TIPO_PAGO_FAPROV', 'Tipo Documento');
		$this->set_mandatory('COD_CUENTA_CORRIENTE', 'Cuenta Corriente');
		$this->set_mandatory('NRO_DOCUMENTO', 'Nº Documento');
		$this->set_mandatory('FECHA_DOCUMENTO', 'Fecha Documento');
		$this->set_mandatory('PAGUESE_A', 'Paguese a');

	}
	
	function fill_record(&$temp, $record) {	
		parent::fill_record($temp, $record);
		
			$COD_PAGO_FAPROV = $this->get_item(0, 'COD_PAGO_FAPROV');
			//$COD_ESTADO_PAGO_FAPROV = $this->get_item(0, 'COD_ESTADO_NCPROV');
			
			if (($COD_PAGO_FAPROV !=''))//or($COD_ESTADO_PAGO_FAPROV != 1))  //
				$temp->setVar('DISABLE_BUTTON', 'style="display:none"');
			else{	
					if ($this->entrable){
						$temp->setVar('DISABLE_BUTTON', '');
						$temp->setVar('DISABLE_BUTTON_NC', '');
					}else{
						$temp->setVar('DISABLE_BUTTON', 'disabled="disabled"');
						$temp->setVar('DISABLE_BUTTON_NC', 'disabled="disabled"');
					}
			}

			if ($this->entrable)
				$temp->setVar('DISABLE_BUTTON_NC', '');
			else
				$temp->setVar('DISABLE_BUTTON_NC', 'disabled="disabled"');
			
	}
	
}

class wi_pago_faprov_base extends w_input {
	const K_ESTADO_PAGO_FAPROV_EMITIDA 		= 1;
	const K_ESTADO_PAGO_FAPROV_IMPRESA		= 2;
	const K_ESTADO_PAGO_FAPROV_ANULADA		= 3;
	
	const K_TIPO_PAGO_FAPROV_CHEQUE			= 1;
	const K_TIPO_PAGO_FAPROV_TRANSFERENCIA	= 2;	
	
	function wi_pago_faprov_base($cod_item_menu) {
		parent::w_input('pago_faprov', $cod_item_menu);
		
		
		$this->dws['dw_pago_faprov'] = new dw_pago_faprov();
		
		// DATAWINDOWS NCPROV_FAPROV
		$this->dws['dw_pago_faprov_faprov'] = new dw_pago_faprov_faprov();
		
		//auditoria Solicitado por IS.
		$this->add_auditoria('COD_TIPO_PAGO_FAPROV');
		$this->add_auditoria('COD_CUENTA_CORRIENTE');
		$this->add_auditoria('NRO_DOCUMENTO');
		$this->add_auditoria('PAGUESE_A');
		$this->add_auditoria('FECHA_DOCUMENTO');		
		$this->add_auditoria('COD_ESTADO_PAGO_FAPROV');

		//al momento de crear un nuevo registro se iniciara en rut de empresa proveedor
		$this->set_first_focus('RUT');
	}
	
	function new_record() {
		$this->dws['dw_pago_faprov']->insert_row();
		$this->dws['dw_pago_faprov']->set_item(0, 'TR_DISPLAY', 'none');
		$this->dws['dw_pago_faprov']->set_item(0, 'FECHA_PAGO_FAPROV', substr($this->current_date_time(), 0, 16));
		$this->dws['dw_pago_faprov']->set_item(0, 'COD_USUARIO', $this->cod_usuario);
		$this->dws['dw_pago_faprov']->set_item(0, 'NOM_USUARIO', $this->nom_usuario);
		$this->dws['dw_pago_faprov']->set_item(0, 'COD_ESTADO_PAGO_FAPROV', self::K_ESTADO_PAGO_FAPROV_EMITIDA);
		$this->dws['dw_pago_faprov']->set_item(0, 'NOM_ESTADO_PAGO_FAPROV', 'EMITIDA');
		$this->dws['dw_pago_faprov']->set_item(0, 'COD_TIPO_PAGO_FAPROV', '1');
		$this->dws['dw_pago_faprov']->set_item(0, 'COD_CUENTA_CORRIENTE', '1');	//comercial
		$this->dws['dw_pago_faprov']->set_item(0, 'FECHA_DOCUMENTO', $this->current_date());
		$this->dws['dw_pago_faprov']->set_item(0, 'ES_NOMINATIVO', 'S');
		$this->dws['dw_pago_faprov']->set_item(0, 'ES_CRUZADO', 'S');
		$this->dws['dw_pago_faprov']->set_item(0, 'TD_DISPLAY', '');
		$this->dws['dw_pago_faprov']->set_item(0, 'MONTO_NC', 0);

		$this->dws['dw_pago_faprov_faprov']->controls['MONTO_ASIGNADO']->set_readonly(true);
		
		if (session::is_set('ADD_VALORES')) {
			$valores = session::get('ADD_VALORES');
			$array = explode('|', $valores);
			$this->carga_pagos($array[0],$array[1]);
			session::un_set('ADD_VALORES');	
		}
	}
	
	function load_record() {
		$cod_pago_faprov = $this->get_item_wo($this->current_record, 'COD_PAGO_FAPROV');
		$this->dws['dw_pago_faprov']->retrieve($cod_pago_faprov);
		$cod_empresa = $this->dws['dw_pago_faprov']->get_item(0, 'COD_EMPRESA');
		
		$COD_TIPO_PAGO_FAPROV = $this->dws['dw_pago_faprov']->get_item(0, 'COD_TIPO_PAGO_FAPROV');
		$COD_ESTADO_PAGO_FAPROV = $this->dws['dw_pago_faprov']->get_item(0, 'COD_ESTADO_PAGO_FAPROV');
		
		$this->b_print_visible 	 = true;
		$this->b_no_save_visible = true;
		$this->b_save_visible 	 = true;
		$this->b_modify_visible	 = true;
		
		$this->dws['dw_pago_faprov_faprov']->retrieve($cod_pago_faprov);
		
		if ($COD_ESTADO_PAGO_FAPROV == self::K_ESTADO_PAGO_FAPROV_EMITIDA) {
			
			unset($this->dws['dw_pago_faprov']->controls['COD_ESTADO_PAGO_FAPROV']);
			$this->dws['dw_pago_faprov']->add_control(new edit_text('COD_ESTADO_PAGO_FAPROV',10,10, 'hidden'));
			$this->dws['dw_pago_faprov']->controls['NOM_ESTADO_PAGO_FAPROV']->type = '';
			
			// DATOS DE EMPRESA NO MODIFICABLES AL CONSULTAR
			$this->dws['dw_pago_faprov']->set_entrable('COD_EMPRESA'			,false);
			$this->dws['dw_pago_faprov']->set_entrable('NOM_EMPRESA'			,false);	
			$this->dws['dw_pago_faprov']->set_entrable('RUT'					,false);
			$this->dws['dw_pago_faprov']->set_entrable('ALIAS'					,false);
		}
		
		else if ($COD_ESTADO_PAGO_FAPROV == self::K_ESTADO_PAGO_FAPROV_IMPRESA){
			
			$sql = "select 	COD_ESTADO_PAGO_FAPROV
							,NOM_ESTADO_PAGO_FAPROV
					from 	ESTADO_PAGO_FAPROV
					where 	COD_ESTADO_PAGO_FAPROV = ".self::K_ESTADO_PAGO_FAPROV_IMPRESA." or
							COD_ESTADO_PAGO_FAPROV = ".self::K_ESTADO_PAGO_FAPROV_ANULADA."
							order by COD_ESTADO_PAGO_FAPROV";
			
			unset($this->dws['dw_pago_faprov']->controls['COD_ESTADO_PAGO_FAPROV']);
			$this->dws['dw_pago_faprov']->add_control($control = new drop_down_dw('COD_ESTADO_PAGO_FAPROV',$sql,110));	
			$control->set_onChange("mostrarOcultar_Anula();");
			$this->dws['dw_pago_faprov']->controls['NOM_ESTADO_PAGO_FAPROV']->type = 'hidden';
			$this->dws['dw_pago_faprov']->add_control(new edit_text_upper('MOTIVO_ANULA',110, 100));
			
			$this->dws['dw_pago_faprov']->controls['USUARIO_CAMBIO']->type = '';
			$this->dws['dw_pago_faprov']->controls['FECHA_CAMBIO']->type = '';
			
			$this->dws['dw_pago_faprov']->controls['COD_USUARIO_ANULA']->type = 'hidden';
			$this->dws['dw_pago_faprov']->controls['FECHA_ANULA']->type = 'hidden';
			
			$this->dws['dw_pago_faprov']->set_entrable('COD_EMPRESA'			,false);
			$this->dws['dw_pago_faprov']->set_entrable('NOM_EMPRESA'			,false);	
			$this->dws['dw_pago_faprov']->set_entrable('RUT'					,false);
			$this->dws['dw_pago_faprov']->set_entrable('ALIAS'					,false);
			
			$this->dws['dw_pago_faprov']->set_entrable('COD_TIPO_PAGO_FAPROV'	,false);
			$this->dws['dw_pago_faprov']->set_entrable('COD_CUENTA_CORRIENTE'	,false);	
			$this->dws['dw_pago_faprov']->set_entrable('NRO_DOCUMENTO'			,false);
			$this->dws['dw_pago_faprov']->set_entrable('PAGUESE_A'				,false);
			$this->dws['dw_pago_faprov']->set_entrable('FECHA_DOCUMENTO'		,false);	
			
			$this->dws['dw_pago_faprov_faprov']->controls['MONTO_ASIGNADO']->set_readonly(true);
			// aqui se dejan no modificables los datos del tab items
			$this->dws['dw_pago_faprov_faprov']->set_entrable_dw(false);

			
		}
		else if ($COD_ESTADO_PAGO_FAPROV == self::K_ESTADO_PAGO_FAPROV_ANULADA) {
			
			$this->b_print_visible 	 = false;
			$this->b_no_save_visible = false;
			$this->b_save_visible 	 = false;
			$this->b_modify_visible  = false;
			
			$this->dws['dw_pago_faprov']->controls['USUARIO_CAMBIO']->type = 'hidden';
			$this->dws['dw_pago_faprov']->controls['FECHA_CAMBIO']->type = 'hidden';
			
			$this->dws['dw_pago_faprov']->controls['COD_USUARIO_ANULA']->type = '';
			$this->dws['dw_pago_faprov']->controls['FECHA_ANULA']->type = '';
		}
	
	}
	
	function get_key() {
		return $this->dws['dw_pago_faprov']->get_item(0, 'COD_PAGO_FAPROV');
	}
	
	function save_record($db) {	
		$COD_PAGO_FAPROV	 	= $this->get_key();		
		$FECHA_PAGO_FAPROV		= $this->dws['dw_pago_faprov']->get_item(0, 'FECHA_PAGO_FAPROV');
		$COD_USUARIO 			= $this->dws['dw_pago_faprov']->get_item(0, 'COD_USUARIO');
		$COD_EMPRESA			= $this->dws['dw_pago_faprov']->get_item(0, 'COD_EMPRESA');		
		$COD_TIPO_PAGO_FAPROV	= $this->dws['dw_pago_faprov']->get_item(0, 'COD_TIPO_PAGO_FAPROV');
		$COD_CUENTA_CORRIENTE	= $this->dws['dw_pago_faprov']->get_item(0, 'COD_CUENTA_CORRIENTE');
		$NRO_DOCUMENTO			= $this->dws['dw_pago_faprov']->get_item(0, 'NRO_DOCUMENTO');
		$FECHA_DOCUMENTO		= $this->dws['dw_pago_faprov']->get_item(0, 'FECHA_DOCUMENTO');
		$PAGUESE_A				= $this->dws['dw_pago_faprov']->get_item(0, 'PAGUESE_A');
		$COD_ESTADO_PAGO_FAPROV	= $this->dws['dw_pago_faprov']->get_item(0, 'COD_ESTADO_PAGO_FAPROV');
		$COD_NCPROV_S			= trim($this->dws['dw_pago_faprov']->get_item(0, 'COD_NCPROV_S'),';');
		$MONTO_NC_H				= $this->dws['dw_pago_faprov']->get_item(0, 'MONTO_NC_H');
		
		$MONTO_DOCUMENTO		= $this->dws['dw_pago_faprov']->get_item(0, 'MONTO_DOCUMENTO_H');
		$MONTO_DOCUMENTO		= ($MONTO_DOCUMENTO =='') ? 0 : "$MONTO_DOCUMENTO";
		
		$FECHA_ANULA		= $this->dws['dw_pago_faprov']->get_item(0, 'FECHA_ANULA');
		$FECHA_ANULA		= ($FECHA_ANULA =='') ? "NULL" : "$FECHA_ANULA";
		$MOTIVO_ANULA		= $this->dws['dw_pago_faprov']->get_item(0, 'MOTIVO_ANULA');
		$MOTIVO_ANULA		= str_replace("'", "''", $MOTIVO_ANULA);
		$COD_USUARIO_ANULA	= $this->dws['dw_pago_faprov']->get_item(0, 'COD_USUARIO_ANULA');
		
		if (($MOTIVO_ANULA != '') && ($COD_USUARIO_ANULA == '')) // se anula 
			$COD_USUARIO_ANULA			= $this->cod_usuario;
		else
			$COD_USUARIO_ANULA			= "NULL";
			
		$ES_NOMINATIVO		= $this->dws['dw_pago_faprov']->get_item(0, 'ES_NOMINATIVO');
		$ES_NOMINATIVO		= ($ES_NOMINATIVO =='') ? "NULL" : "$ES_NOMINATIVO";	
		$ES_CRUZADO			= $this->dws['dw_pago_faprov']->get_item(0, 'ES_CRUZADO');
		$ES_CRUZADO			= ($ES_CRUZADO =='') ? "NULL" : "$ES_CRUZADO";
			
		$COD_PAGO_FAPROV = ($COD_PAGO_FAPROV =='') ? "null" : $COD_PAGO_FAPROV;
		$COD_NCPROV_S = ($COD_NCPROV_S =='') ? "null" : "'$COD_NCPROV_S'";
				
		
		$sp = 'spu_pago_faprov';
	    if ($this->is_new_record())
	    	$operacion = 'INSERT';
	    else
	    	$operacion = 'UPDATE';
	    
		$param	= "	'$operacion'
					,$COD_PAGO_FAPROV
					,$COD_USUARIO				
					,$COD_EMPRESA				
					,$COD_TIPO_PAGO_FAPROV			
					,$COD_CUENTA_CORRIENTE
					,$NRO_DOCUMENTO	
					,'$FECHA_DOCUMENTO'				
					,$MONTO_DOCUMENTO					
					,'$PAGUESE_A'
					,$COD_ESTADO_PAGO_FAPROV	
					,$COD_USUARIO_ANULA
					,'$MOTIVO_ANULA'
					,'$ES_NOMINATIVO'
					,'$ES_CRUZADO'
					,NULL";
		
		if ($db->EXECUTE_SP($sp, $param)){
			if ($this->is_new_record()) {
				$COD_PAGO_FAPROV = $db->GET_IDENTITY();
				$this->dws['dw_pago_faprov']->set_item(0, 'COD_PAGO_FAPROV', $COD_PAGO_FAPROV);
			}				
			for ($i=0; $i<$this->dws['dw_pago_faprov_faprov']->row_count(); $i++) 
				$this->dws['dw_pago_faprov_faprov']->set_item($i, 'COD_PAGO_FAPROV', $COD_PAGO_FAPROV);
			
			$ncprov_sp = "'NCPROV'
						  ,$COD_PAGO_FAPROV
						  ,NULL
						  ,NULL
						  ,NULL
						  ,NULL
						  ,NULL
						  ,NULL
						  ,NULL
						  ,NULL
						  ,NULL
						  ,NULL
						  ,NULL
						  ,NULL
						  ,NULL
						  ,$COD_NCPROV_S";
			if (!$db->EXECUTE_SP('spu_pago_faprov', $ncprov_sp))
			return false;	
					
			if (!$this->dws['dw_pago_faprov_faprov']->update($db, $COD_PAGO_FAPROV)) return false;

			$parametros_sp = "'RECALCULA', NULL, $COD_PAGO_FAPROV";
			if (!$db->EXECUTE_SP('spu_pago_faprov_faprov', $parametros_sp))
				return false;
			
			$parametros_sp = "'ASIGNA_NC', $COD_PAGO_FAPROV";
			if (!$db->EXECUTE_SP('spu_pago_faprov', $parametros_sp))
				return false;
			
			return true;
		}
		return false;		
	}
	function print_record() {
		$cod_pago_faprov = $this->get_key();
		$COD_TIPO_PAGO_FAPROV = $this->dws['dw_pago_faprov']->get_item(0, 'COD_TIPO_PAGO_FAPROV');
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		
		$db->BEGIN_TRANSACTION();
		$sp = 'spu_pago_faprov';
		$param = "'PRINT', $cod_pago_faprov";

		if ($db->EXECUTE_SP($sp, $param)) {		// aqui dentro del sp se cambia el estado y se graba todo lo relacionado
			$db->COMMIT_TRANSACTION();
				//Valida que sea la 1era vez que se imprime. El cheque 
				$cod_estado_pago_faprov = $this->dws['dw_pago_faprov']->get_item(0, 'COD_ESTADO_PAGO_FAPROV');
				if ($cod_estado_pago_faprov == self::K_ESTADO_PAGO_FAPROV_EMITIDA){
					$this->load_record();
					$this->redraw();
					$this->alert('"¿Esta seguro de imprimir el  archivo? Recuerde cambiar el papel en la impresora"');	

					$sql = "exec spr_pago_faprov_tipo_doc $cod_pago_faprov, $COD_TIPO_PAGO_FAPROV";
					$labels = array();
					$labels['strCOD_PAGO_FAPROV'] = $cod_pago_faprov;
					
					if($COD_TIPO_PAGO_FAPROV == self::K_TIPO_PAGO_FAPROV_CHEQUE ){
						$rpt = new rpt_pago_proveedor($sql, $this->root_dir.'appl/pago_faprov/tipo_doc_cheque.xml', $labels, "Pago de Proveedores".$cod_pago_faprov, 0);
						$rpt = new rpt_reverso_ch($sql, $this->root_dir.'appl/pago_faprov/reverso_cheque.xml', $labels, "Reverso_Cheque".$cod_pago_faprov, 0);
					}
					else if ($COD_TIPO_PAGO_FAPROV == self::K_TIPO_PAGO_FAPROV_TRANSFERENCIA){
						$rpt = new rpt_pago_proveedor($sql, $this->root_dir.'appl/pago_faprov/tipo_doc_transferencia.xml', $labels, "Pago de Proveedores".$cod_pago_faprov, 0);
					}
				}else{
				  	$this->redraw();
					$this->alert("Sr(a). Usuario: El pago ya fue impreso. Solo se imprimira el reverso");
					
					$sql = "exec spr_pago_faprov_tipo_doc $cod_pago_faprov, $COD_TIPO_PAGO_FAPROV";
					$labels = array();
					$labels['strCOD_PAGO_FAPROV'] = $cod_pago_faprov;
					$rpt = new rpt_reverso_ch($sql, $this->root_dir.'appl/pago_faprov/reverso_cheque.xml', $labels, "Reverso_Cheque".$cod_pago_faprov, 0);		
				}
				
			$this->b_delete_visible  = false;	
			return true;
		}
		else {
			$db->ROLLBACK_TRANSACTION();
			return false;
		}			
	}
	
	function carga_pagos($cod_empresa, $cod_cuenta_corriente){
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$sql = "SELECT COD_EMPRESA
					  ,NOM_EMPRESA
					  ,ALIAS
					  ,RUT
					  ,DIG_VERIF
					  ,0 MONTO_DOCUMENTO
					  ,0 MONTO_DOCUMENTO_S
					  ,0 TOTAL_MONTO_NC
					  ,0 TOTAL_MONTO_NC_H
				FROM EMPRESA
				WHERE COD_EMPRESA = $cod_empresa";
		
		$result = $db->build_results($sql);
		
		$this->dws['dw_pago_faprov']->set_item(0, 'COD_EMPRESA',		$result[0]['COD_EMPRESA']);
		$this->dws['dw_pago_faprov']->set_item(0, 'COD_EMPRESA_H',		$result[0]['COD_EMPRESA']);
		$this->dws['dw_pago_faprov']->set_item(0, 'NOM_EMPRESA',		$result[0]['NOM_EMPRESA']);
		$this->dws['dw_pago_faprov']->set_item(0, 'PAGUESE_A',			$result[0]['NOM_EMPRESA']);
		$this->dws['dw_pago_faprov']->set_item(0, 'ALIAS',				$result[0]['ALIAS']);
		$this->dws['dw_pago_faprov']->set_item(0, 'RUT',				$result[0]['RUT']);
		$this->dws['dw_pago_faprov']->set_item(0, 'DIG_VERIF',			$result[0]['DIG_VERIF']);
		$this->dws['dw_pago_faprov']->set_item(0, 'MONTO_DOCUMENTO',	$result[0]['MONTO_DOCUMENTO']);
		$this->dws['dw_pago_faprov']->set_item(0, 'MONTO_DOCUMENTO_H',	$result[0]['MONTO_DOCUMENTO']);
		$this->dws['dw_pago_faprov']->set_item(0, 'MONTO_DOCUMENTO_S',	$result[0]['MONTO_DOCUMENTO_S']);
		$this->dws['dw_pago_faprov']->set_item(0, 'TOTAL_MONTO_NC',		$result[0]['TOTAL_MONTO_NC']);
		$this->dws['dw_pago_faprov']->set_item(0, 'TOTAL_MONTO_NC_H',	$result[0]['TOTAL_MONTO_NC_H']);
		$this->dws['dw_pago_faprov']->set_item(0, 'COD_CUENTA_CORRIENTE',	$cod_cuenta_corriente);
		$this->dws['dw_pago_faprov']->set_item(0, 'COD_CUENTA_CORRIENTE_HI',	$cod_cuenta_corriente);
		
		$sql="SELECT 'N' SELECCION
					,0 COD_PAGO_FAPROV_FAPROV
					,0 COD_PAGO_FAPROV
					,F.COD_FAPROV
					,F.NRO_FAPROV
					,convert(varchar(20), F.FECHA_FAPROV, 103) FECHA_FAPROV
					,F.TOTAL_CON_IVA TOTAL_CON_IVA_FA
					,0 MONTO_NCPROV
					,0 MONTO_NCPROV_H
					,dbo.f_pago_faprov_get_por_asignar(F.COD_FAPROV) SALDO_SIN_PAGO_FAPROV
					,dbo.f_pago_faprov_get_por_asignar(F.COD_FAPROV) SALDO_SIN_PAGO_FAPROV_H
					,0 MONTO_ASIGNADO
					,dbo.f_pago_faprov_get_pago_ant(F.COD_FAPROV) PAGO_ANTERIOR
					,cc.NOM_CUENTA_CORRIENTE
					,cc.COD_CUENTA_CORRIENTE
					,CASE
						WHEN CC.COD_CUENTA_CORRIENTE = $cod_cuenta_corriente THEN ''
						ELSE 'none'
					END DISPLAY_TR
			 FROM 	FAPROV F left outer join CUENTA_COMPRA c on c.COD_CUENTA_COMPRA = f.COD_CUENTA_COMPRA
							 left outer join CUENTA_CORRIENTE cc on c.COD_CUENTA_CORRIENTE = cc.COD_CUENTA_CORRIENTE
			 WHERE 	F.COD_EMPRESA = $cod_empresa  
			 and 	dbo.f_pago_faprov_get_por_asignar(F.COD_FAPROV) > 0
			 ORDER BY F.NRO_FAPROV";
			 
		$result = $db->build_results($sql);

		for($i=0 ; $i < count($result) ; $i++){
			$this->dws['dw_pago_faprov_faprov']->insert_row();
			$this->dws['dw_pago_faprov_faprov']->set_item($i, 'SELECCION', $result[$i]['SELECCION']);
			$this->dws['dw_pago_faprov_faprov']->set_item($i, 'COD_FAPROV', $result[$i]['COD_FAPROV']);
			$this->dws['dw_pago_faprov_faprov']->set_item($i, 'NRO_FAPROV', $result[$i]['NRO_FAPROV']);
			$this->dws['dw_pago_faprov_faprov']->set_item($i, 'COD_CUENTA_CORRIENTE_H', $result[$i]['COD_CUENTA_CORRIENTE']);
			$this->dws['dw_pago_faprov_faprov']->set_item($i, 'NOM_CUENTA_CORRIENTE', $result[$i]['NOM_CUENTA_CORRIENTE']);
			$this->dws['dw_pago_faprov_faprov']->set_item($i, 'FECHA_FAPROV', $result[$i]['FECHA_FAPROV']);
			$this->dws['dw_pago_faprov_faprov']->set_item($i, 'TOTAL_CON_IVA_FA', $result[$i]['TOTAL_CON_IVA_FA']);
			$this->dws['dw_pago_faprov_faprov']->set_item($i, 'MONTO_NCPROV', $result[$i]['MONTO_NCPROV']);
			$this->dws['dw_pago_faprov_faprov']->set_item($i, 'MONTO_NCPROV_H', $result[$i]['MONTO_NCPROV_H']);
			$this->dws['dw_pago_faprov_faprov']->set_item($i, 'PAGO_ANTERIOR', $result[$i]['PAGO_ANTERIOR']);
			$this->dws['dw_pago_faprov_faprov']->set_item($i, 'SALDO_SIN_PAGO_FAPROV', $result[$i]['SALDO_SIN_PAGO_FAPROV']);
			$this->dws['dw_pago_faprov_faprov']->set_item($i, 'SALDO_SIN_PAGO_FAPROV_H', $result[$i]['SALDO_SIN_PAGO_FAPROV_H']);
			$this->dws['dw_pago_faprov_faprov']->set_item($i, 'MONTO_ASIGNADO', $result[$i]['MONTO_ASIGNADO']);
			$this->dws['dw_pago_faprov_faprov']->set_item($i, 'DISPLAY_TR', $result[$i]['DISPLAY_TR']);
		}
	}
}

class rpt_reverso_ch extends reporte {
	function rpt_reverso_ch($sql, $xml, $labels=array(), $titulo, $con_logo, $vuelve_a_presentacion=false) {
		parent::reporte($sql, $xml, $labels, $titulo, $con_logo, $vuelve_a_presentacion);		
	}
	function modifica_pdf(&$pdf) {
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$result = $db->build_results($this->sql);
		
		// obtiene datos rut proveedor
		$rut = $result[0]['RUT'];
		$dig_verif = $result[0]['DIG_VERIF'];
		
		// BOLETA FACTURA
		$cod_pago_faprov = $result[0]['COD_PAGO_FAPROV'];
		 
		$sql_boleta_factura = "select dbo.f_get_boleta_factura($cod_pago_faprov) as BOLETA_FACTURA";
		$result_boleta_factura = $db->build_results($sql_boleta_factura);
		$boleta_factura = $result_boleta_factura[0]['BOLETA_FACTURA'];
		
		// obtiene lista de facturas		
		$cadena_faprov = '';
		for($i=0; $i < count($result); $i++){
			$nro_faprov = $result[$i]['NRO_FAPROV'];
			$cadena_faprov = $cadena_faprov.$nro_faprov.'-';
		}
		if ($cadena_faprov != '')
			$cadena_faprov = substr($cadena_faprov, 0, strlen($cadena_faprov) - 1);
		// imprime todo
		$pdf->Rotate(-90, 0, 0);
		$pdf->SetAutoPageBreak(false);
		
		$pdf->SetFont('Arial','',8);
		$pdf->Text(385, -415, "RUT Proveedor: $rut-$dig_verif");
		$pdf->Text(385, -405,$boleta_factura);

		$pdf->SetXY(385, -1195);
		$pdf->MultiCell(160, 12, $cadena_faprov, '', '','L');
	}
}
/////////////////////////////////////////////////////////////
// Se separa por K_CLIENTE
$file_name = dirname(__FILE__)."/".K_CLIENTE."/class_wi_factura.php";
if (file_exists($file_name)) 
	require_once($file_name);
else {
	class wi_pago_faprov extends wi_pago_faprov_base {
		function wi_pago_faprov($cod_item_menu) {
			parent::wi_pago_faprov_base($cod_item_menu); 
		}
	}
}
?>