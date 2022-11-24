<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../empresa/class_dw_help_empresa.php");

class dw_item_faprov extends datawindow {
	
	const K_ESTADO_FAPROV_INGRESADA 		= 1;
	function dw_item_faprov() {	
	
		$sql = "SELECT  'S' SELECCION
						,IT.COD_ITEM_FAPROV
						,IT.COD_FAPROV
						,IT.COD_DOC
						,convert(varchar(20), OC.FECHA_ORDEN_COMPRA, 103) FECHA_ITEM 
						,OC.REFERENCIA
						,OC.COD_NOTA_VENTA
						,OC.TOTAL_NETO TOTAL_NETO_ITEM
						,OC.MONTO_IVA MONTO_IVA_ITEM 
						,OC.TOTAL_CON_IVA TOTAL_CON_IVA_ITEM
						,dbo.f_oc_get_saldo_sin_faprov(OC.COD_ORDEN_COMPRA) + IT.MONTO_ASIGNADO SALDO_SIN_FAPROV
						,dbo.f_oc_get_saldo_sin_faprov(OC.COD_ORDEN_COMPRA) + IT.MONTO_ASIGNADO SALDO_SIN_FAPROV_H
						,IT.MONTO_ASIGNADO 
						,'none' DISPLAY_CERO
						,0 MONTO_ASIGNADO_C
						,'' TD_REFERENCIA_ITEM
						,'' TD_COD_NOTA_VENTA
						,case F.COD_ESTADO_FAPROV
							when ".self::K_ESTADO_FAPROV_INGRESADA." then '' 
							else 'none'
						end TD_SALDO_SIN_FAPROV
				from	ITEM_FAPROV IT, ORDEN_COMPRA OC, FAPROV F
				where	IT.COD_FAPROV = {KEY1} AND
						OC.COD_ORDEN_COMPRA = IT.COD_DOC AND
						F.COD_FAPROV = IT.COD_FAPROV 
						order by OC.COD_ORDEN_COMPRA asc";
		
		parent::datawindow($sql, 'ITEM_FAPROV', true, true);	
		
		$this->add_control($control = new edit_check_box('SELECCION', 'S', 'N'));
		$control->set_onChange("asignacion_monto(this);");
		
		$this->add_control(new static_num('TOTAL_NETO_ITEM'));
		$this->add_control(new static_num('MONTO_IVA_ITEM'));
		$this->add_control(new static_num('TOTAL_CON_IVA_ITEM'));
		$this->add_control(new static_num('SALDO_SIN_FAPROV'));
		$this->add_control(new edit_precio('SALDO_SIN_FAPROV_H',10, 10));
		$this->controls['SALDO_SIN_FAPROV_H']->type = 'hidden';
		
		$this->add_control($control = new edit_precio('MONTO_ASIGNADO',10, 10));
		$control->forzar_js = true;	// para que agregue el js aún cuando este hidden el control
		$control->set_onChange("valida_asignacion(get_num_rec_field(this.id));");
		
		$this->set_computed('MONTO_ASIGNADO_C', '[MONTO_ASIGNADO]');
		$this->controls['MONTO_ASIGNADO_C']->type = 'hidden';

		$this->accumulate('MONTO_ASIGNADO_C', 'copia_suma_a_total();');
		
		$this->set_first_focus('MONTO_ASIGNADO');
		
		// asigna los mandatorys
		$this->set_mandatory('MONTO_ASIGNADO', 'Monto Asignado');
	}
	function update($db, $COD_FAPROV)	{
		$sp = 'spu_item_faprov';
		$operacion = 'DELETE_ALL';
		$param = "'$operacion',null, $COD_FAPROV";			
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
			$COD_ITEM_FAPROV 			= $this->get_item($i, 'COD_ITEM_FAPROV');
			$COD_FAPROV					= $this->get_item($i, 'COD_FAPROV');
			$COD_DOC 					= $this->get_item($i, 'COD_DOC');			
			$MONTO_ASIGNADO				= $this->get_item($i, 'MONTO_ASIGNADO');

			$COD_ITEM_FAPROV = ($COD_ITEM_FAPROV=='') ? "null" : $COD_ITEM_FAPROV;
			
			$operacion = 'INSERT';
			$param = "'$operacion',$COD_ITEM_FAPROV, $COD_FAPROV, $COD_DOC, $MONTO_ASIGNADO";	
			
			if (!$db->EXECUTE_SP($sp, $param))
				
				return false;
			}	
		return true;
	}
}
class dw_pago_faprov extends datawindow {
	function dw_pago_faprov(){
			$sql = "EXEC spdw_faprov_pago {KEY1},'FAPROV'";
			parent::datawindow($sql, 'PAGO_FAPROV');
		
			$this->add_control(new static_text('COD_PAGO_FAPROV'));
			$this->add_control(new static_num('MONTO_ASIGNADO_PAGO'));
	}	
}
class dw_faprov extends dw_help_empresa {
	
	const K_ESTADO_FAPROV_INGRESADA 		= 1;
	const K_ESTADO_FAPROV_APROBADA 			= 2;
	const K_ESTADO_FAPROV_ANULADA			= 5;
	
	const K_TIPO_FAPROV_FACTURA 			= 1;
	const K_TIPO_FAPROV_FACTURA_EXENTA		= 2;
	const K_TIPO_FAPROV_BOLETA_HONORARIO	= 3;
	
	const K_PARAM_PORC_IVA			= 1;
	const K_PARAM_RET_BH			= 2;
	
	const K_ASIGNA_PROY_COMPRA = '993005';
	
	function dw_faprov() {
		
		$sql = "SELECT   COD_FAPROV
						,convert(varchar(20), F.FECHA_REGISTRO, 103) FECHA_REGISTRO
						,F.COD_USUARIO
						,F.COD_EMPRESA
						,E.NOM_EMPRESA
						,E.ALIAS
						,E.RUT
						,E.DIG_VERIF
						,F.COD_TIPO_FAPROV
						,TF.NOM_TIPO_FAPROV
						,EF.NOM_ESTADO_FAPROV
						,NRO_FAPROV
						,convert(varchar(20), FECHA_FAPROV, 103) FECHA_FAPROV
						,TOTAL_NETO
						,TOTAL_NETO TOTAL_NETO_H	
						,MONTO_IVA
						,MONTO_IVA MONTO_IVA_H
						,TOTAL_CON_IVA
						,TOTAL_CON_IVA TOTAL_CON_IVA_H
						,F.COD_USUARIO_ANULA
						,convert(varchar(20), F.FECHA_ANULA, 103) +'  '+ convert(varchar(20), F.FECHA_ANULA, 8)FECHA_ANULA
						,F.MOTIVO_ANULA
						,U.NOM_USUARIO
						,F.COD_ESTADO_FAPROV
						----- datos historicos(despliega la fecha en que se cambia el estado_faprov)-----
						,(select TOP 1 convert(varchar(20), LG.FECHA_CAMBIO, 103) +'  '+ convert(varchar(20), LG.FECHA_CAMBIO, 8) FECHA_CAMBIO
							from	LOG_CAMBIO LG, DETALLE_CAMBIO DC
							where	LG.NOM_TABLA = 'FAPROV' and
									LG.KEY_TABLA = convert(varchar,F.COD_FAPROV) and
									LG.COD_LOG_CAMBIO = DC.COD_LOG_CAMBIO and
									DC.NOM_CAMPO = 'COD_ESTADO_FAPROV' 
									order by LG.FECHA_CAMBIO desc) FECHA_CAMBIO
						----- datos historicos(despliega el usuario que cambia el estado_faprov)-----
						,(select TOP 1 U.NOM_USUARIO
							from	LOG_CAMBIO LG, DETALLE_CAMBIO DC, USUARIO U
							where	LG.NOM_TABLA = 'FAPROV' and
									LG.KEY_TABLA = convert(varchar,F.COD_FAPROV) and
									LG.COD_LOG_CAMBIO = DC.COD_LOG_CAMBIO and
									LG.COD_USUARIO = U.COD_USUARIO and 
									DC.NOM_CAMPO = 'COD_ESTADO_FAPROV' 
									order by LG.FECHA_CAMBIO desc)USUARIO_CAMBIO
									
						,case F.COD_ESTADO_FAPROV 
							when ".self::K_ESTADO_FAPROV_ANULADA." then '' 
							else 'none'
						end TR_DISPLAY	
						,dbo.f_get_parametro(".self::K_PARAM_PORC_IVA.") PORC_IVA_H
						,dbo.f_get_parametro(".self::K_PARAM_RET_BH.") RETENCION_BH_H
						,case F.ORIGEN_FAPROV
							when  'ORDEN_COMPRA' then 'ORDENES DE COMPRA' 
							else 'PARTICIPACION'
						end TITULO_COD_DOC
						,case F.ORIGEN_FAPROV
							when  'ORDEN_COMPRA' then '' 
							else 'none'
						end TD_REFERENCIA_ITEM
						,case F.ORIGEN_FAPROV
							when  'ORDEN_COMPRA' then '' 
							else 'none'
						end TD_COD_NOTA_VENTA
						,case F.COD_ESTADO_FAPROV
							when ".self::K_ESTADO_FAPROV_INGRESADA." then '' 
							else 'none'
						end TD_SALDO_SIN_FAPROV	
						,ORIGEN_FAPROV ORIGEN_FAPROV_H
						,COD_CUENTA_COMPRA
						,case F.COD_TIPO_FAPROV 
							when 3 then 'Monto Bruto' 
							else 'Monto Neto'
						end LABEL_BRUTO_NETO
						,case F.COD_TIPO_FAPROV 
							when 3 then 'Monto Retención' 
							else 'Monto IVA'
						end LABEL_RETENCION_IVA
						,case F.COD_TIPO_FAPROV 
							when 3 then 'Total Líquido' 
							else 'Total c/IVA'
						end LABEL_TOTAL
						,case F.COD_TIPO_FAPROV 
							when 3 then 'Monto Bruto' 
							else 'Monto Neto'
						end LABEL_BRUTO_NETO1
						,case F.COD_TIPO_FAPROV 
							when 3 then 'Monto Retención' 
							else 'Monto IVA'
						end LABEL_RETENCION_IVA1
						,case F.COD_TIPO_FAPROV 
							when 3 then 'Total Líquido' 
							else 'Total c/IVA'
						end LABEL_TOTAL1
				FROM	FAPROV F, EMPRESA E, TIPO_FAPROV TF,ESTADO_FAPROV EF, USUARIO U
				WHERE	F.COD_FAPROV = {KEY1} and
						F.COD_EMPRESA = E.COD_EMPRESA AND
						F.COD_USUARIO = U.COD_USUARIO AND
						F.COD_TIPO_FAPROV = TF.COD_TIPO_FAPROV AND
						F.COD_ESTADO_FAPROV = EF.COD_ESTADO_FAPROV";		
		
		// DATAWINDOWS HELP_EMPRESA
		parent::dw_help_empresa($sql, '', false, false, 'P');
		
		// DATOS GENERALES
		$this->add_control(new edit_nro_doc('COD_FAPROV','FAPROV'));
		$sql = "select COD_CUENTA_COMPRA
						,NOM_CUENTA_COMPRA
				from CUENTA_COMPRA
				order by NOM_CUENTA_COMPRA";								
		$this->add_control($control = new drop_down_dw('COD_CUENTA_COMPRA', $sql,140));
   		if (w_base::get_privilegio_opcion_usuario(self::K_ASIGNA_PROY_COMPRA, $this->cod_usuario)!='E')
   			$this->set_entrable('COD_CUENTA_COMPRA', false);
		
		$sql_tipo_faprov 			= "	select		COD_TIPO_FAPROV
													,NOM_TIPO_FAPROV
									from 			TIPO_FAPROV
									order by		COD_TIPO_FAPROV";								
		$this->add_control($control = new drop_down_dw('COD_TIPO_FAPROV', $sql_tipo_faprov,140));
		$control->set_onChange("calcula_totales();");
		
		$sql_estado_faprov 	= "select 	COD_ESTADO_FAPROV
										,NOM_ESTADO_FAPROV
								from 	ESTADO_FAPROV
								where 	COD_ESTADO_FAPROV = ".self::K_ESTADO_FAPROV_INGRESADA." or
										COD_ESTADO_FAPROV = ".self::K_ESTADO_FAPROV_APROBADA." 
										order by COD_ESTADO_FAPROV";
		$this->add_control(new drop_down_dw('COD_ESTADO_FAPROV', $sql_estado_faprov,140));
		
		$this->add_control(new edit_num('NRO_FAPROV',10, 10, 0, true, false, false));
		$this->add_control(new edit_date('FECHA_FAPROV'));
		$this->add_control(new static_num('TOTAL_NETO'));
		$this->add_control(new static_num('MONTO_IVA'));		
		$this->add_control(new static_num('TOTAL_CON_IVA'));
		
		$this->add_control(new edit_text('TOTAL_NETO_H',20,20,'hidden'));
		$this->add_control(new edit_text('MONTO_IVA_H',20,20,'hidden'));
		$this->add_control(new edit_text('TOTAL_CON_IVA_H',20,20,'hidden'));
		
		$this->add_control(new edit_text('PORC_IVA_H',10,10,'hidden'));
		$this->add_control(new edit_text('RETENCION_BH_H',10,10,'hidden'));
		
		$this->add_control(new edit_text('ORIGEN_FAPROV_H',20,20,'hidden'));
		
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
		$this->add_control(new static_text('LABEL_BRUTO_NETO',20,20));
		$this->add_control(new static_text('LABEL_RETENCION_IVA',20,20));
		$this->add_control(new static_text('LABEL_TOTAL',20,20));
		$this->add_control(new static_text('LABEL_BRUTO_NETO1',20,20));
		$this->add_control(new static_text('LABEL_RETENCION_IVA1',20,20));
		$this->add_control(new static_text('LABEL_TOTAL1',20,20));
		
		// asigna los mandatorys/
		$this->set_mandatory('COD_TIPO_FAPROV', 'Tipo Documento');
		$this->set_mandatory('COD_ESTADO_FAPROV', 'un Estado');
		$this->set_mandatory('NRO_FAPROV', 'Nº Documento');
		$this->set_mandatory('FECHA_FAPROV', 'Fecha Documento');

	}
	function fill_record(&$temp, $record) {	
		parent::fill_record($temp, $record);
			
			$COD_FAPROV = $this->get_item(0, 'COD_FAPROV');
			
			$COD_ESTADO_FAPROV = $this->get_item(0, 'COD_ESTADO_FAPROV');
			
			if (($COD_FAPROV !='')or($COD_ESTADO_FAPROV != 1))  //
				$temp->setVar('DISABLE_BUTTON', 'style="display:none"');
			else{	
					if ($this->entrable)
						$temp->setVar('DISABLE_BUTTON', '');
					else
						$temp->setVar('DISABLE_BUTTON', 'disabled="disabled"');
			}				
	}
	
}
class wi_faprov extends w_input {
	const K_ESTADO_FAPROV_INGRESADA 		= 1;
	const K_ESTADO_FAPROV_APROBADA 			= 2;
	const K_ESTADO_FAPROV_ANULADA 			= 5;
	
	const K_TIPO_FAPROV_FACTURA 			= 1;
	const K_TIPO_FAPROV_FACTURA_EXENTA		= 2;
	const K_TIPO_FAPROV_BOLETA_HONORARIO	= 3;
	const K_TIPO_FAPROV_FACTURA_ELECTRONICA = 4;
	
	const K_CTA_COMPRA_COMERCIAL			= 6;
	
	const K_PARAM_PORC_IVA	= 1;
	const K_PARAM_RET_BH	= 2;
	
	var $faprov_desde = 'ORDEN_COMPRA';
	var $cod_orden_compra = '';	// Tiene dato solo si se ingreso un nro de OC 
	
	function wi_faprov($cod_item_menu) {
		parent::w_input('faprov', $cod_item_menu);

		// DATAWINDOWS FAPROV
		$this->dws['dw_faprov'] = new dw_faprov();

		//auditoria Solicitado por IS.
		$this->add_auditoria('COD_CUENTA_COMPRA');
		$this->add_auditoria('COD_TIPO_FAPROV');
		$this->add_auditoria('NRO_FAPROV');
		$this->add_auditoria('FECHA_FAPROV');
		$this->add_auditoria('COD_ESTADO_FAPROV');		
		
		// DATAWINDOWS ITEM_FAPROV
		$this->dws['dw_item_faprov'] = new dw_item_faprov();
		// DATAWINDOWS PAGO_FAPROV
		$this->dws['dw_pago_faprov'] = new dw_pago_faprov();

		//al momento de crear un nuevo registro se iniciara en rut de empresa proveedor
		$this->set_first_focus('RUT');
	}	
	function new_record() {
		
		if (session::is_set('FAPROV_CREADA_DESDE')) {
			$tipo_faprov = session::get('FAPROV_CREADA_DESDE');
			$res = explode("|", $tipo_faprov);			
			$this->faprov_desde = $res[0];
			$this->cod_orden_compra = $res[1];
			session::un_set('FAPROV_CREADA_DESDE');
			
			if ($this->cod_orden_compra!='')
				$this->set_first_focus('NRO_FAPROV');		
		}
		$this->dws['dw_faprov']->insert_row();
		$this->dws['dw_faprov']->set_item(0, 'TR_DISPLAY', 'none');
		$this->dws['dw_faprov']->set_item(0, 'FECHA_REGISTRO', substr($this->current_date_time(), 0, 16));
		$this->dws['dw_faprov']->set_item(0, 'COD_USUARIO', $this->cod_usuario);
		
		$this->dws['dw_faprov']->set_item(0, 'NOM_USUARIO', $this->nom_usuario);
		$this->dws['dw_faprov']->set_item(0, 'COD_ESTADO_FAPROV', self::K_ESTADO_FAPROV_APROBADA);
		//$this->dws['dw_faprov']->set_item(0, 'COD_CUENTA_COMPRA', self::K_CTA_COMPRA_COMERCIAL);
		/*
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$sql="select valor valor_nc from parametro where cod_parametro=40";
		
		$result_solicitud = $db->build_results($sql_solicitud);
		$cod_contacto = $result_solicitud[0]['COD_CONTACTO'];
		
		$tipo_participacion = $this->dws['dw_faprov']->get_item(0, 'TIPO_PARTICIPACION');
		
		
		
		*/
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$sql = "select dbo.f_get_parametro(".self::K_PARAM_PORC_IVA.") PORC_IVA_H";
		$result = $db->build_results($sql);
		$this->dws['dw_faprov']->set_item(0, 'PORC_IVA_H', $result[0]['PORC_IVA_H']);
		
		$sql = "select dbo.f_get_parametro(".self::K_PARAM_RET_BH.") RETENCION_BH_H";
		$result = $db->build_results($sql);
		$this->dws['dw_faprov']->set_item(0, 'RETENCION_BH_H', $result[0]['RETENCION_BH_H']);
		
		$this->dws['dw_faprov']->set_item(0, 'LABEL_BRUTO_NETO', 'Total Neto');
		$this->dws['dw_faprov']->set_item(0, 'LABEL_RETENCION_IVA', 'Total IVA');
		$this->dws['dw_faprov']->set_item(0, 'LABEL_TOTAL', 'Total c/IVA');
		
		$this->dws['dw_faprov']->set_item(0, 'LABEL_BRUTO_NETO1', 'Total Neto');
		$this->dws['dw_faprov']->set_item(0, 'LABEL_RETENCION_IVA1', 'Total IVA');
		$this->dws['dw_faprov']->set_item(0, 'LABEL_TOTAL1', 'Total c/IVA');
		
		if($this->faprov_desde == 'ORDEN_COMPRA'){
			$this->dws['dw_faprov']->set_item(0, 'TITULO_COD_DOC', 'ORDENES DE COMPRA');
			$this->dws['dw_faprov']->set_item(0, 'TD_REFERENCIA_ITEM', '');
			$this->dws['dw_faprov']->set_item(0, 'TD_COD_NOTA_VENTA', '');
		
			if ($this->cod_orden_compra!='') {
				// Lena los datos de la empresa
				$sql = "select E.COD_EMPRESA
								,E.RUT
								,E.DIG_VERIF
								,E.ALIAS
								,E.NOM_EMPRESA
								,isnull (E.TIPO_PARTICIPACION,'') TIPO_PARTICIPACION
						from ORDEN_COMPRA O, EMPRESA E
						where O.COD_ORDEN_COMPRA = ".$this->cod_orden_compra."
						  and E.COD_EMPRESA = O.COD_EMPRESA";
				$result = $db->build_results($sql);
				$this->dws['dw_faprov']->set_item(0, 'COD_EMPRESA', $result[0]['COD_EMPRESA']);
				$this->dws['dw_faprov']->set_item(0, 'RUT', $result[0]['RUT']);
				$this->dws['dw_faprov']->set_item(0, 'DIG_VERIF', $result[0]['DIG_VERIF']);
				$this->dws['dw_faprov']->set_item(0, 'ALIAS', $result[0]['ALIAS']);
				$this->dws['dw_faprov']->set_item(0, 'NOM_EMPRESA', $result[0]['NOM_EMPRESA']);
				
				$tipo_participacion = ($result[0]['TIPO_PARTICIPACION']);
				
				//echo 'aaaaaaaaa'.$tipo_participacion;
				
				if($tipo_participacion == 'FA' ){
					$this->dws['dw_faprov']->set_item(0, 'COD_TIPO_FAPROV', self::K_TIPO_FAPROV_FACTURA);
				}else if($tipo_participacion == 'BH' ){
					$this->dws['dw_faprov']->set_item(0, 'COD_TIPO_FAPROV', self::K_TIPO_FAPROV_BOLETA_HONORARIO);
				}else if($tipo_participacion == 'FA EX' ){
					$this->dws['dw_faprov']->set_item(0, 'COD_TIPO_FAPROV', self::K_TIPO_FAPROV_FACTURA_EXENTA);
				}else{
					$this->dws['dw_faprov']->set_item(0, 'COD_TIPO_FAPROV', self::K_TIPO_FAPROV_FACTURA_ELECTRONICA);
				}
						// Carga la lista de OC solo con la OC seleccionada
				$sql_original = $this->dws['dw_item_faprov']->get_sql();
				// mismo sql que en "load_lista_item_faprov.php"
				$sql = "SELECT  'N' SELECCION
								,0 COD_ITEM_FAPROV
								,0 COD_FAPROV
								,OC.COD_ORDEN_COMPRA COD_DOC
								,convert(varchar(20), OC.FECHA_ORDEN_COMPRA, 103) FECHA_ITEM 
								,OC.REFERENCIA REFERENCIA_ITEM
								,OC.COD_NOTA_VENTA
								,OC.TOTAL_NETO TOTAL_NETO_ITEM
								,OC.MONTO_IVA MONTO_IVA_ITEM 
								,OC.TOTAL_CON_IVA TOTAL_CON_IVA_ITEM
								,dbo.f_oc_get_saldo_sin_faprov(OC.COD_ORDEN_COMPRA) SALDO_SIN_FAPROV
								,dbo.f_oc_get_saldo_sin_faprov(OC.COD_ORDEN_COMPRA) SALDO_SIN_FAPROV_H
								,0 MONTO_ASIGNADO
								,'none' DISPLAY_CERO
								,0 MONTO_ASIGNADO_C
								,'' TD_REFERENCIA_ITEM
								,'' TD_COD_NOTA_VENTA
								,'' TD_SALDO_SIN_FAPROV
						from	ORDEN_COMPRA OC
						where	OC.COD_ORDEN_COMPRA = ".$this->cod_orden_compra."
						   		and  dbo.f_oc_get_saldo_sin_faprov(OC.COD_ORDEN_COMPRA) > 0
						order by OC.COD_ORDEN_COMPRA asc";
				$this->dws['dw_item_faprov']->set_sql($sql);
				$row = $this->dws['dw_item_faprov']->retrieve();
				$this->dws['dw_item_faprov']->set_sql($sql_original);
		
			}
		}															
		else{
			$this->dws['dw_faprov']->set_item(0, 'TITULO_COD_DOC', 'PARTICIPACIÓN');
			$this->dws['dw_faprov']->set_item(0, 'TD_REFERENCIA_ITEM', 'none');
			$this->dws['dw_faprov']->set_item(0, 'TD_COD_NOTA_VENTA', 'none');
		}
		
		$this->dws['dw_faprov']->set_item(0, 'ORIGEN_FAPROV_H', $this->faprov_desde);
	}	
	function load_record() {
		$cod_faprov = $this->get_item_wo($this->current_record, 'COD_FAPROV');
		
		$this->dws['dw_faprov']->retrieve($cod_faprov);
		$cod_empresa = $this->dws['dw_faprov']->get_item(0, 'COD_EMPRESA');
		
		$COD_ESTADO_FAPROV = $this->dws['dw_faprov']->get_item(0, 'COD_ESTADO_FAPROV');
		
		$this->b_print_visible 	 = true;
		$this->b_no_save_visible = true;
		$this->b_save_visible 	 = true;
		$this->b_modify_visible	 = true;

		$this->dws['dw_faprov']->set_entrable('COD_TIPO_FAPROV'		, false);
		$this->dws['dw_faprov']->set_entrable('NRO_FAPROV'			, false);
		$this->dws['dw_faprov']->set_entrable('FECHA_FAPROV'		, false);
		$this->dws['dw_faprov']->set_entrable('TOTAL_NETO'			, false);
		$this->dws['dw_faprov']->set_entrable('MONTO_IVA'			, false);
		
		// DATOS DE EMPRESA NO MODIFICABLES AL CONSULTAR
		$this->dws['dw_faprov']->set_entrable('COD_EMPRESA'			,false);
		$this->dws['dw_faprov']->set_entrable('NOM_EMPRESA'			,false);	
		$this->dws['dw_faprov']->set_entrable('RUT'					,false);
		$this->dws['dw_faprov']->set_entrable('ALIAS'				,false);	
		
		$this->dws['dw_faprov']->controls['USUARIO_CAMBIO']->type = '';
		$this->dws['dw_faprov']->controls['FECHA_CAMBIO']->type = '';
		
		$this->dws['dw_faprov']->controls['COD_USUARIO_ANULA']->type = 'hidden';
		$this->dws['dw_faprov']->controls['FECHA_ANULA']->type = 'hidden';

		if ($COD_ESTADO_FAPROV == self::K_ESTADO_FAPROV_INGRESADA) {
				
			$this->dws['dw_faprov']->set_entrable('COD_TIPO_FAPROV'		, true);
			$this->dws['dw_faprov']->set_entrable('NRO_FAPROV'			, true);
			$this->dws['dw_faprov']->set_entrable('FECHA_FAPROV'		, true);
			$this->dws['dw_faprov']->set_entrable('TOTAL_NETO'			, true);
			$this->dws['dw_faprov']->set_entrable('MONTO_IVA'			, true);
			
			//select que trae todos los estado de faprov siempre y cuando este en estado ingresada
			$sql = "select 	COD_ESTADO_FAPROV
							,NOM_ESTADO_FAPROV
					from ESTADO_FAPROV	
					order by COD_ESTADO_FAPROV";
			
			unset($this->dws['dw_faprov']->controls['COD_ESTADO_FAPROV']);
			$this->dws['dw_faprov']->add_control($control = new drop_down_dw('COD_ESTADO_FAPROV',$sql,125));
			$control->set_onChange("mostrarOcultar_Anula(this);");
			$this->dws['dw_faprov']->add_control(new edit_text_upper('MOTIVO_ANULA',120, 100));
		}
		else if ($COD_ESTADO_FAPROV == self::K_ESTADO_FAPROV_APROBADA){
			$sql = "select 	COD_ESTADO_FAPROV
							,NOM_ESTADO_FAPROV
					from ESTADO_FAPROV	
					order by COD_ESTADO_FAPROV";
			
			unset($this->dws['dw_faprov']->controls['COD_ESTADO_FAPROV']);
			$this->dws['dw_faprov']->add_control($control = new drop_down_dw('COD_ESTADO_FAPROV',$sql,125));
			$control->set_onChange("mostrarOcultar_Anula(this);");
			$this->dws['dw_faprov']->add_control(new edit_text_upper('MOTIVO_ANULA',120, 100));
			
			$this->dws['dw_item_faprov']->set_entrable_dw(false);
		}
		else if ($COD_ESTADO_FAPROV == self::K_ESTADO_FAPROV_ANULADA) {
			$sql = "select 	COD_ESTADO_FAPROV
							,NOM_ESTADO_FAPROV
					from ESTADO_FAPROV	
					where COD_ESTADO_FAPROV = ".self::K_ESTADO_FAPROV_ANULADA."";
			unset($this->dws['dw_faprov']->controls['COD_ESTADO_FAPROV']);
			$this->dws['dw_faprov']->add_control($control = new drop_down_dw('COD_ESTADO_FAPROV',$sql,125));
			$this->b_print_visible 	 = false;
			$this->b_no_save_visible = false;
			$this->b_save_visible 	 = false;
			$this->b_modify_visible  = false;
			
			$this->dws['dw_faprov']->controls['USUARIO_CAMBIO']->type = 'hidden';
			$this->dws['dw_faprov']->controls['FECHA_CAMBIO']->type = 'hidden';
			
			$this->dws['dw_faprov']->controls['COD_USUARIO_ANULA']->type = '';
			$this->dws['dw_faprov']->controls['FECHA_ANULA']->type = '';
		}
		$sql_original = $this->dws['dw_item_faprov']->get_sql();
		$ORIGEN_FAPROV_H = $this->dws['dw_faprov']->get_item(0, 'ORIGEN_FAPROV_H');
	
		if ($ORIGEN_FAPROV_H == 'ORDEN_COMPRA') {
			$sql_origen = "SELECT  'S' SELECCION
						,IT.COD_ITEM_FAPROV
						,IT.COD_FAPROV
						,IT.COD_DOC
						,convert(varchar(20), OC.FECHA_ORDEN_COMPRA, 103) FECHA_ITEM 
						,OC.REFERENCIA REFERENCIA_ITEM
						,OC.COD_NOTA_VENTA
						,OC.TOTAL_NETO TOTAL_NETO_ITEM
						,OC.MONTO_IVA MONTO_IVA_ITEM 
						,OC.TOTAL_CON_IVA TOTAL_CON_IVA_ITEM
						,dbo.f_oc_get_saldo_sin_faprov(OC.COD_ORDEN_COMPRA) + IT.MONTO_ASIGNADO SALDO_SIN_FAPROV
						,dbo.f_oc_get_saldo_sin_faprov(OC.COD_ORDEN_COMPRA) + IT.MONTO_ASIGNADO SALDO_SIN_FAPROV_H
						,IT.MONTO_ASIGNADO 
						,'none' DISPLAY_CERO
						,0 MONTO_ASIGNADO_C
						,'' TD_REFERENCIA_ITEM
						,'' TD_COD_NOTA_VENTA
						,case F.COD_ESTADO_FAPROV
							when ".self::K_ESTADO_FAPROV_INGRESADA." then '' 
							else 'none'
						end TD_SALDO_SIN_FAPROV
				from	ITEM_FAPROV IT, ORDEN_COMPRA OC, FAPROV F
				where	IT.COD_FAPROV = {KEY1} AND
						OC.COD_ORDEN_COMPRA = IT.COD_DOC AND
						F.COD_FAPROV = IT.COD_FAPROV 
						order by OC.COD_ORDEN_COMPRA asc";
		}			
		else {
			$sql_origen = "SELECT  'S' SELECCION
						,IT.COD_ITEM_FAPROV
						,IT.COD_FAPROV
						,IT.COD_DOC
						,convert(varchar(20), P.FECHA_PARTICIPACION, 103) FECHA_ITEM 
						,'null' REFERENCIA_ITEM
						,'null' COD_NOTA_VENTA
						,P.TOTAL_NETO TOTAL_NETO_ITEM
						,P.MONTO_IVA MONTO_IVA_ITEM 
						,P.TOTAL_CON_IVA TOTAL_CON_IVA_ITEM
						,dbo.f_part_get_saldo_sin_faprov(P.COD_PARTICIPACION) + IT.MONTO_ASIGNADO SALDO_SIN_FAPROV
						,dbo.f_part_get_saldo_sin_faprov(P.COD_PARTICIPACION) + IT.MONTO_ASIGNADO SALDO_SIN_FAPROV_H
						,IT.MONTO_ASIGNADO 
						,'none' DISPLAY_CERO
						,0 MONTO_ASIGNADO_C
						,'none' TD_REFERENCIA_ITEM
						,'none' TD_COD_NOTA_VENTA
						,case F.COD_ESTADO_FAPROV
							when ".self::K_ESTADO_FAPROV_INGRESADA." then '' 
							else 'none'
						end TD_SALDO_SIN_FAPROV
				from	ITEM_FAPROV IT, PARTICIPACION P, FAPROV F
				where	IT.COD_FAPROV = {KEY1} AND
						P.COD_PARTICIPACION = IT.COD_DOC AND
						F.COD_FAPROV = IT.COD_FAPROV 
						order by P.COD_PARTICIPACION asc";	
		}
		
		$sql = $this->dws['dw_item_faprov']->get_sql();
		$this->dws['dw_item_faprov']->set_sql($sql_origen);
		$this->dws['dw_item_faprov']->retrieve($cod_faprov);
		$this->dws['dw_item_faprov']->set_sql($sql);
		$this->dws['dw_pago_faprov']->retrieve($cod_faprov);
	}
	function get_key() {
		return $this->dws['dw_faprov']->get_item(0, 'COD_FAPROV');
	}
	function habilita_boton(&$temp, $boton, $habilita) {
		parent::habilita_boton($temp, $boton, $habilita);
		
		if($boton == 'create'){
			if($habilita){
				$control = '<input name="b_create" id="b_create" src="../../../../commonlib/trunk/images/b_create.jpg" type="image" '.
								 'onMouseDown="MM_swapImage(\'b_create\',\'\',\'../../../../commonlib/trunk/images/b_create_click.jpg\',1)" '.
								 'onMouseUp="MM_swapImgRestore()" onMouseOut="MM_swapImgRestore()" '.
								 'onMouseOver="MM_swapImage(\'b_create\',\'\',\'../../../../commonlib/trunk/images/b_create_over.jpg\',1)"
								 onclick="return request_tipo_faprov2(\'Origen de Recepción de Factura\',\'\');"/>';
			}else{
				$control = '<img src="../../../../commonlib/trunk/images/b_create_d.jpg">';
			}
			
			$temp->setVar("WI_".strtoupper($boton), $control);
		}
	}
	function navegacion(&$temp){
		parent::navegacion($temp);
		
		if($this->modify){
			$this->habilita_boton($temp, 'create', false);
		}else{
			$this->habilita_boton($temp, 'create', true);
		}
	}
	function save_record($db) {	
		$COD_FAPROV		 	= $this->get_key();		
		$FECHA_REGISTRO		= $this->dws['dw_faprov']->get_item(0, 'FECHA_REGISTRO');
		$COD_USUARIO 		= $this->dws['dw_faprov']->get_item(0, 'COD_USUARIO');
		$COD_EMPRESA		= $this->dws['dw_faprov']->get_item(0, 'COD_EMPRESA');		
		$COD_TIPO_FAPROV	= $this->dws['dw_faprov']->get_item(0, 'COD_TIPO_FAPROV');
		$COD_ESTADO_FAPROV	= $this->dws['dw_faprov']->get_item(0, 'COD_ESTADO_FAPROV');
		$NRO_FAPROV			= $this->dws['dw_faprov']->get_item(0, 'NRO_FAPROV');
		$FECHA_FAPROV		= $this->dws['dw_faprov']->get_item(0, 'FECHA_FAPROV');
		$COD_CUENTA_COMPRA	= $this->dws['dw_faprov']->get_item(0, 'COD_CUENTA_COMPRA');		
		$COD_CUENTA_COMPRA	= ($COD_CUENTA_COMPRA =='') ? "NULL" : $COD_CUENTA_COMPRA;
		
		$TOTAL_NETO			= $this->dws['dw_faprov']->get_item(0, 'TOTAL_NETO_H');
		$TOTAL_NETO			= ($TOTAL_NETO =='') ? 0 : "$TOTAL_NETO";
		$MONTO_IVA			= $this->dws['dw_faprov']->get_item(0, 'MONTO_IVA_H');
		$MONTO_IVA			= ($MONTO_IVA =='') ? 0 : "$MONTO_IVA";
		$TOTAL_CON_IVA		= $this->dws['dw_faprov']->get_item(0, 'TOTAL_CON_IVA_H');
		$TOTAL_CON_IVA		= ($TOTAL_CON_IVA =='') ? 0 : "$TOTAL_CON_IVA";		
		
		$FECHA_ANULA		= $this->dws['dw_faprov']->get_item(0, 'FECHA_ANULA');
		$FECHA_ANULA		= ($FECHA_ANULA =='') ? "NULL" : "$FECHA_ANULA";
		$MOTIVO_ANULA		= $this->dws['dw_faprov']->get_item(0, 'MOTIVO_ANULA');
		$MOTIVO_ANULA		= ($MOTIVO_ANULA =='') ? "NULL" : "$MOTIVO_ANULA";
		$COD_USUARIO_ANULA	= $this->dws['dw_faprov']->get_item(0, 'COD_USUARIO_ANULA');
		$ORIGEN_FAPROV		= $this->dws['dw_faprov']->get_item(0, 'ORIGEN_FAPROV_H');
		
		if (($MOTIVO_ANULA != '') && ($COD_USUARIO_ANULA == '')) // se anula 
			$COD_USUARIO_ANULA			= $this->cod_usuario;
		else
			$COD_USUARIO_ANULA			= "null";
		
		$COD_FAPROV = ($COD_FAPROV =='') ? "null" : $COD_FAPROV;		
		
		$sp = 'spu_faprov';
	    if ($this->is_new_record())
	    	$operacion = 'INSERT';
	    else
	    	$operacion = 'UPDATE';
	    
		$param	= "	'$operacion'
					,$COD_FAPROV
					,$COD_USUARIO				
					,$COD_EMPRESA				
					,$COD_TIPO_FAPROV			
					,$COD_ESTADO_FAPROV			
					,$NRO_FAPROV	
					,'$FECHA_FAPROV'			
					,$TOTAL_NETO					
					,$MONTO_IVA				
					,$TOTAL_CON_IVA
					,$COD_USUARIO_ANULA
					,'$MOTIVO_ANULA'
					,'$ORIGEN_FAPROV'
					,$COD_CUENTA_COMPRA";
					
					
		if ($db->EXECUTE_SP($sp, $param)){
			if ($this->is_new_record()) {
				$COD_FAPROV = $db->GET_IDENTITY();
				$this->dws['dw_faprov']->set_item(0, 'COD_FAPROV', $COD_FAPROV);
			}				
			for ($i=0; $i<$this->dws['dw_item_faprov']->row_count(); $i++) 
				$this->dws['dw_item_faprov']->set_item($i, 'COD_FAPROV', $COD_FAPROV);
			
				if (!$this->dws['dw_item_faprov']->update($db, $COD_FAPROV)) return false;
				
			return true;
		}
		return false;		
	}
	function print_record() {	
		$cod_faprov	= $this->get_key();

		$sql= "SELECT   COD_FAPROV
						,ORIGEN_FAPROV ORIGEN_FAPROV_H	
						,convert(varchar(20), GETDATE(), 103) FECHA
						,convert(varchar(20), F.FECHA_REGISTRO, 103) FECHA_REGISTRO
						,U.NOM_USUARIO
						,E.NOM_EMPRESA
						,F.COD_EMPRESA
						,E.RUT
						,E.DIG_VERIF
						,S.DIRECCION
						,COM.NOM_COMUNA
						,CIU.NOM_CIUDAD
						,S.TELEFONO 
						,S.FAX
						,TF.NOM_TIPO_FAPROV
						,NRO_FAPROV
						,convert(varchar(20), FECHA_FAPROV, 103) FECHA_FAPROV
						,TOTAL_NETO
						,MONTO_IVA
						,TOTAL_CON_IVA
						-- datos historicos(despliega la fecha en que se cambia el estado_faprov)-----
						,(select	TOP 1 convert(varchar(20), LG.FECHA_CAMBIO, 103) +'  '+ convert(varchar(20), LG.FECHA_CAMBIO, 8) FECHA_CAMBIO
							from	LOG_CAMBIO LG, DETALLE_CAMBIO DC
							where	LG.NOM_TABLA = 'FAPROV'
							and		LG.KEY_TABLA = convert(varchar,F.COD_FAPROV)
							and		LG.COD_LOG_CAMBIO = DC.COD_LOG_CAMBIO
							and		DC.NOM_CAMPO = 'COD_ESTADO_FAPROV' 
							order by	LG.FECHA_CAMBIO desc)	FECHA_CAMBIO
						-- datos historicos(despliega el usuario que cambia el estado_faprov)-----
						,(	select	TOP 1 U.NOM_USUARIO
							from	LOG_CAMBIO LG, DETALLE_CAMBIO DC, USUARIO U
							where	LG.NOM_TABLA = 'FAPROV' 
							and		LG.KEY_TABLA = convert(varchar,F.COD_FAPROV)
							and		LG.COD_LOG_CAMBIO = DC.COD_LOG_CAMBIO
							and		LG.COD_USUARIO = U.COD_USUARIO
							and		DC.NOM_CAMPO = 'COD_ESTADO_FAPROV' 
							order by	LG.FECHA_CAMBIO desc)	USUARIO_CAMBIO	
						,F.COD_TIPO_FAPROV
						,case F.COD_TIPO_FAPROV 
							when 3 then 'Monto Bruto' 
							else 'Monto Neto'
						end LABEL_BRUTO_NETO
						,case F.COD_TIPO_FAPROV 
							when 3 then 'Monto Retención' 
							else 'Monto IVA'
						end LABEL_RETENCION_IVA
						,case F.COD_TIPO_FAPROV 
							when 3 then 'Total Líquido' 
							else 'Total c/IVA'
						end LABEL_TOTAL				
				FROM	FAPROV F	LEFT OUTER JOIN EMPRESA E ON F.COD_EMPRESA = E.COD_EMPRESA
									LEFT OUTER JOIN SUCURSAL S ON F.COD_EMPRESA = S.COD_EMPRESA
									LEFT OUTER JOIN COMUNA COM ON S.COD_COMUNA = COM.COD_COMUNA
									LEFT OUTER JOIN CIUDAD CIU ON S.COD_CIUDAD = CIU.COD_CIUDAD
						, TIPO_FAPROV TF,ESTADO_FAPROV EF, USUARIO U
				WHERE	F.COD_FAPROV = $cod_faprov
				AND		F.COD_USUARIO = U.COD_USUARIO
				AND		F.COD_TIPO_FAPROV = TF.COD_TIPO_FAPROV
				AND		F.COD_ESTADO_FAPROV = EF.COD_ESTADO_FAPROV
				AND		S.DIRECCION_FACTURA = 'S'";
				
		$labels = array();
		$labels['strCOD_FAPROV'] = $cod_faprov;
		$rpt = new rpt_print_faprov($sql, $this->root_dir.'appl/faprov/faprov.xml', $labels, "Recepción de Factura".$cod_faprov, 0);
		$this->_load_record();
		return true;
	}
	function detalle_record($rec_no) {
		session::set('DESDE_wo_'.$this->nom_tabla, 'desde output');	// para indicar que viene del output
		$url = $this->get_url_mantenedor();
		header ('Location:'.$url.'/wi_'.$this->nom_tabla.'.php?rec_no='.$rec_no.'&cod_item_menu='.$this->cod_item_menu);
	}
	function crear_faprov_desde($tipo_faprov) {
		session::set('FAPROV_CREADA_DESDE', $tipo_faprov);
		$res = explode("|", $tipo_faprov);			
		$faprov_desde = $res[0];
		$cod_orden_compra = $res[1];
		
		if ($faprov_desde=='ORDEN_COMPRA' && $cod_orden_compra!='') {
			// valida que exista la OC
			$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
			$sql = "select count(*) CANT
					from ORDEN_COMPRA
					where COD_ORDEN_COMPRA = $cod_orden_compra";
			$result = $db->build_results($sql);
			if ($result[0]['CANT']==0) {
				$this->redraw();
				$this->alert("La orden de compra $cod_orden_compra no existe");
				return;
			}
			$sql = "SELECT	 TIPO_ORDEN_COMPRA
							,COD_ESTADO_ORDEN_COMPRA
					FROM ORDEN_COMPRA
					WHERE cod_orden_compra = $cod_orden_compra";
			$result = $db->build_results($sql);
			$tipo_oc = $result[0]['TIPO_ORDEN_COMPRA'];
			$cod_estado_oc = $result[0]['COD_ESTADO_ORDEN_COMPRA'];
			
			if ($tipo_oc != 'GASTO_FIJO') {
				// Valida que la OC este pendiente de FA
				$sql = "SELECT  dbo.f_oc_get_saldo_sin_faprov(COD_ORDEN_COMPRA) SALDO
						from ORDEN_COMPRA
						where COD_ORDEN_COMPRA = $cod_orden_compra";
				$result = $db->build_results($sql);
				if ($result[0]['SALDO'] <= 0) {		
					$sql = "SELECT COD_FAPROV
							FROM ITEM_FAPROV
							WHERE COD_DOC = $cod_orden_compra";
					$result = $db->build_results($sql);
					$nro_factura = '';
					for ($i = 0; $i < count($result); $i++){
						$nro_factura .= $result[$i]['COD_FAPROV'].", ";
					}
					$nro_factura = substr($nro_factura, 0, strlen($nro_factura) -2);
					$this->redraw();
					$this->alert("La OC Nº $cod_orden_compra ya tiene recepción de facturas por el total.".'\n'."La(s) Factura(s) proveedor asociadas a la OC son: $nro_factura");
					return;
				}
			}else{
				
				$sql="SELECT  COD_ESTADO_ORDEN_COMPRA
						from ORDEN_COMPRA
						where COD_ORDEN_COMPRA = $cod_orden_compra";
				$result = $db->build_results($sql);
				$cod_estado_orden_compra = $result[0]['COD_ESTADO_ORDEN_COMPRA'];
				if($cod_estado_orden_compra <> 1){
				//if  preguntado por autgoriza  y no este 
				$sql = "SELECT  dbo.f_oc_get_saldo_sin_faprov(COD_ORDEN_COMPRA) SALDO
						from ORDEN_COMPRA
						where COD_ORDEN_COMPRA = $cod_orden_compra";
				$result = $db->build_results($sql);
//				echo $result[0]['SALDO'];
				if ($result[0]['SALDO'] <= 0) {		
					$sql = "SELECT COD_FAPROV
							FROM ITEM_FAPROV
							WHERE COD_DOC = $cod_orden_compra";
					$result = $db->build_results($sql);
					$nro_factura = '';
					for ($i = 0; $i < count($result); $i++){
						$nro_factura .= $result[$i]['COD_FAPROV'].", ";
					}
					$nro_factura = substr($nro_factura, 0, strlen($nro_factura) -2);
					$this->redraw();
					$this->alert("La OC Nº $cod_orden_compra ya tiene recepción de facturas por el total.".'\n'."La(s) Factura(s) proveedor asociadas a la OC son: $nro_factura");
					return;
				}else{
					if ($cod_estado_oc != 4) {
						$this->redraw();
						//$this->alert("La OC Nº $cod_orden_compra ya tiene recepción de facturas por el total.".'\n'."La(s) Factura(s) proveedor asociadas a la OC son: $nro_factura");
						$this->alert("La OC Nº $cod_orden_compra no esta autorizada.");
						return;		
					}
				}
			  }else{
			  	$this->redraw();
						//$this->alert("La OC Nº $cod_orden_compra ya tiene recepción de facturas por el total.".'\n'."La(s) Factura(s) proveedor asociadas a la OC son: $nro_factura");
						$this->alert("La OC Nº $cod_orden_compra no esta autorizada.");
						return;		
			  }	
			}
		}
		$this->detalle_record(K_NEW_RECORD);
	}
	function procesa_event() {		
		if(isset($_POST['b_create_x'])){
			$this->crear_faprov_desde($_POST['wi_hidden']);
		}
		else if(isset($_POST['b_asignar_x']))
			$this->asignar($_POST['wo_hidden']);
		else
			parent::procesa_event();
	}
}

class rpt_print_faprov extends reporte {
	const K_ESTADO_FAPROV_INGRESADA 		= 1;
	const K_COD_TIPO_FAPROV_BH				= 3;	

	function rpt_print_faprov($sql, $xml, $labels=array(), $titulo, $con_logo, $vuelve_a_presentacion=false) {
		parent::reporte($sql, $xml, $labels, $titulo, $con_logo, $vuelve_a_presentacion);		
	}

	function modifica_pdf(&$pdf) {
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$result = $db->build_results($this->sql);

		$COD_FAPROV			=	$result[0]['COD_FAPROV'];
		$COD_TIPO_FAPROV	=	$result[0]['COD_TIPO_FAPROV'];
		$ORIGEN_FAPROV_H 	= 	$result[0]['ORIGEN_FAPROV_H'];

		if ($ORIGEN_FAPROV_H == 'ORDEN_COMPRA') {
			$sql_origen = "SELECT  IT.COD_DOC
									,convert(varchar(20), OC.FECHA_ORDEN_COMPRA, 103) FECHA_ITEM 
									,OC.REFERENCIA REFERENCIA_ITEM
									,OC.COD_NOTA_VENTA
									,OC.TOTAL_NETO TOTAL_NETO_ITEM
									,OC.MONTO_IVA MONTO_IVA_ITEM 
									,OC.TOTAL_CON_IVA TOTAL_CON_IVA_ITEM
									,dbo.f_oc_get_saldo_sin_faprov(OC.COD_ORDEN_COMPRA) + IT.MONTO_ASIGNADO SALDO_SIN_FAPROV
									,IT.MONTO_ASIGNADO
							from	ITEM_FAPROV IT, ORDEN_COMPRA OC, FAPROV F
							where	IT.COD_FAPROV = $COD_FAPROV 
							AND		OC.COD_ORDEN_COMPRA = IT.COD_DOC
							AND		F.COD_FAPROV = IT.COD_FAPROV 
							Order by	OC.COD_ORDEN_COMPRA asc";
		}			
		else {
			$sql_origen = "SELECT  IT.COD_DOC
									,convert(varchar(20), P.FECHA_PARTICIPACION, 103) FECHA_ITEM 
									,'-' REFERENCIA_ITEM
									,'-' COD_NOTA_VENTA
									,P.TOTAL_NETO TOTAL_NETO_ITEM
									,P.MONTO_IVA MONTO_IVA_ITEM 
									,P.TOTAL_CON_IVA TOTAL_CON_IVA_ITEM
									,dbo.f_part_get_saldo_sin_faprov(P.COD_PARTICIPACION) + IT.MONTO_ASIGNADO SALDO_SIN_FAPROV
									,IT.MONTO_ASIGNADO
							from	ITEM_FAPROV IT, PARTICIPACION P, FAPROV F
							where	IT.COD_FAPROV = $COD_FAPROV
							AND		P.COD_PARTICIPACION = IT.COD_DOC
							AND		F.COD_FAPROV = IT.COD_FAPROV 
							Order by	P.COD_PARTICIPACION asc";
		}

		$result_sql_origen = $db->build_results($sql_origen);

		//CANTIDAD DE ITEM_FAPROV
		$count_sql_origen = count($result_sql_origen);

		if($count_sql_origen == 0){
			$y_ini = $pdf->GetY();
			
			//dibujando los TITULOS  para Doctos.	
			$pdf->SetFont('Arial','B',10);
			$pdf->SetTextColor(4, 22, 114);
			$pdf->SetXY(30,$y_ini + 5);
			$pdf->Cell(555,17,'No existen documentos asociados', 'LTBR', '','L');
			
		}else{
				
			$y_ini = $pdf->GetY();
			
			//dibujando los TITULOS  para Doctos.	
			$pdf->SetFont('Arial','B',10);
			$pdf->SetTextColor(4, 22, 114);
			$pdf->SetXY(30,$y_ini + 5);
			if ($ORIGEN_FAPROV_H == 'ORDEN_COMPRA')
				$pdf->Cell(385,17,'Ordenes de Compra', '', '','L');
			else
				$pdf->Cell(385,17,'Participación', '', '','L');
			
			$pdf->SetFont('Arial','B',9);
			$pdf->SetXY(30,$y_ini + 20);
			$pdf->Cell(50,15,'Código', 'LTB', '','C');
			$pdf->SetXY(80,$y_ini + 20);
			$pdf->Cell(50,15,'Fecha', 'LTB', '','C');
			$pdf->SetXY(130,$y_ini + 20);
			$pdf->Cell(145,15,'Referencia', 'LTB', '','C');
			$pdf->SetXY(275,$y_ini + 20);
			$pdf->Cell(50,15,'Nº NV', 'LTRB', '','C');
			$pdf->SetXY(325,$y_ini + 20);
			
			if($COD_TIPO_FAPROV == self::K_COD_TIPO_FAPROV_BH){
				$pdf->Cell(60,15,'Monto Bruto', 'TRB', '','C');
				$pdf->SetXY(385,$y_ini + 20);
				$pdf->Cell(60, 15,'Retención', 'TRB', '','C');
				$pdf->SetXY(440,$y_ini + 20);
				$pdf->Cell(65, 15,'Total Líquido', 'TRB', '','C');
			}
			else{
				$pdf->Cell(60,15,'Monto Neto', 'TRB', '','C');
				$pdf->SetXY(385,$y_ini + 20);
				$pdf->Cell(60, 15,'Monto IVA', 'TRB', '','C');
				$pdf->SetXY(440,$y_ini + 20);
				$pdf->Cell(65, 15,'Total c/ IVA', 'TRB', '','C');
			}
			$pdf->SetXY(505,$y_ini + 20);
			$pdf->Cell(80, 15,'Monto Asignado', 'TRB', '','C');	

			$suma_oc = 0;
			for($i=0, $ii = 0; $i<$count_sql_origen; $i++, $ii++){
				if ($ii == 10) {
						$ii = 0;
						$pdf->AddPage();
						$y_ini = 90;
						$pdf->SetFont('Arial','B',9);
					$pdf->SetXY(30,$y_ini + 20);
					$pdf->Cell(50,15,'Código', 'LTB', '','C');
					$pdf->SetXY(80,$y_ini + 20);
					$pdf->Cell(50,15,'Fecha', 'LTB', '','C');
					$pdf->SetXY(130,$y_ini + 20);
					$pdf->Cell(145,15,'Referencia', 'LTB', '','C');
					$pdf->SetXY(275,$y_ini + 20);
					$pdf->Cell(50,15,'Nº NV', 'LTRB', '','C');
					$pdf->SetXY(325,$y_ini + 20);						
				}
				$nro_oc			 =	$result_sql_origen[$i]['COD_DOC'];
				$fecha_oc		 =	$result_sql_origen[$i]['FECHA_ITEM'];
				$referencia_oc	 =	$result_sql_origen[$i]['REFERENCIA_ITEM'];
				$cod_nota_venta	 =	$result_sql_origen[$i]['COD_NOTA_VENTA'];
				$total_neto_item =	number_format($result_sql_origen[$i]['TOTAL_NETO_ITEM'], 0, ',', '.');
				$monto_iva_item	 =	number_format($result_sql_origen[$i]['MONTO_IVA_ITEM'], 0, ',', '.');
				$total_con_iva_item =	number_format($result_sql_origen[$i]['TOTAL_CON_IVA_ITEM'], 0, ',', '.');
				$monto_asignado		=	number_format($result_sql_origen[$i]['MONTO_ASIGNADO'], 0, ',', '.');
				$monto_oc			=	$result_sql_origen[$i]['MONTO_ASIGNADO'];
	
				$referencia_oc = substr($referencia_oc, 0, 25);// se deja en 25 porque hay casos que se expande mucho
							
				$suma_oc 	=	$suma_oc + $monto_oc;
				
				//FUENTE DE TEXTO
				$pdf->SetFont('Arial','',9);
				$pdf->SetTextColor(0, 0, 0);
				
				$pdf->SetXY(30, $y_ini + 35+(15*$ii));
				$pdf->Cell(50, 15, $nro_oc, 'LTB', '', 'C');
				$pdf->SetXY(80, $y_ini + 35+(15*$ii));
				$pdf->Cell(50, 15, $fecha_oc, 'LTB', '', 'C');
				$pdf->SetXY(130, $y_ini + 35+(15*$ii));
				$pdf->Cell(145, 15, $referencia_oc, 'LTRB', '', 'L');
				$pdf->SetXY(275, $y_ini + 35+(15*$ii));
				$pdf->Cell(50, 15, $cod_nota_venta, 'LTB', '', 'C');
				$pdf->SetXY(325, $y_ini + 35+(15*$ii));
				$pdf->Cell(60, 15, $total_neto_item, 'LTRB', '', 'R');
				$pdf->SetXY(385, $y_ini + 35+(15*$ii));
				$pdf->Cell(60, 15, $monto_iva_item, 'TRB', '', 'R');
				$pdf->SetXY(440, $y_ini + 35+(15*$ii));
				$pdf->Cell(65, 15, $total_con_iva_item, 'TRB', '', 'R');
				$pdf->SetXY(505, $y_ini + 35+(15*$ii));
				$pdf->Cell(80, 15, $monto_asignado, 'TRB', '', 'R');
			}
			
			$y_ini = $pdf->GetY();
			
			
			$suma_oc		=	number_format($suma_oc, 0, ',', '.');
			
			$pdf->SetFont('Arial','',9);
			$pdf->SetTextColor(0, 0, 128);
			$pdf->SetFont('Arial','B',9);
			$pdf->SetXY(445, $y_ini + 35+(15*$ii));
			$pdf->Cell(60, 15, 'Total  $', 'LTRB', '', 'R');
			
			$pdf->SetFont('Arial','',9);
			$pdf->SetTextColor(0, 0, 0);
			$pdf->SetXY(505, $y_ini + 35+(15*$ii));
			$pdf->Cell(80, 15, $suma_oc, 'TRB', '', 'R');	
		}
	}
}
?>