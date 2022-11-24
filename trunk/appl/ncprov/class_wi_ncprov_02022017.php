<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../empresa/class_dw_help_empresa.php");


class dw_ncprov_faprov extends datawindow {
	function dw_ncprov_faprov() {		
		$sql = "SELECT 	'S' SELECCION
						,NF.COD_NCPROV_FAPROV
						,NF.COD_NCPROV
						,NF.COD_FAPROV
						,F.NRO_FAPROV 
						,convert(varchar(20), F.FECHA_FAPROV, 103) FECHA_FAPROV_FA
						,F.TOTAL_NETO TOTAL_NETO_FA 
						,F.MONTO_IVA MONTO_IVA_FA 
						,F.TOTAL_CON_IVA TOTAL_CON_IVA_FA 
						,dbo.f_faprov_get_saldo_sin_ncprov(F.COD_FAPROV)+ NF.MONTO_ASIGNADO SALDO_SIN_NCPROV
						,dbo.f_faprov_get_saldo_sin_ncprov(F.COD_FAPROV)+ NF.MONTO_ASIGNADO SALDO_SIN_NCPROV_H
						,NF.MONTO_ASIGNADO
						,0 MONTO_ASIGNADO_C 
				FROM	NCPROV_FAPROV NF, FAPROV F, NCPROV N
				WHERE 	NF.COD_NCPROV = {KEY1} AND
						N.COD_NCPROV = NF.COD_NCPROV AND
						F.COD_FAPROV = NF.COD_FAPROV
				order by F.NRO_FAPROV desc";
		
		parent::datawindow($sql, 'NCPROV_FAPROV', true, true);	
		
		$this->add_control($control=new edit_check_box('SELECCION','S', 'N'));
		$control->set_onChange("asignacion_monto(this);");
		
		$this->add_control(new static_num('TOTAL_NETO_FA'));
		$this->add_control(new static_num('MONTO_IVA_FA'));
		$this->add_control(new static_num('TOTAL_CON_IVA_FA'));
		$this->add_control(new static_num('SALDO_SIN_NCPROV'));
		$this->add_control(new edit_precio('SALDO_SIN_NCPROV_H',10, 10));
		$this->controls['SALDO_SIN_NCPROV_H']->type = 'hidden';
		
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
	function insert_row($row=-1) {
		$row = parent::insert_row($row);
		return $row;
	}

	function update($db, $COD_NCPROV)	{
		$sp = 'spu_ncprov_faprov';
		$operacion = 'DELETE_ALL';
		$param = "'$operacion',null, $COD_NCPROV";			
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
			$COD_NCPROV_FAPROV		 	= $this->get_item($i, 'COD_NCPROV_FAPROV');
			$COD_NCPROV					= $this->get_item($i, 'COD_NCPROV');
			$COD_FAPROV 				= $this->get_item($i, 'COD_FAPROV');	
			$MONTO_ASIGNADO				= $this->get_item($i, 'MONTO_ASIGNADO');
			
			$COD_NCPROV_FAPROV = ($COD_NCPROV_FAPROV =='') ? "null" : $COD_NCPROV_FAPROV;
			
			$operacion = 'INSERT';
			$param = "'$operacion',$COD_NCPROV_FAPROV, $COD_NCPROV, $COD_FAPROV, $MONTO_ASIGNADO";			
			
			if (!$db->EXECUTE_SP($sp, $param))
				
				return false;
			}	
		return true;
	}
}

class dw_ncprov extends dw_help_empresa{
	const K_ESTADO_NCPROV_INGRESADA 		= 1;
	const K_ESTADO_NCPROV_APROBADA 			= 2;
	const K_ESTADO_NCPROV_ANULADA 			= 4;
	
	const K_PARAM_PORC_IVA			= 1;
	
	
	function dw_ncprov() {
		
		$sql = "SELECT N.COD_NCPROV
						,convert(varchar(20), N.FECHA_REGISTRO, 103) FECHA_REGISTRO
						,N.COD_USUARIO
						,N.COD_EMPRESA
						,N.NRO_NCPROV
						,convert(varchar(20), N.FECHA_NCPROV, 103) FECHA_NCPROV
						,N.TOTAL_NETO
						,N.TOTAL_NETO TOTAL_NETO_H
						,N.MONTO_IVA
						,N.MONTO_IVA MONTO_IVA_H
						,N.TOTAL_CON_IVA
						,N.TOTAL_CON_IVA TOTAL_CON_IVA_H
						,E.NOM_EMPRESA
						,E.ALIAS
						,E.RUT
						,E.DIG_VERIF
						,EN.NOM_ESTADO_NCPROV
						,N.COD_USUARIO_ANULA
						,convert(varchar(20), N.FECHA_ANULA, 103) +'  '+ convert(varchar(20), N.FECHA_ANULA, 8)FECHA_ANULA
						,N.MOTIVO_ANULA
						,U.NOM_USUARIO
						,N.COD_ESTADO_NCPROV
						----- datos historicos(despliega la fecha en que se cambia el estado_ncprov)-----
						,(select TOP 1 convert(varchar(20), LG.FECHA_CAMBIO, 103) +'  '+ convert(varchar(20), LG.FECHA_CAMBIO, 8) FECHA_CAMBIO
							from	LOG_CAMBIO LG, DETALLE_CAMBIO DC
							where	LG.NOM_TABLA = 'NCPROV' and
									LG.KEY_TABLA = convert(varchar, N.COD_NCPROV) and
									LG.COD_LOG_CAMBIO = DC.COD_LOG_CAMBIO and
									DC.NOM_CAMPO = 'COD_ESTADO_NCPROV' 
									order by LG.FECHA_CAMBIO desc) FECHA_CAMBIO
						----- datos historicos(despliega el usuario que cambia el estado_ncprov)-----
						,(select TOP 1 U.NOM_USUARIO
							from	LOG_CAMBIO LG, DETALLE_CAMBIO DC, USUARIO U
							where	LG.NOM_TABLA = 'NCPROV' and
									LG.KEY_TABLA = convert(varchar, N.COD_NCPROV) and
									LG.COD_LOG_CAMBIO = DC.COD_LOG_CAMBIO and
									LG.COD_USUARIO = U.COD_USUARIO and 
									DC.NOM_CAMPO = 'COD_ESTADO_NCPROV' 
									order by LG.FECHA_CAMBIO desc)USUARIO_CAMBIO		
						,case N.COD_ESTADO_NCPROV 
							when ".self::K_ESTADO_NCPROV_ANULADA." then '' 
							else 'none'
						end TR_DISPLAY			
						,dbo.f_get_parametro(".self::K_PARAM_PORC_IVA.") PORC_IVA_H
						,COD_CUENTA_COMPRA
						,COD_TIPO_NCPROV
				FROM 	NCPROV N, EMPRESA E, ESTADO_NCPROV EN, USUARIO U
				WHERE 	N.COD_NCPROV = {KEY1} AND
						E.COD_EMPRESA = N.COD_EMPRESA AND
						EN.COD_ESTADO_NCPROV = N.COD_ESTADO_NCPROV AND
						U.COD_USUARIO = N.COD_USUARIO";

		
		// DATAWINDOWS HELP_EMPRESA
		parent::dw_help_empresa($sql, '', false, false, 'P');	// El último parametro indica que solo acepta proveedores
		
		// DATOS GENERALES
		$this->add_control(new edit_nro_doc('COD_NCPROV','NCPROV'));
		
		$sql = "select COD_CUENTA_COMPRA
						,NOM_CUENTA_COMPRA
				from CUENTA_COMPRA
				order by NOM_CUENTA_COMPRA";								
		$this->add_control(new drop_down_dw('COD_CUENTA_COMPRA', $sql,125));
		
		$sql = "SELECT COD_TIPO_NCPROV
					  ,NOM_TIPO_NCPROV 
				FROM TIPO_NCPROV
				ORDER BY COD_TIPO_NCPROV";								
		$this->add_control($control = new drop_down_dw('COD_TIPO_NCPROV', $sql,125));
		$control->set_onChange("calc_total_con_iva();");
		
		$sql_estado_ncprov   	= "	select	COD_ESTADO_NCPROV
											,NOM_ESTADO_NCPROV
									from 	ESTADO_NCPROV
									where	COD_ESTADO_NCPROV = ".self::K_ESTADO_NCPROV_INGRESADA." or
											COD_ESTADO_NCPROV = ".self::K_ESTADO_NCPROV_APROBADA." 
									order by		COD_ESTADO_NCPROV";								
		$this->add_control(new drop_down_dw('COD_ESTADO_NCPROV', $sql_estado_ncprov,125));
		
		$this->add_control(new edit_num('NRO_NCPROV',10, 10, 0, true, false, false));
		$this->add_control(new edit_date('FECHA_NCPROV'));

		$this->add_control($control = new edit_num('TOTAL_NETO'));
		$control->set_onChange("calc_total_con_iva();");
		
		$this->add_control(new static_num('MONTO_IVA'));
		$this->add_control(new edit_text('MONTO_IVA_H',20,20,'hidden'));
		
		$this->add_control(new static_num('TOTAL_CON_IVA'));
		$this->add_control(new edit_text('TOTAL_CON_IVA_H',20,20,'hidden'));
		
		$this->add_control(new edit_text('TOTAL_NETO_H',20,20,'hidden'));
		$this->add_control(new edit_text('PORC_IVA_H',20,20,'hidden'));
		
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
		
		// asigna los mandatorys/
		$this->set_mandatory('NRO_NCPROV', 'Nº Documento');
		$this->set_mandatory('FECHA_NCPROV', 'Fecha Documento');
		$this->set_mandatory('COD_TIPO_NCPROV', 'Tipo Nota Crédito');	
	}
	
	function fill_record(&$temp, $record) {	
		parent::fill_record($temp, $record);
		
			$COD_NCPROV = $this->get_item(0, 'COD_NCPROV');
			$COD_ESTADO_NCPROV = $this->get_item(0, 'COD_ESTADO_NCPROV');
			
			if (($COD_NCPROV !='')or($COD_ESTADO_NCPROV != 1))  //
				$temp->setVar('DISABLE_BUTTON', 'style="display:none"');
			else{	
					if ($this->entrable)
						$temp->setVar('DISABLE_BUTTON', '');
					else
						$temp->setVar('DISABLE_BUTTON', 'disabled="disabled"');
			}				
	}
}


class wi_ncprov extends w_input {
	
	const K_ESTADO_NCPROV_INGRESADA 		= 1;
	const K_ESTADO_NCPROV_APROBADA 			= 2;
	const K_ESTADO_NCPROV_ANULADA 			= 4;
	const K_PARAM_PORC_IVA					= 1;
	const K_CTA_COMPRA_COMERCIAL			= 6;
	
	
	function wi_ncprov($cod_item_menu) {
		parent::w_input('ncprov', $cod_item_menu);
		
		// DATAWINDOWS NCPROV
		$this->dws['dw_ncprov'] = new dw_ncprov();

		//auditoria Solicitado por IS.
		$this->add_auditoria('COD_CUENTA_COMPRA');
		$this->add_auditoria('NRO_NCPROV');
		$this->add_auditoria('FECHA_NCPROV');
		$this->add_auditoria('COD_ESTADO_NCPROV');

		// DATAWINDOWS NCPROV_FAPROV
		$this->dws['dw_ncprov_faprov'] = new dw_ncprov_faprov();
		
		//al momento de crear un nuevo registro se iniciara en rut de empresa proveedor
		$this->set_first_focus('RUT');
	
	}
	
	function new_record() {
		$this->dws['dw_ncprov']->insert_row();
		$this->dws['dw_ncprov']->set_item(0, 'TR_DISPLAY', 'none');
		$this->dws['dw_ncprov']->set_item(0, 'FECHA_REGISTRO', substr($this->current_date_time(), 0, 16));
		$this->dws['dw_ncprov']->set_item(0, 'COD_USUARIO', $this->cod_usuario);
		$this->dws['dw_ncprov']->set_item(0, 'NOM_USUARIO', $this->nom_usuario);
	
		$this->dws['dw_ncprov']->set_item(0, 'COD_ESTADO_NCPROV', self::K_ESTADO_NCPROV_INGRESADA);
		
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$sql = "select dbo.f_get_parametro(".self::K_PARAM_PORC_IVA.") PORC_IVA_H";
		$result = $db->build_results($sql);
		$this->dws['dw_ncprov']->set_item(0, 'PORC_IVA_H', $result[0]['PORC_IVA_H']);
		
		$this->dws['dw_ncprov']->set_item(0, 'COD_CUENTA_COMPRA', self::K_CTA_COMPRA_COMERCIAL);
		
	}
	
	function load_record() {
		$cod_ncprov = $this->get_item_wo($this->current_record, 'COD_NCPROV');
		$this->dws['dw_ncprov']->retrieve($cod_ncprov);
		$cod_empresa = $this->dws['dw_ncprov']->get_item(0, 'COD_EMPRESA');
		
		$COD_ESTADO_NCPROV = $this->dws['dw_ncprov']->get_item(0, 'COD_ESTADO_NCPROV');
		
		$this->b_no_save_visible = true;
		$this->b_save_visible 	 = true;
		$this->b_modify_visible	 = true;
		
		$this->dws['dw_ncprov']->set_entrable('NRO_NCPROV'			, false);
		$this->dws['dw_ncprov']->set_entrable('FECHA_NCPROV'		, false);

		// DATOS DE EMPRESA NO MODIFICABLES AL CONSULTAR
		$this->dws['dw_ncprov']->set_entrable('COD_EMPRESA'			,false);
		$this->dws['dw_ncprov']->set_entrable('NOM_EMPRESA'			,false);	
		$this->dws['dw_ncprov']->set_entrable('RUT'					,false);
		$this->dws['dw_ncprov']->set_entrable('ALIAS'				,false);	
		
		$this->dws['dw_ncprov']->controls['USUARIO_CAMBIO']->type = '';
		$this->dws['dw_ncprov']->controls['FECHA_CAMBIO']->type = '';
		
		$this->dws['dw_ncprov']->controls['COD_USUARIO_ANULA']->type = 'hidden';
		$this->dws['dw_ncprov']->controls['FECHA_ANULA']->type = 'hidden';
		
		
		if ($COD_ESTADO_NCPROV == self::K_ESTADO_NCPROV_INGRESADA) {
			$this->dws['dw_ncprov']->set_entrable('NRO_NCPROV'		, true);
			$this->dws['dw_ncprov']->set_entrable('FECHA_NCPROV'	, true);
			
			
			//select que trae todos los estado de faprov siempre y cuando este en estado ingresada
			$sql = "select 	COD_ESTADO_NCPROV
							,NOM_ESTADO_NCPROV
					from ESTADO_NCPROV	
					order by COD_ESTADO_NCPROV";
			
			unset($this->dws['dw_ncprov']->controls['COD_ESTADO_NCPROV']);
			$this->dws['dw_ncprov']->add_control($control = new drop_down_dw('COD_ESTADO_NCPROV',$sql,125));
			$control->set_onChange("mostrarOcultar_Anula();");
			$this->dws['dw_ncprov']->add_control(new edit_text_upper('MOTIVO_ANULA',120, 100));
		}
		else if ($COD_ESTADO_NCPROV == self::K_ESTADO_NCPROV_APROBADA){
			$sql = "select 	COD_ESTADO_NCPROV
							,NOM_ESTADO_NCPROV
					from ESTADO_NCPROV	
					order by COD_ESTADO_NCPROV";
			
			unset($this->dws['dw_ncprov']->controls['COD_ESTADO_NCPROV']);
			$this->dws['dw_ncprov']->add_control($control = new drop_down_dw('COD_ESTADO_NCPROV',$sql,125));
			$control->set_onChange("mostrarOcultar_Anula();");
			$this->dws['dw_ncprov']->add_control(new edit_text_upper('MOTIVO_ANULA',120, 100));
			
			$this->dws['dw_ncprov_faprov']->set_entrable_dw(false);
		}
	
		else if ($COD_ESTADO_NCPROV == self::K_ESTADO_NCPROV_ANULADA) {
			$sql = "select 	COD_ESTADO_NCPROV
							,NOM_ESTADO_NCPROV
					from ESTADO_NCPROV	
					where COD_ESTADO_NCPROV = ".self::K_ESTADO_NCPROV_ANULADA."";
			
			unset($this->dws['dw_ncprov']->controls['COD_ESTADO_NCPROV']);
			$this->dws['dw_ncprov']->add_control($control = new drop_down_dw('COD_ESTADO_NCPROV',$sql,125));
			
			$this->b_no_save_visible = false;
			$this->b_save_visible 	 = false;
			$this->b_modify_visible  = false;
			
			$this->dws['dw_ncprov']->controls['USUARIO_CAMBIO']->type = 'hidden';
			$this->dws['dw_ncprov']->controls['FECHA_CAMBIO']->type = 'hidden';
			
			$this->dws['dw_ncprov']->controls['COD_USUARIO_ANULA']->type = '';
			$this->dws['dw_ncprov']->controls['FECHA_ANULA']->type = '';
		}
		$this->dws['dw_ncprov_faprov']->retrieve($cod_ncprov);
	}
	
	function get_key() {
		return $this->dws['dw_ncprov']->get_item(0, 'COD_NCPROV');
	}
	
	function save_record($db) {	
		$COD_NCPROV	 			= $this->get_key();		
		$FECHA_NCPROV			= $this->dws['dw_ncprov']->get_item(0, 'FECHA_NCPROV');
		$COD_USUARIO 			= $this->dws['dw_ncprov']->get_item(0, 'COD_USUARIO');
		$COD_EMPRESA			= $this->dws['dw_ncprov']->get_item(0, 'COD_EMPRESA');		
		$COD_ESTADO_NCPROV		= $this->dws['dw_ncprov']->get_item(0, 'COD_ESTADO_NCPROV');
		$NRO_NCPROV				= $this->dws['dw_ncprov']->get_item(0, 'NRO_NCPROV');
		$FECHA_NCPROV			= $this->dws['dw_ncprov']->get_item(0, 'FECHA_NCPROV');
		
		$TOTAL_NETO				= $this->dws['dw_ncprov']->get_item(0, 'TOTAL_NETO');
		$TOTAL_NETO				= ($TOTAL_NETO =='') ? 0 : "$TOTAL_NETO";
		
		$MONTO_IVA				= $this->dws['dw_ncprov']->get_item(0, 'MONTO_IVA_H');
		$MONTO_IVA				= ($MONTO_IVA =='') ? 0 : "$MONTO_IVA";
		
		$TOTAL_CON_IVA			= $this->dws['dw_ncprov']->get_item(0, 'TOTAL_CON_IVA_H');
		$TOTAL_CON_IVA			= ($TOTAL_CON_IVA =='') ? 0 : "$TOTAL_CON_IVA";
		
		$COD_CUENTA_COMPRA		= $this->dws['dw_ncprov']->get_item(0, 'COD_CUENTA_COMPRA');		
		$COD_CUENTA_COMPRA		= ($COD_CUENTA_COMPRA =='') ? "NULL" : $COD_CUENTA_COMPRA;
		
		$FECHA_ANULA		= $this->dws['dw_ncprov']->get_item(0, 'FECHA_ANULA');
		$FECHA_ANULA		= ($FECHA_ANULA =='') ? "NULL" : "$FECHA_ANULA";
		$MOTIVO_ANULA		= $this->dws['dw_ncprov']->get_item(0, 'MOTIVO_ANULA');
		$MOTIVO_ANULA		= ($MOTIVO_ANULA =='') ? "NULL" : "$MOTIVO_ANULA";
		$COD_USUARIO_ANULA	= $this->dws['dw_ncprov']->get_item(0, 'COD_USUARIO_ANULA');
		
		if (($MOTIVO_ANULA != '') && ($COD_USUARIO_ANULA == '')) // se anula 
			$COD_USUARIO_ANULA			= $this->cod_usuario;
		else
			$COD_USUARIO_ANULA			= "null";
		
		$COD_NCPROV = ($COD_NCPROV =='') ? "null" : $COD_NCPROV;
		
		$COD_TIPO_NCPROV	= $this->dws['dw_ncprov']->get_item(0, 'COD_TIPO_NCPROV');
		$COD_TIPO_NCPROV	= ($COD_TIPO_NCPROV =='') ? "null" : $COD_TIPO_NCPROV;		
		
		$sp = 'spu_ncprov';
	    if ($this->is_new_record())
	    	$operacion = 'INSERT';
	    else
	    	$operacion = 'UPDATE';
	    
		$param	= "	'$operacion'
					,$COD_NCPROV
					,$COD_USUARIO				
					,$COD_EMPRESA				
					,$COD_ESTADO_NCPROV			
					,$NRO_NCPROV	
					,'$FECHA_NCPROV'				
					,$TOTAL_NETO					
					,$MONTO_IVA
					,$TOTAL_CON_IVA
					,$COD_USUARIO_ANULA
					,'$MOTIVO_ANULA'
					,$COD_CUENTA_COMPRA
					,$COD_TIPO_NCPROV";
					
		if ($db->EXECUTE_SP($sp, $param)){
			if ($this->is_new_record()) {
				$COD_NCPROV = $db->GET_IDENTITY();
				$this->dws['dw_ncprov']->set_item(0, 'COD_NCPROV', $COD_NCPROV);
			}				
			for ($i=0; $i<$this->dws['dw_ncprov_faprov']->row_count(); $i++) 
				$this->dws['dw_ncprov_faprov']->set_item($i, 'COD_NCPROV', $COD_NCPROV);
			
				if (!$this->dws['dw_ncprov_faprov']->update($db, $COD_NCPROV)) return false;
				
			return true;
		}
		return false;		
	}
}
?>
