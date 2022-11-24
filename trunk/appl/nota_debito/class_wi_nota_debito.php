<?php
require_once(dirname(__FILE__) . "/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../common_appl/class_w_cot_nv.php");
require_once(dirname(__FILE__)."/../empresa/class_dw_help_empresa.php");

class dw_item_nota_debito extends datawindow {
	const K_ESTADO_SII_EMITIDA 			= 1;
	function dw_item_nota_debito() {		
			$sql = "SELECT ITND.COD_ITEM_NOTA_DEBITO,
						ITND.COD_NOTA_DEBITO,
						ITND.ORDEN,
						ITND.ITEM,
						ITND.COD_PRODUCTO,
						ITND.COD_PRODUCTO COD_PRODUCTO_OLD,
						ITND.NOM_PRODUCTO,
						ITND.CANTIDAD,
						ITND.PRECIO,
						ITND.COD_ITEM_DOC
				FROM    ITEM_NOTA_DEBITO ITND, NOTA_DEBITO ND
				WHERE   ND.COD_NOTA_DEBITO = ITND.COD_NOTA_DEBITO 
				AND	    ITND.COD_NOTA_DEBITO = {KEY1}
				ORDER BY ITND.ORDEN";
		parent::datawindow($sql, 'ITEM_NOTA_DEBITO', true, true);	

		$this->add_control(new edit_text('COD_ITEM_NOTA_DEBITO',10, 10, 'hidden'));
		$this->add_control(new edit_num('ORDEN',4, 10));
		$this->add_control(new edit_text('ITEM',4 , 5));
		$this->add_control(new edit_cantidad('CANTIDAD',12,10));

		$this->add_control(new computed('PRECIO', 0));
		$this->set_computed('TOTAL', '[CANTIDAD] * [PRECIO]');
		$this->accumulate('TOTAL');
		$this->add_controls_producto_help();		// Debe ir despues de los computed donde esta involucrado precio, porque add_controls_producto_help() afecta el campos PRECIO
		$this->set_first_focus('COD_PRODUCTO');
		$this->controls['COD_PRODUCTO']->size = 14;
		$this->controls['NOM_PRODUCTO']->size = 81;
		
		// asigna los mandatorys
		$this->set_mandatory('ORDEN', 'Orden');
		$this->set_mandatory('COD_PRODUCTO', 'Código del producto');
		$this->set_mandatory('CANTIDAD', 'Cantidad');
		
	}
	function insert_row($row=-1) {
		$row = parent::insert_row($row);
		$this->set_item($row, 'ORDEN', $this->row_count() * 10);
		$this->set_item($row, 'ITEM', $this->row_count());
		return $row;
	}
	function update($db, $cod_nota_debito)	{
		$sp = 'spu_item_nota_debito';

		for ($i = 0; $i < $this->row_count(); $i++){
			$statuts = $this->get_status_row($i);
			if ($statuts == K_ROW_NOT_MODIFIED || $statuts == K_ROW_NEW)
				continue;

			$cod_item_nota_debito	= $this->get_item($i, 'COD_ITEM_NOTA_DEBITO');
			$orden 					= $this->get_item($i, 'ORDEN');
			$item 					= $this->get_item($i, 'ITEM');
			$cod_producto 			= $this->get_item($i, 'COD_PRODUCTO');
			$nom_producto 			= $this->get_item($i, 'NOM_PRODUCTO');
			$cantidad 				= $this->get_item($i, 'CANTIDAD');
			$precio 				= $this->get_item($i, 'PRECIO');

			$cod_item_nota_debito = ($cod_item_nota_debito=='') ? "null" : $cod_item_nota_debito;
			
			if ($statuts == K_ROW_NEW_MODIFIED)
				$operacion = 'INSERT';
			elseif ($statuts == K_ROW_MODIFIED)
				$operacion = 'UPDATE';

			$param = "'$operacion'
						,$cod_item_nota_debito
						,$cod_nota_debito
						,$orden
						,'$item'
						,'$cod_producto'
						,'$nom_producto'
						,$cantidad
						,$precio";
			if (!$db->EXECUTE_SP($sp, $param))
				return false;
		}
		for ($i = 0; $i < $this->row_count('delete'); $i++) {
			$statuts = $this->get_status_row($i, 'delete');
			if ($statuts == K_ROW_NEW || $statuts == K_ROW_NEW_MODIFIED)
				continue;
				
			$cod_item_nota_debito = $this->get_item($i, 'COD_ITEM_NOTA_DEBITO', 'delete');
			if (!$db->EXECUTE_SP($sp, "'DELETE', $cod_item_nota_debito")){
				return false;
			}
		}
		//Ordernar
		/*if ($this->row_count() > 0) {
			$parametros_sp = "'COD_ITEM_NOTA_DEBITO','NOTA_DEBITO', $cod_item_nota_debito";
			if (!$db->EXECUTE_SP('sp_orden_no_parametricas', $parametros_sp))
				return false;
		}*/
		return true;
	}
}

class dw_nota_debito extends dw_help_empresa{
	const K_ESTADO_SII_ANULADA			= 4;
	function dw_nota_debito(){
		
		$sql = "SELECT	 ND.COD_NOTA_DEBITO
						,substring(convert(varchar(20), ND.FECHA_NOTA_DEBITO, 103) + ' ' + convert(varchar(20), ND.FECHA_NOTA_DEBITO, 108), 1, 16) FECHA_NOTA_DEBITO
						,ND.COD_USUARIO
						,U.NOM_USUARIO
						,ND.NRO_NOTA_DEBITO
						,EDS.NOM_ESTADO_DOC_SII
						,ND.COD_ESTADO_DOC_SII
						,ND.REFERENCIA
						,ND.COD_EMPRESA
						,ND.COD_SUCURSAL COD_SUCURSAL_FACTURA
						,E.ALIAS
						,E.RUT
						,E.DIG_VERIF
						,E.NOM_EMPRESA
						,E.GIRO													
						,ND.COD_SUCURSAL AS COD_SUCURSAL_NOTA_DEBITO
						,dbo.f_get_direccion('SUCURSAL', ND.COD_SUCURSAL, '[DIRECCION]  [NOM_COMUNA] [NOM_CIUDAD] - TELEFONO: [TELEFONO] - FAX: [FAX]') DIRECCION_NOTA_DEBITO
						,ND.COD_PERSONA
						,dbo.f_emp_get_mail_cargo_persona(ND.COD_PERSONA, '[EMAIL]') MAIL_CARGO_PERSONA
						,ND.COD_DOC	
						,ND.TIPO_DOC
						,ND.SUBTOTAL SUM_TOTAL					
						,ND.TOTAL_NETO
						,ND.PORC_DSCTO1
						,ND.PORC_DSCTO2
						,ND.INGRESO_USUARIO_DSCTO1  
						,ND.MONTO_DSCTO1
						,ND.INGRESO_USUARIO_DSCTO2
						,ND.MONTO_DSCTO2
						,ND.PORC_IVA
						,ND.MONTO_IVA
						,ND.TOTAL_CON_IVA
						,ND.COD_TIPO_NOTA_DEBITO
						,case ND.COD_ESTADO_DOC_SII 
							when ".self::K_ESTADO_SII_ANULADA." then '' 
							else 'none' 
						end TR_DISPLAY
						,'' VISIBLE_DTE
						,ND.MOTIVO_ANULA
						,ND.COD_USUARIO_ANULA
				FROM 	NOTA_DEBITO ND, USUARIO U, EMPRESA E, ESTADO_DOC_SII EDS, TIPO_NOTA_DEBITO TND
				WHERE	ND.COD_NOTA_DEBITO = {KEY1} 
				  and	ND.COD_USUARIO = U.COD_USUARIO 
				  AND	ND.COD_EMPRESA = E.COD_EMPRESA 
				  AND	ND.COD_ESTADO_DOC_SII = EDS.COD_ESTADO_DOC_SII
				  AND	ND.COD_TIPO_NOTA_DEBITO = TND.COD_TIPO_NOTA_DEBITO";
		
		//parent::dw_help_empresa($sql, '', true, true, 'P');	// El último parametro indica que solo acepta proveedores
		parent::dw_help_empresa($sql);
		
		$this->add_control(new edit_nro_doc('NRO_NOTA_DEBITO','NOTA_DEBITO'));
		$this->add_control(new edit_date('FECHA_NOTA_DEBITO'));
		
		$this->add_control(new edit_text('COD_ESTADO_DOC_SII',10,10, 'hidden'));
		$this->add_control(new static_text('NOM_ESTADO_DOC_SII'));
		
		$sql	= "select 	 COD_TIPO_NOTA_DEBITO
							,NOM_TIPO_NOTA_DEBITO
					from 	 TIPO_NOTA_DEBITO
					order by COD_TIPO_NOTA_DEBITO";
		$this->add_control(new drop_down_dw('COD_TIPO_NOTA_DEBITO',$sql,150));
		$this->set_entrable('COD_TIPO_NOTA_DEBITO', true);
		
		$this->add_control(new edit_text_upper('TIPO_DOC', 30, 100));
		$this->add_control(new edit_text('COD_DOC', 30, 100));
		$this->controls['ALIAS']->size = 27;
		$this->add_control(new edit_text_upper('REFERENCIA', 100 , 100));
		
	}
}
class wi_nota_debito_base extends w_cot_nv {
	const K_ESTADO_SII_EMITIDA 			= 1;
	const K_ESTADO_SII_IMPRESA			= 2;
	const K_ESTADO_SII_ENVIADA			= 3;
	const K_ESTADO_SII_ANULADA			= 4;
	const K_AUTORIZA_VISIBLE_BTN_DTE	= '993525';
	const K_IP_FTP		= 42;		// Direccion del FTP
	const K_USER_FTP	= 43;		//usuario para el FTP
	const K_PASS_FTP	= 44;		// password para el FTP	
		
	function wi_nota_debito_base($cod_item_menu) {
		parent::w_cot_nv('nota_debito', $cod_item_menu);
		$this->add_FK_delete_cascada('ITEM_NOTA_DEBITO');
		
		$this->dws['dw_nota_debito'] = new dw_nota_debito();
		$this->add_controls_cot_nv();
		
		//tab items
		$this->dws['dw_item_nota_debito'] = new dw_item_nota_debito();

		//************
		$this->b_print_visible = false;
		//************
	}
	function new_record(){
		$this->dws['dw_nota_debito']->insert_row();	
		$this->b_delete_visible  = false; //cuando es un registro nuevo no muestra el boton eliminar
		$this->dws['dw_nota_debito']->set_item(0, 'TR_DISPLAY', 'none');
		$this->dws['dw_nota_debito']->set_item(0, 'COD_USUARIO', $this->cod_usuario);
		$this->dws['dw_nota_debito']->set_item(0, 'NOM_USUARIO', $this->nom_usuario);
		$this->dws['dw_nota_debito']->set_item(0, 'COD_ESTADO_DOC_SII', self::K_ESTADO_SII_EMITIDA);
		$this->dws['dw_nota_debito']->set_item(0, 'NOM_ESTADO_DOC_SII', 'EMITIDA');
		$this->dws['dw_nota_debito']->set_item(0, 'COD_TIPO_NOTA_DEBITO', 1); //Ventas
		
		$this->dws['dw_nota_debito']->set_entrable('FECHA_NOTA_DEBITO',	false);
		$this->dws['dw_nota_debito']->set_entrable('TIPO_DOC', false);
		$this->dws['dw_nota_debito']->set_entrable('COD_DOC', false);
		
		if(session::is_set("ND_CREADA_DESDE")){
			$nro_nota_credito = session::get("ND_CREADA_DESDE");
			$this->create_from_nc($nro_nota_credito);
			session::un_set("ND_CREADA_DESDE");
		}
	}
	function load_record() {
		
		$cod_nota_debito = $this->get_item_wo($this->current_record, 'COD_NOTA_DEBITO');
		$this->dws['dw_nota_debito']->retrieve($cod_nota_debito);	
		$cod_empresa = $this->dws['dw_nota_debito']->get_item(0, 'COD_EMPRESA');
		$this->dws['dw_nota_debito']->controls['COD_SUCURSAL_FACTURA']->retrieve($cod_empresa);
		$this->dws['dw_nota_debito']->controls['COD_PERSONA']->retrieve($cod_empresa);
		$this->dws['dw_item_nota_debito']->retrieve($cod_nota_debito);
		
		$COD_ESTADO_DOC_SII = $this->dws['dw_nota_debito']->get_item(0, 'COD_ESTADO_DOC_SII');
		
		$this->b_print_visible 	 = true;
		$this->b_no_save_visible = true;
		$this->b_save_visible 	 = true;
		$this->b_modify_visible	 = true;
		$this->b_delete_visible  = true;
				
		$this->dws['dw_nota_debito']->set_entrable('FECHA_NOTA_DEBITO'	,false);
		$this->dws['dw_nota_debito']->set_entrable('REFERENCIA'			, true);
		$this->dws['dw_nota_debito']->set_entrable('NOM_EMPRESA'			, true);
		$this->dws['dw_nota_debito']->set_entrable('ALIAS'					, true);
		$this->dws['dw_nota_debito']->set_entrable('COD_EMPRESA'			, true);
		$this->dws['dw_nota_debito']->set_entrable('RUT'					, true);
		$this->dws['dw_nota_debito']->set_entrable('COD_PERSONA'			, true);
		$this->dws['dw_nota_debito']->set_entrable('TIPO_DOC'	, false);
		// aqui se dejan no modificables los datos del tab items
		$this->dws['dw_item_nota_debito']->set_entrable_dw(true);
		
		if($this->dws['dw_nota_debito']->get_item(0, 'COD_DOC') <> ''){
			$this->dws['dw_nota_debito']->set_entrable('COD_TIPO_NOTA_DEBITO'	,false);
			$this->dws['dw_nota_debito']->set_entrable('COD_EMPRESA'			,false);
			$this->dws['dw_nota_debito']->set_entrable('ALIAS'					,false);
			$this->dws['dw_nota_debito']->set_entrable('RUT'					,false);
			$this->dws['dw_nota_debito']->set_entrable('NOM_EMPRESA'			,false);
			$this->dws['dw_nota_debito']->set_entrable('COD_SUCURSAL_FACTURA'	,false);
			$this->dws['dw_nota_debito']->set_entrable('COD_PERSONA'			,false);
		}
		
		if ($COD_ESTADO_DOC_SII == self::K_ESTADO_SII_IMPRESA) {
			
			if($this->tiene_privilegio_opcion(self::K_AUTORIZA_VISIBLE_BTN_DTE)){
			//	$this->dws['dw_nota_debito']->set_item(0, 'VISIBLE_DTE', '');
				$this->dws['dw_nota_debito']->set_entrable('COD_TIPO_NOTA_DEBITO', true);
			}else{
			//	$this->dws['dw_nota_debito']->set_item(0, 'VISIBLE_DTE', 'none');
				$this->dws['dw_nota_debito']->set_entrable('COD_TIPO_NOTA_DEBITO', false);
			}
			$this->dws['dw_nota_debito']->set_item(0, 'VISIBLE_DTE', 'none');
			$sql = "select 	COD_ESTADO_DOC_SII
							,NOM_ESTADO_DOC_SII
					from ESTADO_DOC_SII
					where COD_ESTADO_DOC_SII = ".self::K_ESTADO_SII_IMPRESA." or
							COD_ESTADO_DOC_SII = ".self::K_ESTADO_SII_ANULADA."
					order by COD_ESTADO_DOC_SII";
					
			unset($this->dws['dw_nota_debito']->controls['COD_ESTADO_DOC_SII']);
			$this->dws['dw_nota_debito']->add_control($control = new drop_down_dw('COD_ESTADO_DOC_SII',$sql,150));	
			$control->set_onChange("mostrarOcultar_Anula(this);");
			$this->dws['dw_nota_debito']->controls['NOM_ESTADO_DOC_SII']->type = 'hidden';
			$this->dws['dw_nota_debito']->add_control(new edit_text_upper('MOTIVO_ANULA',110, 100));

			$this->dws['dw_nota_debito']->set_entrable('FECHA_NOTA_DEBITO'	, true);
			$this->dws['dw_nota_debito']->set_entrable('REFERENCIA'			, false);
			$this->dws['dw_nota_debito']->set_entrable('OBS'					, false);
			$this->dws['dw_nota_debito']->set_entrable('NOM_EMPRESA'			, false);
			$this->dws['dw_nota_debito']->set_entrable('ALIAS'				    , false);
			$this->dws['dw_nota_debito']->set_entrable('COD_EMPRESA'			, false);
			$this->dws['dw_nota_debito']->set_entrable('RUT'					, false);
			$this->dws['dw_nota_debito']->set_entrable('COD_SUCURSAL_FACTURA'  , false);
			$this->dws['dw_nota_debito']->set_entrable('COD_PERSONA'			, false);

			$this->dws['dw_nota_debito']->set_entrable('PORC_DSCTO1'			, false);
			$this->dws['dw_nota_debito']->set_entrable('MONTO_DSCTO1'			, false);
			$this->dws['dw_nota_debito']->set_entrable('PORC_DSCTO2'			, false);
			$this->dws['dw_nota_debito']->set_entrable('MONTO_DSCTO2'			, false);
			$this->dws['dw_nota_debito']->set_entrable('PORC_IVA'				, false);
			$this->dws['dw_nota_debito']->set_entrable('COD_DOC'				, false);		
		
			
			// aqui se dejan no modificables los datos del tab items
			$this->dws['dw_item_nota_debito']->set_entrable_dw(false);

			$this->b_delete_visible  = false;
				
		}
		
	}
	
	function create_from_nc($ve_nro_nota_credito){
		$db	 = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$sql = "SELECT REFERENCIA
					  ,NC.COD_EMPRESA
					  ,E.RUT
					  ,E.DIG_VERIF
					  ,E.NOM_EMPRESA
					  ,ALIAS
					  ,E.GIRO
					  ,COD_SUCURSAL_FACTURA
					  ,COD_PERSONA
					  ,ORDEN
					  ,ITEM
					  ,COD_PRODUCTO
					  ,NOM_PRODUCTO
					  ,CANTIDAD
					  ,PRECIO
					  ,COD_ITEM_NOTA_CREDITO
					  ,PORC_DSCTO1
					  ,PORC_DSCTO2
					  ,MONTO_DSCTO1
					  ,MONTO_DSCTO2
					  ,dbo.f_get_direccion('NOTA_CREDITO', NC.COD_NOTA_CREDITO, '[DIRECCION]  [NOM_COMUNA] [NOM_CIUDAD] - TELEFONO: [TELEFONO] - FAX: [FAX]') DIRECCION_FACTURA
					  ,dbo.f_emp_get_mail_cargo_persona(NC.COD_PERSONA,  '[EMAIL]') MAIL_CARGO_PERSONA
				FROM NOTA_CREDITO NC
					,ITEM_NOTA_CREDITO INC
					,EMPRESA E
				WHERE NC.NRO_NOTA_CREDITO = $ve_nro_nota_credito
				AND NC.COD_NOTA_CREDITO = INC.COD_NOTA_CREDITO
				AND NC.COD_EMPRESA = E.COD_EMPRESA";
		$result = $db->build_results($sql);
		
		$this->dws['dw_nota_debito']->set_item(0, 'TIPO_DOC', 'NOTA CREDITO');
		$this->dws['dw_nota_debito']->set_item(0, 'COD_DOC', $ve_nro_nota_credito);
		$this->dws['dw_nota_debito']->set_item(0, 'REFERENCIA', $result[0]['REFERENCIA']);
		$this->dws['dw_nota_debito']->set_item(0, 'COD_EMPRESA', $result[0]['COD_EMPRESA']);
		$this->dws['dw_nota_debito']->set_item(0, 'RUT', $result[0]['RUT']);
		$this->dws['dw_nota_debito']->set_item(0, 'DIG_VERIF', $result[0]['DIG_VERIF']);
		$this->dws['dw_nota_debito']->set_item(0, 'NOM_EMPRESA', $result[0]['NOM_EMPRESA']);
		$this->dws['dw_nota_debito']->set_item(0, 'ALIAS', $result[0]['ALIAS']);
		$this->dws['dw_nota_debito']->set_item(0, 'GIRO', $result[0]['GIRO']);
		$this->dws['dw_nota_debito']->controls['COD_SUCURSAL_FACTURA']->retrieve($result[0]['COD_EMPRESA']);
		$this->dws['dw_nota_debito']->controls['COD_PERSONA']->retrieve($result[0]['COD_EMPRESA']);
		$this->dws['dw_nota_debito']->set_item(0, 'COD_SUCURSAL_FACTURA', $result[0]['COD_SUCURSAL_FACTURA']);
		$this->dws['dw_nota_debito']->set_item(0, 'COD_PERSONA', $result[0]['COD_PERSONA']);
		$this->dws['dw_nota_debito']->set_item(0, 'MAIL_CARGO_PERSONA', $result[0]['MAIL_CARGO_PERSONA']);
		$this->dws['dw_nota_debito']->set_item(0, 'DIRECCION_NOTA_DEBITO', $result[0]['DIRECCION_FACTURA']);
		
		$this->dws['dw_nota_debito']->set_item(0, 'PORC_DSCTO1', $result[0]['PORC_DSCTO1']);
		$this->dws['dw_nota_debito']->set_item(0, 'PORC_DSCTO2', $result[0]['PORC_DSCTO2']);
		$this->dws['dw_nota_debito']->set_item(0, 'MONTO_DSCTO1', $result[0]['MONTO_DSCTO1']);
		$this->dws['dw_nota_debito']->set_item(0, 'MONTO_DSCTO2', $result[0]['MONTO_DSCTO2']);
		
		for($i=0 ; $i < count($result) ; $i++){
			$this->dws['dw_item_nota_debito']->insert_row();
			$this->dws['dw_item_nota_debito']->set_item($i, 'ORDEN', $result[$i]['ORDEN']);
			$this->dws['dw_item_nota_debito']->set_item($i, 'ITEM', $result[$i]['ITEM']);
			$this->dws['dw_item_nota_debito']->set_item($i, 'COD_PRODUCTO', $result[$i]['COD_PRODUCTO']);
			$this->dws['dw_item_nota_debito']->set_item($i, 'NOM_PRODUCTO', $result[$i]['NOM_PRODUCTO']);
			$this->dws['dw_item_nota_debito']->set_item($i, 'CANTIDAD', $result[$i]['CANTIDAD']);
			$this->dws['dw_item_nota_debito']->set_item($i, 'PRECIO', $result[$i]['PRECIO']);
			$this->dws['dw_item_nota_debito']->set_item($i, 'COD_ITEM_DOC', $result[$i]['COD_ITEM_NOTA_CREDITO']);
			
			$total = $result[$i]['PRECIO'] * $result[$i]['CANTIDAD'];
			$sum_total += $total;
		}
		
		$this->dws['dw_item_nota_debito']->calc_computed();
		
		$total_neto = $sum_total - $result[0]['MONTO_DSCTO1'] - $result[0]['MONTO_DSCTO2'];
		$monto_iva = $total_neto * ($this->get_parametro(1)/100);
		$total_con_iva = $total_neto + $monto_iva;
		
		$this->dws['dw_nota_debito']->set_item(0, 'TOTAL_NETO',		$total_neto);
		$this->dws['dw_nota_debito']->set_item(0, 'MONTO_IVA',		$monto_iva);
		$this->dws['dw_nota_debito']->set_item(0, 'TOTAL_CON_IVA',	$total_con_iva);
		
		$this->dws['dw_nota_debito']->set_entrable('COD_TIPO_NOTA_DEBITO'	,false);
		$this->dws['dw_nota_debito']->set_entrable('COD_EMPRESA'			,false);
		$this->dws['dw_nota_debito']->set_entrable('ALIAS'					,false);
		$this->dws['dw_nota_debito']->set_entrable('RUT'					,false);
		$this->dws['dw_nota_debito']->set_entrable('NOM_EMPRESA'			,false);
		$this->dws['dw_nota_debito']->set_entrable('COD_SUCURSAL_FACTURA'	,false);
		$this->dws['dw_nota_debito']->set_entrable('COD_PERSONA'			,false);
	}
	
	function get_key() {
		return $this->dws['dw_nota_debito']->get_item(0, 'COD_NOTA_DEBITO');
	}
	function save_record($db) {
		$cod_nota_debito = $this->get_key();
		$cod_usuario				= $this->dws['dw_nota_debito']->get_item(0, 'COD_USUARIO');
		$nro_nota_debito			= $this->dws['dw_nota_debito']->get_item(0, 'NRO_NOTA_DEBITO');
		$fecha_nota_debito			= $this->dws['dw_nota_debito']->get_item(0, 'FECHA_NOTA_DEBITO');
		$cod_estado_doc_sii			= $this->dws['dw_nota_debito']->get_item(0, 'COD_ESTADO_DOC_SII');
		$cod_empresa				= $this->dws['dw_nota_debito']->get_item(0, 'COD_EMPRESA');
		$referencia					= $this->dws['dw_nota_debito']->get_item(0, 'REFERENCIA');
		$cod_sucursal_factura		= $this->dws['dw_nota_debito']->get_item(0, 'COD_SUCURSAL_FACTURA');
		$cod_tipo_nota_debito		= $this->dws['dw_nota_debito']->get_item(0, 'COD_TIPO_NOTA_DEBITO');
		$tipo_nota_debito			= $this->dws['dw_nota_debito']->get_item(0, 'TIPO_DOC');
		$cod_doc					= $this->dws['dw_nota_debito']->get_item(0, 'COD_DOC');
		$cod_persona				= $this->dws['dw_nota_debito']->get_item(0, 'COD_PERSONA');
		
		//totales
		$subtotal					= $this->dws['dw_nota_debito']->get_item(0, 'SUM_TOTAL');
		$total_neto					= $this->dws['dw_nota_debito']->get_item(0, 'TOTAL_NETO');
		$porc_dscto1				= $this->dws['dw_nota_debito']->get_item(0, 'PORC_DSCTO1');
		$porc_dscto2				= $this->dws['dw_nota_debito']->get_item(0, 'PORC_DSCTO2');
		$ingreso_usuario_dscto1		= $this->dws['dw_nota_debito']->get_item(0, 'INGRESO_USUARIO_DSCTO1');
		$monto_dscto1				= $this->dws['dw_nota_debito']->get_item(0, 'MONTO_DSCTO1');
		$ingreso_usuario_dscto2		= $this->dws['dw_nota_debito']->get_item(0, 'INGRESO_USUARIO_DSCTO2');
		$monto_dscto2				= $this->dws['dw_nota_debito']->get_item(0, 'MONTO_DSCTO2');
		$porc_iva					= $this->dws['dw_nota_debito']->get_item(0, 'PORC_IVA');
		$monto_iva					= $this->dws['dw_nota_debito']->get_item(0, 'MONTO_IVA');
		$total_con_iva				= $this->dws['dw_nota_debito']->get_item(0, 'TOTAL_CON_IVA');
		$cod_usuario_anula			= $this->cod_usuario;
		$cod_usuario_impresion		= $this->cod_usuario;
		
		//validacion de null
		$cod_nota_debito			= ($cod_nota_debito=='') ? "null" : $cod_nota_debito;
		$nro_nota_debito			= ($nro_nota_debito =='') ? "null" : $nro_nota_debito;
		$fecha_nota_debito			= ($fecha_nota_debito =='') ? "null" : "'$fecha_nota_debito'";
		$tipo_nota_debito			= ($tipo_nota_debito =='') ? "null" : "'$tipo_nota_debito'"; 
		$cod_doc					= ($cod_doc =='') ? "null" : $cod_doc; 
		$cod_sucursal_factura		= ($cod_sucursal_factura == '') ? "null" : $cod_sucursal_factura;
		$subtotal 					= ($subtotal == '' ? 0: $subtotal);
		$total_neto					= ($total_neto == '' ? 0: $total_neto);	
		$porc_dscto1				= ($porc_dscto1 == '' ? 0: $porc_dscto1);
		$porc_dscto2				= ($porc_dscto2 == '' ? 0: $porc_dscto2);
		$ingreso_usuario_dscto1 	= ($ingreso_usuario_dscto1 =='') ? "null" : "'$ingreso_usuario_dscto1'";
		$ingreso_usuario_dscto2 	= ($ingreso_usuario_dscto2 =='') ? "null" : "'$ingreso_usuario_dscto2'";
		$monto_dscto1 				= ($monto_dscto1 == '' ? 0: $monto_dscto1);
		$monto_dscto2 				= ($monto_dscto2 == '' ? 0: $monto_dscto2);
		$porc_iva 					= ($porc_iva == '' ? 0: $porc_iva);
		$monto_iva 					= ($monto_iva == '' ? 0: $monto_iva);
		$total_con_iva 				= ($total_con_iva == '' ? 0: $total_con_iva);	
		$motivo_anula				= ($motivo_anula == '')? "null": $motivo_anula;
		
		$sp = 'spu_nota_debito';
	    if ($this->is_new_record())
	    	$operacion = 'INSERT';
	    else
	    	$operacion = 'UPDATE';
	    		    
	    $param	= "'$operacion'
				,$cod_nota_debito
				,$cod_usuario_impresion
				,$cod_usuario
				,$nro_nota_debito
				,$fecha_nota_debito
				,$cod_estado_doc_sii
				,$cod_empresa
				,'$referencia'
				,$cod_sucursal_factura
				,$cod_tipo_nota_debito
				,$tipo_nota_debito
				,$cod_doc
				,$cod_persona
				,$subtotal
				,$total_neto
				,$porc_dscto1
				,$porc_dscto2
				,$ingreso_usuario_dscto1
				,$monto_dscto1
				,$ingreso_usuario_dscto2
				,$monto_dscto2
				,$porc_iva
				,$monto_iva
				,$total_con_iva
				,$motivo_anula
				,$cod_usuario_anula";
				
		if ($db->EXECUTE_SP($sp, $param)){
			if ($this->is_new_record()) {
				$cod_nota_debito = $db->GET_IDENTITY();
				$this->dws['dw_nota_debito']->set_item(0, 'COD_NOTA_DEBITO', $cod_nota_debito);
			}
			for ($i=0; $i<$this->dws['dw_item_nota_debito']->row_count(); $i++) 
				$this->dws['dw_item_nota_debito']->set_item($i, 'COD_NOTA_DEBITO', $cod_nota_debito);
		
			if (!$this->dws['dw_item_nota_debito']->update($db, $cod_nota_debito)) 
				return false;
		
			
			return true;
		}
		return false;		
				
	}
	function print_record() {
		if (!$this->lock_record())
			return false;
		$cod_nota_debito = $this->get_key();
		$cod_tipo_doc_sii = 4;
		$cod_usuario_impresion = $this->cod_usuario;
		
		$nro_nota_debito = $this->dws['dw_nota_debito']->get_item(0, 'NRO_NOTA_DEBITO');
		
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		if($nro_nota_debito == ''){
			$sql = "select dbo.f_get_nro_doc_sii ($cod_tipo_doc_sii , $cod_usuario_impresion) NRO_NOTA_DEBITO";
			$result = $db->build_results($sql);
			$nro_nota_debito = $result[0]['NRO_NOTA_DEBITO'];
		}
		

		//declrar constante para que el monto con iva del reporte lo transpforme a palabras
		$sql = "select TOTAL_CON_IVA from NOTA_DEBITO where COD_NOTA_DEBITO = $cod_nota_debito";
		
		$resultado = $db->build_results($sql);
		$total_con_iva = $resultado [0] ['TOTAL_CON_IVA'] ;
		$total_en_palabras =  Numbers_Words::toWords($total_con_iva,"es");
		$total_en_palabras = strtr($total_en_palabras, "áéíóú", "aeiou");
			
		if ($nro_nota_debito == -1){
			$this->redraw();		
			$this->dws['dw_nota_debito']->message("Sr(a). Usuario: Ud. no tiene documentos asignados, para imprimir la Nota de Debito.");	
			return false;
		}else{
			$db->BEGIN_TRANSACTION();
			$sp = 'spu_nota_debito';
			$param = "'PRINT', $cod_nota_debito, $cod_usuario_impresion";
			
			if ($db->EXECUTE_SP($sp, $param)) {
				$db->COMMIT_TRANSACTION();
				
					$estado_nd_impresa = self::K_ESTADO_SII_IMPRESA; 
					$cod_estado_nd = $this->dws['dw_nota_debito']->get_item(0, 'COD_ESTADO_DOC_SII');
					//if ($cod_estado_nd != $estado_nd_impresa)//es la 1era vez que se imprime la Guia de Despacho 
						//$this->f_envia_mail('IMPRESO');
						
			$sql= "SELECT	ND.COD_NOTA_debito
							,ND.NRO_NOTA_debito
							,dbo.f_format_date(ND.FECHA_NOTA_DEBITO,3)FECHA_NOTA_debito
							,ND.COD_USUARIO_IMPRESION
							,ND.REFERENCIA
							,ND.NOM_EMPRESA
							,ND.GIRO
							,ND.RUT
							,ND.DIG_VERIF
							,ND.DIRECCION
							,ND.TELEFONO
							,ND.FAX
							,ND.SUBTOTAL
							,ND.PORC_DSCTO1
							,ND.MONTO_DSCTO1
							,ND.PORC_DSCTO2
							,ND.MONTO_DSCTO2
							,ND.MONTO_DSCTO1 + ND.MONTO_DSCTO2 TOTAL_DSCTO
							,ND.TOTAL_NETO
							,ND.PORC_IVA
							,ND.MONTO_IVA
							,ND.TOTAL_CON_IVA
							,COM.NOM_COMUNA
							,CIU.NOM_CIUDAD
							,ITND.ITEM
							,ITND.CANTIDAD
							,ITND.COD_PRODUCTO
							,ITND.NOM_PRODUCTO
							,ITND.PRECIO
							,ITND.PRECIO * ITND.CANTIDAD TOTAL_ND								 
							,'".$total_en_palabras."' TOTAL_EN_PALABRAS
							,convert(varchar(5), GETDATE(), 8) HORA
							,FA.NRO_FACTURA
							,U.INI_USUARIO
					FROM 	NOTA_DEBITO ND LEFT OUTER JOIN FACTURA FA ON ND.COD_DOC = FA.COD_FACTURA 
											LEFT OUTER JOIN COMUNA COM ON COM.COD_COMUNA = ND.COD_COMUNA
											LEFT OUTER JOIN CIUDAD CIU ON CIU.COD_CIUDAD = ND.COD_CIUDAD, ITEM_NOTA_DEBITO ITND, USUARIO U
					WHERE 	ND.COD_NOTA_DEBITO = $cod_nota_debito
					AND		ND.COD_USUARIO = U.COD_USUARIO
					AND		ITND.COD_NOTA_debito = ND.COD_NOTA_debito";
					
						// reporte
					$labels = array();
					$labels['strCOD_NOTA_DEBITO'] = $cod_nota_debito;					
					$file_name = $this->find_file('nota_debito', 'nota_debito.xml');					
					$rpt = new print_nota_debito($sql, $file_name, $labels, "Nota Debito".$cod_nota_debito, 0);										
					$this->_load_record();
					$this->b_delete_visible  = false;	
					return true;
				}
				else {
					$db->ROLLBACK_TRANSACTION();
					return false;
				}			
		}
		$this->unlock_record();
	}
	/*
	
	// esta funcio envia mail  cuando se imprime e documento de guia despacho 
 	function f_envia_mail($estado_nota_debito){
 		$cod_nota_debito = $this->get_key();
 		$remitente = $this->nom_usuario;
        $cod_remitente = $this->cod_usuario;
        
        $db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
        $sql = "SELECT NRO_NOTA_debito FROM NOTA_debito WHERE COD_NOTA_debito = $cod_nota_debito";
        $result = $db->build_results($sql);
        $nro_nota_debito = $result[0]['NRO_NOTA_debito'];		
		
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
        /*
        if($estado_nota_debito == 'IMPRESO')
		{
			$asunto = 'Impresion de Nota de debito Nº '.$nro_nota_debito;
	        $mensaje = 'Se ha <b>IMPRESO</b> la <b>Nota de debito Nº '.$nro_nota_debito.'</b> por el usuario <b><i>'.$remitente.'<i><b>';  
		}
	  	
	 	if($estado_nota_debito == 'ANULADA')
		{
	        $asunto = 'Anulacion de Nota de debito Nº '.$nro_nota_debito;
	        $mensaje = 'Se ha <b>ANULADO</b> la <b>Nota de debito Nº '.$nro_nota_debito.'</b> por el usuario <b><i>'.$remitente.'<i><b>';
		}

	  	$cabeceras  = 'MIME-Version: 1.0' . "\n";
        $cabeceras .= 'Content-type: text/html; charset=iso-8859-1' . "\n";
        $cabeceras .= 'From: '.$mail_remitente. "\n";
        //se comenta el envio de mail por q ya no es necesario => Vmelo. 
        //mail($para_admin1, $asunto, $mensaje, $cabeceras);
        //mail($para_admin2, $asunto, $mensaje, $cabeceras);
 		return 0;
   	}*/
   	
	function Envia_DTE($name_archivo, $fname){
			//SOLO para el CHAITEN
		/*	if (K_SERVER <> "192.168.2.26")
				return false;*/
			$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
			$sql_ftp =	"select dbo.f_get_parametro(".self::K_IP_FTP.") DIRECCION_FTP
								,dbo.f_get_parametro(".self::K_USER_FTP.")	USER_FTP
								,dbo.f_get_parametro(".self::K_PASS_FTP.")	PASS_FTP";

			$result_ftp = $db->build_results($sql_ftp);
			// datos de FTP Local
			$file_name_ftp = (dirname(__FILE__)."../../../ftp_dte.php");
			if (file_exists($file_name_ftp)){ 
			require_once($file_name_ftp);
				$K_DIRECCION_FTP	= K_DIRECCION_FTP;
				$K_USUARIO_FTP		= K_USUARIO_FTP;
				$K_PASSWORD_FTP		= K_PASSWORD_FTP;
				$K_PORT 			= 21;
			}else{
			//datos de FTP
			$K_DIRECCION_FTP	= $result_ftp[0]['DIRECCION_FTP'] ;	//Ip FTP
			$K_USUARIO_FTP		= $result_ftp[0]['USER_FTP'] ;		//Usuario FTP
			$K_PASSWORD_FTP		= $result_ftp[0]['PASS_FTP'] ;		//Password FTP
			$K_PORT 			= 21; 		// PUERTO DEL FTP
			}
			// establecer una conexión básica
			$conn_id = ftp_connect($K_DIRECCION_FTP);
			if ($conn_id===false)
				return false; 
			
			// iniciar una sesión con nombre de usuario y contraseña
			$login_result = ftp_login($conn_id, $K_USUARIO_FTP, $K_PASSWORD_FTP);
			if($login_result === false)
				return false;
			ftp_pasv ($conn_id, true) ;
			// subir un archivo
			//$upload = ftp_put($conn_id, $name_archivo, $fname, FTP_BINARY);  
			if(!(ftp_put($conn_id, $name_archivo, $fname, FTP_BINARY)))
				return false;

			// cerrar la conexión ftp 
			ftp_close($conn_id);
			
			return true;
	}
   	
   	function envia_NC_Electronica(){
   		if (!$this->lock_record())
			return false;
   		$cod_nota_debito = $this->get_key();
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$count1= 0;
		$sql_valida="SELECT CANTIDAD 
			  		 FROM ITEM_NOTA_DEBITO
			  		 WHERE COD_NOTA_DEBITO = $cod_nota_debito";
			  
		$result_valida = $db->build_results($sql_valida);

		for($i = 0 ; $i < count($result_valida) ; $i++){
			if($result_valida[$i] <> 0)
				$count1 = $count1 + 1;
		}
		if($count1 > 18){
			$this->_load_record();
			$this->alert('Se está ingresando más item que la cantidad permitida, favor contacte a IntegraSystem.');
			return false;
		}		
		
		$this->sepa_decimales	= ',';	//Usar , como separador de decimales
		$this->vacio 			= ' ';	//Usar rellenos de blanco, CAMPO ALFANUMERICO
		$this->llena_cero		= 0;	//Usar rellenos con '0', CAMPO NUMERICO
		$this->separador		= ';';	//Usar ; como separador de campos
		$cod_usuario_impresion = $this->cod_usuario;
		$cod_impresora_dte = $_POST['wi_impresora_dte'];
		if($cod_impresora_dte == 100){
		$EMISOR_NC = 'SALA VENTA';
		}else{
		
		if ($cod_impresora_dte == '')
			$sql = "SELECT U.NOM_USUARIO 
					FROM USUARIO U
					where U.COD_USUARIO = $cod_usuario_impresion";
		else
			$sql = "SELECT NOM_REGLA NOM_USUARIO
					FROM IMPRESORA_DTE
					WHERE COD_IMPRESORA_DTE = $cod_impresora_dte";
		$result = $db->build_results($sql);
		$EMISOR_NC = $result[0]['NOM_USUARIO'] ;
		}
		$db->BEGIN_TRANSACTION();
		$sp = 'spu_nota_debito';
		$param = "'ENVIA_DTE', $cod_nota_debito, $cod_usuario_impresion";
		if ($db->EXECUTE_SP($sp, $param)) {
			$db->COMMIT_TRANSACTION();	
				
			$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
			//declrar constante para que el monto con iva del reporte lo transpforme a palabras
			$sql = "select TOTAL_CON_IVA from NOTA_DEBITO where COD_NOTA_DEBITO = $cod_nota_debito";
			
			$resultado = $db->build_results($sql);
			$total_con_iva = $resultado [0] ['TOTAL_CON_IVA'] ;
			$total_en_palabras =  Numbers_Words::toWords($total_con_iva,"es");
			$total_en_palabras = strtr($total_en_palabras, "áéíóú", "aeiou");
			$total_en_palabras = strtoupper($total_en_palabras);
	
	   		$sql_dte= "SELECT	NC.COD_NOTA_DEBITO,
								NC.NRO_NOTA_DEBITO,
								dbo.f_emp_get_mail_cargo_persona(NC.COD_PERSONA, '[EMAIL]') MAIL_CARGO_PERSONA,
								dbo.f_format_date(NC.FECHA_NOTA_DEBITO,1)FECHA_NOTA_DEBITO,
								NC.COD_USUARIO_IMPRESION,
								NC.REFERENCIA,
								NC.NOM_EMPRESA,
								NC.COD_TIPO_NOTA_DEBITO,
								NC.GIRO,
								NC.RUT,
								NC.DIG_VERIF,
								NC.DIRECCION,
								NC.TELEFONO,
								NC.FAX,
								NC.SUBTOTAL,
								NC.PORC_DSCTO1,
								NC.MONTO_DSCTO1,
								NC.PORC_DSCTO2,
								NC.MONTO_DSCTO2,
								NC.MONTO_DSCTO1 + NC.MONTO_DSCTO2 TOTAL_DSCTO,
								NC.TOTAL_NETO,
								NC.PORC_IVA,
								NC.MONTO_IVA,
								NC.TOTAL_CON_IVA,
								COM.NOM_COMUNA,
								CIU.NOM_CIUDAD,
								ITNC.ITEM,
								ITNC.CANTIDAD,
								ITNC.COD_PRODUCTO,
								ITNC.NOM_PRODUCTO,
								ITNC.PRECIO,
								ITNC.PRECIO * ITNC.CANTIDAD TOTAL_NC,								 
								'".$total_en_palabras."' TOTAL_EN_PALABRAS,
								convert(varchar(5), GETDATE(), 8) HORA,
								FA.NRO_FACTURA,
								FA.PORC_IVA PORC_IVA_FA,
								dbo.f_format_date(FA.FECHA_FACTURA,1) FECHA_FACTURA,
								'$EMISOR_NC' NOM_USUARIO,
								ITNC.ORDEN
						FROM 	NOTA_DEBITO NC LEFT OUTER JOIN FACTURA FA ON NC.COD_DOC = FA.COD_FACTURA 
												LEFT OUTER JOIN COMUNA COM ON COM.COD_COMUNA = NC.COD_COMUNA
												LEFT OUTER JOIN CIUDAD CIU ON CIU.COD_CIUDAD = NC.COD_CIUDAD
								, ITEM_NOTA_DEBITO ITNC, USUARIO U											
						WHERE 	NC.COD_NOTA_DEBITO = $cod_nota_debito
						and NC.COD_USUARIO = U.COD_USUARIO
						AND		ITNC.COD_NOTA_DEBITO = NC.COD_NOTA_DEBITO";
			
			$result_dte = $db->build_results($sql_dte);
			//CANTIDAD DE ITEM_NOTA_DEBITO 
			$count = count($result_dte);

			// datos de Nota DEBITO
			$NRO_NOTA_DEBITO	= $result_dte[0]['NRO_NOTA_DEBITO'] ;			// 1 Numero Nota DEBITO
			$FECHA_NOTA_DEBITO	= $result_dte[0]['FECHA_NOTA_DEBITO'] ;		// 2 Fecha Nota DEBITO
			//Email - VE: =>En el caso de las Nota DEBITO y otros documentos, no aplica por lo que se dejan 0;0 
			$TD					= $this->llena_cero;					// 3 Tipo Despacho
			$TT					= $this->llena_cero;					// 4 Tipo Traslado
			//Email - VE: => 
			$PAGO_DTE			= $this->vacio;							// 5 Forma de Pago
			$FV					= $this->vacio;							// 6 Fecha Vencimiento
			$RUT				= $result_dte[0]['RUT'];				
			$DIG_VERIF			= $result_dte[0]['DIG_VERIF'];
			$RUT_EMPRESA		= $RUT.'-'.$DIG_VERIF;					// 7 Rut Empresa
			$NOM_EMPRESA		= $result_dte[0]['NOM_EMPRESA'] ;		// 8 Razol Social_Nombre Empresa
			$GIRO				= $result_dte[0]['GIRO'];				// 9 Giro Empresa
			$DIRECCION			= $result_dte[0]['DIRECCION'];			//10 Direccion empresa
			$MAIL_CARGO_PERSONA	= $result_dte[0]['MAIL_CARGO_PERSONA'];	//11 E-Mail Contacto
			$TELEFONO			= $result_dte[0]['TELEFONO'];			//12 Telefono Empresa
			$REFERENCIA			= $result_dte[0]['REFERENCIA'];			//12 Referencia de la Nota DEBITO  //datos olvidado por VE.
			$NRO_FACTURA		= $result_dte[0]['NRO_FACTURA'];		//Solicitado a VE por SP
			$GENERA_SALIDA		= $this->vacio;							//Solicitado a VE por SP "DESPACHADO"
			$CANCELADA			= $this->vacio;							//Solicitado a VE por SP "CANCELADO"
			$SUBTOTAL			= number_format($result_dte[0]['SUBTOTAL'], 1, ',', '');	//Solicitado a VE por SP "SUBTOTAL"
			$PORC_DSCTO1		= number_format($result_dte[0]['PORC_DSCTO1'], 1, ',', '');	//Solicitado a VE por SP "PORC_DSCTO1"
			$PORC_DSCTO2		= number_format($result_dte[0]['PORC_DSCTO2'], 1, ',', '');	//Solicitado a VE por SP "PORC_DSCTO2"
			$EMISOR_NC			= $result_dte[0]['NOM_USUARIO'];		//Solicitado a VE por SP "EMISOR_NOTA_DEBITO"
			$NOM_COMUNA			= $result_dte[0]['NOM_COMUNA'];			//13 Comuna Recepcion
			$NOM_CIUDAD			= $result_dte[0]['NOM_CIUDAD'];			//14 Ciudad Recepcion
			$DP					= $result_dte[0]['DIRECCION'];			//15 Dirección Postal
			$COP				= $result_dte[0]['NOM_COMUNA'];			//16 Comuna Postal
			$CIP				= $result_dte[0]['NOM_CIUDAD'];			//17 Ciudad Postal
			
			//DATOS DE TOTALES number_format($result_dte[$i]['TOTAL_FA'], 0, ',', '.');
			$TOTAL_NETO			= number_format($result_dte[0]['TOTAL_NETO'], 1, ',', '');		//18 Monto Neto
			$PORC_IVA			= number_format($result_dte[0]['PORC_IVA'], 1, ',', '');		//19 Tasa IVA
			$MONTO_IVA			= number_format($result_dte[0]['MONTO_IVA'], 1, ',', '');		//20 Monto IVA
			$TOTAL_CON_IVA		= number_format($result_dte[0]['TOTAL_CON_IVA'], 1, ',', '');	//21 Monto Total
			$D1					= 'D1';															//22 Tipo de Mov 1 (Desc/Rec)
			$P1					= '$';															//23 Tipo de valor de Desc/Rec 1
			$MONTO_DSCTO1		= number_format($result_dte[0]['MONTO_DSCTO1'], 1, ',', '');	//24 Valor del Desc/Rec 1
			$D2					= 'D2';															//25 Tipo de Mov 2 (Desc/Rec)
			$P2					= '$';															//26 Tipo de valor de Desc/Rec 2
			$MONTO_DSCTO2		= number_format($result_dte[0]['MONTO_DSCTO2'], 1, ',', '');	//27 Valor del Desc/Rec 2
			$D3					= 'D3';															//28 Tipo de Mov 3 (Desc/Rec)
			$P3					= '$';															//29 Tipo de valor de Desc/Rec 3
			$MONTO_DSCTO3		= '';															//30 Valor del Desc/Rec 3
			$NOM_FORMA_PAGO		= $this->vacio;													//Dato Especial forma de pago adicional
			$NRO_ORDEN_COMPRA	= $this->vacio;													//Numero de Orden Pago
			$NRO_NOTA_VENTA		= $result_dte[0]['NRO_FACTURA'];									//Numero de Nota Venta
			$TOTAL_EN_PALABRAS	= $result_dte[0]['TOTAL_EN_PALABRAS'];							//Total en palabras: Posterior al campo Notas
   			$PORC_IVA_FA		= number_format($result_dte[0]['PORC_IVA_FA'], 1, ',', '');		//Tasa IVA Factura
			
			//datos que hacen referencia al documento NC - FA
			//Numero de Factura o Documento que hace referencia
			$FR					= $result_dte[0]['NRO_FACTURA'];								//39 Folio Referencia
			$FECHA_R			= $result_dte[0]['FECHA_FACTURA'];								//40 Fecha de Referencia
			//1 = Anula Documento de Referencia
			//2 = Corrige el Texto de Referencia
			//3 = Corrige el Monto de le Referencia 
			$CR					= $result_dte[0]['COD_TIPO_NOTA_DEBITO'];						//41 Código de Referencia
			$RER				= $result_dte[0]['REFERENCIA'];									//42 Razón explícita de la referencia

		   	//datos que hacen referencia al documento NC - FA
		   	
			if($FR != ''){
				if($PORC_IVA_FA != 0){ 
					//38 Tipo documento referencia
					$TDR = 33;	//La Nota DEBITO hace referencia a una FACTURA AFECTA
				}else{
					//38 Tipo documento referencia
					$TDR = 34;	//La Nota DEBITO hace referencia a una FACTURA EXENTA
				}
			}else{
				//41 Código de Referencia
				$CR = '';
				//38 Tipo documento referencia
				$TDR = '';	//La Nota DEBITO No hace referencia a una ningun Documento.
			}

			
			//GENERA EL NOMBRE DEL ARCHIVO
			$TIPO_FACT = 61;	//NOTA_DEBITO

			//GENERA EL ALFANUMERICO ALETORIO Y LLENA LA VARIABLE $RES = ALETORIO
			$length = 36;
			$source = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
			$source .= '1234567890';
			
			if($length>0){
		        $RES = "";
		        $source = str_split($source,1);
		        for($i=1; $i<=$length; $i++){
		            mt_srand((double)microtime() * 1000000);
		            $num	= mt_rand(1,count($source));
		            $RES	.= $source[$num-1];
		        }
			 
		    }			
			
			//GENERA ESPACIOS EN BLANCO
			$space = ' ';
			$i = 0; 
			while($i<=100){
				$space .= ' ';
			$i++;
			}
			
			//GENERA ESPACIOS CON CEROS
			$llena_cero = 0;
			$i = 0; 
			while($i<=100){
				$llena_cero .= 0;
			$i++;
			}
			
			//Asignando espacios en blanco Nota DEBITO
			//LINEA 3
			$NRO_NOTA_DEBITO	= substr($NRO_NOTA_DEBITO.$space, 0, 10);		// 1 Numero Nota DEBITO
			$FECHA_NOTA_DEBITO	= substr($FECHA_NOTA_DEBITO.$space, 0, 10);		// 2 Fecha Nota DEBITO
			$TD				= substr($TD.$space, 0, 1);					// 3 Tipo Despacho
			$TT				= substr($TT.$space, 0, 1);					// 4 Tipo Traslado
			$PAGO_DTE		= substr($PAGO_DTE.$space, 0, 1);			// 5 Forma de Pago
			$FV				= substr($FV.$space, 0, 10);				// 6 Fecha Vencimiento
			$RUT_EMPRESA	= substr($RUT_EMPRESA.$space, 0, 10);		// 7 Rut Empresa
			$NOM_EMPRESA	= substr($NOM_EMPRESA.$space, 0, 100);		// 8 Razol Social_Nombre Empresa
			$GIRO			= substr($GIRO.$space, 0, 40);				// 9 Giro Empresa
			$DIRECCION		= substr($DIRECCION.$space, 0, 60);			//10 Direccion empresa
			$MAIL_CARGO_PERSONA = substr($MAIL_CARGO_PERSONA.$space, 0, 60);//11 E-Mail Contacto
			$TELEFONO		= substr($TELEFONO.$space, 0, 15);			//12 Telefono Empresa
			$REFERENCIA		= substr($REFERENCIA.$space, 0, 80);
			$NRO_FACTURA	= substr($NRO_FACTURA.$space, 0, 20);//Solicitado a VE por SP
			$GENERA_SALIDA	= substr($GENERA_SALIDA.$space, 0, 30);		//DESPACHADO
			$CANCELADA		= substr($CANCELADA.$space, 0, 30);			//CANCELADO
			$SUBTOTAL		= substr($SUBTOTAL.$space, 0, 18);			//Solicitado a VE por SP "SUBTOTAL"
			$PORC_DSCTO1	= substr($PORC_DSCTO1.$space, 0, 5);		//Solicitado a VE por SP "PORC_DSCTO1"
			$PORC_DSCTO2	= substr($PORC_DSCTO2.$space, 0, 5);		//Solicitado a VE por SP "PORC_DSCTO2"
			$EMISOR_NC		= substr($EMISOR_NC.$space, 0, 50);			//Solicitado a VE por SP "EMISOR_NOTA_DEBITO"
			//LINEA4
			$NOM_COMUNA		= substr($NOM_COMUNA.$space, 0, 20);		//13 Comuna Recepcion
			$NOM_CIUDAD		= substr($NOM_CIUDAD.$space, 0, 20);		//14 Ciudad Recepcion
			$DP				= substr($DP.$space, 0, 60);				//15 Dirección Postal
			$COP			= substr($COP.$space, 0, 20);				//16 Comuna Postal
			$CIP			= substr($CIP.$space, 0, 20);				//17 Ciudad Postal

			//Asignando espacios en blanco Totales de Nota DEBITO
			$TOTAL_NETO		= substr($TOTAL_NETO.$space, 0, 18);		//18 Monto Neto
			$PORC_IVA		= substr($PORC_IVA.$space, 0, 5);			//19 Tasa IVA
			$MONTO_IVA		= substr($MONTO_IVA.$space, 0, 18);			//20 Monto IVA
			$TOTAL_CON_IVA	= substr($TOTAL_CON_IVA.$space, 0, 18);		//21 Monto Total
			$D1				= substr($D1.$space, 0, 1);					//22 Tipo de Mov 1 (Desc/Rec)
			$P1				= substr($P1.$space, 0, 1);					//23 Tipo de valor de Desc/Rec 1
			$MONTO_DSCTO1	= substr($MONTO_DSCTO1.$space, 0, 18);		//24 Valor del Desc/Rec 1
			$D2				= substr($D2.$space, 0, 1);					//25 Tipo de Mov 2 (Desc/Rec)
			$P2				= substr($P2.$space, 0, 1);					//26 Tipo de valor de Desc/Rec 2
			$MONTO_DSCTO2	= substr($MONTO_DSCTO2.$space, 0, 18);		//27 Valor del Desc/Rec 2
			$D3				= substr($D3.$space, 0, 1);					//28 Tipo de Mov 3 (Desc/Rec)
			$P3				= substr($P3.$space, 0, 1);					//29 Tipo de valor de Desc/Rec 3
			$MONTO_DSCTO3	= substr($MONTO_DSCTO3.$space, 0, 18);		//30 Valor del Desc/Rec 3
			$NOM_FORMA_PAGO = substr($NOM_FORMA_PAGO.$space, 0, 80);	//Dato Especial forma de pago adicional
			$NRO_ORDEN_COMPRA= substr($NRO_ORDEN_COMPRA.$space, 0, 20);	//Numero de Orden Pago
			$NRO_NOTA_VENTA = substr($NRO_NOTA_VENTA.$space, 0, 20);	//Numero de Nota Venta
			$TOTAL_EN_PALABRAS = substr($TOTAL_EN_PALABRAS.' PESOS.'.$space.$space, 0, 200);	//Total en palabras: Posterior al campo Notas
			
			$name_archivo = $TIPO_FACT."_NPG_".$RES.".SPF";
			$fname = tempnam("/tmp", $name_archivo);
			$handle = fopen($fname,"w");
			//DATOS DE NOTA_DEBITO A EXPORTAR 
			//linea 1 y 2
			fwrite($handle, "\r\n"); //salto de linea
			fwrite($handle, "\r\n"); //salto de linea
			//linea 3		
			fwrite($handle, ' ');									// 0 space 2
			fwrite($handle, $NRO_NOTA_DEBITO.$this->separador);			// 1 Numero Nota DEBITO
			fwrite($handle, $FECHA_NOTA_DEBITO.$this->separador);		// 2 Fecha Nota DEBITO
			fwrite($handle, $TD.$this->separador);					// 3 Tipo Despacho
			fwrite($handle, $TT.$this->separador);					// 4 Tipo Traslado
			fwrite($handle, $PAGO_DTE.$this->separador);			// 5 Forma de Pago
			fwrite($handle, $FV.$this->separador);					// 6 Fecha Vencimiento
			fwrite($handle, $RUT_EMPRESA.$this->separador);			// 7 Rut Empresa
			fwrite($handle, $NOM_EMPRESA.$this->separador);			// 8 Razol Social_Nombre Empresa
			fwrite($handle, $GIRO.$this->separador);				// 9 Giro Empresa
			fwrite($handle, $DIRECCION.$this->separador);			//10 Direccion empresa
			//Personalizados Linea 3
			fwrite($handle, $MAIL_CARGO_PERSONA.$this->separador);	//11 E-Mail Contacto 
			fwrite($handle, $TELEFONO.$this->separador);			//12 Telefono Empresa
			fwrite($handle, $REFERENCIA.$this->separador);			//Referencia de la Nota DEBITO
			fwrite($handle, $NRO_FACTURA.$this->separador);	//Solicitado a VE por SP
			fwrite($handle, $GENERA_SALIDA.$this->separador);		//DESPACHADO Solicitado a VE por SP
			fwrite($handle, $CANCELADA.$this->separador);			//CANCELADO Solicitado a VE por SP
			fwrite($handle, $SUBTOTAL.$this->separador);			//Solicitado a VE por SP "SUBTOTAL"
			fwrite($handle, $PORC_DSCTO1.$this->separador);			//Solicitado a VE por SP "PORC_DSCTO1"
			fwrite($handle, $PORC_DSCTO2.$this->separador);			//Solicitado a VE por SP "PORC_DSCTO2"
			fwrite($handle, $EMISOR_NC.$this->separador);			//Solicitado a VE por SP "EMISOR_NOTA_DEBITO"
			fwrite($handle, "\r\n"); //salto de linea
			
			//linea 4
			fwrite($handle, ' ');									// 0 space 2
			fwrite($handle, $NOM_COMUNA.$this->separador);			//13 Comuna Recepcion
			fwrite($handle, $NOM_CIUDAD.$this->separador);			//14 Ciudad Recepcion
			fwrite($handle, $DP.$this->separador);					//15 Dirección Postal
			fwrite($handle, $COP.$this->separador);					//16 Comuna Postal
			fwrite($handle, $CIP.$this->separador);					//17 Ciudad Postal
			fwrite($handle, $TOTAL_NETO.$this->separador);			//18 Monto Neto
			fwrite($handle, $PORC_IVA.$this->separador);			//19 Tasa IVA
			fwrite($handle, $MONTO_IVA.$this->separador);			//20 Monto IVA
			fwrite($handle, $TOTAL_CON_IVA.$this->separador);		//21 Monto Total
			fwrite($handle, $D1.$this->separador);					//22 Tipo de Mov 1 (Desc/Rec)
			fwrite($handle, $P1.$this->separador);					//23 Tipo de valor de Desc/Rec 1
			fwrite($handle, $MONTO_DSCTO1.$this->separador);		//24 Valor del Desc/Rec 1
			fwrite($handle, $D2.$this->separador);					//25 Tipo de Mov 2 (Desc/Rec)
			fwrite($handle, $P2.$this->separador);					//26 Tipo de valor de Desc/Rec 2
			fwrite($handle, $MONTO_DSCTO2.$this->separador);		//27 Valor del Desc/Rec 2
			fwrite($handle, $D3.$this->separador);					//28 Tipo de Mov 3 (Desc/Rec)
			fwrite($handle, $P3.$this->separador);					//29 Tipo de valor de Desc/Rec 3			
			fwrite($handle, $MONTO_DSCTO3.$this->separador);		//30 Valor del Desc/Rec 2
			fwrite($handle, $NOM_FORMA_PAGO.$this->separador);		//Dato Especial forma de pago adicional
			fwrite($handle, $NRO_ORDEN_COMPRA.$this->separador);	//Numero de Orden Pago
			fwrite($handle, $NRO_NOTA_VENTA.$this->separador);		//Numero de Nota Venta
			fwrite($handle, $TOTAL_EN_PALABRAS.$this->separador);	//Total en palabras: Posterior al campo Notas
			fwrite($handle, "\r\n"); //salto de linea
			
			//datos de dw_item_nota_DEBITO linea 5 a 34
			for ($i = 0; $i < 30; $i++){
				if($i < $count){
					fwrite($handle, ' '); //0 space 2
					$ORDEN		= $result_dte[$i]['ORDEN'];
					$MODELO		= $result_dte[$i]['COD_PRODUCTO'];
					$NOM_PRODUCTO = $result_dte[$i]['NOM_PRODUCTO'];
					$CANTIDAD	= number_format($result_dte[$i]['CANTIDAD'], 1, ',', '');
					$P_UNITARIO	= number_format($result_dte[$i]['PRECIO'], 1, ',', '');
					$TOTAL		= number_format($result_dte[$i]['TOTAL_NC'], 1, ',', '');
					$DESCRIPCION= $MODELO; // se repite el modelo
					$CANTIDAD_DETALLE = $CANTIDAD; // se repite el $CANTIDAD
					$ORDEN = $ORDEN / 10; //ELIMINA EL CERO
					
					//Asignando espacios en blanco dw_item_nota_DEBITO
					$ORDEN		= substr($ORDEN.$space, 0, 2);
					$MODELO		= substr($MODELO.$space, 0, 35);
					$NOM_PRODUCTO= substr($NOM_PRODUCTO.$space, 0, 80);
					$CANTIDAD	= substr($CANTIDAD.$space, 0, 18);
					$P_UNITARIO	= substr($P_UNITARIO.$space, 0, 18);
					$TOTAL		= substr($TOTAL.$space, 0, 18);
					$DESCRIPCION= substr($DESCRIPCION.$space, 0, 59);
					$CANTIDAD_DETALLE = substr($CANTIDAD_DETALLE.$space, 0, 18);

					//DATOS DE ITEM_NOTA_DEBITO A EXPORTAR
					fwrite($handle, $ORDEN.$this->separador);		//31 Número de Línea
					fwrite($handle, $MODELO.$this->separador);		//32 Código item
					fwrite($handle, $NOM_PRODUCTO.$this->separador);//33 Nombre del Item
					fwrite($handle, $CANTIDAD.$this->separador);	//34 Cantidad
					fwrite($handle, $P_UNITARIO.$this->separador);	//35 Precio Unitario
					fwrite($handle, $TOTAL.$this->separador);		//36 Valor por linea de detalle
					fwrite($handle, $DESCRIPCION.$this->separador);	//37 personalizados Zona Detalles(Modelo ítem)
					fwrite($handle, $CANTIDAD_DETALLE.$this->separador);	//personalizados Zona Detalles SE REPITE $CANTIDAD
				}
				fwrite($handle, "\r\n");
			}
			
			//Linea 35 a 44	Referencia
			//$count_NV = 1;
			for($i = 0; $i < 1; $i++){
				fwrite($handle, ' '); //0 space 2			
					//Asignando espacios en blanco Referencia
					$TDR	= substr($TDR.$space, 0, 3);
					$FR		= substr($FR.$space, 0, 18);
					$FECHA_R= substr($FECHA_R.$space, 0, 10);
					$CR		= substr($CR.$space, 0, 1);
					$RER	= substr($RER.$space, 0, 100);					
					
					fwrite($handle, $TDR.$this->separador);			//38 Tipo documento referencia
					fwrite($handle, $FR.$this->separador);			//39 Folio Referencia
					fwrite($handle, $FECHA_R.$this->separador);		//40 Fecha de Referencia
					fwrite($handle, $CR.$this->separador);			//41 Código de Referencia
					fwrite($handle, $RER.$this->separador);			//42 Razón explícita de la referencia
				fwrite($handle, "\r\n");
			}
			/*fclose($handle);
			header("Content-Type: application/x-msexcel; name=\"$name_archivo\"");
			header("Content-Disposition: inline; filename=\"$name_archivo\"");
			$fh=fopen($fname, "rb");
			fpassthru($fh);*/

			$upload = $this->Envia_DTE($name_archivo, $fname);
			$NRO_NOTA_DEBITO = trim($NRO_NOTA_DEBITO); 
			if (!$upload) {
				$this->_load_record();
				$this->alert('No se pudo enviar Nota DEBITO Electronica Nº '.$NRO_NOTA_DEBITO.', Por favor contacte a IntegraSystem.');								
			}else{
				$this->_load_record();
				$this->alert('Gestión Realizada con exíto. Nota DEBITO Electronica Nº '.$NRO_NOTA_DEBITO.'.');								
			}
			unlink($fname);
		}else{
			$db->ROLLBACK_TRANSACTION();
			return false;
		}
		$this->unlock_record();
   	}
	function procesa_event() {		
		if(isset($_POST['b_print_dte_x']))
			$this->envia_NC_Electronica();
		else
			parent::procesa_event();
	}
}
class print_nota_debito extends reporte {	
	function print_nota_debito($sql, $xml, $labels=array(), $titulo, $con_logo, $vuelve_a_presentacion=false) {
		parent::reporte($sql, $xml, $labels, $titulo, $con_logo, $vuelve_a_presentacion);			
	}
	
	function modifica_pdf(&$pdf) {
		$pdf->AutoPageBreak=false;
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$result = $db->build_results($this->sql);
		$count = count($result);

		$fecha = $result[0]['FECHA_NOTA_DEBITO'];		
		// CABECERA		
		$cod_nota_DEBITO = $result[0]['COD_NOTA_DEBITO'];		
		$nro_nota_DEBITO = $result[0]['NRO_NOTA_DEBITO'];
		$nom_empresa = $result[0]['NOM_EMPRESA'];
		$rut = number_format($result[0]['RUT'], 0, ',', '.')."-".$result[0]['DIG_VERIF'];
		$direccion = $result[0]['DIRECCION'];
		$comuna = $result[0]['NOM_COMUNA'];		
		$ciudad = $result[0]['NOM_CIUDAD'];		
		$giro = $result[0]['GIRO'];
		$fono = $result[0]['TELEFONO'];
		$total_en_palabras = $result[0]['TOTAL_EN_PALABRAS'];
		$subtotal = number_format($result[0]['SUBTOTAL'], 0, ',', '.');
		$porc_dscto1 = number_format($result[0]['PORC_DSCTO1'], 1, ',', '.');
		$monto_dscto1 = number_format($result[0]['MONTO_DSCTO1'], 0, ',', '.');
		$porc_dscto2 = number_format($result[0]['PORC_DSCTO2'], 1, ',', '.');
		$monto_dscto2 = number_format($result[0]['MONTO_DSCTO2'], 0, ',', '.');
		$total_dscto = number_format($result[0]['TOTAL_DSCTO'], 0, ',', '.');
		$neto = number_format($result[0]['TOTAL_NETO'], 0, ',', '.');
		$porc_iva = number_format($result[0]['PORC_IVA'], 0, ',', '.');
		$monto_iva = number_format($result[0]['MONTO_IVA'], 0, ',', '.');
		$total_con_iva = number_format($result[0]['TOTAL_CON_IVA'], 0, ',', '.');
		$REFERENCIA	= 'REF: '.substr ($result[0]['REFERENCIA'], 0, 68);
		$NRO_FACTURA	= $result[0]['NRO_FACTURA'];
		$INI_USUARIO	= $result[0]['INI_USUARIO'];

		// DIBUJANDO LA CABECERA	
		$pdf->SetFont('Arial','',11);
		$pdf->Text(83, 143,$fecha);
		$pdf->SetFont('Arial','',8);
		$pdf->Text(433, 117, $nro_nota_DEBITO);
		$pdf->Text(515, 117, $INI_USUARIO);
		$pdf->SetFont('Arial','',11);
		$pdf->SetXY(83,155);
		$pdf->SetFont('Arial','B',11);
		$pdf->MultiCell(250,10,"$nom_empresa");
		$pdf->Text(398, 164, $rut);
		$pdf->SetFont('Arial','',11);
		//$pdf->Text(83, 213, $direccion);
		$pdf->SetXY(80,205);
		$pdf->MultiCell(250,9,"$direccion");	
		
		$pdf->Text(418, 213, $comuna);
		$pdf->Text(68, 236, $ciudad);

		$pdf->SetXY(217, 228);
		$pdf->MultiCell(150,9,"$giro");	

		$pdf->Text(408, 236, $fono);		
		$pdf->SetFont('Arial','B',10);
		$pdf->Text(150, 310, "$REFERENCIA");
		$pdf->SetFont('Arial','',10);
		
		//DIBUJANDO LOS ITEMS DE LA NOTA_DEBITO
		for($i=0; $i<$count; $i++){
			$item = $result[$i]['ITEM'];
			$cantidad = $result[$i]['CANTIDAD'];
			$modelo = $result[$i]['COD_PRODUCTO'];
			$detalle = substr ($result[$i]['NOM_PRODUCTO'], 0, 45);
			$p_unitario = number_format($result[$i]['PRECIO'], 0, ',', '.');
			$total = number_format($result[$i]['TOTAL_NC'], 0, ',', '.');
			// por cada pasada le asigna una nueva posicion	
			$pdf->Text(43, 334+(14*$i), $item);			
			$pdf->Text(73, 334+(14*$i), $cantidad);
			$pdf->Text(103, 334+(14*$i), $modelo);
			
			//$pdf->SetXY(158,326+(14*$i));
			$pdf->SetXY(158,331+(14*$i));
			$pdf->Cell(4,1,"$detalle");

			$pdf->SetXY(400,329+(14*$i));
			$pdf->MultiCell(80,5, $p_unitario,0, 'R');		
			$pdf->SetXY(467,329+(14*$i));
			$pdf->MultiCell(80,5, $total,0, 'R');
}					
		
		// DIBUJANDO TOTALES
		$pdf->SetFont('Arial','',12);
		$pdf->Text(98, 622, 'Son: '.$total_en_palabras.' pesos.');
		//$pdf->Text(98, 630, 'Son: '.$total_en_palabras.' pesos.');

		if($total_dscto <> 0){//tiene dscto
			if(($monto_dscto1 <> 0 && $monto_dscto2 == 0) || ($monto_dscto2 <> 0 && $monto_dscto1 == 0)){//solo tiene un DSCTO 1
				$pdf->SetXY(412,649);
				$pdf->SetFont('Arial','',9);
				$pdf->MultiCell(80,4, 'SUBTOTAL  $ ',0, 'R');
			
				$pdf->SetXY(444,649);
				$pdf->SetFont('Arial','B',11);
				$pdf->MultiCell(105,4,$subtotal,0, 'R');

				if($monto_dscto1 <> 0){
					$pdf->SetXY(412,664);
					$pdf->SetFont('Arial','',9);
					$pdf->MultiCell(80,4,$porc_dscto1.' % DSCTO $ ',0, 'R');

					$pdf->SetXY(444,664);
					$pdf->SetFont('Arial','B',11);
					$pdf->MultiCell(105,4,$monto_dscto1, 0, 'R');
				}
				else{
					$pdf->SetXY(412,664);
					$pdf->SetFont('Arial','',9);
					$pdf->MultiCell(80,4,$porc_dscto2.' % DSCTO $ ',0, 'R');

					$pdf->SetXY(444,664);
					$pdf->SetFont('Arial','B',11);
					$pdf->MultiCell(105,4,$monto_dscto2, 0, 'R');				
				}				
			}else if($monto_dscto1 <> 0 && $monto_dscto2 <> 0){//tiene ambos DSCTO

				$pdf->SetXY(412,634);
				$pdf->SetFont('Arial','',9);
				$pdf->MultiCell(80,4, 'SUBTOTAL  $ ',0, 'R');
			
				$pdf->SetXY(444,634);
				$pdf->SetFont('Arial','B',11);
				$pdf->MultiCell(105,4,$subtotal,0, 'R');

				$pdf->SetXY(402,650);
				$pdf->SetFont('Arial','',9);
				$pdf->MultiCell(90,5,$porc_dscto1.' % DSCTO 1 $ ',0, 'R');

				$pdf->SetXY(444,649);
				$pdf->SetFont('Arial','B',11);
				$pdf->MultiCell(105,4,$monto_dscto1, 0, 'R');	
				
				$pdf->SetXY(402,665);
				$pdf->SetFont('Arial','',9);
				$pdf->MultiCell(90,5,$porc_dscto2.' % DSCTO 2 $ ',0, 'R');

				$pdf->SetXY(444,664);
				$pdf->SetFont('Arial','B',11);
				$pdf->MultiCell(105,4,$monto_dscto2, 0, 'R');	
			}
		}

		$pdf->SetXY(412,679);
		$pdf->SetFont('Arial','',9);
		$pdf->MultiCell(80,4, 'TOTAL NETO  $ ',0, 'R');
		$pdf->SetXY(444,679);
		$pdf->SetFont('Arial','B',11);
		$pdf->MultiCell(105,4,$neto,0, 'R');
		$pdf->SetXY(412,694);
		$pdf->SetFont('Arial','',9);
		$pdf->MultiCell(80,4, $porc_iva.' % IVA  $ ',0, 'R');
		$pdf->SetXY(444,694);
		$pdf->SetFont('Arial','B',11);
		$pdf->MultiCell(105,4,$monto_iva,0, 'R');
		$pdf->Rect( 430, 705, 120, 2, 'f');
		$pdf->SetXY(412,714);
		$pdf->SetFont('Arial','',9);
		$pdf->MultiCell(80,4,'TOTAL  $ ',0, 'R');
		$pdf->SetXY(444,714);
		$pdf->SetFont('Arial','B',11);
		$pdf->MultiCell(105,4,$total_con_iva,0, 'R');	


		//DIBUJANDO PERSONA QUE RETIRA PRODUCTOS 			
		$pdf->SetFont('Arial','',13);
		$pdf->Text(43, 685, $NRO_FACTURA);
		
	}
}
// Se separa por K_CLIENTE
$file_name = dirname(__FILE__)."/".K_CLIENTE."/class_wi_nota_debito.php";
if (file_exists($file_name)) 
	require_once($file_name);
else {
	class wi_nota_debito extends wi_nota_debito_base {
		function wi_nota_debito($cod_item_menu) {
			parent::wi_nota_debito_base($cod_item_menu);
		}
	}
	
}
?>