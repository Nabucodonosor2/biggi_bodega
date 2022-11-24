<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../empresa/class_dw_help_empresa.php");
require_once(dirname(__FILE__)."/../common_appl/class_w_cot_nv.php");

/*********************************************************************************/
/*****************************FACTURA********************************************/

class dw_factura_read_only extends dw_help_empresa {
	const K_ESTADO_SII_EMITIDA 			= 1;	
	const K_ESTADO_SII_ANULADA			= 4;
	const K_PARAM_PORC_DSCTO_MAX 		= 26;
		
	function dw_factura_read_only() {
		$sql = "SELECT	F.COD_FACTURA,
					F.FECHA_REGISTRO,
					F.COD_USUARIO,
					U.NOM_USUARIO,
					F.NRO_FACTURA,
					convert(varchar(20), F.FECHA_FACTURA, 103) FECHA_FACTURA,
					convert(varchar(20), F.FECHA_FACTURA, 103) FECHA_FACTURA_I,
					convert(varchar(20), F.FECHA_FACTURA, 103) FECHA_FACTURA_P,
					convert(varchar(20), F.FECHA_FACTURA, 103) FECHA_FACTURA_C,
					F.COD_ESTADO_DOC_SII,
					EDS.NOM_ESTADO_DOC_SII,
					F.COD_EMPRESA,
					F.COD_SUCURSAL_FACTURA,
					dbo.f_get_direccion('FACTURA', F.COD_FACTURA, '[DIRECCION]  [NOM_COMUNA] [NOM_CIUDAD] - TELEFONO: [TELEFONO] - FAX: [FAX]') DIRECCION_FACTURA,
					F.COD_PERSONA,
					dbo.f_emp_get_mail_cargo_persona(F.COD_PERSONA,  '[EMAIL] - [NOM_CARGO]') MAIL_CARGO_PERSONA,
					F.REFERENCIA,
					F.NRO_ORDEN_COMPRA,
					convert(varchar(20), F.FECHA_ORDEN_COMPRA_CLIENTE, 103) FECHA_ORDEN_COMPRA_CLIENTE,
					F.OBS,
					F.NOM_CIUDAD,
					F.NOM_COMUNA,
					F.NOM_FORMA_PAGO,
					DESDE_4D,
					F.COD_USUARIO_VENDEDOR1,
					F.PORC_VENDEDOR1,
					F.COD_USUARIO_VENDEDOR2,
					F.PORC_VENDEDOR2,
					F.COD_ORIGEN_VENTA,
					F.RETIRADO_POR,
					F.RUT_RETIRADO_POR,
					F.DIG_VERIF_RETIRADO_POR,
					F.GUIA_TRANSPORTE,
					F.PATENTE,
					F.COD_BODEGA,
					F.COD_TIPO_FACTURA,
					F.COD_TIPO_FACTURA COD_TIPO_FACTURA_H,
					F.COD_DOC,
					F.SUBTOTAL SUM_TOTAL,
					F.TOTAL_NETO,
					F.INGRESO_USUARIO_DSCTO1,
					F.MONTO_DSCTO1,
					F.PORC_DSCTO1,
					F.PORC_DSCTO2,
					dbo.f_get_parametro(".self::K_PARAM_PORC_DSCTO_MAX.") PORC_DSCTO_MAX,
					F.INGRESO_USUARIO_DSCTO2,
					F.MONTO_DSCTO2,	
					F.PORC_IVA,
					F.MONTO_IVA,
					F.TOTAL_CON_IVA,
					F.TOTAL_CON_IVA STATIC_TOTAL_CON_IVA,
					dbo.f_fa_total_ingreso_pago(F.COD_FACTURA)SUM_MONTO_H,
					dbo.f_fa_saldo(F.COD_FACTURA) STATIC_SALDO,
					convert(varchar(20), F.FECHA_ANULA, 103) +'  '+ convert(varchar(20), F.FECHA_ANULA, 8) FECHA_ANULA,
					F.MOTIVO_ANULA,
					F.COD_USUARIO_ANULA, 			
					F.RUT RUT_FA,
					F.DIG_VERIF DIG_VERIF_FA,
					F.NOM_EMPRESA NOM_EMPRESA_FA,
					F.GIRO GIRO_FA,
					F.NOM_SUCURSAL,
					E.ALIAS,
					E.RUT,
					E.DIG_VERIF,
					E.GIRO,
					F.DIRECCION,
					F.TELEFONO,
					F.FAX,
					F.NOM_PERSONA,
					F.MAIL,
					F.COD_CARGO,	
					F.TELEFONO,
					F.FAX,
					F.COD_USUARIO_IMPRESION,
					F.COD_FORMA_PAGO,
					F.PORC_FACTURA_PARCIAL,
					F.NOM_FORMA_PAGO_OTRO,
					(select NOM_USUARIO from USUARIO USU where USU.COD_USUARIO = F.COD_USUARIO_VENDEDOR1) VENDEDOR1,
					(select NOM_USUARIO from USUARIO USU where USU.COD_USUARIO = F.COD_USUARIO_VENDEDOR2) VENDEDOR2,
					(select valor from parametro where cod_parametro=29 ) VALOR_FA_H,
					case F.COD_ESTADO_DOC_SII 
						when ".self::K_ESTADO_SII_ANULADA." then '' 
						else 'none'
					end TR_DISPLAY,
					case F.COD_ESTADO_DOC_SII 
						when ".self::K_ESTADO_SII_ANULADA." then 'ANULADA' 
						else ''
					end TITULO_DOC_ANULADA,	
					case
						when f.COD_DOC IS NULL then ''
						else 'none'
					end TD_DISPLAY_ELIMINAR,
					case
						when F.COD_DOC IS not NULL and F.COD_ESTADO_DOC_SII = ".self::K_ESTADO_SII_EMITIDA." then ''
						else 'none'
					end TD_DISPLAY_CANT_POR_FACT,
					GENERA_SALIDA,
					CANCELADA,
					F.TIPO_DOC,
					'' VISIBLE_DTE,
					F.COD_CENTRO_COSTO,
					F.COD_VENDEDOR_SOFLAND,
					'' TAB_153505,
					'' TAB_153510,
					'' TAB_153515,
					'' TAB_153520,	
					'none' TAB_153525,
					'none' TAB_153530
				FROM  FACTURA F,USUARIO U, EMPRESA E, ESTADO_DOC_SII EDS 
				WHERE F.COD_FACTURA = {KEY1} AND
					  F.COD_USUARIO = U.COD_USUARIO AND
					  E.COD_EMPRESA = F.COD_EMPRESA AND
					  EDS.COD_ESTADO_DOC_SII = F.COD_ESTADO_DOC_SII";
		
		parent::dw_help_empresa($sql);

		$this->add_control(new edit_text('COD_FACTURA',10,10, 'hidden', false, true));
		$this->add_control(new edit_nro_doc('NRO_FACTURA','FACTURA'));

		$this->add_control(new static_text('FECHA_FACTURA_I'));
		$this->add_control(new static_text('FECHA_FACTURA_P'));
		$this->add_control(new static_text('FECHA_FACTURA_C'));
		$this->add_control(new edit_date('FECHA_FACTURA'));
		//$control->set_onChange("change_fecha();");
		
		$this->add_control(new edit_text_upper('NRO_ORDEN_COMPRA', 25, 40));
		$this->add_control(new edit_date('FECHA_ORDEN_COMPRA_CLIENTE'));
		//$control->set_onChange("change_fecha();");
		
		
		$sql	= "select 	 COD_TIPO_FACTURA
							,NOM_TIPO_FACTURA
					from 	 TIPO_FACTURA
					order by COD_TIPO_FACTURA";
		$this->add_control(new drop_down_dw('COD_TIPO_FACTURA',$sql,150));
		$this->set_entrable('COD_TIPO_FACTURA', false);
		$this->add_control(new edit_text('COD_TIPO_FACTURA_H',10,10, 'hidden'));		
		$this->add_control(new edit_text('COD_ESTADO_DOC_SII',10,10, 'hidden'));
		$this->add_control(new static_text('NOM_ESTADO_DOC_SII'));
		
		$sql = "select 		COD_ORIGEN_VENTA,
							NOM_ORIGEN_VENTA,
							ORDEN
				from 		ORIGEN_VENTA
				order by 	ORDEN";
		$this->add_control(new drop_down_dw('COD_ORIGEN_VENTA', $sql, 120));
		$sql	= "select 	 COD_CENTRO_COSTO
							,NOM_CENTRO_COSTO
					from 	 CENTRO_COSTO
					order by COD_CENTRO_COSTO";
		$this->add_control(new drop_down_dw('COD_CENTRO_COSTO',$sql,150));
		$sql	= "select 	 COD_VENDEDOR_SOFLAND
							,NOM_VENDEDOR_SOFLAND
					from 	 VENDEDOR_SOFLAND
					order by NOM_VENDEDOR_SOFLAND";
		$this->add_control(new drop_down_dw('COD_VENDEDOR_SOFLAND',$sql,150));
		
		$this->add_control(new static_link('COD_DOC', '../../../../commonlib/trunk/php/link_wi.php?modulo_origen=inf_ventas_por_equipo&modulo_destino=nota_venta&cod_modulo_destino=[COD_DOC]&cod_item_menu=1510&current_tab_page=0'));

		$this->add_control(new edit_text_upper('REFERENCIA',120, 100));	
		$this->add_control(new edit_check_box('GENERA_SALIDA','S','N','GENERA SALIDA'));
		$this->add_control(new edit_check_box('CANCELADA','S','N','CANCELADA'));
		
		$sql_forma_pago	= "	select COD_FORMA_PAGO
								,NOM_FORMA_PAGO
								,CANTIDAD_DOC
							from FORMA_PAGO
						   	order by ORDEN";
		$this->add_control(new drop_down_dw('COD_FORMA_PAGO', $sql_forma_pago, 160));
		//$control->set_onChange("change_forma_pago('', this);");
		$this->add_control(new edit_text('NOM_FORMA_PAGO_OTRO',115, 100));
		$this->add_control(new static_text('NOM_FORMA_PAGO'));
		
		$this->add_control(new edit_num_doc_forma_pago('CANTIDAD_DOC_FORMA_PAGO_OTRO'));
		//$control->set_onChange("change_forma_pago('OTRO', this);");
		
		
		$this->add_control(new static_num('STATIC_TOTAL_CON_IVA'));
		$this->add_control(new static_num('SUM_MONTO_H'));
		$this->add_control(new static_num('STATIC_SALDO'));
		$this->add_control(new static_num('TOTAL_NETO'));
		$this->add_control(new static_num('MONTO_DSCTO1'));
		$this->add_control(new static_num('MONTO_IVA'));
		$this->add_control(new static_num('MONTO_DSCTO2'));
		$this->add_control(new static_num('TOTAL_CON_IVA'));
		
		//PARAMETROS FACTURA max cant items
		//$this->add_control(new edit_text('VALOR_FA_H',10, 10, 'hidden'));
		
		// usuario anulación
		$sql = "select 	COD_USUARIO
						,NOM_USUARIO
				from USUARIO";
								
		$this->add_control(new drop_down_dw('COD_USUARIO_ANULA',$sql,150));	
		$this->set_entrable('COD_USUARIO_ANULA', false);			
		
		// campos duplicados
		$this->add_control(new static_num('RUT_FA'));
		$this->add_control(new static_text('DIG_VERIF_FA'));
		$this->add_control(new static_text('NOM_EMPRESA_FA'));
		$this->add_control(new static_text('GIRO_FA'));
		
		$this->add_control(new static_text('NOM_SUCURSAL'));
		$this->add_control(new static_text('NOM_PERSONA'));
	
		$this->add_control(new edit_text_upper('RETIRADO_POR',37, 100));
		$this->add_control(new edit_text_upper('GUIA_TRANSPORTE',37, 100));
		$this->add_control(new edit_text_upper('PATENTE',37, 100));
		$this->add_control(new edit_text_multiline('OBS',59,1));
		$this->add_control(new edit_text('PORC_DSCTO_MAX',10, 10, 'hidden'));
		$this->add_control(new edit_rut('RUT_RETIRADO_POR', 10, 10, 'DIG_VERIF_RETIRADO_POR'));
		$this->add_control(new edit_dig_verif('DIG_VERIF_RETIRADO_POR', 'RUT_RETIRADO_POR'));		
		
		/*
		// asigna los mandatorys
		$this->set_mandatory('COD_ESTADO_DOC_SII', 'Estado');
		$this->set_mandatory('FECHA_FACTURA', 'Fecha de Factura');
		$this->set_mandatory('COD_EMPRESA', 'Empresa');
		$this->set_mandatory('COD_SUCURSAL_FACTURA', 'Sucursal de factura');
		$this->set_mandatory('COD_PERSONA', 'Persona');
		$this->set_mandatory('REFERENCIA', 'Referencia');
		$this->set_mandatory('COD_TIPO_FACTURA', 'Tipo Factura');
		$this->set_mandatory('COD_FORMA_PAGO', 'forma de pago');
		
		
		$this->add_control(new edit_text('COD_CIUDAD',10, 100, 'hidden'));
		$this->add_control(new edit_text('COD_PAIS',10, 100, 'hidden'));
		$this->add_control(new edit_text('TIPO_DOC',10, 100, 'hidden'));
			
		
		$this->set_first_focus('NRO_ORDEN_COMPRA');
		*/

	}
}

class dw_bitacora_factura_read_only extends datawindow {
	function dw_bitacora_factura_read_only() {
		$sql = "select BF.COD_BITACORA_FACTURA
						,convert(varchar, BF.FECHA_BITACORA_FACTURA, 103) FECHA_BITACORA_FACTURA
						,substring(convert(varchar, BF.FECHA_BITACORA_FACTURA, 108),1 , 5) HORA_BITACORA_FACTURA
						,BF.COD_USUARIO
						,U1.INI_USUARIO
						,BF.COD_FACTURA
						,BF.COD_ACCION_COBRANZA
						,BF.CONTACTO
						,BF.TELEFONO
						,BF.MAIL
						,BF.GLOSA
						,BF.TIENE_COMPROMISO
						,convert(varchar, BF.FECHA_COMPROMISO, 103) FECHA_COMPROMISO
						,substring(convert(varchar, BF.FECHA_COMPROMISO, 108),1 , 5) HORA_COMPROMISO
						,BF.GLOSA_COMPROMISO
						,BF.COMPROMISO_REALIZADO
						,convert(varchar, BF.FECHA_REALIZADO, 103) FECHA_REALIZADO
						,substring(convert(varchar, BF.FECHA_REALIZADO, 108),1 , 5) HORA_REALIZADO
						,BF.COD_USUARIO_REALIZADO
						,U2.INI_USUARIO INI_USUARIO_REALIZADO
				from BITACORA_FACTURA BF left outer join USUARIO U2 on U2.COD_USUARIO = BF.COD_USUARIO_REALIZADO, USUARIO U1 
				where BF.COD_FACTURA = {KEY1}
				  and U1.COD_USUARIO = BF.COD_USUARIO";
		parent::datawindow($sql, 'BITACORA_FACTURA', true, false);
		
		// controls
		$this->add_control(new static_text('FECHA_BITACORA_FACTURA'));
		$this->add_control(new static_text('HORA_BITACORA_FACTURA'));
		$this->add_control(new static_text('INI_USUARIO'));
		$sql = "select COD_ACCION_COBRANZA
						,NOM_ACCION_COBRANZA
				from ACCION_COBRANZA
				order by NOM_ACCION_COBRANZA";
		$this->add_control(new drop_down_dw('COD_ACCION_COBRANZA', $sql, 103));
		$this->add_control(new edit_text_upper('CONTACTO', 20, 100));
		$this->add_control(new edit_text_upper('TELEFONO', 20, 100));
		$this->add_control(new edit_mail('MAIL', 20, 100));
		$this->add_control(new edit_text_multiline('GLOSA', 30, 1));
		$this->add_control(new edit_check_box('TIENE_COMPROMISO', 'S', 'N'));
		//$control->set_onClick("tiene_compromiso(this);");
		//$this->add_control(new edit_protected('FECHA_COMPROMISO', new edit_date('FECHA_COMPROMISO_E'), new static_text('FECHA_COMPROMISO_S')));
		//$this->add_control(new edit_protected('HORA_COMPROMISO', new edit_time('HORA_COMPROMISO_E'), new static_text('HORA_COMPROMISO_S')));
		//$this->add_control(new edit_protected('GLOSA_COMPROMISO', new edit_text_upper('GLOSA_COMPROMISO_E', 51, 100), new static_text('GLOSA_COMPROMISO_S')));
		$this->add_control(new edit_check_box('COMPROMISO_REALIZADO', 'S', 'N'));
		//$control->set_onClick("compromiso_realizado(this);");
		$this->add_control(new static_text('FECHA_REALIZADO'));
		$this->add_control(new static_text('HORA_REALIZADO'));
		$this->add_control(new static_text('INI_USUARIO_REALIZADO'));
		
		/*
		// mandatory
		$this->set_mandatory('COD_ACCION_COBRANZA', 'Acción de cobranza');
		$this->set_mandatory('CONTACTO', 'Contacto');
		
		// first focus
		$this->set_first_focus('COD_ACCION_COBRANZA');
        
		// protected
		$this->set_protect('FECHA_COMPROMISO', "[TIENE_COMPROMISO]=='N'");
		$this->set_protect('HORA_COMPROMISO', "[TIENE_COMPROMISO]=='N'");
		$this->set_protect('GLOSA_COMPROMISO', "[TIENE_COMPROMISO]=='N'");
		$this->set_protect('COMPROMISO_REALIZADO', "[TIENE_COMPROMISO]=='N'");
        */
	}
}

class dw_ingreso_pago_fa_read_only extends datawindow{
	function dw_ingreso_pago_fa_read_only() {
		$sql = "SELECT	IP.COD_INGRESO_PAGO 
						, convert(varchar(20), IP.FECHA_INGRESO_PAGO, 103) FECHA_INGRESO_PAGO
						, TDP.NOM_TIPO_DOC_PAGO
						, DIP.NRO_DOC
						, DIP.MONTO_DOC
						, B.NOM_BANCO
						, IPF.MONTO_ASIGNADO
						,MDA.MONTO_DOC_ASIGNADO
						, EIP.NOM_ESTADO_INGRESO_PAGO
						, convert(varchar(20), DIP.FECHA_DOC, 103) FECHA_DOCTO
						, IP.COD_USUARIO
						, IP.COD_USUARIO_CONFIRMA
				FROM	INGRESO_PAGO_FACTURA IPF, INGRESO_PAGO IP, ESTADO_INGRESO_PAGO EIP , DOC_INGRESO_PAGO DIP LEFT OUTER JOIN BANCO B ON DIP.COD_BANCO = B.COD_BANCO, TIPO_DOC_PAGO TDP
						,MONTO_DOC_ASIGNADO MDA
				WHERE	IPF.COD_INGRESO_PAGO = IP.COD_INGRESO_PAGO
				AND		DIP.COD_INGRESO_PAGO = IP.COD_INGRESO_PAGO
				AND		DIP.COD_TIPO_DOC_PAGO = TDP.COD_TIPO_DOC_PAGO
				AND		IP.COD_ESTADO_INGRESO_PAGO = EIP.COD_ESTADO_INGRESO_PAGO
				AND		IPF.COD_DOC = {KEY1}
				AND 	MDA.COD_DOC_INGRESO_PAGO = DIP.COD_DOC_INGRESO_PAGO
				AND MDA.COD_INGRESO_PAGO_FACTURA = IPF.COD_INGRESO_PAGO_FACTURA";
		
		parent::datawindow($sql, 'INGRESO_PAGO_FACTURA', true, true);
		$sql	= "select 	 COD_USUARIO
							,NOM_USUARIO
					from 	 USUARIO";
		$this->add_control(new drop_down_dw('COD_USUARIO',$sql,150));
		$this->set_entrable('COD_USUARIO', false);
		
		$sql	= "select 	 COD_USUARIO
							,NOM_USUARIO
					from 	 USUARIO";
		$this->add_control(new drop_down_dw('COD_USUARIO_CONFIRMA',$sql,150));
		$this->set_entrable('COD_USUARIO_CONFIRMA', false);	
	
		//$this->add_control(new static_link('COD_INGRESO_PAGO', '../../../../commonlib/trunk/php/link_wi.php?modulo_origen=factura&modulo_destino=ingreso_pago&cod_modulo_destino=[COD_INGRESO_PAGO]&cod_item_menu=2505'));
		$this->add_control(new static_text('COD_INGRESO_PAGO'));
		$this->add_control(new static_text('NRO_DOC'));
		$this->add_control(new static_num('MONTO_DOC'));
		$this->add_control(new static_num('MONTO_DOC_ASIGNADO'));
		$this->add_control(new static_text('NOM_TIPO_DOC_PAGO'));
		$this->add_control(new static_text('NOM_ESTADO_INGRESO_PAGO'));
		$this->add_control(new static_text('NOM_BANCO'));
		
	}
}

class dw_item_factura_base_read_only extends datawindow {
	const K_ESTADO_SII_EMITIDA 			= 1;
	
	function dw_item_factura_base_read_only() {
		$sql = " SELECT ifa.COD_ITEM_FACTURA,
						ifa.COD_FACTURA,
						ifa.ORDEN,
						ifa.ITEM,
						ifa.COD_PRODUCTO,
						ifa.COD_PRODUCTO COD_PRODUCTO_OLD,
						ifa.NOM_PRODUCTO,
						ifa.CANTIDAD,
						ifa.PRECIO,
						ifa.COD_ITEM_DOC,
						ifa.TIPO_DOC,
						case ifa.TIPO_DOC
							when 'ITEM_NOTA_VENTA' then dbo.f_nv_cant_por_facturar(ifa.COD_ITEM_DOC, default)
							when 'ITEM_GUIA_DESPACHO' then dbo.f_gd_cant_por_facturar(ifa.COD_ITEM_DOC, default)
						end CANTIDAD_POR_FACTURAR,
						case
							when f.COD_DOC IS not NULL and f.COD_ESTADO_DOC_SII = ".self::K_ESTADO_SII_EMITIDA." then ''
							else 'none'
						end TD_DISPLAY_CANT_POR_FACT,	
						case
							when f.COD_DOC IS NULL then ''
							else 'none'
						end TD_DISPLAY_ELIMINAR,
						COD_TIPO_TE,
						MOTIVO_TE,
						'' BOTON_PRECIO, -- se utiliza en funcion comun js 'ingreso_TE'
						COD_TIPO_GAS,
						COD_TIPO_ELECTRICIDAD
				FROM    ITEM_FACTURA ifa, factura f
				WHERE   f.cod_factura=ifa.cod_factura and ifa.COD_FACTURA = {KEY1}
				ORDER BY ORDEN";
		
		 
		parent::datawindow($sql, 'ITEM_FACTURA', true, true);
		
		$this->add_control(new edit_text_upper('COD_ITEM_FACTURA',10, 10, 'hidden'));
		$this->add_control(new edit_num('ORDEN',4, 10));
		$this->add_control(new edit_text_upper('ITEM',4, 5));
		$this->add_control(new edit_cantidad('CANTIDAD',12,10));
		//$control->set_onChange("this.value = valida_ct_x_facturar(this);");
		//$this->add_control(new edit_text('COD_TIPO_TE',10, 100, 'hidden'));
		//$this->add_control(new edit_text('MOTIVO_TE',10, 100, 'hidden'));
		//$this->add_control(new edit_text('BOTON_PRECIO',10, 10, 'hidden'));
		$this->add_control(new static_num('CANTIDAD_POR_FACTURAR',1));
		$this->add_control(new computed('PRECIO', 0));
		$this->set_computed('TOTAL', '[CANTIDAD] * [PRECIO]');
		$this->accumulate('TOTAL', "calc_dscto();");
		$this->add_controls_producto_help();		// Debe ir despues de los computed donde esta involucrado precio, porque add_controls_producto_help() afecta el campos PRECIO
		
		//COD_TIPO_ELECTRICIDAD, COD_TIPO_GAS no son campos de K_CLIENTE =  COMERCIAL
		/*
		$this->controls['COD_PRODUCTO']->set_onChange("change_item_factura(this, 'COD_PRODUCTO');");
		
		$sql = "select COD_TIPO_GAS, NOM_TIPO_GAS
				from TIPO_GAS
				order by ORDEN";
		$this->add_control(new drop_down_dw('COD_TIPO_GAS', $sql, 80));					

		$sql = "select COD_TIPO_ELECTRICIDAD, NOM_TIPO_ELECTRICIDAD
				from TIPO_ELECTRICIDAD
				order by ORDEN";
		$this->add_control(new drop_down_dw('COD_TIPO_ELECTRICIDAD', $sql, 80));					
		
		//$this->set_first_focus('COD_PRODUCTO');
		*/
	}
}
//*****************************FIN FACTURA******************************************

class dw_nota_credito_read_only extends datawindow {
	const K_ESTADO_SII_EMITIDA 			= 1;	
	const K_ESTADO_SII_ANULADA			= 4;
	const K_PARAM_PORC_DSCTO_MAX 		= 26;

	function dw_nota_credito_read_only(){
		$sql = "SELECT	NC.COD_NOTA_CREDITO COD_NOTA_CREDITO_NC,
					NC.FECHA_REGISTRO FECHA_REGISTRO_NC,
					NC.COD_USUARIO COD_USUARIO_NC,
					U.NOM_USUARIO NOM_USUARIO_NC,
					NC.NRO_NOTA_CREDITO NRO_NOTA_CREDITO_NC,
					convert(varchar(20), NC.FECHA_NOTA_CREDITO, 103) FECHA_NOTA_CREDITO_NC,
					convert(varchar(20), NC.FECHA_NOTA_CREDITO, 103) FECHA_NOTA_CREDITO_I,
					NC.COD_ESTADO_DOC_SII COD_ESTADO_DOC_SII_NC,
					EDS.NOM_ESTADO_DOC_SII NOM_ESTADO_DOC_SII_NC,
					NC.COD_EMPRESA COD_EMPRESA_NC,
					NC.COD_SUCURSAL_FACTURA COD_SUCURSAL_FACTURA_NC,
					dbo.f_get_direccion('NOTA_CREDITO', NC.COD_NOTA_CREDITO, '[DIRECCION]  [NOM_COMUNA] [NOM_CIUDAD] - TELEFONO: [TELEFONO] - FAX: [FAX]') DIRECCION_FACTURA_NC,
					NC.COD_PERSONA COD_PERSONA_NC,
					dbo.f_emp_get_mail_cargo_persona(NC.COD_PERSONA,  '[EMAIL]') MAIL_CARGO_PERSONA_NC,
					NC.REFERENCIA REFERENCIA_NC,
					NC.OBS OBS_NC,
					NC.COD_BODEGA COD_BODEGA_NC,
					NC.COD_TIPO_NOTA_CREDITO COD_TIPO_NOTA_CREDITO_NC,
					NC.COD_DOC COD_DOC_H_NC,
					NC.SUBTOTAL SUM_TOTAL_NC,
					NC.TOTAL_NETO TOTAL_NETO_NC,
					NC.INGRESO_USUARIO_DSCTO1 INGRESO_USUARIO_DSCTO1_NC,
					NC.MONTO_DSCTO1 MONTO_DSCTO1_NC,
					NC.PORC_DSCTO1 PORC_DSCTO1_NC,
					NC.PORC_DSCTO2 PORC_DSCTO2_NC,
					NC.INGRESO_USUARIO_DSCTO2 INGRESO_USUARIO_DSCTO2_NC,
					NC.MONTO_DSCTO2 MONTO_DSCTO2_NC,	
					NC.PORC_IVA PORC_IVA_NC,
					NC.MONTO_IVA MONTO_IVA_NC,
					NC.TOTAL_CON_IVA TOTAL_CON_IVA_NC,
					convert(varchar(20), NC.FECHA_ANULA, 103) +'  '+ convert(varchar(20), NC.FECHA_ANULA, 8) FECHA_ANULA,
					NC.MOTIVO_ANULA,
					NC.COD_USUARIO_ANULA COD_USUARIO_ANULA_NC, 			
					NC.RUT RUT_NC,
					NC.DIG_VERIF DIG_VERIF_NC,
					NC.NOM_EMPRESA NOM_EMPRESA_NC,
					NC.GIRO GIRO_NC,
					NC.NOM_SUCURSAL NOM_SUCURSAL_NC,
					E.ALIAS,
					E.RUT,
					E.DIG_VERIF,
					E.GIRO,
					NC.DIRECCION,
					NC.COD_CARGO,	
					NC.TELEFONO,
					NC.FAX,
					NC.NOM_PERSONA NOM_PERSONA_NC,
					NC.MAIL,
					NC.COD_CARGO,
					NC.COD_USUARIO_IMPRESION,
					case NC.COD_ESTADO_DOC_SII 
						when ".self::K_ESTADO_SII_ANULADA." then '' 
						else 'none'
					end TR_DISPLAY 	,
					'' VISIBLE_DTE,	
					case
						when NC.COD_DOC IS NULL then ''
						else 'none'
					end TD_DISPLAY_ELIMINAR,
					case
						when NC.COD_DOC IS not NULL and NC.COD_ESTADO_DOC_SII = ".self::K_ESTADO_SII_EMITIDA." then ''
						else 'none'
					end TD_DISPLAY_CANT_POR_NC,
					(select valor from parametro where cod_parametro=40 ) VALOR_NC_H,
					FA.NRO_FACTURA NRO_FACTURA_NC,
					NC.COD_MOTIVO_NOTA_CREDITO COD_MOTIVO_NOTA_CREDITO_NC,
					NC.GENERA_ENTRADA				
				FROM  NOTA_CREDITO NC LEFT OUTER JOIN FACTURA FA ON NC.COD_DOC = FA.COD_FACTURA, USUARIO U, EMPRESA E, ESTADO_DOC_SII EDS
				WHERE NC.COD_NOTA_CREDITO = {KEY1} AND
					  NC.COD_USUARIO = U.COD_USUARIO AND
					  E.COD_EMPRESA = NC.COD_EMPRESA AND
					  EDS.COD_ESTADO_DOC_SII = NC.COD_ESTADO_DOC_SII";
		
		parent::datawindow($sql);
		
		$this->add_control(new edit_text('COD_NOTA_CREDITO_NC',10,10, 'hidden', false, true));
		$this->add_control(new edit_nro_doc('NRO_NOTA_CREDITO_NC','NOTA_CREDITO'));

		$this->add_control(new static_text('FECHA_NOTA_CREDITO_I'));
		$this->add_control(new edit_date('FECHA_NOTA_CREDITO_NC'));
		//$control->set_onChange("change_fecha();");

		$sql	= "select 	 COD_TIPO_NOTA_CREDITO
							,NOM_TIPO_NOTA_CREDITO
					from 	 TIPO_NOTA_CREDITO
					order by COD_TIPO_NOTA_CREDITO";
		$this->add_control(new drop_down_dw('COD_TIPO_NOTA_CREDITO_NC',$sql,150));
		//$this->set_entrable('COD_TIPO_NOTA_CREDITO_NC', true);

		$this->add_control(new edit_text('COD_ESTADO_DOC_SII_NC',10,10, 'hidden'));
		$this->add_control(new static_text('NOM_ESTADO_DOC_SII_NC'));
		
		$this->add_control(new edit_text('COD_DOC_H_NC',10,10, 'hidden'));
		$this->add_control(new static_text('NRO_FACTURA_NC'));
		$this->add_control(new edit_text_upper('REFERENCIA_NC',200, 100));
		$sql	= "select 	 COD_MOTIVO_NOTA_CREDITO
							,NOM_MOTIVO_NOTA_CREDITO
					from 	 MOTIVO_NOTA_CREDITO
					order by COD_MOTIVO_NOTA_CREDITO";
		$this->add_control(new drop_down_dw('COD_MOTIVO_NOTA_CREDITO_NC',$sql,150));
		$this->add_control(new edit_text_multiline('OBS_NC',50,2));
				
		// usuario anulación
		$sql = "select 	COD_USUARIO
						,NOM_USUARIO
				from USUARIO";
								
		$this->add_control(new drop_down_dw('COD_USUARIO_ANULA_NC',$sql,150));	
		//$this->set_entrable('COD_USUARIO_ANULA_NC', false);			
		
		// campos duplicados
		$this->add_control(new static_num('RUT_NC'));
		$this->add_control(new static_text('DIG_VERIF_NC'));
		$this->add_control(new static_text('NOM_EMPRESA_NC'));
		$this->add_control(new static_text('GIRO_NC'));
		
		$this->add_control(new static_text('NOM_SUCURSAL_NC'));
		$this->add_control(new static_text('NOM_PERSONA_NC'));
		
		$this->add_control(new static_num('SUM_TOTAL_NC'));
		$this->add_control(new static_num('TOTAL_NETO_NC'));
		$this->add_control(new static_num('MONTO_DSCTO1_NC'));
		$this->add_control(new static_num('MONTO_IVA_NC'));
		$this->add_control(new static_num('MONTO_DSCTO2_NC'));
		$this->add_control(new static_num('TOTAL_CON_IVA_NC'));
	
	     /*
		// asigna los mandatorys
		$this->set_mandatory('COD_ESTADO_DOC_SII', 'Estado');
		$this->set_mandatory('FECHA_NOTA_CREDITO', 'Fecha de Nota Credito');
		$this->set_mandatory('COD_EMPRESA', 'Empresa');
		$this->set_mandatory('COD_SUCURSAL_FACTURA', 'Sucursal de factura');
		$this->set_mandatory('COD_PERSONA', 'Persona');
		$this->set_mandatory('REFERENCIA', 'Referencia');
		$this->set_mandatory('COD_MOTIVO_NOTA_CREDITO', 'Motivo NC');
		$this->set_mandatory('COD_TIPO_NOTA_CREDITO', 'Tipo NC');
		
		$this->add_control(new edit_text('COD_CIUDAD',10, 100, 'hidden'));
		$this->add_control(new edit_text('COD_PAIS',10, 100, 'hidden'));	

		$this->add_control(new edit_text('VALOR_NC_H',10, 10, 'hidden'));
		*/
	}
}

class dw_item_nota_credito_read_only extends datawindow {
	const K_ESTADO_SII_EMITIDA 			= 1;
	
	function dw_item_nota_credito_read_only() {
		$sql = "SELECT ITNC.COD_ITEM_NOTA_CREDITO,
						ITNC.COD_NOTA_CREDITO COD_NOTA_CREDITO_INC,
						ITNC.ORDEN ORDEN_INC,
						ITNC.ITEM ITEM_INC,
						ITNC.COD_PRODUCTO COD_PRODUCTO_INC,
						ITNC.COD_PRODUCTO COD_PRODUCTO_OLD_INC,
						ITNC.NOM_PRODUCTO NOM_PRODUCTO_INC,
						ITNC.CANTIDAD CANTIDAD_INC,
						ITNC.PRECIO PRECIO_INC,
						ITNC.COD_ITEM_DOC COD_ITEM_DOC_INC,
						dbo.f_fa_cant_por_nc(ITNC.COD_ITEM_DOC, default) CANTIDAD_POR_INC,
						case
							when NC.COD_DOC IS not NULL and NC.COD_ESTADO_DOC_SII = ".self::K_ESTADO_SII_EMITIDA." then ''
							else 'none'
						end TD_DISPLAY_CANT_POR,
						case
							when NC.COD_DOC IS NULL then ''
							else 'none'
						end TD_DISPLAY_ELIMINAR,
						NC.COD_DOC COD_DOC_INC,
						COD_TIPO_TE,
						MOTIVO_TE,
						'' BOTON_PRECIO -- se utiliza en funcion comun js 'ingreso_TE'
				FROM    ITEM_NOTA_CREDITO ITNC, NOTA_CREDITO NC
				WHERE   NC.COD_NOTA_CREDITO = ITNC.COD_NOTA_CREDITO AND
					    ITNC.COD_NOTA_CREDITO = {KEY1}
				ORDER BY ORDEN";
		
		 
		parent::datawindow($sql, 'ITEM_NOTA_CREDITO', true, true);
		
		$this->add_control(new edit_text_upper('COD_ITEM_NOTA_CREDITO',10, 10, 'hidden'));
		$this->add_control(new edit_text_upper('COD_DOC_INC',10, 10, 'hidden'));
		$this->add_control(new edit_num('ORDEN_INC',4, 10));
		$this->add_control(new edit_text_upper('ITEM_INC',4, 5));
		$this->add_control($control = new edit_cantidad('CANTIDAD_INC',12,10));
		$control->set_onChange("this.value = valida_ct_x_nc(this);");
		$this->add_control(new edit_text('COD_TIPO_TE',10, 100, 'hidden'));
		$this->add_control(new edit_text('MOTIVO_TE',10, 100, 'hidden'));
		$this->add_control(new edit_text('BOTON_PRECIO',10, 10, 'hidden'));
		$this->add_control(new static_num('CANTIDAD_POR_INC',1));
		
		$this->add_control(new computed('PRECIO_INC', 0));		
		$this->set_computed('TOTAL_INC', '[CANTIDAD_INC] * [PRECIO_INC]');
		$this->accumulate('TOTAL_INC', "calc_dscto();");
		//$this->add_controls_producto_help();		// Debe ir despues de los computed donde esta involucrado precio, porque add_controls_producto_help() afecta el campos PRECIO
		$this->add_control(new static_num('COD_PRODUCTO_INC','hidden'));
		/*
		// Agrega script adicional a COD_PRODUCTO 
        
		//$this->controls['COD_PRODUCTO_INC']->set_onChange("change_item_nota_credito(this, 'COD_PRODUCTO');");
		
		//$this->set_first_focus('COD_PRODUCTO');


		// asigna los mandatorys
		$this->set_mandatory('ORDEN', 'Orden');
		$this->set_mandatory('COD_PRODUCTO', 'Código del producto');
		$this->set_mandatory('CANTIDAD', 'Cantidad');
*/
	}
}


class wi_inf_ventas_por_equipo extends w_input {
	var $tipo_doc;
	var $cod_doc;
	
	function wi_inf_ventas_por_equipo($cod_item_menu) {
		parent::w_input('inf_ventas_por_equipo', $cod_item_menu);
		//FACTURA
		$this->dws['dw_factura'] = new dw_factura_read_only();
		$this->dws['dw_bitacora_factura'] = new dw_bitacora_factura_read_only();
		$this->dws['dw_ingreso_pago_fa'] = new dw_ingreso_pago_fa_read_only();
		$this->dws['dw_item_factura_base'] = new dw_item_factura_base_read_only();
		//NOTA CREDITO
		$this->dws['dw_nota_credito'] = new dw_nota_credito_read_only();
		$this->dws['dw_item_nota_credito'] = new dw_item_nota_credito_read_only();
	}
	function load_record() {
		$this->tipo_doc = $this->get_item_wo($this->current_record, 'TIPO_DOC');
		$this->cod_doc = $this->get_item_wo($this->current_record, 'COD_DOC');
		//FACTURA
		$this->dws['dw_factura']->retrieve($this->cod_doc);
		$this->dws['dw_bitacora_factura']->retrieve($this->cod_doc);
		$this->dws['dw_ingreso_pago_fa']->retrieve($this->cod_doc);
		$this->dws['dw_item_factura_base']->retrieve($this->cod_doc);
		//NOTA CREDITO
		$this->dws['dw_nota_credito']->retrieve($this->cod_doc);
		$this->dws['dw_item_nota_credito']->retrieve($this->cod_doc);

		if ($this->tipo_doc == 'FA') {
			$this->ruta_menu = 'Ventas por Equipo->Factura->';
			$this->current_tab_page = 0;
			
			$this->dws['dw_factura']->set_item(0, 'TAB_153525', 'none');		
			$this->dws['dw_factura']->set_item(0, 'TAB_153530', 'none');
			$this->dws['dw_factura']->set_item(0, 'TAB_153505', ''); 
			$this->dws['dw_factura']->set_item(0, 'TAB_153510', '');		
			$this->dws['dw_factura']->set_item(0, 'TAB_153515', '');		
			$this->dws['dw_factura']->set_item(0, 'TAB_153520', '');
						
			
		}
		else if ($this->tipo_doc == 'NC'){
			$this->ruta_menu = 'Ventas por Equipo->Nota Crédito->';
			$this->current_tab_page = 4;

			$this->dws['dw_factura']->set_item(0, 'TAB_153505', 'none'); 
			$this->dws['dw_factura']->set_item(0, 'TAB_153510', 'none');		
			$this->dws['dw_factura']->set_item(0, 'TAB_153515', 'none');		
			$this->dws['dw_factura']->set_item(0, 'TAB_153520', 'none');
			$this->dws['dw_factura']->set_item(0, 'TAB_153525', '');		
			$this->dws['dw_factura']->set_item(0, 'TAB_153530', '');
				
			
		}
	}
	function get_key() {
		return $this->cod_doc;
	}
	function get_key_para_ruta_menu() {
		
		if ($this->tipo_doc == 'FA')
			return $this->dws['dw_factura']->get_item(0, 'NRO_FACTURA');
		else if ($this->tipo_doc == 'NC')
			return $this->dws['dw_nota_credito']->get_item(0, 'NRO_NOTA_CREDITO_NC');
	
		return parent::get_key_para_ruta_menu();
	}
}
?>