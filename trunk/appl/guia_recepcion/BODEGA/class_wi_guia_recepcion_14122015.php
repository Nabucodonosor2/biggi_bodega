<?php
require_once(dirname(__FILE__)."/../../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../../empresa/class_dw_help_empresa.php");

class dw_item_guia_recepcion extends dw_item_guia_recepcion_base {
	
	const K_ESTADO_GR_EMITIDA 		= 1;
	const K_TIPO_GR_DEVOLUCION		= 1;
	const K_TIPO_GR_GARANTIA		= 2;
	const K_TIPO_GR_OTRO			= 3;
	
	function dw_item_guia_recepcion() {
		$sql = " SELECT IGR.COD_ITEM_GUIA_RECEPCION 
						,IGR.COD_GUIA_RECEPCION 
						,IGR.COD_PRODUCTO
						,IGR.NOM_PRODUCTO
						,IGR.CANTIDAD
						,GR.COD_DOC
						,GR.COD_TIPO_GUIA_RECEPCION
						,IGR.COD_ITEM_DOC
						,case GR.TIPO_DOC 
							when 'FACTURA' THEN dbo.f_gr_fa_cant_por_recep(IGR.COD_ITEM_DOC)+ CANTIDAD
							ELSE CASE GR.TIPO_DOC
								WHEN 'GUIA_DESPACHO' THEN dbo.f_gr_gd_cant_por_recep(IGR.COD_ITEM_DOC)+ CANTIDAD
							end					
						end POR_RECEPCIONAR
						,case GR.TIPO_DOC 
							when 'FACTURA' THEN dbo.f_gr_fa_cant_por_recep(IGR.COD_ITEM_DOC) + CANTIDAD
							ELSE CASE GR.TIPO_DOC
								WHEN 'GUIA_DESPACHO' THEN dbo.f_gr_gd_cant_por_recep(IGR.COD_ITEM_DOC)+ CANTIDAD
							end
						end POR_RECEPCIONAR_H
						,case
							when IGR.COD_ITEM_DOC IS NULL then ''
							else 'none'
						end TD_DISPLAY_ELIMINAR
						,'none' TD_DISPLAY_POR_RECEP
						,GR.TIPO_DOC TIPO_DOC_GR
				FROM    ITEM_GUIA_RECEPCION IGR, GUIA_RECEPCION GR
				WHERE   IGR.COD_GUIA_RECEPCION = {KEY1} AND
						GR.COD_GUIA_RECEPCION  = IGR.COD_GUIA_RECEPCION 
						order by IGR.COD_ITEM_DOC";
		
		 
		parent::datawindow($sql, 'ITEM_GUIA_RECEPCION', true, true);
		$this->add_control(new edit_text_upper('COD_ITEM_GUIA_RECEPCION',10, 10, 'hidden'));
		$this->add_control($control = new edit_cantidad('CANTIDAD',12,10));
		$control->set_onChange("this.value = valida_ct_x_gd(this);");		
		$this->add_control(new static_num('POR_RECEPCIONAR',1));
		$this->add_control(new edit_cantidad('POR_RECEPCIONAR_H',10));
		$this->controls['POR_RECEPCIONAR_H']->type = 'hidden';
		$this->add_control(new edit_num('COD_ITEM_DOC',10, 10));
		$this->controls['COD_ITEM_DOC']->type = 'hidden';
		$this->add_controls_producto_help();		// Debe ir despues de los computed donde esta involucrado precio, porque add_controls_producto_help() afecta el campos PRECIO
		$this->set_first_focus('COD_PRODUCTO');
		$this->add_control(new edit_text_upper('NOM_PRODUCTO',100, 100));
		
		// asigna los mandatorys
		$this->set_mandatory('COD_PRODUCTO', 'Código del producto');
		$this->set_mandatory('CANTIDAD', 'Cantidad');
	}
	
	function insert_row($row=-1) {
		$row = parent::insert_row($row);
		$this->set_item($row, 'COD_ITEM_GUIA_RECEPCION', $this->row_count());
		$this->set_item($row, 'TD_DISPLAY_POR_RECEP', 'none');
		return $row;
	}
	
	function fill_record(&$temp, $record) {
		parent::fill_record($temp, $record);
		// si existe COD_ITEM_DOC no despliega boton "-".
		$COD_ITEM = $this->get_item(0, 'COD_ITEM_DOC');
		if ($COD_ITEM != ''){ 
			$row = $this->redirect($record);
			$eliminar = '<img src="../../../../commonlib/trunk/images/b_delete_line.jpg" onClick="del_line_item(\''.$this->label_record.'_'.$row.'\', \''.$this->nom_tabla.'\');" style="display:none">';
			$temp->setVar($this->label_record.".ELIMINAR_".strtoupper($this->label_record), $eliminar);
		}
	}
	
	function fill_template(&$temp) {
		parent::fill_template($temp);
		// si existe COD_DOC no despliega boton "+".
		
		if ($this->row_count()==0)
			$COD_ITEM = '';		// debe ser == '' para que se agregue el boton "+"
		else
			$COD_ITEM = $this->get_item(0, 'COD_ITEM_DOC');
		if ($COD_ITEM != ''){ 
			$agregar = '<img src="../../../../commonlib/trunk/images/b_add_line.jpg" onClick="add_line_item(\''.$this->label_record.'\', \''.$this->nom_tabla.'\');" style="display:none">';
			$temp->setVar("AGREGAR_".strtoupper($this->label_record), $agregar);
		}
	}
	
	function update($db, $COD_GUIA_RECEPCION)	{
		$sp = 'spu_item_guia_recepcion';
		$operacion = 'DELETE_ALL';
		$param = "'$operacion',null, $COD_GUIA_RECEPCION";			
		if (!$db->EXECUTE_SP($sp, $param)){
			return false;
		}

		for ($i = 0; $i < $this->row_count(); $i++){
		
			$statuts = $this->get_status_row($i);
			if ($statuts == K_ROW_NOT_MODIFIED || $statuts == K_ROW_NEW){
				continue;
			}
			$CANTIDAD = $this->get_item($i, 'CANTIDAD');
			if ($CANTIDAD == 0)
				continue;
			$COD_ITEM_GUIA_RECEPCION	= $this->get_item($i, 'COD_ITEM_GUIA_RECEPCION');
			$COD_GUIA_RECEPCION			= $this->get_item($i, 'COD_GUIA_RECEPCION');
			$COD_PRODUCTO	 			= $this->get_item($i, 'COD_PRODUCTO');
			$NOM_PRODUCTO 				= $this->get_item($i, 'NOM_PRODUCTO');
			$CANTIDAD 					= $this->get_item($i, 'CANTIDAD');		
			$COD_ITEM					= $this->get_item($i, 'COD_ITEM_DOC');
			$TIPO_DOC_GR 				= $this->get_item($i, 'TIPO_DOC_GR');
			
			if ($TIPO_DOC_GR == "'FACTURA'")
				$TIPO_DOC = "'ITEM_FACTURA'";
			else if ($TIPO_DOC_GR == "'GUIA_DESPACHO'")
				$TIPO_DOC = "'ITEM_GUIA_DESPACHO'";
			else if ($TIPO_DOC_GR == "'ARRIENDO'")
				$TIPO_DOC = "'ITEM_ARRIENDO'";
			else
				$TIPO_DOC = "null";
			
			$COD_ITEM_GUIA_RECEPCION   	= ($COD_ITEM_GUIA_RECEPCION =='') ? "null" : $COD_ITEM_GUIA_RECEPCION;
			$COD_ITEM					= ($COD_ITEM =='') ? "null" : $COD_ITEM;
			
			$operacion = 'INSERT';
			$param = "'$operacion', $COD_ITEM_GUIA_RECEPCION, $COD_GUIA_RECEPCION, '$COD_PRODUCTO', '$NOM_PRODUCTO', $CANTIDAD, $COD_ITEM, $TIPO_DOC";
			if (!$db->EXECUTE_SP($sp, $param))
				
				return false;
			}	
		return true;
	}
}	
	
class dw_guia_recepcion extends dw_guia_recepcion_base{
	
	const K_ESTADO_GR_EMITIDA 		= 1;
	const K_ESTADO_GR_IMPRESA	 	= 2;
	const K_ESTADO_GR_ANULADA	 	= 3;
	
	const K_TIPO_GR_DEVOLUCION		= 1;
	const K_TIPO_GR_GARANTIA		= 2;
	const K_TIPO_GR_OTRO			= 3;
	const K_TIPO_GR_ARRIENDO		= 4;
	
	function dw_guia_recepcion() {
		$sql = "SELECT	GR.COD_GUIA_RECEPCION 
						,convert(varchar(20), GR.FECHA_GUIA_RECEPCION, 103) FECHA_GUIA_RECEPCION
						,GR.COD_USUARIO
						,GR.COD_EMPRESA
						,GR.COD_ESTADO_GUIA_RECEPCION 
						,EGR.NOM_ESTADO_GUIA_RECEPCION 
						,GR.COD_TIPO_GUIA_RECEPCION
						,GR.TIPO_DOC
						,GR.NRO_DOC 
						,GR.COD_DOC
						,GR.OBS
						,GR.COD_USUARIO_ANULA	
						,convert(varchar(20), GR.FECHA_ANULA, 103) +'  '+ convert(varchar(20), GR.FECHA_ANULA, 8) FECHA_ANULA
						,GR.MOTIVO_ANULA
						,GR.COD_PERSONA
						,GR.COD_SUCURSAL  AS COD_SUCURSAL_FACTURA
						,E.NOM_EMPRESA
						,E.ALIAS
						,E.RUT
						,E.DIG_VERIF
						,E.GIRO
						,dbo.f_get_direccion('SUCURSAL', GR.COD_SUCURSAL, '[DIRECCION]  [NOM_COMUNA] [NOM_CIUDAD] - TELEFONO:[TELEFONO] - FAX:[FAX]') DIRECCION_FACTURA
						,U.NOM_USUARIO						
						,'' VISIBLE_TAB
						,case GR.COD_ESTADO_GUIA_RECEPCION
							when ".self::K_ESTADO_GR_ANULADA." then '' 
							else 'none'
						end TR_DISPLAY 	
						,case GR.COD_TIPO_GUIA_RECEPCION
							when ".self::K_TIPO_GR_OTRO." then 'none' 
							else ''
						end TR_DISPLAY_TIPO_DOC 
						,case
							when GR.COD_DOC IS NULL then ''
							else 'none'
						end TD_DISPLAY_ELIMINAR
						,'none' TD_DISPLAY_POR_RECEP
						,'' DISPLAY_RECEP_TODO
				FROM	GUIA_RECEPCION GR, EMPRESA E,ESTADO_GUIA_RECEPCION EGR,
						TIPO_GUIA_RECEPCION TGR, USUARIO U
				WHERE	GR.COD_GUIA_RECEPCION = {KEY1} AND
						GR.COD_EMPRESA = E.COD_EMPRESA AND 
						GR.COD_ESTADO_GUIA_RECEPCION = EGR.COD_ESTADO_GUIA_RECEPCION AND
						GR.COD_TIPO_GUIA_RECEPCION = TGR.COD_TIPO_GUIA_RECEPCION AND
						GR.COD_USUARIO = U.COD_USUARIO";


		////////////////////
		// tab GUIA_RECEPCION
		parent::dw_help_empresa($sql);

		// DATOS GENERALES
		$this->add_control(new edit_nro_doc('COD_GUIA_RECEPCION','GUIA_RECEPCION'));
		
		$this->add_control(new edit_text_upper('TIPO_DOC',20,30));
		$this->add_control($control = new drop_down_list('TIPO_DOC',array('','FACTURA','GUIA_DESPACHO'),array('','FACTURA','GUIA DESPACHO'),150));
		$control->set_onChange("mostrarOcultar_datos()");
		$this->add_control($control = new edit_num('NRO_DOC',10,10));
		$control->set_onChange("existe_fa_gd();mostrarOcultar_nro_doc();");
		$control->con_separador_miles = false;
		$this->add_control(new edit_num('COD_DOC',10,10));
		$this->controls['COD_DOC']->type = 'hidden';
		
		$this->add_control(new edit_text('COD_ESTADO_GUIA_RECEPCION',10,10, 'hidden'));
		$this->add_control(new static_text('NOM_ESTADO_GUIA_RECEPCION'));
	
		$sql_tipo_gr	= "select 	 COD_TIPO_GUIA_RECEPCION
									,NOM_TIPO_GUIA_RECEPCION
							from 	 TIPO_GUIA_RECEPCION
							where 	COD_TIPO_GUIA_RECEPCION <> ".self::K_TIPO_GR_ARRIENDO."
							order by ORDEN";
		$this->add_control($control = new drop_down_dw('COD_TIPO_GUIA_RECEPCION',$sql_tipo_gr,100));
		$control->set_onChange("mostrarOcultar_tipo_doc(); mostrarOcultar_item(this);");
		
		$this->add_control(new edit_text_multiline('OBS',54,3));
		
		//USUARIO_ANULA
		$sql = "select 	COD_USUARIO
						,NOM_USUARIO
				from USUARIO";
								
		$this->add_control(new drop_down_dw('COD_USUARIO_ANULA',$sql,150));	
		$this->set_entrable('COD_USUARIO_ANULA', false);

		$this->add_control(new edit_text('FECHA_ANULA',10,10));
		$this->set_entrable('FECHA_ANULA', false);
		
		// asigna los mandatorys
		$this->set_mandatory('COD_TIPO_GUIA_RECEPCION', 'Tipo Guia Recepción');
		$this->set_mandatory('OBS', 'Observaciones');
		
	}
	function fill_record(&$temp, $record) {	
		
		parent::fill_record($temp, $record);
		
			$COD_DOC = $this->get_item(0, 'COD_DOC');
			$COD_ESTADO_GUIA_RECEPCION = $this->get_item(0, 'COD_ESTADO_GUIA_RECEPCION');
			
			if (($COD_DOC != '') or ($COD_ESTADO_GUIA_RECEPCION >= 1))  //la GD viene desde NV, o estado <> emitida
				$temp->setVar('DISABLE_BUTTON', 'style="display:none"');
			else{	
					if ($this->entrable)
						$temp->setVar('DISABLE_BUTTON', '');
					else
						$temp->setVar('DISABLE_BUTTON', 'disabled="disabled"');
			}				
	}
	
}


class wi_guia_recepcion extends wi_guia_recepcion_base {
	
	const K_ESTADO_GR_EMITIDA	 	= 1;
	const K_ESTADO_GR_IMPRESA	 	= 2;
	const K_ESTADO_GR_ANULADA	 	= 3;
	
	const K_TIPO_GR_DEVOLUCION		= 1;
	const K_TIPO_GR_GARANTIA		= 2;
	const K_TIPO_GR_OTRO			= 3;
	
	const K_PARAM_NOM_EMPRESA        =6;
	const K_PARAM_RUT_EMPRESA        =20;
	const K_PARAM_DIR_EMPRESA        =10;
	const K_PARAM_TEL_EMPRESA        =11;
	const K_PARAM_FAX_EMPRESA        =12;
	const K_PARAM_MAIL_EMPRESA       =13;
	const K_PARAM_CIUDAD_EMPRESA     =14;
	const K_PARAM_PAIS_EMPRESA       =15;
	const K_PARAM_SMTP 				 =17;
	const K_PARAM_SITIO_WEB_EMPRESA  =25;
		
	function wi_guia_recepcion($cod_item_menu) {		
		parent::w_input('guia_recepcion', $cod_item_menu);

		// tab guia_recepcion
		// DATAWINDOWS GUIA_RECEPCION
		$this->dws['dw_guia_recepcion'] = new dw_guia_recepcion();
		
		// tab items
		// DATAWINDOWS ITEMS GUIA_RECEPCION
		$this->dws['dw_item_guia_recepcion'] = new dw_item_guia_recepcion();
		
		//auditoria Solicitado por IS.
		$this->add_auditoria('COD_TIPO_GUIA_RECEPCION');
		$this->add_auditoria('COD_ESTADO_GUIA_RECEPCION');
		$this->add_auditoria('COD_EMPRESA');
		$this->add_auditoria('COD_PERSONA');
	}

	function new_record() {
		$this->dws['dw_guia_recepcion']->insert_row();
		$this->dws['dw_guia_recepcion']->set_item(0, 'TR_DISPLAY', 'none');
		$this->dws['dw_guia_recepcion']->set_item(0, 'TR_DISPLAY_TIPO_DOC', 'none');
		$this->dws['dw_guia_recepcion']->set_item(0, 'FECHA_GUIA_RECEPCION', $this->current_date());
		$this->dws['dw_guia_recepcion']->set_item(0, 'COD_USUARIO', $this->cod_usuario);
		$this->dws['dw_guia_recepcion']->set_item(0, 'NOM_USUARIO', $this->nom_usuario);
		$this->dws['dw_guia_recepcion']->set_item(0, 'COD_ESTADO_GUIA_RECEPCION', self::K_ESTADO_GR_EMITIDA);
		$this->dws['dw_guia_recepcion']->set_item(0, 'NOM_ESTADO_GUIA_RECEPCION', 'EMITIDA');
		$this->dws['dw_guia_recepcion']->set_item(0, 'VISIBLE_TAB', 'none');
		$this->dws['dw_guia_recepcion']->controls['RUT']->readonly 	 			 = true;
		$this->dws['dw_guia_recepcion']->controls['ALIAS']->readonly 			 = true;
		$this->dws['dw_guia_recepcion']->controls['COD_EMPRESA']->readonly 		 = true;
		$this->dws['dw_guia_recepcion']->controls['NOM_EMPRESA']->readonly 		 = true;
	}
	
	function load_record() {
		$cod_guia_recepcion = $this->get_item_wo($this->current_record, 'COD_GUIA_RECEPCION');
		$this->dws['dw_guia_recepcion']->retrieve($cod_guia_recepcion);
		$cod_empresa = $this->dws['dw_guia_recepcion']->get_item(0, 'COD_EMPRESA');
		$this->dws['dw_guia_recepcion']->controls['COD_SUCURSAL_FACTURA']->retrieve($cod_empresa);
		$this->dws['dw_guia_recepcion']->controls['COD_PERSONA']->retrieve($cod_empresa);
		
		$COD_ESTADO_GUIA_RECEPCION = $this->dws['dw_guia_recepcion']->get_item(0, 'COD_ESTADO_GUIA_RECEPCION');
		
		$this->b_no_save_visible = true;
		$this->b_save_visible 	 = true;
		$this->b_modify_visible	 = true;
		
		$this->dws['dw_guia_recepcion']->set_entrable('COD_TIPO_GUIA_RECEPCION' , true);
		$this->dws['dw_guia_recepcion']->set_entrable('TIPO_DOC'				, true);
		$this->dws['dw_guia_recepcion']->set_entrable('NRO_DOC'				 	, true);
		$this->dws['dw_guia_recepcion']->set_entrable('OBS'					 	, true);
		
		$this->dws['dw_guia_recepcion']->set_entrable('NOM_EMPRESA'				, false);
		$this->dws['dw_guia_recepcion']->set_entrable('ALIAS'					, false);
		$this->dws['dw_guia_recepcion']->set_entrable('COD_EMPRESA'				, false);
		$this->dws['dw_guia_recepcion']->set_entrable('RUT'						, false);
		$this->dws['dw_guia_recepcion']->set_entrable('COD_SUCURSAL_FACTURA'	, true);
		$this->dws['dw_guia_recepcion']->set_entrable('COD_PERSONA'				, true);
		$this->dws['dw_item_guia_recepcion']->set_entrable('COD_PRODUCTO'   	, false);
		$this->dws['dw_item_guia_recepcion']->set_entrable('NOM_PRODUCTO'  		, false);
		$this->dws['dw_item_guia_recepcion']->set_entrable('CANTIDAD'  			, true);
		
		// aqui se dejan modificables los datos del tab items
		$this->dws['dw_item_guia_recepcion']->set_entrable_dw(true);
		$this->dws['dw_guia_recepcion']->set_item(0, 'DISPLAY_RECEP_TODO', 'none');
		$this->dws['dw_guia_recepcion']->set_item(0, 'TD_DISPLAY_POR_RECEP', 'none');
		

		
		if ($COD_ESTADO_GUIA_RECEPCION == self::K_ESTADO_GR_EMITIDA) {
			unset($this->dws['dw_guia_recepcion']->controls['COD_ESTADO_GUIA_RECEPCION']);
			$this->dws['dw_guia_recepcion']->add_control(new edit_text('COD_ESTADO_GUIA_RECEPCION',10,10, 'hidden'));
			$this->dws['dw_guia_recepcion']->controls['NOM_ESTADO_GUIA_RECEPCION']->type = '';

			$this->dws['dw_guia_recepcion']->set_entrable('NOM_EMPRESA'				, true);
			$this->dws['dw_guia_recepcion']->set_entrable('ALIAS'					, true);
			$this->dws['dw_guia_recepcion']->set_entrable('COD_EMPRESA'				, true);
			$this->dws['dw_guia_recepcion']->set_entrable('RUT'						, true);
			$this->dws['dw_item_guia_recepcion']->set_entrable('COD_PRODUCTO'   	, true);
			$this->dws['dw_item_guia_recepcion']->set_entrable('NOM_PRODUCTO'  		, true);
			$this->dws['dw_item_guia_recepcion']->set_entrable('CANTIDAD'  			, true);					

			$COD_TIPO_GUIA_RECEPCION = $this->dws['dw_guia_recepcion']->get_item(0, 'COD_TIPO_GUIA_RECEPCION');
			if ($COD_TIPO_GUIA_RECEPCION  == self::K_TIPO_GR_OTRO) {	
				$this->dws['dw_guia_recepcion']->set_entrable('TIPO_DOC'				, true);	
			}
			else if($COD_TIPO_GUIA_RECEPCION  == self::K_TIPO_GR_DEVOLUCION ||$COD_TIPO_GUIA_RECEPCION  == self::K_TIPO_GR_GARANTIA){
				$this->dws['dw_guia_recepcion']->set_entrable('COD_TIPO_GUIA_RECEPCION'	, false);					
			}
		}
		
		else if ($COD_ESTADO_GUIA_RECEPCION == self::K_ESTADO_GR_IMPRESA) {
			$sql = "select 	COD_ESTADO_GUIA_RECEPCION
							,NOM_ESTADO_GUIA_RECEPCION
					from 	ESTADO_GUIA_RECEPCION
					where 	COD_ESTADO_GUIA_RECEPCION = ".self::K_ESTADO_GR_IMPRESA." or
							COD_ESTADO_GUIA_RECEPCION = ".self::K_ESTADO_GR_ANULADA."
					order by COD_ESTADO_GUIA_RECEPCION";
					
			unset($this->dws['dw_guia_recepcion']->controls['COD_ESTADO_GUIA_RECEPCION']);
			$this->dws['dw_guia_recepcion']->add_control($control = new drop_down_dw('COD_ESTADO_GUIA_RECEPCION',$sql,150));	
			$control->set_onChange("mostrarOcultar_Anula(this);");
			$this->dws['dw_guia_recepcion']->controls['NOM_ESTADO_GUIA_RECEPCION']->type = 'hidden';
			$this->dws['dw_guia_recepcion']->add_control(new edit_text_upper('MOTIVO_ANULA',110, 100));

			$this->dws['dw_guia_recepcion']->set_entrable('COD_TIPO_GUIA_RECEPCION' , false);
			$this->dws['dw_guia_recepcion']->set_entrable('NRO_DOC'					, false);
			$this->dws['dw_guia_recepcion']->set_entrable('COD_DOC'					, false);
			$this->dws['dw_guia_recepcion']->set_entrable('TIPO_DOC'				, false);
			$this->dws['dw_guia_recepcion']->set_entrable('OBS'					 	, false);
			
			$this->dws['dw_guia_recepcion']->set_entrable('NOM_EMPRESA'				, false);
			$this->dws['dw_guia_recepcion']->set_entrable('ALIAS'					, false);
			$this->dws['dw_guia_recepcion']->set_entrable('COD_EMPRESA'				, false);
			$this->dws['dw_guia_recepcion']->set_entrable('RUT'						, false);
			$this->dws['dw_guia_recepcion']->set_entrable('COD_SUCURSAL_FACTURA'	, false);
			$this->dws['dw_guia_recepcion']->set_entrable('COD_PERSONA'				, false);
			
			// aqui se dejan no modificables los datos del tab items
			$this->dws['dw_item_guia_recepcion']->set_entrable_dw(false);
				
		}
		else if ($COD_ESTADO_GUIA_RECEPCION == self::K_ESTADO_GR_ANULADA) {
			$this->b_print_visible 	 = false;
			$this->b_no_save_visible = false;
			$this->b_save_visible 	 = false;
			$this->b_modify_visible  = false;
		}
		
		$this->dws['dw_item_guia_recepcion']->retrieve($cod_guia_recepcion);
	}
	
	function get_key() {
		return $this->dws['dw_guia_recepcion']->get_item(0, 'COD_GUIA_RECEPCION');
	}	
		
	
	function save_record($db) {
		$COD_GUIA_RECEPCION			= $this->get_key();
		$FECHA_GUIA_RECEPCION		= $this->dws['dw_guia_recepcion']->get_item(0, 'FECHA_GUIA_RECEPCION');
		$COD_USUARIO				= $this->dws['dw_guia_recepcion']->get_item(0, 'COD_USUARIO');
		$COD_EMPRESA 				= $this->dws['dw_guia_recepcion']->get_item(0, 'COD_EMPRESA');
		$COD_SUCURSAL	 			= $this->dws['dw_guia_recepcion']->get_item(0, 'COD_SUCURSAL_FACTURA');
		$COD_PERSONA				= $this->dws['dw_guia_recepcion']->get_item(0, 'COD_PERSONA');	
		$COD_ESTADO_GUIA_RECEPCION 	= $this->dws['dw_guia_recepcion']->get_item(0, 'COD_ESTADO_GUIA_RECEPCION');	
		$COD_TIPO_GUIA_RECEPCION	= $this->dws['dw_guia_recepcion']->get_item(0, 'COD_TIPO_GUIA_RECEPCION');	
		$TIPO_DOC					= $this->dws['dw_guia_recepcion']->get_item(0, 'TIPO_DOC');	
		$NRO_DOC					= $this->dws['dw_guia_recepcion']->get_item(0, 'NRO_DOC');	
		$COD_DOC					= $this->dws['dw_guia_recepcion']->get_item(0, 'COD_DOC');	
		$OBS						= $this->dws['dw_guia_recepcion']->get_item(0, 'OBS');
		$OBS 						= str_replace("'", "''", $OBS);	
		$MOTIVO_ANULA				= $this->dws['dw_guia_recepcion']->get_item(0, 'MOTIVO_ANULA');
		$MOTIVO_ANULA 				= str_replace("'", "''", $MOTIVO_ANULA);	
		$COD_USUARIO_ANULA			= $this->dws['dw_guia_recepcion']->get_item(0, 'COD_USUARIO_ANULA');
		
		if (($MOTIVO_ANULA != '') && ($COD_USUARIO_ANULA== '')) // se anula 
			$COD_USUARIO_ANULA		= $this->cod_usuario;
		else
			$COD_USUARIO_ANULA		= "null";
		
		$TIPO_DOC			= ($TIPO_DOC =='') ? "null" : "'$TIPO_DOC'";
		$NRO_DOC			= ($NRO_DOC =='') ? "null" : $NRO_DOC;
		$COD_DOC			= ($COD_DOC =='') ? "null" : $COD_DOC;
		$MOTIVO_ANULA		= ($MOTIVO_ANULA =='') ? "NULL" : "$MOTIVO_ANULA";
		$OBS				= ($OBS =='') ? "null" : "'$OBS'";
		$COD_GUIA_RECEPCION = ($COD_GUIA_RECEPCION =='') ? "null" : $COD_GUIA_RECEPCION;		
    
		$sp = 'spu_guia_recepcion';
	    if ($this->is_new_record())
	    	$operacion = 'INSERT';
	    else
	    	$operacion = 'UPDATE';
	    
		$param	= "	'$operacion'					
					,$COD_GUIA_RECEPCION			
					,$COD_USUARIO				
					,$COD_EMPRESA				
					,$COD_SUCURSAL				
					,$COD_PERSONA				
					,$COD_ESTADO_GUIA_RECEPCION	
					,$COD_TIPO_GUIA_RECEPCION	
					,$TIPO_DOC					
					,$NRO_DOC					
					,$COD_DOC					
					,$OBS						
					,$COD_USUARIO_ANULA		
					,'$MOTIVO_ANULA'";

		if ($db->EXECUTE_SP($sp, $param)){
			if ($this->is_new_record()) {
				$COD_GUIA_RECEPCION = $db->GET_IDENTITY();
				$this->dws['dw_guia_recepcion']->set_item(0, 'COD_GUIA_RECEPCION', $COD_GUIA_RECEPCION);
			}
			if (($MOTIVO_ANULA != 'null') && ($COD_USUARIO_ANULA != 'null')){ // se anula 
				$this->f_envia_mail('ANULADA');
			}
			for ($i=0; $i<$this->dws['dw_item_guia_recepcion']->row_count(); $i++){ 
				$this->dws['dw_item_guia_recepcion']->set_item($i, 'COD_GUIA_RECEPCION', $COD_GUIA_RECEPCION);
				$this->dws['dw_item_guia_recepcion']->set_item($i, 'TIPO_DOC_GR', $TIPO_DOC);
			}
			if (!$this->dws['dw_item_guia_recepcion']->update($db, $COD_GUIA_RECEPCION)) return false;	
			return true;
		}
		return false;
	}
	
	// esta funcio envia mail  cuando se imprime e documento de guia despacho 
 	function f_envia_mail($estado_guia_recepcion){
 		$cod_guia_recepcion = $this->get_key();
 		$remitente = $this->nom_usuario;
        $cod_remitente = $this->cod_usuario;

        $db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
        $sql = "select COD_GUIA_RECEPCION from GUIA_RECEPCION WHERE COD_GUIA_RECEPCION = $cod_guia_recepcion";
        $result = $db->build_results($sql);
        $nro_guia_recepcion = $result[0]['COD_GUIA_RECEPCION'];		
		
        $db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
        // obtiene el mail de quien creo la tarea y manda el mail
        $sql_remitente = "SELECT MAIL from USUARIO where COD_USUARIO = $cod_remitente";
        $result_remitente = $db->build_results($sql_remitente);
        $mail_remitente = $result_remitente[0]['MAIL'];
		
 		// Mail destinatarios
        $para_admin1 = 'mulloa@integrasystem.cl';
        $para_admin2 = 'mulloa@integrasystem.cl';
        /*
        $para_admin1 = 'mherrera@integrasystem.cl';
        $para_admin2 = 'imeza@integrasystem.cl';
		*/
        
        if($estado_guia_recepcion == 'IMPRESO')
		{
	        $asunto = 'Impresion de Guia de Recepcion Nº '.$nro_guia_recepcion;
	        $mensaje = 'Se ha <b>IMPRESO</b> la <b>Guia de Recepcion Nº '.$nro_guia_recepcion.'</b> por el usuario <b><i>'.$remitente.'<i><b>';  
		}
	  	
	 	if($estado_guia_recepcion == 'ANULADA')
		{
	        $asunto = 'Anulacion de Guia de Recepcion Nº '.$nro_guia_recepcion;
	        $mensaje = 'Se ha <b>ANULADO</b> la <b>Guia de Recepcion Nº '.$nro_guia_recepcion.'</b> por el usuario <b><i>'.$remitente.'<i><b>';
		}
		
	  	$cabeceras  = 'MIME-Version: 1.0' . "\n";
        $cabeceras .= 'Content-type: text/html; charset=iso-8859-1' . "\n";
        $cabeceras .= 'From: '.$mail_remitente. "\n";
        //se comenta el envio de mail por q ya no es necesario => Vmelo. 
        //mail($para_admin1, $asunto, $mensaje, $cabeceras);
        //mail($para_admin2, $asunto, $mensaje, $cabeceras);
 		return 0;
   	}
	
	function print_record() {
	
		$cod_guia_recepcion = $this->get_key();
		$COD_ESTADO_GUIA_RECEPCION = $this->dws['dw_guia_recepcion']->get_item(0, 'COD_ESTADO_GUIA_RECEPCION');
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$db->BEGIN_TRANSACTION();
		$sp = 'spu_guia_recepcion';
		$param = "'PRINT', $cod_guia_recepcion, $this->cod_usuario";
	
		if ($db->EXECUTE_SP($sp, $param)) {		// aqui dentro del sp se cambia el estado y se graba todo lo relacionado
			$db->COMMIT_TRANSACTION();
				
				$estado_gr_impresa = self::K_ESTADO_GR_IMPRESA; 
				$cod_estado_guia_recepcion = $this->dws['dw_guia_recepcion']->get_item(0, 'COD_ESTADO_GUIA_RECEPCION');
				if ($cod_estado_guia_recepcion != $estado_gr_impresa)//es la 1era vez que se imprime la Guia de Despacho 
					$this->f_envia_mail('IMPRESO');

			$sql= "SELECT	GR.COD_GUIA_RECEPCION 
							,dbo.f_format_date(GR.FECHA_GUIA_RECEPCION,3)FECHA_GUIA_RECEPCION
							,GR.COD_TIPO_GUIA_RECEPCION
							,CASE GR.TIPO_DOC 
								WHEN 'GUIA_DESPACHO' THEN 'GUIA DESPACHO' 
								WHEN 'FACTURA' THEN 'FACTURA'
								WHEN 'ARRIENDO' THEN 'CONTRATO ARRIENDO'
								ELSE NULL
							END TIPO_DOC
							,GR.NRO_DOC
							,OBS
							,E.NOM_EMPRESA
							,E.RUT
							,E.DIG_VERIF
							,U.NOM_USUARIO
							,P.NOM_PERSONA
							,TGR.NOM_TIPO_GUIA_RECEPCION
							,S.DIRECCION
							,S.TELEFONO
							,S.FAX
							,IGR.COD_PRODUCTO
							,IGR.NOM_PRODUCTO
							,IGR.CANTIDAD
							,COM.NOM_COMUNA
							,CIU.NOM_CIUDAD
					FROM	GUIA_RECEPCION GR,
							SUCURSAL S left outer join COMUNA COM on S.COD_COMUNA = COM.COD_COMUNA, 
							ITEM_GUIA_RECEPCION IGR, EMPRESA E, USUARIO U, PERSONA P,
							TIPO_GUIA_RECEPCION TGR, CIUDAD CIU
					WHERE	GR.COD_GUIA_RECEPCION = ".$cod_guia_recepcion." AND
							IGR.COD_GUIA_RECEPCION = GR.COD_GUIA_RECEPCION AND
							E.COD_EMPRESA = GR.COD_EMPRESA AND
							U.COD_USUARIO = GR.COD_USUARIO AND
							P.COD_PERSONA = GR.COD_PERSONA AND
							TGR.COD_TIPO_GUIA_RECEPCION = GR.COD_TIPO_GUIA_RECEPCION AND
							S.COD_SUCURSAL = GR.COD_SUCURSAL AND
							S.COD_CIUDAD = CIU.COD_CIUDAD";
			//// reporte
			$labels = array();
			$labels['strCOD_GUIA_RECEPCION'] = $cod_guia_recepcion;
			$rpt = new print_guia_recepcion($sql, $this->root_dir.'appl/guia_recepcion/guia_recepcion.xml', $labels, "Guia de Recepcion".$cod_guia_recepcion, 1);
			$this->_load_record();
			return true;
			}
		else {
			$db->ROLLBACK_TRANSACTION();
			return false;
		}			
	}
}

class print_guia_recepcion extends print_guia_recepcion_base {	
	function print_guia_recepcion($sql, $xml, $labels=array(), $titulo, $con_logo, $vuelve_a_presentacion=false) {
		parent::reporte($sql, $xml, $labels, $titulo, $con_logo, $vuelve_a_presentacion);			
	}
	
	function modifica_pdf(&$pdf) {
			$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
			$result = $db->build_results($this->sql);
			
			$cod_guia_recepcion	= $result[0]['COD_GUIA_RECEPCION'];
			
			$y_ini = $pdf->GetY() + 50;
			
			$sql=	"SELECT	'OBSERVACION:' TITULO_OBS
							,OBS
							,'RECIBI CONFORME' CONFORME
					FROM	GUIA_RECEPCION GR,
							SUCURSAL S left outer join COMUNA COM on S.COD_COMUNA = COM.COD_COMUNA, 
							ITEM_GUIA_RECEPCION IGR, EMPRESA E, USUARIO U, PERSONA P,
							TIPO_GUIA_RECEPCION TGR, CIUDAD CIU
					WHERE	GR.COD_GUIA_RECEPCION = $cod_guia_recepcion AND
							IGR.COD_GUIA_RECEPCION = GR.COD_GUIA_RECEPCION AND
							E.COD_EMPRESA = GR.COD_EMPRESA AND
							U.COD_USUARIO = GR.COD_USUARIO AND
							P.COD_PERSONA = GR.COD_PERSONA AND
							TGR.COD_TIPO_GUIA_RECEPCION = GR.COD_TIPO_GUIA_RECEPCION AND
							S.COD_SUCURSAL = GR.COD_SUCURSAL AND
							S.COD_CIUDAD = CIU.COD_CIUDAD";
			
			$result_guia_recepcion = $db->build_results($sql);
			
			$obs	=	$result_guia_recepcion[0]['OBS'];
			$titulo_obs	=	$result_guia_recepcion[0]['TITULO_OBS'];
			$conforme	=	$result_guia_recepcion[0]['CONFORME'];	
			
			$pdf->SetFont('Arial','',8.5);
			$pdf->SetXY(30,$y_ini-15);
			$pdf->Cell(555, 15, $titulo_obs, '', '','L');
			
			$pdf->SetXY(30,$y_ini);
			$pdf->Cell(355,90, '', 'LTRB', '','C');
			
			$pdf->SetXY(30,$y_ini+5);
			$pdf->MultiCell(355, 10, $obs, '', 'L');
			
			$pdf->SetXY(385,$y_ini);
			$pdf->Cell(200,75,'', 'TR', '','C');
			
			$pdf->SetXY(385,$y_ini+65);
			$pdf->Cell(200,25, $conforme, 'TRB', '','C');
			
	}
}
?>