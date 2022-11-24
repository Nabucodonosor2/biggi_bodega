<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

ini_set('memory_limit', '100M');
class edit_direccion extends edit_radio_button {	
	function edit_direccion($field) {
		parent::edit_radio_button($field, 'S', 'N', '', $field);
	}
	function draw_entrable($dato, $record) {
		$value_true = $this->value_true;
		$this->value_true = $this->value_true.'_'.$record;
		$dato = $dato.'_'.$record;
		$draw = parent::draw_entrable($dato, $record);
		$this->value_true = $value_true;
		return $draw;
	}		
	function get_values_from_POST($record) {
		$value_true = $this->value_true.'_'.$record;
		$field_post = $this->group;
		if (isset($_POST[$field_post]) && $_POST[$field_post]==$value_true)
			return $this->value_true;
		else 
			return $this->value_false;
	}
}
class edit_nom_sucursal extends edit_text_upper {
	function edit_nom_sucursal() {
		parent::edit_text_upper('NOM_SUCURSAL', 29, 100);
		$this->set_onChange("load_sucursal_persona(this.id)");
	}
}
class dw_sucursal extends datawindow {
	function dw_sucursal() {
		$sql = "select COD_SUCURSAL, 
			NOM_SUCURSAL, 
			COD_EMPRESA, 
			DIRECCION, 
			COD_COMUNA, 
			COD_CIUDAD,
			COD_PAIS, 
			DIRECCION_FACTURA, 
			DIRECCION_DESPACHO, 
			TELEFONO, 
			FAX
		from SUCURSAL
		where COD_EMPRESA = {KEY1}";
		
		parent::datawindow($sql, 'SUCURSAL', true, true);	
		$this->add_control(new edit_nom_sucursal());
		$this->add_control(new edit_text('COD_SUCURSAL', 10, 10, 'hidden'));
		$this->add_control(new edit_text_upper('DIRECCION', 35, 100));
		$this->add_control(new edit_direccion('DIRECCION_FACTURA'));
		$this->add_control(new edit_direccion('DIRECCION_DESPACHO'));

		$sql = "select COD_PAIS, NOM_PAIS from PAIS order by NOM_PAIS";
		$this->add_control($control = new drop_down_dw('COD_PAIS', $sql, 90));
		$control->set_drop_down_dependiente('empresa', 'COD_CIUDAD');
		
		$sql = "select COD_CIUDAD, 
						NOM_CIUDAD
				from CIUDAD
				where COD_PAIS = {KEY1}
				order by NOM_CIUDAD";
		$this->add_control($control = new drop_down_dw('COD_CIUDAD', $sql, 90));	
		$control->set_drop_down_dependiente('empresa', 'COD_COMUNA');
			
		$sql = "select COD_COMUNA, 
						NOM_COMUNA
				from COMUNA
				where COD_CIUDAD = {KEY1}
				order by NOM_COMUNA";
		$this->add_control(new drop_down_dw('COD_COMUNA', $sql, 90));			
		$this->add_control(new edit_text_upper('TELEFONO', 10, 100));	
		$this->add_control(new edit_text_upper('FAX', 10, 100));	
	
		// asigna los mandatorys	
		$this->set_mandatory('NOM_SUCURSAL', 'Nombre de la Sucursal');
		$this->set_mandatory('DIRECCION', 'Dirección de la Sucursal');
		$this->set_mandatory('COD_PAIS', 'Pais de la Sucursal');
		$this->set_mandatory('COD_CIUDAD', 'Ciudad de la Sucursal');


		// Setea el focus en COD_EMPRESA para las nuevas lineas
		$this->set_first_focus('NOM_SUCURSAL');
	}
	function insert_row($row=-1) {
		$row = parent::insert_row($row);
		$this->set_item($row, 'COD_PAIS', 56);	// por defecto CHILE
		$this->set_item($row, 'COD_SUCURSAL', - $this->redirect($row) - 100);		// Se suma -100 para asegura q que negativo (para el caso 0)
		return $row;
	}
	function fill_record(&$temp, $record) {
		$cod_pais = $this->get_item($record, 'COD_PAIS');
		if ($cod_pais=='') $cod_pais = 0;
		$this->controls['COD_CIUDAD']->retrieve($cod_pais);
		$cod_ciudad = $this->get_item($record, 'COD_CIUDAD');
		if ($cod_ciudad=='') $cod_ciudad = 0;
		$this->controls['COD_COMUNA']->retrieve($cod_ciudad);
		parent::fill_record($temp, $record);
		
		if ($this->entrable) {
			$eliminar = '<img src="../../../../commonlib/trunk/images/b_delete_line.jpg" onClick="del_line_sucursal(\''.$this->label_record.'_'.$record.'\', \''.$this->nom_tabla.'\');" style="cursor:pointer">';
			$temp->setVar($this->label_record.".ELIMINAR_".strtoupper($this->label_record), $eliminar);
		}
	}
	function update($db){
		$sp = 'spu_sucursal';
		for ($i = 0; $i < $this->row_count(); $i++)	{
			$statuts = $this->get_status_row($i);
			if ($statuts == K_ROW_NOT_MODIFIED || $statuts == K_ROW_NEW)
				continue;
			
			$COD_SUCURSAL = $this->get_item($i, 'COD_SUCURSAL');
			$NOM_SUCURSAL = $this->get_item($i, 'NOM_SUCURSAL');
			$NOM_SUCURSAL = str_replace("'", "''", $NOM_SUCURSAL);	
			$COD_EMPRESA = $this->get_item($i, 'COD_EMPRESA');
			$DIRECCION = $this->get_item($i, 'DIRECCION');
			$DIRECCION = str_replace("'", "''", $DIRECCION);
			$COD_COMUNA = $this->get_item($i, 'COD_COMUNA');
			$COD_CIUDAD = $this->get_item($i, 'COD_CIUDAD');
			$COD_PAIS = $this->get_item($i, 'COD_PAIS');
			$DIRECCION_FACTURA = $this->get_item($i, 'DIRECCION_FACTURA');
			$DIRECCION_DESPACHO = $this->get_item($i, 'DIRECCION_DESPACHO');
			$TELEFONO = $this->get_item($i, 'TELEFONO');
			$FAX = $this->get_item($i, 'FAX');
				
			$COD_COMUNA = ($COD_COMUNA=='') ? "null" : $COD_COMUNA;
			$COD_CIUDAD = ($COD_CIUDAD=='') ? "null" : $COD_CIUDAD;
			$COD_PAIS = ($COD_PAIS=='') ? "null" : $COD_PAIS;
			$TELEFONO = ($TELEFONO=='') ? "null" : "'$TELEFONO'";
			$FAX = ($FAX=='') ? "null" : "'$FAX'";		
			
			$COD_SUCURSAL = ($COD_SUCURSAL=='') ? "null" : $COD_SUCURSAL;
			
			if ($statuts == K_ROW_NEW_MODIFIED)
				$operacion = 'INSERT';
			elseif ($statuts == K_ROW_MODIFIED)
				$operacion = 'UPDATE';
			
			$param = "'$operacion',$COD_SUCURSAL, '$NOM_SUCURSAL', $COD_EMPRESA, '$DIRECCION', $COD_COMUNA, $COD_CIUDAD, $COD_PAIS, '$DIRECCION_FACTURA', '$DIRECCION_DESPACHO', $TELEFONO, $FAX";
			if ($db->EXECUTE_SP($sp, $param)) {
				if ($statuts == K_ROW_NEW_MODIFIED) {
					$COD_SUCURSAL = $db->GET_IDENTITY();
					$this->set_item($i, 'COD_SUCURSAL', $COD_SUCURSAL);		
				}
			}
			else
				return false;
		}
		for ($i = 0; $i < $this->row_count('delete'); $i++) {
			$statuts = $this->get_status_row($i, 'delete');
			if ($statuts == K_ROW_NEW || $statuts == K_ROW_NEW_MODIFIED)
				continue;
				
			$COD_SUCURSAL = $this->get_item($i, 'COD_SUCURSAL', 'delete');
			if (!$db->EXECUTE_SP($sp, "'DELETE', $COD_SUCURSAL"))
				return false;			
		}		
		return true;
	}
}

class dw_persona extends datawindow {
	public	$cod_empresa = null;
	
	function dw_persona() {		
		$sql = "select 	P.COD_PERSONA, 
						P.COD_SUCURSAL P_COD_SUCURSAL, 
						P.NOM_PERSONA, 
						P.COD_CARGO, 
						P.TELEFONO as TELEFONO_PERSONA,   
						P.FAX as FAX_PERSONA, 
						P.EMAIL,						
						S.COD_EMPRESA
				from PERSONA P, SUCURSAL S
				where S.COD_EMPRESA= {KEY1} and 
					P.COD_SUCURSAL = S.COD_SUCURSAL
				order by P.COD_PERSONA";	
				
		// el el SQL utilizo alias para TELEFONO y FAX, ya en dw SUCURSAL tambien existen estos campos y se confunde el sistema en el despliegue de datos    
		parent::datawindow($sql, 'PERSONA', true, true);	
		
		$this->add_control(new edit_text('COD_PERSONA', 10, 10, 'hidden'));
		$this->add_control($control = new edit_text_upper('NOM_PERSONA', 35, 100));
		$control->set_onChange("change_nom_persona(this.id)");
		
		$sql = "select 	 COD_CARGO, 
						 NOM_CARGO,
						 ORDEN						
				from 	 CARGO
				order by COD_CARGO";
				
		$this->add_control(new drop_down_dw('COD_CARGO', $sql, 170));

		$sql = "select COD_SUCURSAL,
						NOM_SUCURSAL 	
				from SUCURSAL
				where COD_EMPRESA = {KEY1}
				order by NOM_SUCURSAL";			
		$this->add_control(new drop_down_dw('P_COD_SUCURSAL', $sql, 147));
		$this->add_control(new edit_text_upper('TELEFONO_PERSONA', 15, 100));	
		$this->add_control(new edit_text_upper('FAX_PERSONA', 15, 100));
		$this->add_control(new edit_mail('EMAIL', 35, 100));	
		
		// asigna los mandatorys	
		$this->set_mandatory('P_COD_SUCURSAL', 'Sucursal de la Persona');
		$this->set_mandatory('NOM_PERSONA', 'Nombre de la Persona');	

		// Setea el focus en COD_EMPRESA para las nuevas lineas
		$this->set_first_focus('NOM_PERSONA');
	}
	function insert_row($row=-1) {
		$row = parent::insert_row($row);
		$this->set_item($row, 'COD_PERSONA', - $this->redirect($row) - 100);		// Se suma -100 para asegura q que negativo (para el caso 0)
		return $row;
	}
	function fill_record(&$temp, $record) {
		if ($this->cod_empresa){
			$this->controls['P_COD_SUCURSAL']->retrieve($this->cod_empresa);
		}	
		parent::fill_record($temp, $record);
		if ($this->entrable) {
			$eliminar = '<img src="../../../../commonlib/trunk/images/b_delete_line.jpg" onClick="del_line_persona(\''.$this->label_record.'_'.$record.'\', \''.$this->nom_tabla.'\');" style="cursor:pointer">';
			$temp->setVar($this->label_record.".ELIMINAR_".strtoupper($this->label_record), $eliminar);
		}
	}	
	function fill_template(&$temp) {
		parent::fill_template($temp);
		if ($this->entrable) {
			$agregar = '<img src="../../../../commonlib/trunk/images/b_add_line.jpg" onClick="add_line_persona(\''.$this->label_record.'\', \''.$this->nom_tabla.'\');" style="cursor:pointer">';
			$temp->setVar("AGREGAR_".strtoupper($this->label_record), $agregar);
		}
	}	
	function update($db){
		$sp = 'spu_persona';
		for ($i = 0; $i < $this->row_count(); $i++)		{
			$statuts = $this->get_status_row($i);
			if ($statuts == K_ROW_NOT_MODIFIED || $statuts == K_ROW_NEW)
				continue;
			
			$COD_PERSONA = $this->get_item($i, 'COD_PERSONA');
			$COD_SUCURSAL = $this->get_item($i, 'P_COD_SUCURSAL');
			
			$NOM_PERSONA = $this->get_item($i, 'NOM_PERSONA');
			$COD_CARGO = $this->get_item($i, 'COD_CARGO');
			$TELEFONO = $this->get_item($i, 'TELEFONO_PERSONA');
			$FAX = $this->get_item($i, 'FAX_PERSONA');
			$EMAIL = $this->get_item($i, 'EMAIL');
						
			$COD_CARGO = ($COD_CARGO=='') ? "null" : $COD_CARGO;
			$TELEFONO = ($TELEFONO=='') ? "null" : "'$TELEFONO'";
			$FAX = ($FAX=='') ? "null" : "'$FAX'";
			$EMAIL = ($EMAIL=='') ? "null" : "'$EMAIL'";		
			
			$COD_PERSONA = ($COD_PERSONA=='') ? "null" : $COD_PERSONA;
			
			if ($statuts == K_ROW_NEW_MODIFIED)
				$operacion = 'INSERT';
			elseif ($statuts == K_ROW_MODIFIED)
				$operacion = 'UPDATE';
			
			$param = "'$operacion',$COD_PERSONA, $COD_SUCURSAL, '$NOM_PERSONA', $COD_CARGO, $TELEFONO, $FAX, $EMAIL";
			
			if ($db->EXECUTE_SP($sp, $param)) {
				if ($statuts == K_ROW_NEW_MODIFIED) {
					$COD_PERSONA = $db->GET_IDENTITY();
					$this->set_item($i, 'COD_PERSONA', $COD_PERSONA);		
				}
			}
			else
				return false;				
		}
			
		for ($i = 0; $i < $this->row_count('delete'); $i++) {
			$statuts = $this->get_status_row($i, 'delete');
			if ($statuts == K_ROW_NEW || $statuts == K_ROW_NEW_MODIFIED)
				continue;
				
			$COD_PERSONA = $this->get_item($i, 'COD_PERSONA', 'delete');
			if (!$db->EXECUTE_SP($sp, "'DELETE', $COD_PERSONA"))
				return false;	
		}
		return true;
	}
}

class dw_costo_producto extends datawindow {
	function dw_costo_producto() {		
		$sql = "select	PP.COD_PRODUCTO_PROVEEDOR
						,PP.COD_EMPRESA
						,PP.COD_PRODUCTO
						,P.NOM_PRODUCTO
						,PP.COD_INTERNO_PRODUCTO
						,dbo.f_prod_get_precio_costo(PP.COD_PRODUCTO, PP.COD_EMPRESA, getdate()) PRECIO
						,PP.ORDEN
				from PRODUCTO_PROVEEDOR PP, PRODUCTO P
				WHERE PP.COD_EMPRESA = {KEY1}
					and PP.ELIMINADO = 'N'
					and P.COD_PRODUCTO = PP.COD_PRODUCTO
				order by PP.COD_PRODUCTO";	
		
		parent::datawindow($sql, 'COSTO_PRODUCTO', true, true);	
		
		$this->add_controls_producto_help();
		$this->add_control(new edit_precio('PRECIO'));
		$this->add_control(new edit_text_upper('COD_INTERNO_PRODUCTO', 30, 30));
		
		// asigna los mandatorys	
		$this->set_mandatory('COD_PRODUCTO', 'Código del Producto');
		$this->set_mandatory('PRECIO', 'Precio del Producto');
	}
	function insert_row($row=-1) {
		$row = parent::insert_row($row);
		$this->set_item($row, 'ORDEN', 9999);	// por orden al final
		return $row;
	}
	function update($db)	{
		$sp = 'spu_producto_proveedor';
		for ($i = 0; $i < $this->row_count(); $i++)		{
			$statuts = $this->get_status_row($i);
			if ($statuts == K_ROW_NOT_MODIFIED || $statuts == K_ROW_NEW)
				continue;
			
			$COD_PRODUCTO_PROVEEDOR = $this->get_item($i, 'COD_PRODUCTO_PROVEEDOR');
			$COD_EMPRESA = $this->get_item($i, 'COD_EMPRESA');
			$COD_PRODUCTO = $this->get_item($i, 'COD_PRODUCTO');
			$COD_INTERNO_PRODUCTO = $this->get_item($i, 'COD_INTERNO_PRODUCTO');
			$PRECIO = $this->get_item($i, 'PRECIO');
			$ORDEN = $this->get_item($i, 'ORDEN');
			$COD_USUARIO = session::get("COD_USUARIO");

			$COD_PRODUCTO_PROVEEDOR = ($COD_PRODUCTO_PROVEEDOR=='') ? "null" : $COD_PRODUCTO_PROVEEDOR;
			
			if ($statuts == K_ROW_NEW_MODIFIED)
				$operacion = 'INSERT';
			elseif ($statuts == K_ROW_MODIFIED)
				$operacion = 'UPDATE';
			$param = "'$operacion', $COD_PRODUCTO_PROVEEDOR, $COD_EMPRESA, '$COD_PRODUCTO', '$COD_INTERNO_PRODUCTO', $PRECIO, $ORDEN, $COD_USUARIO";			
			if (!$db->EXECUTE_SP($sp, $param)) 
				return false;
		}
		for ($i = 0; $i < $this->row_count('delete'); $i++) {
			$statuts = $this->get_status_row($i, 'delete');
			if ($statuts == K_ROW_NEW || $statuts == K_ROW_NEW_MODIFIED)
				continue;
				
			$COD_PRODUCTO_PROVEEDOR = $this->get_item($i, 'COD_PRODUCTO_PROVEEDOR', 'delete');
			if (!$db->EXECUTE_SP($sp, "'DELETE', $COD_PRODUCTO_PROVEEDOR"))
				return false;			

			$COD_PRODUCTO = $this->get_item($i, 'COD_PRODUCTO', 'delete');
			$parametros_sp = "'PRODUCTO_PROVEEDOR','PRODUCTO', null, '$COD_PRODUCTO'";
			if (!$db->EXECUTE_SP('sp_orden_no_parametricas', $parametros_sp)) return false;
		}
		
		// reordena
		for ($i = 0; $i < $this->row_count(); $i++)		{
			$statuts = $this->get_status_row($i);
			if ($statuts == K_ROW_NEW_MODIFIED) {
				$COD_PRODUCTO = $this->get_item($i, 'COD_PRODUCTO');
				$parametros_sp = "'PRODUCTO_PROVEEDOR','PRODUCTO', null, '$COD_PRODUCTO'";
				if (!$db->EXECUTE_SP('sp_orden_no_parametricas', $parametros_sp)) return false;
			}
		}
		return true;
	}
}

class dw_bitacora_empresa extends datawindow {
	function dw_bitacora_empresa() {		
			
		$sql = "select 		BE.COD_BITACORA_EMPRESA,
							BE.NOM_BITACORA_EMPRESA,
							Convert(varchar, BE.FECHA_BITACORA_EMPRESA,103)+ ' ' + convert(varchar(20), BE.FECHA_BITACORA_EMPRESA, 108)  FECHA_BITACORA_EMPRESA,
							BE.COD_USUARIO,
							U.NOM_USUARIO,
							BE.COD_EMPRESA,
							'N' IS_NEW
				from 		BITACORA_EMPRESA BE
							,USUARIO U
				where 		BE.COD_EMPRESA = {KEY1} AND
							U.COD_USUARIO = BE.COD_USUARIO
				order by	BE.COD_BITACORA_EMPRESA DESC";

		parent::datawindow($sql, 'BITACORA_EMPRESA', true, true);	
		
		$this->add_control(new edit_text_upper('NOM_BITACORA_EMPRESA', 100, 100));
		$this->set_protect('NOM_BITACORA_EMPRESA', "[IS_NEW]=='N'");
		
		// asigna los mandatorys	
		$this->set_mandatory('NOM_BITACORA_EMPRESA', 'Observación de la Bitácora');
	}
	function insert_row($row = -1) {
		$row = parent::insert_row($row);
		$this->set_item($row, 'IS_NEW', 'S');		
		$this->set_item($row, 'FECHA_BITACORA_EMPRESA', $this->current_date_time());		
		$this->set_item($row, 'COD_USUARIO', session::get("COD_USUARIO"));
		$this->set_item($row, 'NOM_USUARIO', session::get("NOM_USUARIO"));
		return $row;
	}
	function fill_record(&$temp, $record) {
		parent::fill_record($temp, $record);
		$is_new = $this->get_item($record, 'IS_NEW');
		if ($is_new=='N') {
			$eliminar = '<img src="../../../../commonlib/trunk/images/b_delete_line_d.jpg">';
			$temp->setVar($this->label_record.".ELIMINAR_".strtoupper($this->label_record), $eliminar);
		}
	}
	function update($db)	{
		$sp = 'spu_bitacora_empresa';
		for ($i = 0; $i < $this->row_count(); $i++) {
			$statuts = $this->get_status_row($i);
			if ($statuts != K_ROW_NEW_MODIFIED)		// Solo permite filas nuevas
				continue;
						
			$NOM_BITACORA_EMPRESA = $this->get_item($i, 'NOM_BITACORA_EMPRESA');
			$COD_EMPRESA = $this->get_item($i, 'COD_EMPRESA');
			$COD_USUARIO = $this->get_item($i, 'COD_USUARIO');
						
			$operacion = 'INSERT';
			$param = "'$operacion', '$NOM_BITACORA_EMPRESA', $COD_USUARIO, $COD_EMPRESA";
			if (!$db->EXECUTE_SP($sp, $param)) 
				return false;				
			
		}
		return true;
	}
}												

class dw_valor_defecto_compra_base extends datawindow {		
	function dw_valor_defecto_compra_base() {		
			
		$sql = "SELECT	V.COD_VALOR_DEFECTO_COMPRA
						,V.COD_PERSONA COD_PERSONA_DEFECTO
						,V.COD_FORMA_PAGO
						,E.COD_EMPRESA
						,E.DSCTO_PROVEEDOR
						,case E.ES_PROVEEDOR_INTERNO 
							WHEN 'S' THEN '' 
							ELSE CASE E.ES_PROVEEDOR_EXTERNO
									WHEN 'S' THEN ''
									ELSE 'none'
								end
						end VISIBLE_TAB
				FROM	EMPRESA E LEFT OUTER JOIN VALOR_DEFECTO_COMPRA V on E.COD_EMPRESA = V.COD_EMPRESA
				WHERE	E.COD_EMPRESA = {KEY1}
				order by	V.COD_VALOR_DEFECTO_COMPRA DESC";

		parent::datawindow($sql);
				
		$sql_atencion			= "	SELECT 	P.COD_PERSONA COD_PERSONA_DEFECTO,
											P.NOM_PERSONA
									FROM 	PERSONA P, SUCURSAL S
									WHERE	P.COD_SUCURSAL = S.COD_SUCURSAL
										AND S.COD_EMPRESA = {KEY1}";		

		$this->add_control(new drop_down_dw('COD_PERSONA_DEFECTO',$sql_atencion,150));
 
		$sql_forma_pago			= "	select 			COD_FORMA_PAGO
													,NOM_FORMA_PAGO													
						   			from			FORMA_PAGO
						   			order by  		ORDEN";
		$this->add_control(new drop_down_dw('COD_FORMA_PAGO',$sql_forma_pago,150));	
		$this->add_control(new edit_porcentaje('DSCTO_PROVEEDOR'));
	}
	
	function fill_record(&$temp, $record) {
		$cod_empresa = $this->get_item($record,'COD_EMPRESA');
		if ($cod_empresa=='') $cod_empresa = 0; 
			$this->controls['COD_PERSONA_DEFECTO']->retrieve($cod_empresa);			
		parent::fill_record($temp, $record);		
	}
	
}

//CLASE PRINCIPAL QUE HEREDA DE INPUT 
class wi_empresa_base extends w_input {
	function wi_empresa_base($cod_item_menu) {
		parent::w_input('empresa', $cod_item_menu);
		$sql = "select COD_EMPRESA,
						RUT,	
						RUT as RUT_NO_ING,	
						DIG_VERIF,
						DIG_VERIF as DIG_VERIF_NO_ING,
						ALIAS,
						ALIAS as ALIAS_NO_ING,
						NOM_EMPRESA,
						NOM_EMPRESA as NOM_EMPRESA_NO_ING,
						GIRO,
						DSCTO_PROVEEDOR,
						'' SIZE,
						'none' CONTACTO_WEB,
						'' NOM_CONTACTO_WEB,
						'' RUT_CONTACTO_WEB,
						'' EMPRESA_CONTACTO_WEB,
						'' CIUDAD_CONTACTO_WEB,
						'' TELEFONO_CONTACTO_WEB,
						'' CELULAR_CONTACTO_WEB,
						'' EMAIL_CONTACTO_WEB,
						'' COMENTARIO_CONTACTO_WEB,
						GIRO as GIRO_NO_ING, 
						COD_CLASIF_EMPRESA,
						DIRECCION_INTERNET,
						RUT_REPRESENTANTE,
						DIG_VERIF_REPRESENTANTE,
						NOM_REPRESENTANTE,
						ES_CLIENTE,
						case ES_CLIENTE 
							WHEN 'S' THEN '' 
							ELSE CASE ES_CLIENTE
									WHEN 'S' THEN ''
									ELSE 'none'
								end
						end VISIBLE_TAB2,
						ES_PROVEEDOR_INTERNO,
						ES_PROVEEDOR_EXTERNO,
						ES_PERSONAL,
						CASE ES_PERSONAL
							WHEN 'S' THEN ''
							ELSE 'none'
						end TD_CATEGORIA,
						CASE ES_PERSONAL
							WHEN 'S' THEN 'none'
							ELSE ''
						end TD_ESPACIO,
						TIPO_PARTICIPACION,
						IMPRIMIR_EMP_MAS_SUC,
						SUJETO_A_APROBACION,
						COD_USUARIO,
						dbo.f_get_porc_dscto_corporativo_empresa(cod_empresa, getdate()) PORC_DSCTO_CORPORATIVO,
						0 TOTAL_DISPONIBLE,
						0 TOTAL_POR_FACTURAR,
						0 TOTAL_CREDITO_ASIGNADO,
						0 TOTAL_POR_COBRAR,
						0 TOTAL_ATRASADO,
						year(getdate()) ANO_ACTUAL,
						year(getdate())-1  ANO_ANTERIOR,
						COD_FORMA_PAGO_CLIENTE,
						ALIAS_CONTABLE,
						MONTO_COMPRA_MAX_SIN_AUTORIZAR
					from EMPRESA 
					where COD_EMPRESA = {KEY1}";				
												
		$this->dws['dw_empresa'] = new datawindow($sql);	
		
		$this->dws['dw_empresa']->add_control(new static_text('NOM_CONTACTO_WEB'));
		$this->dws['dw_empresa']->add_control(new static_text('RUT_CONTACTO_WEB'));
		$this->dws['dw_empresa']->add_control(new static_text('EMPRESA_CONTACTO_WEB'));
		$this->dws['dw_empresa']->add_control(new static_text('CIUDAD_CONTACTO_WEB'));
		$this->dws['dw_empresa']->add_control(new static_text('TELEFONO_CONTACTO_WEB'));
		$this->dws['dw_empresa']->add_control(new static_text('CELULAR_CONTACTO_WEB'));
		$this->dws['dw_empresa']->add_control(new static_text('EMAIL_CONTACTO_WEB'));
		$this->dws['dw_empresa']->add_control(new static_text('COMENTARIO_CONTACTO_WEB'));
		
		$this->dws['dw_empresa']->add_control($control = new static_text('COD_EMPRESA'));	
		$this->dws['dw_empresa']->add_control($control = new edit_rut('RUT'));	
		$control->set_onChange("existe_rut(this);");
		
		$this->dws['dw_empresa']->add_control(new edit_dig_verif('DIG_VERIF'));	
		$this->dws['dw_empresa']->add_control(new edit_text_upper('ALIAS', 27, 30));
		$this->dws['dw_empresa']->add_control(new edit_text_upper('ALIAS_CONTABLE', 65, 30));	
		$this->dws['dw_empresa']->add_control(new edit_text_upper('NOM_EMPRESA', 132, 100));	
		$this->dws['dw_empresa']->add_control(new edit_text_upper('GIRO', 60, 50));	
		$this->dws['dw_empresa']->add_control(new edit_num('MONTO_COMPRA_MAX_SIN_AUTORIZAR', 10, 10));	
		
		// se crean alias en el select, para los campos que son ingresables en el primer tab y no ingresables en el segundo
		$this->dws['dw_empresa']->add_control(new edit_rut('RUT_NO_ING'));
		$this->dws['dw_empresa']->set_entrable('RUT_NO_ING', false);
		$this->dws['dw_empresa']->set_entrable('DIG_VERIF_NO_ING', false);
		$this->dws['dw_empresa']->set_entrable('ALIAS_NO_ING', false);
		$this->dws['dw_empresa']->set_entrable('NOM_EMPRESA_NO_ING', false);
		$this->dws['dw_empresa']->set_entrable('GIRO_NO_ING', false);
		
		$sql = "select 		COD_CLASIF_EMPRESA, 
							NOM_CLASIF_EMPRESA,
							ORDEN
				from 		CLASIF_EMPRESA
				order by 	ORDEN";
		$this->dws['dw_empresa']->add_control(new drop_down_dw('COD_CLASIF_EMPRESA', $sql, 165));				
		$this->dws['dw_empresa']->add_control(new edit_text_lower('DIRECCION_INTERNET', 30, 30));
		$this->dws['dw_empresa']->add_control(new edit_check_box('SUJETO_A_APROBACION', 'S', 'N'));
		$this->dws['dw_empresa']->add_control(new edit_rut('RUT_REPRESENTANTE', 10, 10, 'DIG_VERIF_REPRESENTANTE'));
		$this->dws['dw_empresa']->add_control(new edit_dig_verif('DIG_VERIF_REPRESENTANTE', 'RUT_REPRESENTANTE'));	
		$this->dws['dw_empresa']->add_control(new edit_text_upper('NOM_REPRESENTANTE', 30, 100));	
		$this->dws['dw_empresa']->add_control($control = new edit_check_box('ES_CLIENTE', 'S', 'N', 'Cliente'));
		$control->set_onChange("muestra_tab_cliente()");	
		
		$sql_fp_cliente		= "	select 			COD_FORMA_PAGO COD_FORMA_PAGO_CLIENTE
													,NOM_FORMA_PAGO	 NOM_FORMA_PAGO_CLIENTE												
						   			from			FORMA_PAGO
						   			order by  		ORDEN";
		$this->dws['dw_empresa']->add_control(new drop_down_dw('COD_FORMA_PAGO_CLIENTE',$sql_fp_cliente,150));	
		
		$this->dws['dw_empresa']->add_control($control = new edit_check_box('ES_PROVEEDOR_INTERNO', 'S', 'N', 'Prov Int'));
		$control->set_onChange("muestra_nuevo_tab(); if (this.checked) document.getElementById('ES_PROVEEDOR_EXTERNO_0').checked=false");
		$this->dws['dw_empresa']->add_control($control = new edit_check_box('ES_PROVEEDOR_EXTERNO', 'S', 'N', 'Prov Ext'));
		$control->set_onChange("muestra_nuevo_tab(); if (this.checked) document.getElementById('ES_PROVEEDOR_INTERNO_0').checked=false");
		
		$this->dws['dw_empresa']->add_control($control = new edit_check_box('ES_PERSONAL', 'S', 'N', 'Personal'));
		$control->set_onChange("valida_personal();");
		
		$this->dws['dw_empresa']->add_control(new drop_down_list('TIPO_PARTICIPACION',array('','FA','BH','FA EX'),array('','FA','BH','FA EX'),40));
		
		
		
		$this->dws['dw_empresa']->add_control(new edit_check_box('IMPRIMIR_EMP_MAS_SUC', 'S', 'N'));
		$sql_usuario = "SELECT COD_USUARIO, NOM_USUARIO FROM USUARIO WHERE ES_VENDEDOR = 'S'";
		$this->dws['dw_empresa']->add_control(new drop_down_dw('COD_USUARIO', $sql_usuario, 150));		
			
		// EL CAMPO PORC_DSCTO_CORPORATIVO FUE ELIMINADO DE LA BD							
		$this->dws['dw_empresa']->add_control(new edit_porcentaje('PORC_DSCTO_CORPORATIVO'));
		
		
		$this->dws['dw_empresa']->add_control(new edit_precio('TOTAL_DISPONIBLE'));
		$this->dws['dw_empresa']->set_entrable('TOTAL_DISPONIBLE', false);
		$this->dws['dw_empresa']->add_control(new edit_precio('TOTAL_POR_FACTURAR'));
		$this->dws['dw_empresa']->set_entrable('TOTAL_POR_FACTURAR', false);
		$this->dws['dw_empresa']->add_control(new edit_precio('TOTAL_CREDITO_ASIGNADO'));
		$this->dws['dw_empresa']->set_entrable('TOTAL_CREDITO_ASIGNADO', false);
		$this->dws['dw_empresa']->add_control(new edit_precio('TOTAL_POR_COBRAR'));
		$this->dws['dw_empresa']->set_entrable('TOTAL_POR_COBRAR', false);
		$this->dws['dw_empresa']->add_control(new edit_precio('TOTAL_ATRASADO'));
		$this->dws['dw_empresa']->set_entrable('TOTAL_ATRASADO', false);	
		
		
		// asigna los mandatorys
		$this->dws['dw_empresa']->set_mandatory('RUT', 'Rut de la Empresa');		
		$this->dws['dw_empresa']->set_mandatory('DIG_VERIF', 'Dígito verificador del RUT de la Empresa');		
		$this->dws['dw_empresa']->set_mandatory('ALIAS', 'Alías de la Empresa');		
		$this->dws['dw_empresa']->set_mandatory('NOM_EMPRESA', 'Razón Social');		
		$this->dws['dw_empresa']->set_mandatory('GIRO', 'Giro de la Empresa');		
		$this->dws['dw_empresa']->set_mandatory('COD_CLASIF_EMPRESA', 'Clasificación de la Empresa');
		$this->dws['dw_empresa']->set_mandatory('COD_USUARIO', 'Un Vendedor');				
		
		$this->dws['dw_sucursal'] = new dw_sucursal();
		$this->dws['dw_persona'] = new dw_persona();
		$this->dws['dw_costo_producto'] = new dw_costo_producto();
		$this->dws['dw_valor_defecto_compra'] = new dw_valor_defecto_compra();
		
		/* tab historial ******* POR DEFINIR
		$this->dws['dw_prorroga'] = new datawindow($sql, "PRORROGA");		//***********
		$this->dws['dw_protesto'] = new datawindow($sql, "PROTESTO");		//***********
		*/
		
		$this->dws['dw_bitacora_empresa'] = new dw_bitacora_empresa();
		
		// estadistica de ventas
		$sql = "execute spdw_estadistica_venta {KEY1}";
		$this->dws['dw_factura'] = new datawindow($sql, "FACTURA");
	
		$this->dws['dw_factura']->add_control(new edit_mes('MES'));
		$this->dws['dw_factura']->add_control(new computed('SUBTOTAL_ANT'));
		$this->dws['dw_factura']->add_control(new edit_precio('MONTO_DSCTO_ANT'));
		$this->dws['dw_factura']->add_control(new edit_precio('MONTO_NETO_ANT'));
		$this->dws['dw_factura']->add_control(new edit_precio('SUBTOTAL_ACT'));
		$this->dws['dw_factura']->add_control(new edit_precio('MONTO_DSCTO_ACT'));
		$this->dws['dw_factura']->add_control(new edit_precio('MONTO_NETO_ACT'));
		$this->dws['dw_factura']->add_control(new edit_porcentaje('CRECIMIENTO'));
		
		$this->dws['dw_factura']->accumulate('SUBTOTAL_ANT', '', false);
		$this->dws['dw_factura']->accumulate('MONTO_DSCTO_ANT', '', false);
		$this->dws['dw_factura']->accumulate('MONTO_NETO_ANT', '', false);
		$this->dws['dw_factura']->accumulate('SUBTOTAL_ACT', '', false);
		$this->dws['dw_factura']->accumulate('MONTO_DSCTO_ACT', '', false);
		$this->dws['dw_factura']->accumulate('MONTO_NETO_ACT', '', false);
		$this->dws['dw_factura']->set_entrable_dw(false);	/// no funcionó dejar no entrable la dw.  IS 25/02/09
		
		//auditoria EMPRESA
		$this->add_auditoria('NOM_EMPRESA');
		$this->add_auditoria('GIRO');
		$this->add_auditoria('ALIAS');
		$this->add_auditoria('COD_CLASIF_EMPRESA');
		$this->add_auditoria('DIRECCION_INTERNET');
		$this->add_auditoria('RUT_REPRESENTANTE');
		$this->add_auditoria('DIG_VERIF_REPRESENTANTE');
		$this->add_auditoria('NOM_REPRESENTANTE');
		$this->add_auditoria('ES_CLIENTE');
		$this->add_auditoria('ES_PROVEEDOR_INTERNO');
		$this->add_auditoria('ES_PROVEEDOR_EXTERNO');
		$this->add_auditoria('ES_PERSONAL');
		$this->add_auditoria('TIPO_PARTICIPACION');
		$this->add_auditoria('IMPRIMIR_EMP_MAS_SUC');
		$this->add_auditoria('SUJETO_A_APROBACION');
		$this->add_auditoria('COD_USUARIO');
		
		//auditoria SUCURSAL
		$this->add_auditoria_relacionada('SUCURSAL', 'NOM_SUCURSAL');
		$this->add_auditoria_relacionada('SUCURSAL', 'DIRECCION');
		$this->add_auditoria_relacionada('SUCURSAL', 'DIRECCION_FACTURA');
		$this->add_auditoria_relacionada('SUCURSAL', 'DIRECCION_DESPACHO');
		$this->add_auditoria_relacionada('SUCURSAL', 'COD_COMUNA');
		$this->add_auditoria_relacionada('SUCURSAL', 'COD_CIUDAD');
		$this->add_auditoria_relacionada('SUCURSAL', 'COD_PAIS');
		$this->add_auditoria_relacionada('SUCURSAL', 'FAX');
		$this->add_auditoria_relacionada('SUCURSAL', 'TELEFONO');

		// dscto corporativo
		$this->add_auditoria_relacionada('DSCTO_CORPORATIVO_EMPRESA', 'PORC_DSCTO_CORPORATIVO');
		$this->add_auditoria_relacionada('DSCTO_CORPORATIVO_EMPRESA', 'FECHA_INICIO_VIGENCIA');
		// se cae falta considerar estas auditorias		
		/*
		//auditoria PERSONA
		$this->add_auditoria_relacionada('PERSONA', 'NOM_PERSONA');
		$this->add_auditoria_relacionada('PERSONA', 'COD_CARGO');
		$this->add_auditoria_relacionada('PERSONA', 'COD_SUCURSAL');
		$this->add_auditoria_relacionada('PERSONA', 'TELEFONO');
		$this->add_auditoria_relacionada('PERSONA', 'FAX');
		$this->add_auditoria_relacionada('PERSONA', 'EMAIL');
	
		//auditoria PRODUCTO_PROVEEDOR
		$this->add_auditoria_relacionada('PRODUCTO_PROVEEDOR', 'COD_PRODUCTO');
		$this->add_auditoria_relacionada('PRODUCTO_PROVEEDOR', 'COD_INTERNO_PRODUCTO');
		$this->add_auditoria_relacionada('PRODUCTO_PROVEEDOR', 'PRECIO');
		*/		
		$this->first_focus = 'RUT';
	}
	function new_record() {
		
		$row = $this->dws['dw_empresa']->insert_row();
		$this->dws['dw_empresa']->set_item($row, 'ES_CLIENTE', 'S');
		$this->dws['dw_empresa']->controls['TIPO_PARTICIPACION']->enabled = false;
		$this->dws['dw_empresa']->set_item(0, 'TD_CATEGORIA', 'none');
		$this->dws['dw_empresa']->set_item(0, 'TD_ESPACIO', '');
		$this->dws['dw_valor_defecto_compra']->insert_row();
		$this->dws['dw_valor_defecto_compra']->set_item(0, 'VISIBLE_TAB', 'none');
		$this->dws['dw_empresa']->set_item(0, 'CONTACTO_WEB','none');
		$size = '512px';
        $this->dws['dw_empresa']->set_item(0, 'SIZE',$size);
        
        unset($this->dws['dw_empresa']->controls['COD_USUARIO']);
		$sql="SELECT COD_USUARIO, 
					 NOM_USUARIO 
			 FROM USUARIO 
			 WHERE ES_VENDEDOR = 'S' 
			 AND AUTORIZA_INGRESO = 'S'
			 order by NOM_USUARIO";
		$this->dws['dw_empresa']->add_control($control = new drop_down_dw('COD_USUARIO',$sql,150));
				
		$cod_solicitud = session::get("SOLICITUD_COTIZACION");
		if($cod_solicitud <> ''){
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$sql_solicitud = "SELECT COD_CONTACTO
				FROM SOLICITUD_COTIZACION
				WHERE COD_SOLICITUD_COTIZACION =$cod_solicitud"; 
		$result_solicitud = $db->build_results($sql_solicitud);
		$cod_contacto = $result_solicitud[0]['COD_CONTACTO'];
				
		$sql_contacto="SELECT C.NOM_CONTACTO 
						, C.RUT 
						, C.DIG_VERIF
						, C.NOM_CIUDAD
						,dbo.f_contacto_telefono(CP.COD_CONTACTO_PERSONA,1) TELEFONO 
						,dbo.f_contacto_telefono(CP.COD_CONTACTO_PERSONA,2) CELULAR 
						,CP.NOM_PERSONA
						,CP.MAIL 
						,LL.MENSAJE
				FROM	  ITEM_SOLICITUD_COTIZACION ISC
						, SOLICITUD_COTIZACION SC
						, CONTACTO C
						, PRODUCTO P
						,CONTACTO_PERSONA CP
						,LLAMADO LL
				WHERE	  C.COD_CONTACTO = $cod_contacto
				AND		  ISC.COD_SOLICITUD_COTIZACION = SC.COD_SOLICITUD_COTIZACION
				AND		  C.COD_CONTACTO = SC.COD_CONTACTO
				AND		  P.COD_PRODUCTO = ISC.COD_PRODUCTO
				AND       C.COD_CONTACTO = CP.COD_CONTACTO
				AND       SC.COD_LLAMADO = LL.COD_LLAMADO
				ORDER BY  COD_ITEM_SOLICITUD_COTIZACION";
		$result_contacto = $db->build_results($sql_contacto);		
				
	  	$this->dws['dw_empresa']->set_item(0,'NOM_CONTACTO_WEB', $result_contacto[0]['NOM_PERSONA']);
        $this->dws['dw_empresa']->set_item(0,'RUT_CONTACTO_WEB', $result_contacto[0]['RUT'].'-'.$result_contacto[0]['DIG_VERIF']);
        $this->dws['dw_empresa']->set_item(0,'EMPRESA_CONTACTO_WEB', $result_contacto[0]['NOM_CONTACTO']);
        $this->dws['dw_empresa']->set_item(0,'CIUDAD_CONTACTO_WEB', $result_contacto[0]['NOM_CIUDAD']);
        $this->dws['dw_empresa']->set_item(0,'TELEFONO_CONTACTO_WEB', $result_contacto[0]['TELEFONO']);
        $this->dws['dw_empresa']->set_item(0,'CELULAR_CONTACTO_WEB', $result_contacto[0]['CELULAR']);
        $this->dws['dw_empresa']->set_item(0,'EMAIL_CONTACTO_WEB', $result_contacto[0]['MAIL']);
        $this->dws['dw_empresa']->set_item(0,'COMENTARIO_CONTACTO_WEB', $result_contacto[0]['MENSAJE']);
        $this->dws['dw_empresa']->set_item(0, 'CONTACTO_WEB','');
        $size = '610px';
        $this->dws['dw_empresa']->set_item(0, 'SIZE',$size);		
		}		
	}
	function load_record() {
		$COD_EMPRESA = $this->get_item_wo($this->current_record, 'COD_EMPRESA');
		$this->dws['dw_empresa']->retrieve($COD_EMPRESA);
		$this->dws['dw_empresa']->set_entrable('RUT', false);		// El rut no es modificable
		$this->dws['dw_empresa']->set_entrable('DIG_VERIF', false);		// El rut no es modificable
		$this->dws['dw_sucursal']->retrieve($COD_EMPRESA);
		$this->dws['dw_persona']->retrieve($COD_EMPRESA);
		$this->dws['dw_persona']->cod_empresa = $COD_EMPRESA;
		$this->dws['dw_costo_producto']->retrieve($COD_EMPRESA);
		$this->dws['dw_bitacora_empresa']->retrieve($COD_EMPRESA);
		$this->dws['dw_valor_defecto_compra']->retrieve($COD_EMPRESA);
		if ($this->dws['dw_valor_defecto_compra']->row_count()==0)
			$this->dws['dw_valor_defecto_compra']->insert_row();
				
						
		/**  por definir ************
		$this->dws['dw_prorroga']->retrieve();
		$this->dws['dw_protesto']->retrieve();
		*/
		
		$this->dws['dw_factura']->retrieve($COD_EMPRESA);
		
		$ES_PERSONAL = $this->dws['dw_empresa']->get_item(0, 'ES_PERSONAL');
		
		if($ES_PERSONAL == 'S'){
			$this->dws['dw_empresa']->controls['TIPO_PARTICIPACION']->enabled = true;
		}
		$this->dws['dw_empresa']->set_item(0, 'CONTACTO_WEB','none');
		$size = '514px';
        $this->dws['dw_empresa']->set_item(0, 'SIZE',$size);

        // para que muestre solo usuarios viegente y el asignado
		$cod_usuario = $this->dws['dw_empresa']->get_item(0, 'COD_USUARIO');
		if ($cod_usuario=='') 
			$cod_usuario = 0;
        $sql="SELECT COD_USUARIO, 
					 NOM_USUARIO 
			 FROM USUARIO 
			 WHERE ES_VENDEDOR = 'S' 
			 AND AUTORIZA_INGRESO = 'S'
			 union
			 SELECT COD_USUARIO, 
					 NOM_USUARIO 
			 FROM USUARIO 
			 WHERE COD_USUARIO = $cod_usuario
			 order by NOM_USUARIO";
        $this->dws['dw_empresa']->controls['COD_USUARIO']->set_sql($sql);
		$this->dws['dw_empresa']->controls['COD_USUARIO']->retrieve();
		
		$priv = $this->get_privilegio_opcion_usuario('996505', $this->cod_usuario);
		if($priv == 'E')
			$this->dws['dw_empresa']->set_entrable('MONTO_COMPRA_MAX_SIN_AUTORIZAR', true);	
		else
			$this->dws['dw_empresa']->set_entrable('MONTO_COMPRA_MAX_SIN_AUTORIZAR', false);
	}
	function get_key() {
		return $this->dws['dw_empresa']->get_item(0, 'COD_EMPRESA');
	}
	function save_record($db) {
		$COD_EMPRESA = $this->get_key();
		$RUT = $this->dws['dw_empresa']->get_item(0, 'RUT');
		$DIG_VERIF = $this->dws['dw_empresa']->get_item(0, 'DIG_VERIF');
		$ALIAS = $this->dws['dw_empresa']->get_item(0, 'ALIAS');
		$ALIAS_CONTABLE = $this->dws['dw_empresa']->get_item(0, 'ALIAS_CONTABLE');
		$NOM_EMPRESA = $this->dws['dw_empresa']->get_item(0, 'NOM_EMPRESA');
		$NOM_EMPRESA = str_replace("'", "''", $NOM_EMPRESA);
		$GIRO = $this->dws['dw_empresa']->get_item(0, 'GIRO');
		$GIRO = str_replace("'", "''", $GIRO);
		$COD_CLASIF_EMPRESA = $this->dws['dw_empresa']->get_item(0, 'COD_CLASIF_EMPRESA');
		$DIRECCION_INTERNET = $this->dws['dw_empresa']->get_item(0, 'DIRECCION_INTERNET');
		$RUT_REPRESENTANTE = $this->dws['dw_empresa']->get_item(0, 'RUT_REPRESENTANTE');
		$DIG_VERIF_REPRESENTANTE = $this->dws['dw_empresa']->get_item(0, 'DIG_VERIF_REPRESENTANTE');
		$NOM_REPRESENTANTE = $this->dws['dw_empresa']->get_item(0, 'NOM_REPRESENTANTE');	
		$ES_CLIENTE = $this->dws['dw_empresa']->get_item(0, 'ES_CLIENTE');
		$ES_PROVEEDOR_INTERNO = $this->dws['dw_empresa']->get_item(0, 'ES_PROVEEDOR_INTERNO');
		$ES_PROVEEDOR_EXTERNO = $this->dws['dw_empresa']->get_item(0, 'ES_PROVEEDOR_EXTERNO');
		$ES_PERSONAL = $this->dws['dw_empresa']->get_item(0, 'ES_PERSONAL');
		$IMPRIMIR_EMP_MAS_SUC = $this->dws['dw_empresa']->get_item(0, 'IMPRIMIR_EMP_MAS_SUC');
		$SUJETO_A_APROBACION = $this->dws['dw_empresa']->get_item(0, 'SUJETO_A_APROBACION');
		$VENDEDOR_CABECERA = $this->dws['dw_empresa']->get_item(0, 'COD_USUARIO');
		// EL CAMPO PORC_DSCTO_CORPORATIVO FUE ELIMINADO DE LA BD	
		$PORC_DSCTO_CORPORATIVO = $this->dws['dw_empresa']->get_item(0, 'PORC_DSCTO_CORPORATIVO');
		$DSCTO_PROVEEDOR = $this->dws['dw_valor_defecto_compra']->get_item(0, 'DSCTO_PROVEEDOR');
		
		if($DSCTO_PROVEEDOR == ''){
		$DSCTO_PROVEEDOR = 0 ;	
		}
		$MONTO_COMPRA_MAX_SIN_AUTORIZAR = $this->dws['dw_empresa']->get_item(0, 'MONTO_COMPRA_MAX_SIN_AUTORIZAR');
		
		$DIRECCION_INTERNET = ($DIRECCION_INTERNET=='') ? "null" : "'$DIRECCION_INTERNET'";
		$RUT_REPRESENTANTE = ($RUT_REPRESENTANTE=='') ? "null" : $RUT_REPRESENTANTE;
		$DIG_VERIF_REPRESENTANTE = ($DIG_VERIF_REPRESENTANTE=='') ? "null" : "'$DIG_VERIF_REPRESENTANTE'";
		$NOM_REPRESENTANTE = ($NOM_REPRESENTANTE=='') ? "null" : "'$NOM_REPRESENTANTE'";
		
		//se valida en la función validate que se ingresen los demás campos mandatory
		// EL CAMPO PORC_DSCTO_CORPORATIVO FUE ELIMINADO DE LA BD	
		$PORC_DSCTO_CORPORATIVO = ($PORC_DSCTO_CORPORATIVO=='') ? "0" : $PORC_DSCTO_CORPORATIVO;

		$COD_EMPRESA = ($COD_EMPRESA=='') ? "null" : $COD_EMPRESA;
		if ($ALIAS_CONTABLE=='')
			$ALIAS_CONTABLE = $ALIAS;
		
		$TIPO_PARTICIPACION = $this->dws['dw_empresa']->get_item(0, 'TIPO_PARTICIPACION');
		$TIPO_PARTICIPACION			= ($TIPO_PARTICIPACION =='') ? "null" : "'$TIPO_PARTICIPACION'";
	   	
		$COD_FORMA_PAGO_CLIENTE = $this->dws['dw_empresa']->get_item(0, 'COD_FORMA_PAGO_CLIENTE');
		$COD_FORMA_PAGO_CLIENTE	= ($COD_FORMA_PAGO_CLIENTE =='') ? "null" : $COD_FORMA_PAGO_CLIENTE;
		$MONTO_COMPRA_MAX_SIN_AUTORIZAR	= ($MONTO_COMPRA_MAX_SIN_AUTORIZAR =='') ? 0 : $MONTO_COMPRA_MAX_SIN_AUTORIZAR;	

		$sp = 'spu_empresa';		
	    if ($this->is_new_record())
	    	$operacion = 'INSERT';
	    else
	    	$operacion = 'UPDATE';
		
		
		// EL CAMPO PORC_DSCTO_CORPORATIVO FUE ELIMINADO DE LA BD	
		$param = "'$operacion'
				,$COD_EMPRESA
				,$RUT
				,'$DIG_VERIF'
				,'$ALIAS'
				,'$ALIAS_CONTABLE'
				,'$NOM_EMPRESA'
				,'$GIRO'
				,$COD_CLASIF_EMPRESA
				,$DIRECCION_INTERNET
				,$RUT_REPRESENTANTE
				,$DIG_VERIF_REPRESENTANTE
				,$NOM_REPRESENTANTE
				,'$ES_CLIENTE'
				,'$ES_PROVEEDOR_INTERNO'
				,'$ES_PROVEEDOR_EXTERNO'
				,'$ES_PERSONAL'
				,'$IMPRIMIR_EMP_MAS_SUC'
				,'$SUJETO_A_APROBACION'
				,$PORC_DSCTO_CORPORATIVO
				,$VENDEDOR_CABECERA
				,$TIPO_PARTICIPACION
				,$DSCTO_PROVEEDOR
				,$COD_FORMA_PAGO_CLIENTE
				,$MONTO_COMPRA_MAX_SIN_AUTORIZAR";
		 
		if ($db->EXECUTE_SP($sp, $param)){
			if ($this->is_new_record()) {
				$COD_EMPRESA = $db->GET_IDENTITY();
				$this->dws['dw_empresa']->set_item(0, 'COD_EMPRESA', $COD_EMPRESA);		
				
				$param = "'DSCTO_CORPORATIVO_EMPRESA',$COD_EMPRESA, $PORC_DSCTO_CORPORATIVO";
				$sp = 'spu_empresa';
				if (!$db->EXECUTE_SP($sp, $param))
					return false;
			}
			for ($i=0; $i<$this->dws['dw_sucursal']->row_count(); $i++)
				$this->dws['dw_sucursal']->set_item($i, 'COD_EMPRESA', $COD_EMPRESA);
				
				if (!$this->dws['dw_sucursal']->update($db))
					return false;
			
			for ($i=0; $i<$this->dws['dw_persona']->row_count(); $i++) {
				$COD_SUCURSAL = $this->dws['dw_persona']->get_item($i, 'P_COD_SUCURSAL');
				if ($COD_SUCURSAL < 0) {
						$row = $this->dws['dw_sucursal']->un_redirect(- $COD_SUCURSAL - 100);		// el -100 viene del insert_row
						$COD_SUCURSAL = $this->dws['dw_sucursal']->get_item($row, 'COD_SUCURSAL');
						$this->dws['dw_persona']->set_item($i, 'P_COD_SUCURSAL', $COD_SUCURSAL);		
				}
			}
			if (!$this->dws['dw_persona']->update($db))
				return false;
			
			//se hace insert a las tablas de contacto
			if ($this->is_new_record()){
				$param = "'CONTACTO',$COD_EMPRESA";
				if (!$db->EXECUTE_SP($sp, $param))
					return false;
			}
				
			for ($i=0; $i<$this->dws['dw_costo_producto']->row_count(); $i++)
				$this->dws['dw_costo_producto']->set_item($i, 'COD_EMPRESA', $COD_EMPRESA);				
			if (!$this->dws['dw_costo_producto']->update($db))
				return false;
								
			for ($i=0; $i<$this->dws['dw_bitacora_empresa']->row_count(); $i++)
				$this->dws['dw_bitacora_empresa']->set_item($i, 'COD_EMPRESA', $COD_EMPRESA);				
			if (!$this->dws['dw_bitacora_empresa']->update($db))
				return false;

			// TAB VALOR DEFECTO COMPRA //	
			$prov_int			 	= $this->dws['dw_empresa']->get_item(0, 'ES_PROVEEDOR_INTERNO');
			$prov_ext			 	= $this->dws['dw_empresa']->get_item(0, 'ES_PROVEEDOR_EXTERNO');
		
			$cod_persona_defecto 	= $this->dws['dw_valor_defecto_compra']->get_item(0, 'COD_PERSONA_DEFECTO');
			$cod_persona_defecto	= ($cod_persona_defecto=='') ? "null" : $cod_persona_defecto;
			$cod_forma_pago 		= $this->dws['dw_valor_defecto_compra']->get_item(0, 'COD_FORMA_PAGO');
			$cod_forma_pago			= ($cod_forma_pago=='') ? "null" : $cod_forma_pago;
			
			if ($cod_persona_defecto < 0) {
				$row = $this->dws['dw_persona']->un_redirect(- $cod_persona_defecto - 100);
				$cod_persona_defecto = $this->dws['dw_persona']->get_item($row, 'COD_PERSONA');
				$this->dws['dw_valor_defecto_compra']->set_item($i, 'COD_PERSONA_DEFECTO', $cod_persona_defecto);		
			}
			
			if($prov_int == 'S' or $prov_ext == 'S')			
	    		$operacion = 'INSERT';	    		
	    	else	    		
	    		$operacion = 'DELETE';
			$sp = 'spu_valor_defecto_compra';

			$param = "'$operacion',$COD_EMPRESA,$cod_persona_defecto,$cod_forma_pago";
			 			 
			if (!$db->EXECUTE_SP($sp, $param))
				return false;
			 
			return true;
		}
		else
			return false;						
	}
}
/////////////////////////////////////////////////////////////
// Se separa por K_CLIENTE
$file_name = dirname(__FILE__)."/".K_CLIENTE."/class_wi_empresa.php";
if (file_exists($file_name)) 
	require_once($file_name);
else {
	//AQUI SE RECONSTRUYE LA CLASE 
	class wi_empresa extends wi_empresa_base {
		function wi_empresa($cod_item_menu) {
			parent::wi_empresa_base($cod_item_menu); 
		}
	}
	class dw_valor_defecto_compra extends dw_valor_defecto_compra_base {
		function dw_valor_defecto_compra() {
			parent::dw_valor_defecto_compra_base(); 
		}
}
	
	
}

?>