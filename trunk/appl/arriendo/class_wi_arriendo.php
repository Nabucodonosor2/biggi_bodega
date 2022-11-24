<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../common_appl/class_w_cot_nv.php");
require_once(dirname(__FILE__)."/../empresa/class_dw_help_empresa.php");
require_once(dirname(__FILE__)."/class_print_arriendo.php");

class dw_item_arriendo extends dw_item {
	function dw_item_arriendo() {
		$sql = "select COD_ITEM_ARRIENDO
						,COD_ARRIENDO
						,ORDEN
						,ITEM
						,COD_PRODUCTO
						,NOM_PRODUCTO
						,CANTIDAD
						,PRECIO
						,PRECIO_VENTA
						,COD_TIPO_TE
						,MOTIVO_TE
				from ITEM_ARRIENDO
				where COD_ARRIENDO = {KEY1}
				  and COD_ITEM_MOD_ARRIENDO is null
				order by ORDEN";
		parent::dw_item($sql, 'ITEM_ARRIENDO', true, true, 'COD_PRODUCTO');

		$this->add_control(new edit_text('COD_ITEM_ARRIENDO',10, 10, 'hidden'));
		$this->add_control(new edit_num('ORDEN',4, 10));
		$this->add_control(new edit_text('ITEM',4 , 5));
		$this->add_control(new edit_cantidad('CANTIDAD',12,10));
		$this->add_control(new computed('PRECIO_VENTA'), 0);
		$this->add_control(new computed('PRECIO', 0));
		$this->set_computed('TOTAL', '[CANTIDAD] * [PRECIO]');
		$this->accumulate('TOTAL');
		$this->set_computed('TOTAL_VENTA', '[CANTIDAD] * [PRECIO_VENTA]');
		$this->accumulate('TOTAL_VENTA', "calc_adicional();");
		$this->add_controls_producto_help();		// Debe ir despues de los computed donde esta involucrado precio, porque add_controls_producto_help() afecta el campos PRECIO
		$this->add_control(new edit_text('COD_TIPO_TE',10, 100, 'hidden'));
		$this->add_control(new edit_text('MOTIVO_TE',10, 100, 'hidden'));		
		
		$this->set_first_focus('COD_PRODUCTO');

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
	function update($db, $cod_arriendo)	{
		$sp = 'spu_item_arriendo';

		for ($i = 0; $i < $this->row_count(); $i++){
			$statuts = $this->get_status_row($i);
			if ($statuts == K_ROW_NOT_MODIFIED || $statuts == K_ROW_NEW)
				continue;

			$cod_item_arriendo 		= $this->get_item($i, 'COD_ITEM_ARRIENDO');
			$orden 					= $this->get_item($i, 'ORDEN');
			$item 					= $this->get_item($i, 'ITEM');
			$cod_producto 			= $this->get_item($i, 'COD_PRODUCTO');
			$nom_producto 			= $this->get_item($i, 'NOM_PRODUCTO');
			$cantidad 				= $this->get_item($i, 'CANTIDAD');
			$precio 				= $this->get_item($i, 'PRECIO');
			$precio_venta 			= $this->get_item($i, 'PRECIO_VENTA');
			$cod_tipo_te 			= $this->get_item($i, 'COD_TIPO_TE');
			$motivo_te				= $this->get_item($i, 'MOTIVO_TE');
			
			$cod_item_arriendo = ($cod_item_arriendo=='') ? "null" : $cod_item_arriendo;
			$cod_tipo_te = ($cod_tipo_te=='') ? "null" : $cod_tipo_te;
			$motivo_te = ($motivo_te=='') ? "null" : "'$motivo_te'";
			
			if ($statuts == K_ROW_NEW_MODIFIED)
				$operacion = 'INSERT';
			elseif ($statuts == K_ROW_MODIFIED)
				$operacion = 'UPDATE';

			$param = "'$operacion'
						,$cod_item_arriendo
						,$cod_arriendo
						,$orden
						,'$item'
						,'$cod_producto'
						,'$nom_producto'
						,$cantidad
						,$precio
						,$precio_venta
						,$cod_tipo_te
						,$motivo_te";
			if (!$db->EXECUTE_SP($sp, $param))
				return false;
		}
		for ($i = 0; $i < $this->row_count('delete'); $i++) {
			$statuts = $this->get_status_row($i, 'delete');
			if ($statuts == K_ROW_NEW || $statuts == K_ROW_NEW_MODIFIED)
				continue;
				
			$cod_item_arriendo = $this->get_item($i, 'COD_ITEM_ARRIENDO', 'delete');
			if (!$db->EXECUTE_SP($sp, "'DELETE', $cod_item_arriendo")){
				return false;
			}
		}
		//Ordernar
		if ($this->row_count() > 0) {
			$parametros_sp = "'ITEM_ARRIENDO','ARRIENDO', $cod_arriendo";
			if (!$db->EXECUTE_SP('sp_orden_no_parametricas', $parametros_sp))
				return false;
		}
		return true;
	}
}
class dw_item_mod_arriendo2 extends datawindow {
	// Se renombra para evitar que tenga el mimso nombre la clase que en MOD_ARRIENDOL
	function dw_item_mod_arriendo2() {
		$sql = "select IM.COD_MOD_ARRIENDO 	MA_COD_MOD_ARRIENDO
						,I.ITEM 			MA_ITEM
						,I.COD_PRODUCTO		MA_COD_PRODUCTO
						,I.NOM_PRODUCTO		MA_NOM_PRODUCTO
						,I.CANTIDAD			MA_CANTIDAD
						,I.PRECIO			MA_PRECIO
						,Round(I.PRECIO * I.CANTIDAD, 0) MA_TOTAL
				from ITEM_ARRIENDO I, ITEM_MOD_ARRIENDO IM 
				where I.COD_ARRIENDO = {KEY1}
				  and IM.COD_ITEM_MOD_ARRIENDO = I.COD_ITEM_MOD_ARRIENDO 
				order by IM.COD_MOD_ARRIENDO, I.ORDEN";
		parent::datawindow($sql, 'ITEM_MOD_ARRIENDO');
		
		$this->add_control(new static_link('MA_COD_MOD_ARRIENDO', '../../../../commonlib/trunk/php/link_wi.php?modulo_origen=arriendo&modulo_destino=mod_arriendo&cod_modulo_destino=[MA_COD_MOD_ARRIENDO]&cod_item_menu=2015&current_tab_page=3'));
		$this->add_control(new static_num('MA_PRECIO'));
		$this->add_control(new static_num('MA_TOTAL'));
	}
}
class dw_arriendo extends dw_help_empresa {
	const K_EMITIDA = 1;
	const K_PARAM_NRO_MESES = 46;
	const K_PARAM_PORC_RECUPERACION = 47;
	const K_PARAM_PORC_ARRIENDO = 48;
	const K_PARAM_MIN_PORC_ARRIENDO = 49;
	const K_PARAM_MAX_PORC_ARRIENDO = 50;

	function dw_arriendo() {
		$sql = "select	A.COD_ARRIENDO
						,convert(varchar(20), A.FECHA_ARRIENDO, 103) FECHA_ARRIENDO
						,A.COD_USUARIO
						,U.NOM_USUARIO
						,A.COD_USUARIO_VENDEDOR1
						,A.NRO_ORDEN_COMPRA
						,A.REFERENCIA
						,A.CENTRO_COSTO_CLIENTE
						,A.COD_ESTADO_ARRIENDO
						,A.COD_COTIZACION_ARRIENDO
						,convert(varchar(20), A.FECHA_ENTREGA, 103) FECHA_ENTREGA
						,A.COD_EMPRESA
						,E.ALIAS
						,E.RUT
						,E.DIG_VERIF
						,E.NOM_EMPRESA
						,E.GIRO
						,A.COD_SUCURSAL
						,dbo.f_get_direccion('SUCURSAL', A.COD_SUCURSAL, '[DIRECCION]  [NOM_COMUNA] [NOM_CIUDAD] - TELEFONO: [TELEFONO] - FAX: [FAX]') DIRECCION_SUCURSAL
						,A.COD_PERSONA
						,dbo.f_emp_get_mail_cargo_persona(A.COD_PERSONA,  '[EMAIL] - [NOM_CARGO]') MAIL_CARGO_PERSONA
						,A.NOM_ARRIENDO
						,A.UBICACION_DIRECCION      
						,A.UBICACION_COD_COMUNA     
						,A.UBICACION_COD_CIUDAD     
						,A.EJECUTIVO_CONTACTO       
						,A.EJECUTIVO_TELEFONO       
						,A.EJECUTIVO_MAIL           
						,A.NRO_MESES
						,A.PORC_ARRIENDO
						,dbo.f_get_parametro(".self::K_PARAM_MIN_PORC_ARRIENDO.") MIN_PORC_ARRIENDO
						,dbo.f_get_parametro(".self::K_PARAM_MAX_PORC_ARRIENDO.") MAX_PORC_ARRIENDO
						,A.PORC_ADICIONAL_RECUPERACION
						,A.MONTO_ADICIONAL_RECUPERACION
						,A.SUBTOTAL SUM_TOTAL
						,A.TOTAL_NETO
						,A.PORC_IVA
						,A.MONTO_IVA
						,A.TOTAL_CON_IVA
						,A.COD_BODEGA
						,B.NOM_BODEGA
						,A.OBS
						,dbo.f_arr_total_actual(A.COD_ARRIENDO) TOTAL_ACTUAL
				from 	ARRIENDO A left outer join BODEGA B ON B.COD_BODEGA = A.COD_BODEGA, USUARIO U, EMPRESA E
				where	A.COD_ARRIENDO = {KEY1} 
				  and	U.COD_USUARIO = A.COD_USUARIO
				  and	E.COD_EMPRESA = A.COD_EMPRESA";
		parent::dw_help_empresa($sql);
		
		//DATOS GENERALES
		$sql_usuario_vendedor =  "select 	COD_USUARIO
											,NOM_USUARIO
											,PORC_PARTICIPACION
									from USUARIO
									where ES_RENTAL = 'S'
									order by NOM_USUARIO asc";
		$this->add_control(new drop_down_dw('COD_USUARIO_VENDEDOR1',$sql_usuario_vendedor,110));
		$this->add_control(new edit_text_upper('NRO_ORDEN_COMPRA', 30, 100));
		$this->add_control(new edit_text_upper('REFERENCIA', 100, 100));
		$this->add_control(new edit_text_upper('CENTRO_COSTO_CLIENTE', 30, 100));
		$sql = "select COD_ESTADO_ARRIENDO
						,NOM_ESTADO_ARRIENDO
				from ESTADO_ARRIENDO
				order by COD_ESTADO_ARRIENDO";
		$this->add_control(new drop_down_dw('COD_ESTADO_ARRIENDO',$sql));
		$this->add_control($control = new edit_date('FECHA_ENTREGA'));
		$control->set_onChange("valida_fecha_entrega(this);");
		
		//OBSERVACIONES 
		
		$this->add_control(new edit_text_multiline('OBS',54,4));
		

		// DIRECCION
		$this->add_control(new drop_down_sucursal('COD_SUCURSAL'));
		$this->add_control(new static_text('DIRECCION_SUCURSAL'));
		$this->add_control(new drop_down_persona('COD_PERSONA'));
		
		// DOCUMENTOS *****************
		
		// DATOS CONTRATO
		$this->add_control(new edit_text_upper('NOM_ARRIENDO', 80, 100));
		$this->add_control(new edit_text_upper('UBICACION_DIRECCION', 80, 100));
		$sql = "select COD_CIUDAD, 
						NOM_CIUDAD
				from CIUDAD
				where COD_PAIS = 56	/*CHILE*/
				order by NOM_CIUDAD";
		$this->add_control($control = new drop_down_dw('UBICACION_COD_CIUDAD', $sql, 90));	
		$control->set_drop_down_dependiente('arriendo', 'UBICACION_COD_COMUNA');
		$sql = "select COD_COMUNA, 
						NOM_COMUNA
				from COMUNA
				where COD_CIUDAD = {KEY1}
				order by NOM_COMUNA";
		$this->add_control(new drop_down_dw('UBICACION_COD_COMUNA', $sql, 90));			
		$this->add_control(new edit_text_upper('EJECUTIVO_CONTACTO', 80, 100));
		$this->add_control(new edit_text_upper('EJECUTIVO_TELEFONO', 20, 100));
		$this->add_control(new edit_mail('EJECUTIVO_MAIL', 30, 100));


		// Totales
		$this->add_control(new edit_num('NRO_MESES', 3,3));
		$this->add_control(new edit_porcentaje('PORC_ADICIONAL_RECUPERACION'));
		$this->add_control($control = new edit_porcentaje('PORC_ARRIENDO'));
		$control->set_onChange("valida_porc_arriendo(this);");
		$this->add_control(new edit_text('MIN_PORC_ARRIENDO',10, 10, 'hidden'));
		$this->add_control(new edit_text('MAX_PORC_ARRIENDO',10, 10, 'hidden'));
		
		// total contrato actual tab de stock
		$this->add_control(new static_num('TOTAL_ACTUAL'));		
		
		$this->set_computed('MONTO_ADICIONAL_RECUPERACION', '[SUM_TOTAL] * [PORC_ADICIONAL_RECUPERACION] / 100');
		$this->set_computed('TOTAL_NETO', '[SUM_TOTAL] + [MONTO_ADICIONAL_RECUPERACION]');
		$this->add_control(new drop_down_iva());
		// Elimina la opción de IVA= 0
		unset($this->controls['PORC_IVA']->aValues[1]);
		unset($this->controls['PORC_IVA']->aLabels[1]);
		
		$this->set_computed('MONTO_IVA', '[TOTAL_NETO] * [PORC_IVA] / 100');
		$this->set_computed('TOTAL_CON_IVA', '[TOTAL_NETO] + [MONTO_IVA]');
		
		$this->set_mandatory('NOM_ARRIENDO', 'Nombre contrato');
		$this->set_mandatory('COD_USUARIO_VENDEDOR1', 'Vendedor');
		$this->set_mandatory('REFERENCIA', 'Referencia');
		$this->set_mandatory('FECHA_ENTREGA', 'Fecha de entrega');
		$this->set_mandatory('COD_EMPRESA', 'Cliente');
		$this->set_mandatory('COD_SUCURSAL', 'Dirección');
		$this->set_mandatory('COD_PERSONA', 'Atención a');
		$this->set_mandatory('REFERENCIA', 'Referencia');
		$this->set_mandatory('NRO_MESES', 'Número de meses');
		$this->set_mandatory('PORC_ARRIENDO', 'Porcentaje arriendo');
		$this->set_mandatory('UBICACION_DIRECCION', 'Dirección arriendo');
		$this->set_mandatory('UBICACION_COD_CIUDAD', 'Ciudad');
		$this->set_mandatory('UBICACION_COD_COMUNA', 'Comuna');
	}
	function new_arriendo() {
		 $this->insert_row();

		$this->set_item(0, 'FECHA_ARRIENDO', $this->current_date());
		$this->set_item(0, 'COD_USUARIO', $this->cod_usuario);
		$this->set_item(0, 'NOM_USUARIO', $this->nom_usuario);
		$this->set_item(0, 'COD_USUARIO_VENDEDOR1',$this->cod_usuario);
		
		$this->set_item(0, 'COD_ESTADO_ARRIENDO', self::K_EMITIDA);
		$this->set_entrable('COD_ESTADO_ARRIENDO', false);
		
		$this->set_item(0, 'NRO_MESES', $this->get_parametro(self::K_PARAM_NRO_MESES));
		$this->set_item(0, 'PORC_ADICIONAL_RECUPERACION', $this->get_parametro(self::K_PARAM_PORC_RECUPERACION));
		$this->set_item(0, 'PORC_ARRIENDO', $this->get_parametro(self::K_PARAM_PORC_ARRIENDO));
		$this->set_item(0, 'MIN_PORC_ARRIENDO', $this->get_parametro(self::K_PARAM_MIN_PORC_ARRIENDO));
		$this->set_item(0, 'MAX_PORC_ARRIENDO', $this->get_parametro(self::K_PARAM_MAX_PORC_ARRIENDO));
	}
}
class dw_arriendo_stock extends datawindow {
	function dw_arriendo_stock() {
		$sql = "select distinct I.COD_PRODUCTO ST_COD_PRODUCTO
						,I.NOM_PRODUCTO ST_NOM_PRODUCTO
						,dbo.f_bodega_stock(I.COD_PRODUCTO, A.COD_BODEGA, getdate()) ST_CANTIDAD
						,I.PRECIO_VENTA ST_PRECIO_VENTA
						,I.PRECIO ST_PRECIO
						,dbo.f_bodega_stock(I.COD_PRODUCTO, A.COD_BODEGA, getdate()) * I.PRECIO ST_TOTAL
				from ARRIENDO A, ITEM_ARRIENDO I
				where A.COD_ARRIENDO = {KEY1}
  				  and I.COD_ARRIENDO = A.COD_ARRIENDO
  				  and dbo.f_bodega_stock(I.COD_PRODUCTO, A.COD_BODEGA, getdate()) > 0";
		parent::datawindow($sql, 'ARRIENDO_STOCK');
		
		$this->add_control(new static_num('ST_CANTIDAD', 1));
		$this->add_control(new static_num('ST_PRECIO_VENTA'));
		$this->add_control(new static_num('ST_PRECIO'));
		$this->add_control(new static_num('ST_TOTAL'));
	}
}
class wi_arriendo extends w_cot_nv {
	const K_ARRIENDO_EMITIDO = 1;
	const K_ARRIENDO_APROBADO = 2;
	const K_ARRIENDO_ANULADO = 3;
	
	function wi_arriendo($cod_item_menu) {
		parent::w_cot_nv('arriendo', $cod_item_menu);

		$this->dws['dw_arriendo'] = new dw_arriendo();
		$this->dws['dw_item_arriendo'] = new dw_item_arriendo();
		$this->dws['dw_item_mod_arriendo'] = new dw_item_mod_arriendo2();
		$this->dws['dw_arriendo_stock'] = new dw_arriendo_stock();

		$this->set_first_focus('REFERENCIA');
	}
	function new_record() {
		$this->dws['dw_arriendo']->new_arriendo();
	}
	function load_record() {
		$cod_arriendo = $this->get_item_wo($this->current_record, 'COD_ARRIENDO');
		$this->dws['dw_arriendo']->retrieve($cod_arriendo);
		$cod_empresa = $this->dws['dw_arriendo']->get_item(0, 'COD_EMPRESA');
		$this->dws['dw_arriendo']->controls['COD_SUCURSAL']->retrieve($cod_empresa);
		$this->dws['dw_arriendo']->controls['COD_PERSONA']->retrieve($cod_empresa);		
		$cod_ciudad = $this->dws['dw_arriendo']->get_item(0, 'UBICACION_COD_CIUDAD');
		if ($cod_ciudad != '')
			$this->dws['dw_arriendo']->controls['UBICACION_COD_COMUNA']->retrieve($cod_ciudad);		
		$this->dws['dw_item_arriendo']->retrieve($cod_arriendo);
		$this->dws['dw_item_mod_arriendo']->retrieve($cod_arriendo);
		$this->dws['dw_arriendo_stock']->retrieve($cod_arriendo);
		
		$this->b_print_visible = true;
		
		$cod_estado_arriendo = $this->dws['dw_arriendo']->get_item(0, 'COD_ESTADO_ARRIENDO');
		if ($cod_estado_arriendo==self::K_ARRIENDO_APROBADO) {
			$this->b_delete_visible = false;
			$this->b_save_visible = false;
			$this->b_no_save_visible = false;
			$this->b_modify_visible = false;
		}
		else if ($cod_estado_arriendo==self::K_ARRIENDO_ANULADO) {
			$this->b_delete_visible = false;
			$this->b_save_visible = false;
			$this->b_no_save_visible = false;
			$this->b_modify_visible = false;
			$this->b_print_visible = false;
		}
	}
	function get_key() {
		return $this->dws['dw_arriendo']->get_item(0, 'COD_ARRIENDO');
	}
	function print_record() {
		$cod_arriendo = $this->get_key();
		$adicional = $this->dws['dw_arriendo']->get_item(0, 'MONTO_ADICIONAL_RECUPERACION');
		
		if($adicional > 0){
			$labels = array();
			$labels['strCOD_ARRIENDO'] = $cod_arriendo;			
			$file_name = $this->find_file('arriendo', 'arriendo_adicional.xml');					
			$rpt = new print_arriendo($cod_arriendo, $file_name, $labels, "Arriendo_adicional".$cod_arriendo, 1);
			$this->_load_record();
			return true;
		}
		else{
			// reporte
			$labels = array();
			$labels['strCOD_ARRIENDO'] = $cod_arriendo;			
			$file_name = $this->find_file('arriendo', 'arriendo.xml');					
			$rpt = new print_arriendo($cod_arriendo, $file_name, $labels, "Arriendo".$cod_arriendo, 1);
			$this->_load_record();
			return true;
		}
			
	}
	
	function save_record($db) {
		$cod_arriendo = $this->get_key();
		$nom_arriendo = $this->dws['dw_arriendo']->get_item(0, 'NOM_ARRIENDO');
		$cod_usuario_vendedor1 = $this->dws['dw_arriendo']->get_item(0, 'COD_USUARIO_VENDEDOR1');
		$nro_orden_compra = $this->dws['dw_arriendo']->get_item(0, 'NRO_ORDEN_COMPRA');
		$cod_empresa = $this->dws['dw_arriendo']->get_item(0, 'COD_EMPRESA');
		$cod_sucursal = $this->dws['dw_arriendo']->get_item(0, 'COD_SUCURSAL');
		$cod_persona = $this->dws['dw_arriendo']->get_item(0, 'COD_PERSONA');		
		$ejecutivo_contacto = $this->dws['dw_arriendo']->get_item(0, 'EJECUTIVO_CONTACTO');
		$ejecutivo_telefono = $this->dws['dw_arriendo']->get_item(0, 'EJECUTIVO_TELEFONO');
		$ejecutivo_mail = $this->dws['dw_arriendo']->get_item(0, 'EJECUTIVO_MAIL');
		$cod_cotizacion_arriendo = $this->dws['dw_arriendo']->get_item(0, 'COD_COTIZACION_ARRIENDO');
		$referencia = $this->dws['dw_arriendo']->get_item(0, 'REFERENCIA');
		$centro_costo_cliente = $this->dws['dw_arriendo']->get_item(0, 'CENTRO_COSTO_CLIENTE');		
		$porc_adicional_recuperacion = $this->dws['dw_arriendo']->get_item(0, 'PORC_ADICIONAL_RECUPERACION');
		$monto_adicional_recuperacion = $this->dws['dw_arriendo']->get_item(0, 'MONTO_ADICIONAL_RECUPERACION');
		$nro_meses = $this->dws['dw_arriendo']->get_item(0, 'NRO_MESES');
		$porc_arriendo = $this->dws['dw_arriendo']->get_item(0, 'PORC_ARRIENDO');
		$subtotal = $this->dws['dw_arriendo']->get_item(0, 'SUM_TOTAL');
		$total_neto = $this->dws['dw_arriendo']->get_item(0, 'TOTAL_NETO');
		$porc_iva = $this->dws['dw_arriendo']->get_item(0, 'PORC_IVA');
		$monto_iva = $this->dws['dw_arriendo']->get_item(0, 'MONTO_IVA');
		$total_con_iva = $this->dws['dw_arriendo']->get_item(0, 'TOTAL_CON_IVA');		
		$fecha_entrega = $this->dws['dw_arriendo']->get_item(0, 'FECHA_ENTREGA');
		$ubicacion_direccion = $this->dws['dw_arriendo']->get_item(0, 'UBICACION_DIRECCION');
		$ubicacion_cod_comuna = $this->dws['dw_arriendo']->get_item(0, 'UBICACION_COD_COMUNA');
		$ubicacion_cod_ciudad = $this->dws['dw_arriendo']->get_item(0, 'UBICACION_COD_CIUDAD');
		$cod_estado_arriendo = $this->dws['dw_arriendo']->get_item(0, 'COD_ESTADO_ARRIENDO');
		$obs = $this->dws['dw_arriendo']->get_item(0, 'OBS');
		
		
		
    	$cod_arriendo = ($cod_arriendo=='') ? 'null' : $cod_arriendo;
		$cod_usuario_vendedor1 = ($cod_usuario_vendedor1=='') ? 'null' : $cod_usuario_vendedor1;
		$nro_orden_compra = ($nro_orden_compra=='') ? 'null' : "'$nro_orden_compra'";
		$ejecutivo_contacto = ($ejecutivo_contacto=='') ? 'null' : "'$ejecutivo_contacto'";
		$ejecutivo_telefono = ($ejecutivo_telefono=='') ? 'null' : "'$ejecutivo_telefono'";
		$ejecutivo_mail = ($ejecutivo_mail=='') ? 'null' : "'$ejecutivo_mail'";
		$cod_cotizacion_arriendo = ($cod_cotizacion_arriendo=='') ? 'null' : $cod_cotizacion_arriendo;
		$centro_costo_cliente = ($centro_costo_cliente=='') ? 'null' : "'$centro_costo_cliente'";		
		$porc_adicional_recuperacion = ($porc_adicional_recuperacion=='') ? 0 : $porc_adicional_recuperacion;
		$monto_adicional_recuperacion = ($monto_adicional_recuperacion=='') ? 0 : $monto_adicional_recuperacion;
		$nro_meses = ($nro_meses=='') ? 0 : $nro_meses;
		$porc_arriendo = ($porc_arriendo=='') ? 0 : $porc_arriendo;
		$subtotal = ($subtotal=='') ? 0 : $subtotal;
		$total_neto = ($total_neto=='') ? 0 : $total_neto;
		$porc_iva = ($porc_iva=='') ? 0 : $porc_iva;
		$monto_iva = ($monto_iva=='') ? 0 : $monto_iva;
		$total_con_iva = ($total_con_iva=='') ? 0 : $total_con_iva;
		$fecha_entrega = $this->str2date($fecha_entrega);
		$obs = ($obs=='') ? 'null' : "'$obs'";
		
		$sp = 'spu_arriendo';
	    if ($this->is_new_record()) {
	    	$operacion = 'INSERT';
	    	$cod_estado_arriendo_old = '';	// no tenia estado
	    }
	    else {
	    	$operacion = 'UPDATE';
			// obtiene el valor anterior del estado (antes de grabar)
			$sql = "select COD_ESTADO_ARRIENDO
					from ARRIENDO
					where COD_ARRIENDO = $cod_arriendo"; 
			$result = $db->build_results($sql);
	    	$cod_estado_arriendo_old = $result[0]['COD_ESTADO_ARRIENDO'];
	    }
	    
		$param	= "'$operacion'
					,$cod_arriendo
					,'$nom_arriendo'
					,$this->cod_usuario
					,$cod_usuario_vendedor1
					,$nro_orden_compra
					,$cod_empresa
					,$cod_sucursal
					,$cod_persona
					,$ejecutivo_contacto
					,$ejecutivo_telefono
					,$ejecutivo_mail
					,$cod_cotizacion_arriendo
					,'$referencia'
					,$centro_costo_cliente
					,$porc_adicional_recuperacion
					,$monto_adicional_recuperacion
					,$nro_meses
					,$porc_arriendo
					,$subtotal
					,$total_neto
					,$porc_iva
					,$monto_iva
					,$total_con_iva
					,$fecha_entrega
					,'$ubicacion_direccion'
					,$ubicacion_cod_comuna
					,$ubicacion_cod_ciudad
					,$cod_estado_arriendo
					,$obs";

		if ($db->EXECUTE_SP($sp, $param)){
			if ($this->is_new_record()) {
				$cod_arriendo = $db->GET_IDENTITY();
				$this->dws['dw_arriendo']->set_item(0, 'COD_ARRIENDO', $cod_arriendo);
			}

			if (!$this->dws['dw_item_arriendo']->update($db, $cod_arriendo))
				return false;
				
			if ($cod_estado_arriendo_old == self::K_ARRIENDO_EMITIDO && $cod_estado_arriendo == self::K_ARRIENDO_APROBADO) // se esta aprobando un arriendo
				if (!$db->EXECUTE_SP($sp, "'APROBAR', $cod_arriendo"))
					return false;
				
			return true;
		}
		return false;
	}	
}
?>