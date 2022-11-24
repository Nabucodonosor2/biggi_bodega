<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../common_appl/class_w_cot_nv.php");
require_once(dirname(__FILE__)."/../empresa/class_dw_help_empresa.php");


class dw_item_nota_venta extends dw_item {
	function dw_item_nota_venta () {		
	
		//todos los campos que se agreguen en el select se deben agregar en función "creada_desde"
		$sql = "select COD_ITEM_NOTA_VENTA,
					COD_NOTA_VENTA,
					ORDEN,
					ITEM,
					COD_PRODUCTO,
					COD_PRODUCTO COD_PRODUCTO_OLD,
					COD_PRODUCTO COD_PRODUCTO_H,
					NOM_PRODUCTO,
					CANTIDAD,
					PRECIO,
					'' MOTIVO,
					dbo.f_nv_get_ct_con_preorden(COD_ITEM_NOTA_VENTA) CANTIDAD_PRECOMPRA,			
					dbo.f_nv_get_ct_con_orden(COD_ITEM_NOTA_VENTA)  CANTIDAD_COMPRA		
				from ITEM_NOTA_VENTA
				where COD_NOTA_VENTA = {KEY1}
				order by ORDEN asc";
					
					
		parent::dw_item($sql, 'ITEM_NOTA_VENTA', true, true, 'COD_PRODUCTO');	
		
		$this->add_control(new edit_num('ORDEN',4, 5));
		$this->add_control(new edit_text_upper('ITEM',4, 5));
		$this->add_control(new edit_cantidad('CANTIDAD',5));
		$this->add_control(new edit_text_hidden('COD_ITEM_NOTA_VENTA'));
		$this->add_control(new edit_text_hidden('COD_PRODUCTO_H'));
		$this->add_control(new edit_text('MOTIVO',10, 100, 'hidden'));
		
		$this->add_control(new computed('PRECIO', 0));		
		$this->set_computed('TOTAL', '[CANTIDAD] * [PRECIO]');
		$this->accumulate('TOTAL', "calc_dscto();");
		$this->add_controls_producto_help();		// Debe ir despues de los computed donde esta involucrado precio, porque add_controls_producto_help() afecta el campos PRECIO
		
		// Agrega script adicional a ITEM para traspasar el cambi a PRE_ORDEN_COMPRA
		$this->controls['ITEM']->set_onChange("change_item_nota_venta(this, 'ITEM');");
		
		
		// Agrega script adicional a COD_PRODUCTO para traspasar el cambi a PRE_ORDEN_COMPRA
		$this->controls['COD_PRODUCTO']->set_onChange("change_item_nota_venta(this, 'COD_PRODUCTO');");
		
		// Agrega script adicional a NOM_PRODUCTO para traspasar el cambi a PRE_ORDEN_COMPRA
		$this->controls['NOM_PRODUCTO']->set_onChange("change_item_nota_venta(this, 'NOM_PRODUCTO');");
		
		// Agrega script adicional a CANTIDAD para traspasar el cambi a PRE_ORDEN_COMPRA
		$java_script = $this->controls['CANTIDAD']->get_onChange();
		$java_script .= " change_item_nota_venta(this, 'CANTIDAD');";
		$this->controls['CANTIDAD']->set_onChange($java_script);
		
		$this->add_control(new edit_text('CANTIDAD_PRECOMPRA', 20, 20, 'hidden'));
		$this->add_control(new edit_text('CANTIDAD_COMPRA', 20, 20, 'hidden'));
		
		$this->set_first_focus('COD_PRODUCTO');
		
		// asigna los mandatorys
		$this->set_mandatory('ORDEN', 'Orden');
		$this->set_mandatory('COD_PRODUCTO', 'Código del producto');
		$this->set_mandatory('CANTIDAD', 'Cantidad');

	}
}	
class dw_nv_orden_compra extends datawindow {
	function dw_nv_orden_compra() {
	
		$sql = "select convert(varchar, OC.COD_ORDEN_COMPRA)+'|'+convert(varchar, OC.COD_ORDEN_COMPRA) COD_ORDEN_COMPRA,
						dbo.f_format_date(OC.FECHA_ORDEN_COMPRA, 1) FECHA_ORDEN_COMPRA,
						E.NOM_EMPRESA OC_NOM_EMPRESA,
						OC.COD_ESTADO_ORDEN_COMPRA,
						OC.TOTAL_NETO OC_TOTAL_NETO,
						OC.MONTO_IVA OC_MONTO_IVA,
						OC.TOTAL_CON_IVA OC_TOTAL_CON_IVA,
						CASE OC.COD_ESTADO_ORDEN_COMPRA
							WHEN 2 
							THEN 'NULA'							 
						END ANULADA
				from ORDEN_COMPRA OC, EMPRESA E
				where OC.COD_NOTA_VENTA = {KEY1} and
					E.COD_EMPRESA = OC.COD_EMPRESA";
		parent::datawindow($sql, "ORDEN_COMPRA");	
		
		$this->add_control(new static_link('COD_ORDEN_COMPRA', '../../../../commonlib/trunk/php/link_wi.php?modulo_origen=inf_ventas_por_mes&modulo_destino=orden_compra&cod_modulo_destino=[COD_ORDEN_COMPRA]&cod_item_menu=1520&current_tab_page=2'));
		$this->add_control(new static_text('OC_NOM_EMPRESA'));
		$this->add_control(new static_num('OC_TOTAL_NETO', 0));
		$this->add_control(new static_num('OC_MONTO_IVA', 0));
		$this->add_control(new static_num('OC_TOTAL_CON_IVA', 0));
		
		$this->add_control(new edit_text('COD_ESTADO_ORDEN_COMPRA',10,10, 'hidden'));

		$this->accumulate('OC_TOTAL_NETO', '', false);
		$this->accumulate('OC_MONTO_IVA', '', false);
		$this->accumulate('OC_TOTAL_CON_IVA', '', false);
	}
}
class dw_lista_guia_despacho extends datawindow {
	const K_TIPOGD_VENTA = 1;
	const K_ESTADO_SII_IMPRESA = 2;
	const K_ESTADO_SII_ENVIADA = 3;
	const K_ITEM_MENU_GUIA_DESPACHO = '1525';
	
	function dw_lista_guia_despacho() {
		$sql = "select convert(varchar, NRO_GUIA_DESPACHO)+'|'+convert(varchar, COD_GUIA_DESPACHO) NRO_GUIA_DESPACHO
				from   GUIA_DESPACHO
				where  COD_DOC = {KEY1}
	  			and  COD_TIPO_GUIA_DESPACHO = ".self::K_TIPOGD_VENTA."
	  			and  COD_ESTADO_DOC_SII in (".self::K_ESTADO_SII_IMPRESA.", ".self::K_ESTADO_SII_ENVIADA.")";
		parent::datawindow($sql, 'GD_RELACIONADA');

		$this->add_control(new static_link('NRO_GUIA_DESPACHO', '../../../../commonlib/trunk/php/link_wi.php?modulo_origen=inf_ventas_por_mes&modulo_destino=guia_despacho&cod_modulo_destino=[NRO_GUIA_DESPACHO]&cod_item_menu=1525'));
	}
	function fill_record(&$temp, $record) {
		parent::fill_record($temp, $record);
		if ($record < $this->row_count() - 1)
			$temp->setVar($this->label_record.'.GD_SEPARADOR', '-');
	}
}
class dw_lista_factura extends datawindow {
	const K_TIPOGD_VENTA = 1;
	const K_ESTADO_SII_IMPRESA = 2;
	const K_ESTADO_SII_ENVIADA = 3;
	
	function dw_lista_factura() {
		$sql = "select convert(varchar, NRO_FACTURA)+'|'+convert(varchar, COD_FACTURA) NRO_FACTURA
				from   FACTURA
				where  COD_DOC = {KEY1}
	  			and  COD_TIPO_FACTURA = ".self::K_TIPOGD_VENTA."
	  			and  COD_ESTADO_DOC_SII in (".self::K_ESTADO_SII_IMPRESA.", ".self::K_ESTADO_SII_ENVIADA.")";
		parent::datawindow($sql, 'FA_RELACIONADA');

		$this->add_control(new static_link('NRO_FACTURA', '../../../../commonlib/trunk/php/link_wi.php?modulo_origen=inf_ventas_por_mes&modulo_destino=factura&cod_modulo_destino=[NRO_FACTURA]&cod_item_menu=1535'));
	}
	function fill_record(&$temp, $record) {
		parent::fill_record($temp, $record);
		if ($record < $this->row_count() - 1)
			$temp->setVar($this->label_record.'.FA_SEPARADOR', '-');
	}
}
class dw_lista_pago extends datawindow {
	function dw_lista_pago() {
		$sql = "exec spdw_nv_ingreso_pago {KEY1}";
		parent::datawindow($sql, 'PAGO_RELACIONADA');
		$this->add_control(new static_link('COD_INGRESO_PAGO', '../../../../commonlib/trunk/php/link_wi.php?modulo_origen=inf_ventas_por_mes&modulo_destino=ingreso_pago&cod_modulo_destino=[COD_INGRESO_PAGO]&cod_item_menu=2505'));
	}
	function fill_record(&$temp, $record) {
		parent::fill_record($temp, $record);
		if ($record < $this->row_count() - 1)
			$temp->setVar($this->label_record.'.PAGO_SEPARADOR', '-');
	}
}
class dw_nota_venta extends dw_help_empresa {
	function dw_nota_venta($sql) {
		parent::dw_help_empresa($sql);
	}
}
class wi_inf_ventas_por_mes extends w_cot_nv {
	const K_ESTADO_EMITIDA 			= 1;	
	const K_ESTADO_CERRADA			= 2;
	const K_ESTADO_ANULADA			= 3;
	const K_ESTADO_CONFIRMADA		= 4;
	const K_PARAM_GTE_VTA 			= 16;
	const K_CAMBIA_DSCTO_CORPORATIVO = '991010';
	const K_PARAM_PORC_DSCTO_MAX 	=26;
	
	function wi_inf_ventas_por_mes($cod_item_menu) {
		parent::w_cot_nv('inf_ventas_por_mes', $cod_item_menu);
		$this->dw_tabla = 'dw_nota_venta';
		$this->dw_tabla_item = 'dw_item_nota_venta';
		$this->ruta_menu = 'Nota de Venta: ';
		
		$this->constructor_base();
	}
	function constructor_base() {		
		// valida si el usuario puede modificar los desctos corporativos
		if ($this->tiene_privilegio_opcion(self::K_CAMBIA_DSCTO_CORPORATIVO))
			$cambia_dscto_corp = 'S';
		else
			$cambia_dscto_corp = 'N';	

		$sql = "select COD_NOTA_VENTA, 
					convert(varchar, FECHA_NOTA_VENTA, 103) FECHA_NOTA_VENTA,
					NV.COD_USUARIO,
					U.NOM_USUARIO,
					NRO_ORDEN_COMPRA,
					CENTRO_COSTO_CLIENTE,
					COD_COTIZACION,
					NV.COD_ESTADO_NOTA_VENTA, 
					ENV.NOM_ESTADO_NOTA_VENTA, 
					COD_MONEDA, 
					VALOR_TIPO_CAMBIO,  
					COD_USUARIO_VENDEDOR1, 
					PORC_VENDEDOR1, 
					COD_USUARIO_VENDEDOR2, 
					PORC_VENDEDOR2,
					COD_ORIGEN_VENTA,
					NV.COD_CUENTA_CORRIENTE, 
					CC.NOM_CUENTA_CORRIENTE, 
					CC.NRO_CUENTA_CORRIENTE, 
					REFERENCIA, 
					NV.COD_EMPRESA,
					E.ALIAS,
					E.RUT,
					E.DIG_VERIF,
					E.NOM_EMPRESA,
					E.GIRO, 
					COD_SUCURSAL_DESPACHO, 
					dbo.f_get_direccion('SUCURSAL', COD_SUCURSAL_DESPACHO, '[DIRECCION]  [NOM_COMUNA] [NOM_CIUDAD] - TELEFONO: [TELEFONO] - FAX: [FAX]') DIRECCION_DESPACHO,
					COD_SUCURSAL_FACTURA, 
					dbo.f_get_direccion('SUCURSAL', COD_SUCURSAL_FACTURA, '[DIRECCION]  [NOM_COMUNA] [NOM_CIUDAD] - TELEFONO: [TELEFONO] - FAX: [FAX]') DIRECCION_FACTURA,
					COD_PERSONA,
					dbo.f_emp_get_mail_cargo_persona(COD_PERSONA,  '[EMAIL] - [NOM_CARGO]') MAIL_CARGO_PERSONA,
					Convert(varchar, FECHA_ENTREGA, 103) FECHA_ENTREGA, 
					OBS_DESPACHO,
					OBS,
					SUBTOTAL SUM_TOTAL, 
					PORC_DSCTO1, 
					MONTO_DSCTO1, 
					PORC_DSCTO2, 
					MONTO_DSCTO2, 
					TOTAL_NETO,
					TOTAL_NETO STATIC_TOTAL_NETO,
					TOTAL_NETO STATIC_TOTAL_NETO2,
					PORC_IVA, 
					MONTO_IVA, 
					TOTAL_CON_IVA,   
					TOTAL_CON_IVA STATIC_TOTAL_CON_IVA,
					TOTAL_CON_IVA STATIC_TOTAL_CON_IVA2,
					dbo.f_nv_total_pago(NV.COD_NOTA_VENTA) TOTAL_PAGO,
					TOTAL_CON_IVA - dbo.f_nv_total_pago(NV.COD_NOTA_VENTA) TOTAL_POR_PAGAR,
					COD_FORMA_PAGO, 
					INGRESO_USUARIO_DSCTO1,  
					INGRESO_USUARIO_DSCTO2,
					dbo.f_get_parametro(".self::K_PARAM_PORC_DSCTO_MAX.") PORC_DSCTO_MAX,
					datediff(d, FECHA_NOTA_VENTA, getdate()) EMITIDA_HACE,
					--resultados
					dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'VENTA_NETA') VENTA_NETA,
					PORC_DSCTO_CORPORATIVO,
					PORC_DSCTO_CORPORATIVO PORC_DSCTO_CORPORATIVO_STATIC,
					dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'MONTO_DSCTO_CORPORATIVO') MONTO_DSCTO_CORPORATIVO,
					dbo.f_get_parametro_porc('AA', getdate())PORC_AA,
					dbo.f_get_parametro_porc('GF', getdate())PORC_GF,
					dbo.f_get_parametro_porc('GV', getdate())PORC_GV,
					dbo.f_get_parametro_porc('ADM', getdate())PORC_ADM,
					dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'MONTO_DIRECTORIO') MONTO_DIRECTORIO,
					dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'MONTO_GASTO_FIJO') MONTO_GASTO_FIJO,
					dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'SUM_OC_TOTAL') SUM_OC_TOTAL,
					dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'RESULTADO') RESULTADO,
					dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'RESULTADO') STATIC_RESULTADO,			
					case NV.TOTAL_NETO when 0 then
						0
					else
						dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'PORC_RESULTADO')
					end PORC_RESULTADO,
					case NV.TOTAL_NETO when 0 then
						0
					else
						dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'PORC_RESULTADO')
					end STATIC_PORC_RESULTADO,
					dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'COMISION_V1')COMISION_V1,
					dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'COMISION_V2')COMISION_V2,
					dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'COMISION_GV')COMISION_GV,
					dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'COMISION_ADM')COMISION_ADM,
					(select NOM_USUARIO from USUARIO USU where USU.COD_USUARIO = NV.COD_USUARIO_VENDEDOR1) VENDEDOR1,
					(select NOM_USUARIO from USUARIO USU where USU.COD_USUARIO = NV.COD_USUARIO_VENDEDOR2) VENDEDOR2,
					dbo.f_get_parametro(".self::K_PARAM_GTE_VTA.") GTE_VTA,
					dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'REMANENTE') REMANENTE,
					-- no modificable en tab de resultados la comision del vendedor
					PORC_VENDEDOR1 PORC_VENDEDOR1_R,  
					PORC_VENDEDOR2 PORC_VENDEDOR2_R,
					'".$cambia_dscto_corp."' CAMBIA_DSCTO_CORPORATIVO,
					-- despachado
					case NV.SUBTOTAL when 0 then
						0
					else
						Round((select isnull(sum((CANTIDAD - dbo.f_nv_cant_por_despachar(COD_ITEM_NOTA_VENTA, default)) * PRECIO), 0) 	from ITEM_NOTA_VENTA IT where IT.COD_NOTA_VENTA = NV.COD_NOTA_VENTA) * 100 / NV.SUBTOTAL, 0)
					end PORC_GD,
					-- facturado
					dbo.f_nv_porc_facturado(NV.COD_NOTA_VENTA) PORC_FACTURA,
					-- pagado
					--0 PORC_PAGOS,
					Round((dbo.f_nv_total_pago(NV.COD_NOTA_VENTA) / TOTAL_CON_IVA) * 100, 0) PORC_PAGOS,
					-- historial de modificacion descto. corporativo
					(select count(*)
					from LOG_CAMBIO LG, DETALLE_CAMBIO DC
					where LG.NOM_TABLA = 'NOTA_VENTA' and LG.KEY_TABLA = CAST(NV.COD_NOTA_VENTA AS VARCHAR) and
						LG.COD_LOG_CAMBIO = DC.COD_LOG_CAMBIO and
						DC.NOM_CAMPO = 'PORC_DSCTO_CORPORATIVO') CANT_CAMBIO_PORC_DESCTO_CORP,
					-- datos cierre NV
					case NV.COD_ESTADO_NOTA_VENTA
						when ".self::K_ESTADO_CERRADA." then ''
						else 'none'
					end TABLE_CIERRE_DISPLAY,
					case NV.COD_ESTADO_NOTA_VENTA
						when ".self::K_ESTADO_CERRADA." then 'none'
						else ''
					end BOTON_CIERRE_DISPLAY,
					'' TABLE_PENDIENTE_DISPLAY,
					'' ES_VENDEDOR, 
					-- datos anulación
					convert(varchar(20), NV.FECHA_ANULA, 103) +'  '+ convert(varchar(20), NV.FECHA_ANULA, 8) FECHA_ANULA,
					MOTIVO_ANULA,
					COD_USUARIO_ANULA,
					case NV.COD_ESTADO_NOTA_VENTA
						when ".self::K_ESTADO_ANULADA." then ''
						else 'none'
					end TR_DISPLAY_ANULADA,
					COD_USUARIO_CONFIRMA COD_USUARIO_CONFIRMA_H,
					FECHA_CONFIRMA,
					case NV.COD_ESTADO_NOTA_VENTA
						when ".self::K_ESTADO_CERRADA." then 'CERRADA'
						when ".self::K_ESTADO_ANULADA." then 'ANULADA'
						when ".self::K_ESTADO_CONFIRMADA." then 'CONFIRMADA' 
						else ''
					end TITULO_ESTADO_NOTA_VENTA
					,case NV.COD_ESTADO_NOTA_VENTA
						when ".self::K_ESTADO_CONFIRMADA." then 'none' 
						else ''
					end DISPLAY_PREORDEN
				from NOTA_VENTA NV, USUARIO U, EMPRESA E, ESTADO_NOTA_VENTA ENV, CUENTA_CORRIENTE CC
				where COD_NOTA_VENTA = {KEY1} and
					U.COD_USUARIO = NV.COD_USUARIO and
					E.COD_EMPRESA = NV.COD_EMPRESA and
					ENV.COD_ESTADO_NOTA_VENTA = NV.COD_ESTADO_NOTA_VENTA and
					CC.COD_CUENTA_CORRIENTE = NV.COD_CUENTA_CORRIENTE";
					
		////////////////////
		// tab NV
		// DATAWINDOWS NOTA_VENTA
	
		$this->dws['dw_nota_venta'] = new dw_nota_venta($sql);

		$java = $this->dws['dw_nota_venta']->controls['RUT']->get_onChange();
		 $this->dws['dw_nota_venta']->controls['RUT']->set_onChange($java." porc_dscto_corporativo();");
		 
		 $java = $this->dws['dw_nota_venta']->controls['ALIAS']->get_onChange();
		 $this->dws['dw_nota_venta']->controls['ALIAS']->set_onChange($java." porc_dscto_corporativo();");
	
		 $java = $this->dws['dw_nota_venta']->controls['COD_EMPRESA']->get_onChange();
		 $this->dws['dw_nota_venta']->controls['COD_EMPRESA']->set_onChange($java." porc_dscto_corporativo();");
		 
		 $java = $this->dws['dw_nota_venta']->controls['NOM_EMPRESA']->get_onChange();
		 $this->dws['dw_nota_venta']->controls['NOM_EMPRESA']->set_onChange($java." porc_dscto_corporativo();");
	
		$this->dws['dw_nota_venta']->add_control(new edit_nro_doc('COD_NOTA_VENTA','NOTA_VENTA'));
		$this->dws['dw_nota_venta']->add_control(new edit_text_upper('NRO_ORDEN_COMPRA', 25, 20));
		$this->dws['dw_nota_venta']->add_control(new edit_text_upper('CENTRO_COSTO_CLIENTE', 25, 30));	
		$this->dws['dw_nota_venta']->add_control(new edit_nro_doc('COD_COTIZACION', 'COTIZACION'));	
		
		$this->add_controls_cot_nv();
		$this->dws['dw_nota_venta']->set_computed('STATIC_TOTAL_NETO', '[TOTAL_NETO]');	
		$this->dws['dw_nota_venta']->set_computed('STATIC_TOTAL_NETO2', '[TOTAL_NETO]');	
		$this->dws['dw_nota_venta']->set_computed('STATIC_TOTAL_CON_IVA', '[TOTAL_CON_IVA]');	
		$this->dws['dw_nota_venta']->set_computed('STATIC_TOTAL_CON_IVA2', '[TOTAL_CON_IVA]');	
		$this->dws['dw_nota_venta']->add_control(new static_num('TOTAL_PAGO'));	
		$this->dws['dw_nota_venta']->set_computed('TOTAL_POR_PAGAR', '[TOTAL_CON_IVA] - [TOTAL_PAGO]');	
		$this->dws['dw_nota_venta']->add_control(new edit_text('COD_ESTADO_NOTA_VENTA',10,10, 'hidden'));
		$this->dws['dw_nota_venta']->add_control(new static_text('NOM_ESTADO_NOTA_VENTA'));
		$this->dws['dw_nota_venta']->add_control(new static_num('VALOR_TIPO_CAMBIO', 2));
			
		$sql = "select 		COD_ORIGEN_VENTA,
							NOM_ORIGEN_VENTA,
							ORDEN
				from 		ORIGEN_VENTA
				order by 	ORDEN";
		$this->dws['dw_nota_venta']->add_control(new drop_down_dw('COD_ORIGEN_VENTA', $sql, 120));
		$this->dws['dw_nota_venta']->add_control(new edit_text('COD_CUENTA_CORRIENTE', 20, 20, 'hidden'));		
		$this->dws['dw_nota_venta']->add_control(new static_text('NOM_CUENTA_CORRIENTE'));		
		$this->dws['dw_nota_venta']->add_control(new static_text('NRO_CUENTA_CORRIENTE'));		
		
		// datos anulación
		$sql = "select 	COD_USUARIO
						,NOM_USUARIO
				from USUARIO";				
		$this->dws['dw_nota_venta']->add_control(new drop_down_dw('COD_USUARIO_ANULA',$sql,150));	
		$this->dws['dw_nota_venta']->set_entrable('COD_USUARIO_ANULA', false);	
		
		
		
		$this->dws['dw_nota_venta']->add_control(new edit_date('FECHA_ENTREGA'));
		$this->dws['dw_nota_venta']->add_control(new edit_text_multiline('OBS_DESPACHO',54,3));
		$this->dws['dw_nota_venta']->add_control(new edit_text_multiline('OBS',54,3));
		$this->dws['dw_nota_venta']->add_control(new edit_text('PORC_DSCTO_MAX',10, 10, 'hidden'));
		
		$sql_forma_pago	= "	select COD_FORMA_PAGO
								,NOM_FORMA_PAGO
								,CANTIDAD_DOC
							from FORMA_PAGO
						   	order by ORDEN";
		$this->dws['dw_nota_venta']->add_control($control = new drop_down_dw('COD_FORMA_PAGO', $sql_forma_pago, 180));
		$control->set_onChange("change_forma_pago('', this);");
		
		$this->dws['dw_nota_venta']->add_control(new edit_text('COD_USUARIO_CONFIRMA_H',10, 10, 'hidden'));
		
		// asigna los mandatorys
		$this->dws['dw_nota_venta']->set_mandatory('COD_ESTADO_NOTA_VENTA', 'Estado');
		//$this->dws['dw_nota_venta']->set_mandatory('COD_ORIGEN_VENTA', 'Origen');
		$this->dws['dw_nota_venta']->set_mandatory('REFERENCIA', 'Referencia');
		$this->dws['dw_nota_venta']->set_mandatory('COD_EMPRESA', 'Empresa');
		$this->dws['dw_nota_venta']->set_mandatory('COD_SUCURSAL_DESPACHO', 'Sucursal de Despacho');
		$this->dws['dw_nota_venta']->set_mandatory('COD_SUCURSAL_FACTURA', 'Sucursal de Factura');
		$this->dws['dw_nota_venta']->set_mandatory('COD_PERSONA', 'Persona');
		$this->dws['dw_nota_venta']->set_mandatory('FECHA_ENTREGA', 'Fecha de Entrega');
		$this->dws['dw_nota_venta']->set_mandatory('COD_FORMA_PAGO', 'Forma de Pago');
		
		$this->dws['dw_lista_guia_despacho'] = new dw_lista_guia_despacho();
		$this->dws['dw_lista_factura'] = new dw_lista_factura();
		$this->dws['dw_lista_pago'] = new dw_lista_pago();
		
		////////////////////
		// tab items
		$this->dws['dw_item_nota_venta'] = new dw_item_nota_venta();
					
		//ordenes de compra
		$this->dws['dw_orden_compra'] = new dw_nv_orden_compra();	
		
		//resultados	
		if ($cambia_dscto_corp == 'S'){	
			$this->dws['dw_nota_venta']->add_control(new edit_porcentaje('PORC_DSCTO_CORPORATIVO'));
		} 
		else{
			$this->dws['dw_nota_venta']->add_control(new static_num('PORC_DSCTO_CORPORATIVO', 1));
		} 
		$this->dws['dw_nota_venta']->add_control(new static_num('PORC_DSCTO_CORPORATIVO_STATIC', 1));		
		$this->dws['dw_nota_venta']->add_control(new static_num('PORC_AA', 1));
		$this->dws['dw_nota_venta']->add_control(new static_num('PORC_GF', 1));
		$this->dws['dw_nota_venta']->add_control(new static_num('PORC_GV', 1));
		$this->dws['dw_nota_venta']->add_control(new static_num('PORC_ADM', 1));
		$this->dws['dw_nota_venta']->add_control(new static_num('SUM_OC_TOTAL', 0));
		$this->dws['dw_nota_venta']->add_control(new static_num('PORC_VENDEDOR1_R', 2));
		$this->dws['dw_nota_venta']->add_control(new static_num('PORC_VENDEDOR2_R', 2));
		$this->dws['dw_nota_venta']->add_control(new static_text('VENDEDOR1'));
		$this->dws['dw_nota_venta']->add_control(new static_text('VENDEDOR2'));
		$this->dws['dw_nota_venta']->add_control(new static_text('GTE_VTA'));	
		
		$this->dws['dw_nota_venta']->set_computed('MONTO_DSCTO_CORPORATIVO', '[TOTAL_NETO] * [PORC_DSCTO_CORPORATIVO] / 100');		
		$this->dws['dw_nota_venta']->set_computed('VENTA_NETA_FINAL', '[TOTAL_NETO] - [MONTO_DSCTO_CORPORATIVO]');	
		$this->dws['dw_nota_venta']->set_computed('MONTO_DIRECTORIO', '[RESULTADO] * [PORC_AA] / 100');
		$this->dws['dw_nota_venta']->set_computed('MONTO_GASTO_FIJO', '[VENTA_NETA_FINAL] * [PORC_GF] / 100');
		$this->dws['dw_nota_venta']->set_computed('RESULTADO', '[TOTAL_NETO] - [SUM_OC_TOTAL] - [MONTO_GASTO_FIJO] - [MONTO_DSCTO_CORPORATIVO]');
		$this->dws['dw_nota_venta']->set_computed('STATIC_RESULTADO', '[TOTAL_NETO] - [SUM_OC_TOTAL] - [MONTO_GASTO_FIJO] - [MONTO_DSCTO_CORPORATIVO]');	
		$this->dws['dw_nota_venta']->set_computed('PORC_RESULTADO', '[RESULTADO] / ([TOTAL_NETO] - [MONTO_DSCTO_CORPORATIVO]) * 100');
		$this->dws['dw_nota_venta']->set_computed('STATIC_PORC_RESULTADO', '[RESULTADO] / ([TOTAL_NETO] - [MONTO_DSCTO_CORPORATIVO]) * 100');
		$this->dws['dw_nota_venta']->set_computed('COMISION_V1', '([PORC_VENDEDOR1] / 100) * [RESULTADO]');	
		$this->dws['dw_nota_venta']->set_computed('COMISION_V2', '([PORC_VENDEDOR2] / 100) * [RESULTADO]');	
		$this->dws['dw_nota_venta']->set_computed('COMISION_GV', '[RESULTADO] * [PORC_GV] / 100');	
		$this->dws['dw_nota_venta']->set_computed('COMISION_ADM', '[RESULTADO] * [PORC_ADM] / 100');	
		$this->dws['dw_nota_venta']->set_computed('REMANENTE', '[RESULTADO] - [MONTO_DIRECTORIO] - [COMISION_V1] - [COMISION_V2] - [COMISION_GV] - [COMISION_ADM]');	
		
		// registra historial de quien modifico comisiones
		$this->add_auditoria('PORC_VENDEDOR1');
		$this->add_auditoria('PORC_VENDEDOR2');
		
		// registra historial de quien modifico el descuento corporativo
		$this->add_auditoria('PORC_DSCTO_CORPORATIVO');
	
		$this->dws['dw_nota_venta']->add_control(new edit_text('CANT_CAMBIO_PORC_DESCTO_CORP',10, 10, 'hidden'));
		
		// dw pagos
		$this->dws['dw_nota_venta']->add_control(new static_num('TOTAL_MONTO_DOC'));
		
		// focus 
		$this->set_first_focus('NRO_ORDEN_COMPRA');	
	}
	function make_menu(&$temp) {
   		/*  MODIFICACION PARA USUARIO ANGEL SCIANCA, EN EL INFORME DE VENTAS SE ENCOJE EL TAMAÑO DEL MENU */   		
	   	$menu = session::get('menu_appl');
	    $menu->ancho_completa_menu = 1;
	    $menu->draw($temp);
	    $menu->ancho_completa_menu = 79;	    	    	    		
    }
	////////////////////
	function goto_list() {
		session::set('ULTIMA_NV_CONSAULTADA', $this->get_key());
		parent::goto_list();		
	}
	////////////////////
	
	function load_record() {
		$COD_NOTA_VENTA = $this->get_item_wo($this->current_record, 'COD_NOTA_VENTA');
		$this->dws['dw_nota_venta']->retrieve($COD_NOTA_VENTA);
		$COD_EMPRESA = $this->dws['dw_nota_venta']->get_item(0, 'COD_EMPRESA');
		$this->dws['dw_nota_venta']->controls['COD_SUCURSAL_FACTURA']->retrieve($COD_EMPRESA);
		$this->dws['dw_nota_venta']->controls['COD_SUCURSAL_DESPACHO']->retrieve($COD_EMPRESA);
		$this->dws['dw_nota_venta']->controls['COD_PERSONA']->retrieve($COD_EMPRESA);		
		$this->dws['dw_lista_guia_despacho']->retrieve($COD_NOTA_VENTA);	
		$this->dws['dw_lista_factura']->retrieve($COD_NOTA_VENTA);	
		$this->dws['dw_lista_pago']->retrieve($COD_NOTA_VENTA);	
		$this->dws['dw_item_nota_venta']->retrieve($COD_NOTA_VENTA);	
		$this->dws['dw_orden_compra']->retrieve($COD_NOTA_VENTA);
	
		$COD_ESTADO_NOTA_VENTA = $this->dws['dw_nota_venta']->get_item(0, 'COD_ESTADO_NOTA_VENTA');
		
		$this->b_print_visible 	 = true;
		$this->b_no_save_visible = true;
		$this->b_save_visible 	 = true;
		$this->b_modify_visible  = true;
		$this->b_delete_visible  = true;		
		if ($COD_ESTADO_NOTA_VENTA == self::K_ESTADO_EMITIDA){	
			// si estado = emitida se puede CONFIRMAR, ANULAR
			$sql = "select COD_ESTADO_NOTA_VENTA,
						NOM_ESTADO_NOTA_VENTA
					from ESTADO_NOTA_VENTA
					where COD_ESTADO_NOTA_VENTA = ".self::K_ESTADO_EMITIDA." or
						COD_ESTADO_NOTA_VENTA = ".self::K_ESTADO_CONFIRMADA." or
						COD_ESTADO_NOTA_VENTA = ".self::K_ESTADO_ANULADA."
					order by ORDEN";


			unset($this->dws['dw_nota_venta']->controls['COD_ESTADO_NOTA_VENTA']);
			$this->dws['dw_nota_venta']->add_control($control = new drop_down_dw('COD_ESTADO_NOTA_VENTA',$sql,141));	
			$control->set_onChange("mostrarOcultar_Anula(this);");
			$this->dws['dw_nota_venta']->controls['NOM_ESTADO_NOTA_VENTA']->type = 'hidden';
			$this->dws['dw_nota_venta']->add_control(new edit_text_upper('MOTIVO_ANULA',100, 100));
		}
		else if ($COD_ESTADO_NOTA_VENTA == self::K_ESTADO_CONFIRMADA){
			// si estado = confirmada se puede CERRAR, ANULAR
			$sql = "select COD_ESTADO_NOTA_VENTA,
						NOM_ESTADO_NOTA_VENTA
					from ESTADO_NOTA_VENTA
					where COD_ESTADO_NOTA_VENTA = ".self::K_ESTADO_CONFIRMADA." or
						COD_ESTADO_NOTA_VENTA = ".self::K_ESTADO_ANULADA."
					order by ORDEN";

			unset($this->dws['dw_nota_venta']->controls['COD_ESTADO_NOTA_VENTA']);
			$this->dws['dw_nota_venta']->add_control($control = new drop_down_dw('COD_ESTADO_NOTA_VENTA',$sql,141));	
			$control->set_onChange("mostrarOcultar_Anula(this);");
			$this->dws['dw_nota_venta']->controls['NOM_ESTADO_NOTA_VENTA']->type = 'hidden';
			$this->dws['dw_nota_venta']->add_control(new edit_text_upper('MOTIVO_ANULA',100, 100));
			
			// deja no entrable campos tab Nota Venta
			$this->dws['dw_nota_venta']->set_entrable('NRO_ORDEN_COMPRA'        , false);
			$this->dws['dw_nota_venta']->set_entrable('COD_ORIGEN_VENTA'        , false);
			$this->dws['dw_nota_venta']->set_entrable('CENTRO_COSTO_CLIENTE'    , false);
			$this->dws['dw_nota_venta']->set_entrable('REFERENCIA'       		, false);
			$this->dws['dw_nota_venta']->set_entrable('COD_EMPRESA'        	 	, false);
			$this->dws['dw_nota_venta']->set_entrable('ALIAS'        			, false);
			$this->dws['dw_nota_venta']->set_entrable('RUT'        			 	, false);
			$this->dws['dw_nota_venta']->set_entrable('NOM_EMPRESA'        	 	, false);
			$this->dws['dw_nota_venta']->set_entrable('COD_SUCURSAL_DESPACHO'   , false);
			$this->dws['dw_nota_venta']->set_entrable('COD_SUCURSAL_FACTURA'    , false);
			$this->dws['dw_nota_venta']->set_entrable('COD_PERSONA'        	 	, false);
			
			// deja no entrable campos tab Ítems
			$this->dws['dw_item_nota_venta']->set_entrable_dw(false);
			$this->dws['dw_nota_venta']->set_entrable('PORC_DSCTO1'			, false);
			$this->dws['dw_nota_venta']->set_entrable('MONTO_DSCTO1'		, false);
			$this->dws['dw_nota_venta']->set_entrable('PORC_DSCTO2'			, false);
			$this->dws['dw_nota_venta']->set_entrable('MONTO_DSCTO2'		, false);
			$this->dws['dw_nota_venta']->set_entrable('PORC_IVA'			, false);
			
		}
		else if ($COD_ESTADO_NOTA_VENTA == self::K_ESTADO_ANULADA) {
			$sql = "select COD_ESTADO_NOTA_VENTA,
						NOM_ESTADO_NOTA_VENTA
					from ESTADO_NOTA_VENTA
					where COD_ESTADO_NOTA_VENTA = ".self::K_ESTADO_ANULADA;

			unset($this->dws['dw_nota_venta']->controls['COD_ESTADO_NOTA_VENTA']);
			$this->dws['dw_nota_venta']->add_control(new drop_down_dw('COD_ESTADO_NOTA_VENTA',$sql,141));	
			$this->dws['dw_nota_venta']->controls['NOM_ESTADO_NOTA_VENTA']->type = 'hidden';
			
			$this->b_print_visible 	 = false;
			$this->b_no_save_visible = false;
			$this->b_save_visible 	 = false;
			$this->b_modify_visible  = false;
			$this->b_delete_visible  = false;		
		}
		else if ($COD_ESTADO_NOTA_VENTA == self::K_ESTADO_CERRADA) {
			$sql = "select COD_ESTADO_NOTA_VENTA,
						NOM_ESTADO_NOTA_VENTA
					from ESTADO_NOTA_VENTA
					where COD_ESTADO_NOTA_VENTA = ".self::K_ESTADO_CERRADA;

			unset($this->dws['dw_nota_venta']->controls['COD_ESTADO_NOTA_VENTA']);
			$this->dws['dw_nota_venta']->add_control(new drop_down_dw('COD_ESTADO_NOTA_VENTA',$sql,141));	
			$this->dws['dw_nota_venta']->controls['NOM_ESTADO_NOTA_VENTA']->type = 'hidden';
			
			$this->b_print_visible 	 = false;
			$this->b_no_save_visible = false;
			$this->b_save_visible 	 = false;
			$this->b_modify_visible  = false;
			$this->b_delete_visible  = false;		
		}
			
		// si es vendedor 1 puede cerrar la NV, 
		//para el caso en que no tenga 'dw_tipo_pendiente_nota_venta' por autorizar
		$COD_USUARIO_VENDEDOR1 = $this->dws['dw_nota_venta']->get_item(0, 'COD_USUARIO_VENDEDOR1');
		if ($this->cod_usuario == $COD_USUARIO_VENDEDOR1)
			$es_vendedor = 'S';
		else
			$es_vendedor = 'N';	
		$this->dws['dw_nota_venta']->set_item(0, 'ES_VENDEDOR', $es_vendedor);
	}
	function get_key() {
		return $this->dws['dw_nota_venta']->get_item(0, 'COD_NOTA_VENTA');
	}
	function por_despachar() {
		$cod_nota_venta = $this->get_key();
		$sql = $sql = "exec spr_nv_por_fact_por_desp $cod_nota_venta, 'POR_DESPACHAR'";
		$labels = array();
		$labels['strCOD_NOTA_VENTA'] = $cod_nota_venta;
		$rpt = new reporte($sql, $this->root_dir.'appl/nota_venta/por_despachar.xml', $labels, "Por despachar Nota Venta ".$cod_nota_venta, true);
		$this->redraw();
		return;
	}
	function procesa_event() {
		if ($_POST['wi_hidden']=='save_desde_por_despachar')
			$this->por_despachar();
		else
			parent::procesa_event();
	}
}
?>