<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../common_appl/class_w_cot_nv.php");
require_once(dirname(__FILE__)."/../empresa/class_dw_help_empresa.php");
require_once(dirname(__FILE__)."/class_dw_item_orden_compra.php");
require_once(dirname(__FILE__)."/class_print_reporte.php");

class dw_pago_docs extends datawindow{
	function dw_pago_docs(){
		$sql ="SELECT F.COD_FAPROV
					  ,CONVERT(VARCHAR, F.FECHA_REGISTRO, 103)				FECHA_REGISTRO
					  ,F.NRO_FAPROV
					  ,CONVERT(VARCHAR, FECHA_FAPROV, 103)					FECHA_FAPROV
					  ,F.TOTAL_NETO											DO_TOTAL_NETO				
					  ,F.MONTO_IVA											DO_MONTO_IVA
					  ,F.TOTAL_CON_IVA										DO_TOTAL_CON_IVA
					  ,ISNULL((SELECT SUM(MONTO_ASIGNADO) 
					  	FROM PAGO_FAPROV_FAPROV
					  	WHERE COD_FAPROV = F.COD_FAPROV),0)					PASADO
				FROM ORDEN_COMPRA O
					,FAPROV F
					,ITEM_FAPROV I 
				WHERE O.COD_ORDEN_COMPRA = {KEY1} 
				AND F.ORIGEN_FAPROV = 'ORDEN_COMPRA'
				AND F.COD_ESTADO_FAPROV = 2						--CONFIRMADA
				AND I.COD_FAPROV = F.COD_FAPROV
				AND I.COD_DOC = O.COD_ORDEN_COMPRA";
		
		parent::datawindow($sql, 'PAGO_DOCS');
		
		$this->add_control(new static_num('DO_TOTAL_NETO'));
		$this->add_control(new static_num('DO_MONTO_IVA'));
		$this->add_control(new static_num('DO_TOTAL_CON_IVA'));
		$this->add_control(new static_num('PASADO'));
	}
}


class dw_pago_docs_link extends datawindow{
	function dw_pago_docs_link(){
		$sql ="SELECT F.NRO_FAPROV
					  ,dbo.f_pago_link(O.COD_ORDEN_COMPRA, f.COD_FAPROV) LINKS
				FROM ORDEN_COMPRA O
					,FAPROV F
					,ITEM_FAPROV I 
				WHERE O.COD_ORDEN_COMPRA = {KEY1} 
				AND F.ORIGEN_FAPROV = 'ORDEN_COMPRA'
				AND F.COD_ESTADO_FAPROV = 2						--CONFIRMADA
				AND I.COD_FAPROV = F.COD_FAPROV
				AND I.COD_DOC = O.COD_ORDEN_COMPRA";
		
		parent::datawindow($sql, 'PAGO_DOCS_LINK');
		$this->add_control(new static_text('COD_FAPROV'));
	}
	
	function fill_record(&$temp, $record){	
		parent::fill_record($temp, $record);
			
		$string_link = $this->get_item($record, 'LINKS');
		$array_pagos = explode("|", $string_link);
		
		for($i=0 ; $i < count($array_pagos) ; $i++)
			$links .= "<a href=../../../../commonlib/trunk/php/link_wi.php?modulo_origen=orden_compra&modulo_destino=pago_faprov&cod_modulo_destino=".$array_pagos[$i]."&cod_item_menu=2530&current_tab_page=3>".$array_pagos[$i]."</a> ";
		
		$temp->setVar('PAGO_DOCS_LINK.LINK_S', $links);
		
	}
	
}

class dw_pago_orden_compra extends datawindow {
	function dw_pago_orden_compra () {
		$sql = "EXEC spdw_faprov_pago {KEY1},'ORDEN_COMPRA'";
		parent::datawindow($sql, 'ORDEN_COMPRA');
		
		$this->add_control(new static_text('OC_COD_FAPROV'));
		$this->add_control(new static_num('OC_TOTAL_NETO'));
		$this->add_control(new static_text('OC_COD_PAGO_FAPROV'));
		$this->add_control(new static_num('OC_MONTO_ASIGNADO'));
	}
}

/*
Clase : WI_ORDEN_COMPRA
*/

class dw_orden_compra extends dw_help_empresa {
	const K_ESTADO_ANULADA  = 2;
	
	function dw_orden_compra() {	
		$cod_usuario = session::get('COD_USUARIO');
		
		$sql = "SELECT	 O.COD_ORDEN_COMPRA
						,substring(convert(varchar(20), O.FECHA_ORDEN_COMPRA, 103) + ' ' + convert(varchar(20), O.FECHA_ORDEN_COMPRA, 108), 1, 16) FECHA_ORDEN_COMPRA
						,O.COD_USUARIO
						,U.NOM_USUARIO
						,O.COD_USUARIO_SOLICITA
						,O.COD_MONEDA				
						,EOC.NOM_ESTADO_ORDEN_COMPRA	
						,O.COD_ESTADO_ORDEN_COMPRA
						,O.COD_ESTADO_ORDEN_COMPRA COD_ESTADO_ORDEN_COMPRA_H
						,O.COD_NOTA_VENTA						
						,O.COD_CUENTA_CORRIENTE
						,O.COD_CUENTA_CORRIENTE AS NRO_CUENTA_CORRIENTE
						,O.REFERENCIA
						,O.NRO_ORDEN_COMPRA_4D
						,O.COD_EMPRESA
						,E.ALIAS
						,E.RUT
						,E.DIG_VERIF
						,E.NOM_EMPRESA
						,E.GIRO													
						,COD_SUCURSAL AS COD_SUCURSAL_FACTURA	 				
						,dbo.f_get_direccion('SUCURSAL', COD_SUCURSAL, '[DIRECCION]  [NOM_COMUNA] [NOM_CIUDAD] - TELEFONO: [TELEFONO] - FAX: [FAX]') DIRECCION_FACTURA						
						,O.COD_PERSONA
						,dbo.f_emp_get_mail_cargo_persona(COD_PERSONA, '[EMAIL]') MAIL_CARGO_PERSONA
						,O.SUBTOTAL	SUM_TOTAL					
						,O.PORC_DSCTO1
						,O.MONTO_DSCTO1  
						,O.PORC_DSCTO2
						,O.MONTO_DSCTO2  
						,O.TOTAL_NETO
						,O.PORC_IVA
						,O.MONTO_IVA
						,O.TOTAL_CON_IVA
						,O.OBS
						,substring(convert(varchar(20), O.FECHA_ANULA, 103) + ' ' + convert(varchar(20), O.FECHA_ANULA, 108), 1, 16) FECHA_ANULA						
						,O.MOTIVO_ANULA
						,O.COD_USUARIO_ANULA	
						,INGRESO_USUARIO_DSCTO1  
						,INGRESO_USUARIO_DSCTO2	
						,case O.COD_ESTADO_ORDEN_COMPRA
							when ".self::K_ESTADO_ANULADA." then '' 
							else 'none'
						end TR_DISPLAY
						,(SELECT ISNULL(PORC_MODIFICA_PRECIO_OC, 0) FROM USUARIO WHERE COD_USUARIO = $cod_usuario) PORC_MODIFICA_PRECIO_OC_H	
						,O.COD_DOC	
						,O.TIPO_ORDEN_COMPRA
						,O.AUTORIZADA
						, dbo.f_last_mod('NOM_USUARIO', 'ORDEN_COMPRA', 'AUTORIZADA', O.COD_ORDEN_COMPRA) USUARIO_AUTORIZA
                        , dbo.f_last_mod('FECHA_CAMBIO', 'ORDEN_COMPRA', 'AUTORIZADA', O.COD_ORDEN_COMPRA) FECHA_AUTORIZA
                        , 'none' TR_AUTORIZADA_TE
						,convert(numeric(10),(TOTAL_NETO_ORIGINAL * 0.2) + TOTAL_NETO_ORIGINAL) MAXIMO_PRECIO_OC_H --20%
						, 'none' TR_MODIF_20_PORC
						, AUTORIZADA_20_PROC
						, dbo.f_last_mod('NOM_USUARIO', 'ORDEN_COMPRA', 'AUTORIZADA_20_PROC', O.COD_ORDEN_COMPRA) USUARIO_AUTORIZA_20_PROC
                        , dbo.f_last_mod('FECHA_CAMBIO', 'ORDEN_COMPRA', 'AUTORIZADA_20_PROC', O.COD_ORDEN_COMPRA) FECHA_AUTORIZA_20_PROC
                        ,O.AUTORIZA_FACTURACION
                        ,CONVERT(VARCHAR, O.FECHA_SOLICITA_FACTURACION, 103) FECHA_SOLICITA_FACTURACION
                        ,USUARIO_AUTORIZA_MONTO_COMPRA
                        ,FECHA_AUTORIZA_MONTO_COMPRA
                        ,AUTORIZA_MONTO_COMPRA
                        ,(SELECT NOM_USUARIO FROM USUARIO WHERE COD_USUARIO = USUARIO_AUTORIZA_MONTO_COMPRA) AUT_MONTO_NOM_USUARIO
                        ,CONVERT(VARCHAR, FECHA_AUTORIZA_MONTO_COMPRA, 103) +' '+ CONVERT(VARCHAR, FECHA_AUTORIZA_MONTO_COMPRA, 108) FECHA_AUTORIZA_MONTO_COMPRA
                        ,CASE
                        	WHEN AUTORIZA_MONTO_COMPRA <> 'S' OR AUTORIZA_MONTO_COMPRA IS NULL THEN 'none'
                        	ELSE ''
                        END DISPLAY_AUT_MONTO_COMPRA	
                FROM 	ORDEN_COMPRA O, USUARIO U, EMPRESA E, ESTADO_ORDEN_COMPRA EOC
				WHERE	O.COD_ORDEN_COMPRA = {KEY1} and
						U.COD_USUARIO = O.COD_USUARIO AND
						E.COD_EMPRESA = O.COD_EMPRESA AND
						EOC.COD_ESTADO_ORDEN_COMPRA = O.COD_ESTADO_ORDEN_COMPRA";
							
		////////////////////
		// tab orden_compra
		parent::dw_help_empresa($sql, '', false, false, 'P');	// El último parametro indica que solo acepta proveedores
		
		// DATOS GENERALES
		//autorizacion de OC modifica 
		$this->add_control(new static_text('AUT_MONTO_NOM_USUARIO'));
		$this->add_control(new static_text('FECHA_AUTORIZA_MONTO_COMPRA'));
		$this->add_control($control = new edit_check_box('AUTORIZA_MONTO_COMPRA', 'S', 'N'));
		$control->set_onChange("display_aut_monto();");
		$this->add_control(new edit_check_box('AUTORIZADA', 'S', 'N'));
		$this->add_control($control = new edit_check_box('AUTORIZA_FACTURACION', 'S', 'N'));
		$control->set_onChange("valida_aut_facturacion();");
		$this->add_control($control = new edit_date('FECHA_SOLICITA_FACTURACION'));
		$control->set_onChange("valida_aut_facturacion();");
        $this->add_control(new static_text('FECHA_AUTORIZA'));
        $this->add_control(new static_text('USUARIO_AUTORIZA'));
        $this->add_control(new static_text('FECHA_ORDEN_COMPRA'));
        //que no se pueda modificar mas alla de un % (de parámetro = 20 % incial)
        $this->add_control(new edit_text('MAXIMO_PRECIO_OC_H',10,10, 'hidden'));
        $this->add_control(new edit_check_box('AUTORIZADA_20_PROC', 'S', 'N'));
        
		$this->add_control(new edit_nro_doc('COD_ORDEN_COMPRA','ORDEN_COMPRA'));
		
		$sql_usuario			= "select	COD_USUARIO
											,NOM_USUARIO
									from	USUARIO	
									WHERE	ES_SOLICITANTE_OC = 'S'
									and 	VENDEDOR_VISIBLE_FILTRO = 1 
									order by	NOM_USUARIO asc";									
		$this->add_control(new drop_down_dw('COD_USUARIO_SOLICITA',$sql_usuario,145));
		$sql_moneda 			= "	select			COD_MONEDA
													,NOM_MONEDA
													,ORDEN
									from 			MONEDA
									order by		ORDEN";								
		$this->add_control(new drop_down_dw('COD_MONEDA', $sql_moneda,145));
		$this->add_control(new edit_text('COD_ESTADO_ORDEN_COMPRA',10,10, 'hidden'));
		$this->add_control(new edit_text('COD_ESTADO_ORDEN_COMPRA_H',10,10, 'hidden'));
		$this->add_control(new static_text('NOM_ESTADO_ORDEN_COMPRA'));
		
		$this->add_control(new edit_text_upper('REFERENCIA',60,150));			
		$this->add_control(new edit_num('NRO_ORDEN_COMPRA_4D',27,27,0,true,false,false));
		
		$sql_cta_cte 			= " select 			COD_CUENTA_CORRIENTE
													,NOM_CUENTA_CORRIENTE
													,ORDEN
									from			CUENTA_CORRIENTE
									order by		ORDEN";											
		$this->add_control(new drop_down_dw('COD_CUENTA_CORRIENTE',$sql_cta_cte,145));
		
		$sql_cta_cte_nom		= " select 			COD_CUENTA_CORRIENTE
													,NRO_CUENTA_CORRIENTE
													,ORDEN
									from			CUENTA_CORRIENTE
									order by		ORDEN";											
		$this->add_control(new drop_down_dw('NRO_CUENTA_CORRIENTE',$sql_cta_cte_nom,150));
		
		/* VMC, 24-01-2011, SP solicita que las OC de Loreto Leiva sea obligatorio que le agregue la NV
		 * el problema es que normalmente las OC de Loreto ella no sabe a que nv son o estas estan cerradas
		 */
		//if($this->cod_usuario == 38) //loreto leiva
		//	$this->add_control(new static_num('COD_NOTA_VENTA'));
		//else{
			$this->add_control($control = new edit_num('COD_NOTA_VENTA'));
			$control->set_onChange("existe_nv(this);");
			$control->con_separador_miles = false;			
		//}
		
		$this->add_control(new edit_text('PORC_MODIFICA_PRECIO_OC_H',10, 100, 'hidden'));
		
		// usuario anulación
		$sql = "select 	COD_USUARIO
						,NOM_USUARIO
				from USUARIO";
								
		$this->add_control(new drop_down_dw('COD_USUARIO_ANULA',$sql,150));	
		$this->set_entrable('COD_USUARIO_ANULA', false);
		
		// asigna los mandatorys/
		$this->set_mandatory('COD_USUARIO_SOLICITA', 'Solicitante');
		$this->set_mandatory('COD_MONEDA', 'Moneda');
		$this->set_mandatory('COD_ESTADO_ORDEN_COMPRA', 'un Estado');
		$this->set_mandatory('COD_CUENTA_CORRIENTE', 'Cuenta Corriente');
		$this->set_mandatory('NRO_CUENTA_CORRI ENTE', 'Nro. Cuenta Corriente');		
		$this->set_mandatory('REFERENCIA', 'Referencia');
			
		$this->add_control(new edit_num('VALIDEZ_OFERTA',2,2));
		$this->add_control(new edit_text_upper('GARANTIA',109,140));
		$this->add_control(new edit_text_multiline('OBS',54,4));
		
		// asigna los mandatorys
		// ?
		//consultar la funcion que comple
		$this->set_mandatory('VALIDEZ_OFERTA', 'Validez Oferta');
		$this->set_mandatory('GARANTIA', 'Garantía');	
	}
}

class drop_down_iva_oc_negativo extends drop_down_iva  {
	const K_PARAM_IVA = 1;
	const K_PARAM_BH = 2;
	
	function drop_down_iva_oc_negativo() {
		// No se llama al ancestro directo drop_down_iva
		$porc_iva = $this->get_parametro(self::K_PARAM_IVA);
		$porc_iva = number_format($porc_iva, 1, ',', '.');

		$porc_bh = $this->get_parametro(self::K_PARAM_BH);
		$porc_bh = number_format( - $porc_bh, 1, ',', '.');
		parent::drop_down_list('PORC_IVA',array($porc_iva,$porc_bh,0),array($porc_iva,$porc_bh,'0'),52);
	}
}

class drop_down_iva_oc extends drop_down_iva  {
	const K_PARAM_IVA = 1;
	const K_PARAM_BH = 2;
	
	function drop_down_iva_oc() {
		// No se llama al ancestro directo drop_down_iva
		$porc_iva = $this->get_parametro(self::K_PARAM_IVA);
		$porc_iva = number_format($porc_iva, 1, ',', '.');

		parent::drop_down_list('PORC_IVA',array($porc_iva,0),array($porc_iva,'0'),52);
	}
}
class wi_orden_compra_base extends w_cot_nv {
	const K_ESTADO_EMITIDA 	= 1;
	const K_ESTADO_ANULADA  = 2;
	const K_ESTADO_CERRADA	= 3;
	const K_MONEDA			= 1;
	const K_NV_CERRADA		= 2;
	
	const K_PARAM_NOM_EMPRESA        =6;
	const K_PARAM_RUT_EMPRESA        =20;
	const K_PARAM_DIR_EMPRESA        =10;
	const K_PARAM_TEL_EMPRESA        =11;
	const K_PARAM_FAX_EMPRESA        =12;
	const K_PARAM_MAIL_EMPRESA       =13;
	const K_PARAM_CIUDAD_EMPRESA     =14;
	const K_PARAM_PAIS_EMPRESA       =15;
	const K_PARAM_GIRO_EMPRESA       =21;
	const K_PARAM_SITIO_WEB_EMPRESA  =25;
	const K_AUTORIZA_PORC_NEGATIVO	 = '991505';
	const K_AUTORIZA_ANULACION_OC	 = '991510';
	const K_AUTORIZA_CAMBIO_NV_BACKCHARGE	 = '991520';
	const K_AUTORIZA_TE_OC	 = '991525';
	const K_AUTORIZA_20_PORC_OC	 = '991530';

	var $wo_inf_oc_por_facturar_tdnx = false;
	
	function wi_orden_compra_base($cod_item_menu) {
		
		if (session::is_set('DESDE_wo_inf_oc_por_facturar_tdnx')) {
			session::un_set('DESDE_wo_inf_oc_por_facturar_tdnx');
			$this->desde_wo_inf_oc_por_facturar_tdnx = true;
		}
		if (session::is_set('DESDE_wo_inf_oc_por_facturar_bodega')) {
			session::un_set('DESDE_wo_inf_oc_por_facturar_bodega');
			$this->desde_wo_inf_oc_por_facturar_bodega = true;
		}
		if (session::is_set('DESDE_wo_inf_oc_por_facturar_servindus')) {
			session::un_set('DESDE_wo_inf_oc_por_facturar_servindus');
			$this->desde_wo_inf_oc_por_facturar_serv = true;
		}
		
		parent::w_cot_nv('orden_compra', $cod_item_menu);
		
		$this->dws['dw_orden_compra'] = new dw_orden_compra();
		$this->add_controls_cot_nv();
		
		// DATAWINDOWS NCPROV_FAPROV
		$this->dws['dw_item_orden_compra'] = new dw_item_orden_compra();
		
		//PAGO ORDEN_COMPRA
		$this->dws['dw_pago_orden_compra'] = new dw_pago_orden_compra();
		
		//PAGO DOCS
		$this->dws['dw_pago_docs'] = new dw_pago_docs();
		
		//prueba
		$this->dws['dw_pago_docs_link'] = new dw_pago_docs_link();
		
		$this->add_auditoria_relacionada('ITEM_ORDEN_COMPRA', 'COD_PRODUCTO');		
		$this->add_auditoria_relacionada('ITEM_ORDEN_COMPRA', 'CANTIDAD');		
		$this->add_auditoria_relacionada('ITEM_ORDEN_COMPRA', 'PRECIO');		
		
		//auditoria Solicitado por IS.
		$this->add_auditoria('COD_EMPRESA');
		$this->add_auditoria('COD_USUARIO_SOLICITA');
		$this->add_auditoria('COD_ESTADO_ORDEN_COMPRA');
		$this->add_auditoria('COD_NOTA_VENTA');
		$this->add_auditoria('COD_SUCURSAL');
		$this->add_auditoria('COD_PERSONA');
		$this->add_auditoria('COD_CUENTA_CORRIENTE');
		
		//autoriza_OC
		$this->add_auditoria('AUTORIZADA');
		$this->add_auditoria('AUTORIZADA_20_PROC');
	
		// VMC, 23-01-2011 se hace obligatorio el nro de NV
		// asigna los mandatorys
		$this->dws['dw_orden_compra']->set_mandatory('COD_NOTA_VENTA', 'Nota de Venta');	
		
		//al momento de crear un nuevo registro se iniciara en rut de empresa proveedor
		$this->set_first_focus('RUT');
		
		$priv = $this->get_privilegio_opcion_usuario('991550', $this->cod_usuario);
		if($priv=='E')
			$this->dws['dw_orden_compra']->set_entrable('AUTORIZA_MONTO_COMPRA', true);
      	else
			$this->dws['dw_orden_compra']->set_entrable('AUTORIZA_MONTO_COMPRA', false);
	}
	////////////////////
	// funciones auxiliares para cuando se accede a la FA desde_wo_inf_oc_por_facturar_tdnx
	function load_wo() {
		if ($this->desde_wo_inf_oc_por_facturar_tdnx)
			$this->wo = session::get("wo_inf_oc_por_facturar_tdnx");
		else if ($this->desde_wo_inf_oc_por_facturar_bodega)
			$this->wo = session::get("wo_inf_oc_por_facturar_bodega");
		else if($this->desde_wo_inf_oc_por_facturar_serv)
			$this->wo = session::get("wo_inf_oc_por_facturar_servindus");
		else
			parent::load_wo();
	}
	function get_url_wo() {
		if ($this->desde_wo_inf_oc_por_facturar_tdnx) 
			return $this->root_url.'appl/inf_oc_por_facturar_tdnx/wo_inf_oc_por_facturar_tdnx.php';
		else if ($this->desde_wo_inf_oc_por_facturar_bodega) 
			return $this->root_url.'appl/inf_oc_por_facturar_bodega/wo_inf_oc_por_facturar_bodega.php';
		else if ($this->desde_wo_inf_oc_por_facturar_serv) 
			return $this->root_url.'appl/inf_oc_por_facturar_servindus/wo_inf_oc_por_facturar_servindus.php';	
		else
			return parent::get_url_wo();
	}
	////////////////////
	function add_controls_cot_nv() {
		parent::add_controls_cot_nv();
		
		//Se reasigna dropdownIVA para agregar el -10% de BH
		//valida si el usuario puede ingresar porcentajes negativos en OC.
		$priv = $this->get_privilegio_opcion_usuario(self::K_AUTORIZA_PORC_NEGATIVO, $this->cod_usuario);
		if ($priv=='E') {
			$this->dws[$this->dw_tabla]->add_control(new drop_down_iva_oc_negativo());
      	}
      	else {
			$this->dws[$this->dw_tabla]->add_control(new drop_down_iva_oc());
      	}
		$this->dws[$this->dw_tabla]->set_computed('MONTO_IVA', '[TOTAL_NETO] * [PORC_IVA] / 100');
		$this->dws[$this->dw_tabla]->set_computed('TOTAL_CON_IVA', '[TOTAL_NETO] + [MONTO_IVA]');
	}
	
	function new_record() {
		$this->dws['dw_orden_compra']->insert_row();
		$this->dws['dw_orden_compra']->set_item(0, 'TR_DISPLAY', 'none');
		$this->dws['dw_orden_compra']->set_item(0, 'FECHA_ORDEN_COMPRA', substr($this->current_date_time(), 0, 16));
		$this->dws['dw_orden_compra']->set_item(0, 'COD_USUARIO', $this->cod_usuario);
		$this->dws['dw_orden_compra']->set_item(0, 'NOM_USUARIO', $this->nom_usuario);
		//*** el 1er tab no aparece el nom_estado... ¿lo dejamos asi?
		$this->dws['dw_orden_compra']->set_item(0, 'COD_ESTADO_ORDEN_COMPRA', self::K_ESTADO_EMITIDA);
		$this->dws['dw_orden_compra']->set_item(0, 'COD_ESTADO_ORDEN_COMPRA_H', self::K_ESTADO_EMITIDA);
		$this->dws['dw_orden_compra']->add_control(new edit_text('COD_ESTADO_ORDEN_COMPRA',10,10, 'hidden'));
		$this->dws['dw_orden_compra']->set_item(0, 'NOM_ESTADO_ORDEN_COMPRA', 'EMITIDA');		
		$this->dws['dw_orden_compra']->set_entrable('COD_ESTADO_ORDEN_COMPRA', false);
		$this->dws['dw_orden_compra']->set_item(0, 'COD_MONEDA', self::K_MONEDA);
		
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$sql = "SELECT ISNULL(PORC_MODIFICA_PRECIO_OC, 0) PORC_MODIFICA_PRECIO_OC_H FROM USUARIO WHERE COD_USUARIO = $this->cod_usuario";
		$result = $db->build_results($sql);		
		$porc_modifica_precio = $result[0]['PORC_MODIFICA_PRECIO_OC_H'];
		$this->dws['dw_orden_compra']->set_item(0, 'PORC_MODIFICA_PRECIO_OC_H', $porc_modifica_precio);
		$this->dws['dw_orden_compra']->set_item(0, 'TR_AUTORIZADA_TE', 'none');
		$this->dws['dw_orden_compra']->set_item(0, 'TR_MODIF_20_PORC', 'none');
		
		$sql_usuario			= "select	COD_USUARIO
											,NOM_USUARIO
									from	USUARIO	
									WHERE	ES_SOLICITANTE_OC = 'S'
									and 	VENDEDOR_VISIBLE_FILTRO = 1 
									order by	NOM_USUARIO asc";	
					
		unset($this->dws['dw_orden_compra']->controls['COD_USUARIO_SOLICITA']);
		$this->dws['dw_orden_compra']->add_control(new drop_down_dw('COD_USUARIO_SOLICITA',$sql_usuario,150));
		
		//que todas este autorizadas hasta vuelta de vacaciones
		$this->dws['dw_orden_compra']->set_item(0, 'AUTORIZADA', 'S');
		$this->dws['dw_orden_compra']->set_item(0, 'AUTORIZADA_20_PROC', 'S');
		//que todas este autorizadas hasta vuelta de vacaciones
		
		$this->dws['dw_orden_compra']->set_item(0, 'DISPLAY_AUT_MONTO_COMPRA', 'none');
		$this->dws['dw_orden_compra']->set_item(0, 'AUT_MONTO_NOM_USUARIO', $this->nom_usuario);
		$this->dws['dw_orden_compra']->set_item(0, 'FECHA_AUTORIZA_MONTO_COMPRA', $this->current_date_time());
	}
	
	function load_record() {
		$cod_orden_compra = $this->get_item_wo($this->current_record, 'COD_ORDEN_COMPRA');
		$this->dws['dw_orden_compra']->retrieve($cod_orden_compra);
		$this->dws['dw_pago_docs']->retrieve($cod_orden_compra);
		$this->dws['dw_pago_docs_link']->retrieve($cod_orden_compra);		
		//*********VMC, deberia ser codigo generico ???
		$cod_empresa = $this->dws['dw_orden_compra']->get_item(0, 'COD_EMPRESA');
		$this->dws['dw_orden_compra']->controls['COD_SUCURSAL_FACTURA']->retrieve($cod_empresa);
		$this->dws['dw_orden_compra']->controls['COD_PERSONA']->retrieve($cod_empresa);
		$COD_ESTADO_ORDEN_COMPRA = $this->dws['dw_orden_compra']->get_item(0, 'COD_ESTADO_ORDEN_COMPRA');
		
		if ($this->desde_wo_inf_oc_por_facturar_tdnx 
			|| $this->desde_wo_inf_oc_por_facturar_bodega
				|| $this->desde_wo_inf_oc_por_facturar_serv){
			$this->b_print_visible 	 = false;
			$this->b_no_save_visible = false;
			$this->b_save_visible 	 = false;
			$this->b_modify_visible	 = false;
			$this->b_delete_visible  = false;
		}else{
			$this->b_print_visible 	 = true;
			$this->b_no_save_visible = true;
			$this->b_save_visible 	 = true;
			$this->b_modify_visible	 = true;
			$this->b_delete_visible  = true;
		}		
		//el nro NV nunca es modificable ESTO SE CAMBIA POR SOLICITUD DE SP y MH
		$priv = $this->get_privilegio_opcion_usuario(self::K_AUTORIZA_CAMBIO_NV_BACKCHARGE, $this->cod_usuario);
		if ($priv=='E') {
			$this->dws['dw_orden_compra']->set_entrable('COD_NOTA_VENTA', true);
		}
		else {
			$this->dws['dw_orden_compra']->set_entrable('COD_NOTA_VENTA', false);
		}
		
		$this->dws['dw_orden_compra']->set_item(0, 'TR_AUTORIZADA_TE', 'none');
		$this->dws['dw_orden_compra']->set_item(0, 'TR_MODIF_20_PORC', 'none');
		//autoriza_oc con TE mayor al precio venta
		/*$autoriza_oc_te = $this->dws['dw_orden_compra']->get_item(0, 'AUTORIZADA');
		$autoriza_oc_usuario = $this->dws['dw_orden_compra']->get_item(0, 'USUARIO_AUTORIZA');
		$autoriza_oc_fecha = $this->dws['dw_orden_compra']->get_item(0, 'FECHA_AUTORIZA');
		$fecha_oc = $this->dws['dw_orden_compra']->get_item(0, 'FECHA_ORDEN_COMPRA');
		$autorizada_20_proc = $this->dws['dw_orden_compra']->get_item(0, 'AUTORIZADA_20_PROC');
		
		$this->dws['dw_orden_compra']->set_entrable('AUTORIZADA', false);
		$priv_te_oc = $this->get_privilegio_opcion_usuario(self::K_AUTORIZA_TE_OC, $this->cod_usuario);
		if (($priv_te_oc=='E') AND ($autoriza_oc_te == 'N')) {
			$this->dws['dw_orden_compra']->set_entrable('AUTORIZADA', true);
		}
		
		if(($autoriza_oc_te == 'S') AND  ($autoriza_oc_usuario == '') AND ($autoriza_oc_fecha == '')){
			$this->dws['dw_orden_compra']->set_item(0, 'TR_AUTORIZADA_TE', 'none');
			$this->dws['dw_orden_compra']->set_item(0, 'USUARIO_AUTORIZA', 'SISTEMA BIGGI');
			$this->dws['dw_orden_compra']->set_item(0, 'FECHA_AUTORIZA', $fecha_oc);
		}else if($autoriza_oc_te == 'N'){
			$this->dws['dw_orden_compra']->set_item(0, 'TR_AUTORIZADA_TE', '');
			$this->dws['dw_orden_compra']->set_item(0, 'USUARIO_AUTORIZA', '');
			$this->dws['dw_orden_compra']->set_item(0, 'FECHA_AUTORIZA', '');
		}else{
			$this->dws['dw_orden_compra']->set_item(0, 'TR_AUTORIZADA_TE', '');
		}
		
		$this->b_print_visible 	 = true;
		if(($autoriza_oc_te == 'N') OR  ($autorizada_20_proc == 'N')){
			$this->b_print_visible 	 = false;
		}*/

		//privilegio que se pueda modificar mas alla de un % (de parámetro = 20 % incial) 
		$this->dws['dw_orden_compra']->set_entrable('AUTORIZADA_20_PROC', false);
		$priv = $this->get_privilegio_opcion_usuario(self::K_AUTORIZA_20_PORC_OC, $this->cod_usuario);
		if ($priv=='E') {
			$this->dws['dw_orden_compra']->set_item(0, 'TR_MODIF_20_PORC', '');
			if ($autorizada_20_proc == 'N') {
				$this->dws['dw_orden_compra']->set_entrable('AUTORIZADA_20_PROC', true);	
			}
		}

		$cod_usuario_solicita = $this->dws['dw_orden_compra']->get_item(0, 'COD_USUARIO_SOLICITA');
		$sql_usuario			= "select	COD_USUARIO
											,NOM_USUARIO
									from	USUARIO	
									WHERE	ES_SOLICITANTE_OC = 'S'
									and 	(VENDEDOR_VISIBLE_FILTRO = 1 
									or COD_USUARIO = $cod_usuario_solicita)
									order by	NOM_USUARIO asc";	
					
		unset($this->dws['dw_orden_compra']->controls['COD_USUARIO_SOLICITA']);
		$this->dws['dw_orden_compra']->add_control(new drop_down_dw('COD_USUARIO_SOLICITA',$sql_usuario,150));
		
		if ($COD_ESTADO_ORDEN_COMPRA == self::K_ESTADO_EMITIDA) {

			$sql = "select 	COD_ESTADO_ORDEN_COMPRA
							,NOM_ESTADO_ORDEN_COMPRA
							,ORDEN
					from ESTADO_ORDEN_COMPRA
					where COD_ESTADO_ORDEN_COMPRA = ".self::K_ESTADO_EMITIDA." or
							COD_ESTADO_ORDEN_COMPRA = ".self::K_ESTADO_ANULADA."
					order by COD_ESTADO_ORDEN_COMPRA";
					
			unset($this->dws['dw_orden_compra']->controls['COD_ESTADO_ORDEN_COMPRA']);
			$this->dws['dw_orden_compra']->add_control($control = new drop_down_dw('COD_ESTADO_ORDEN_COMPRA',$sql,150));	
			$control->set_onChange("mostrarOcultar_Anula(this);");
			$this->dws['dw_orden_compra']->controls['NOM_ESTADO_ORDEN_COMPRA']->type = 'hidden';
				$priv = $this->get_privilegio_opcion_usuario(self::K_AUTORIZA_ANULACION_OC, $this->cod_usuario);
				if ($priv=='E') {
					$this->dws['dw_orden_compra']->set_entrable('COD_ESTADO_ORDEN_COMPRA', true);
				}
				else {
					$this->dws['dw_orden_compra']->set_entrable('COD_ESTADO_ORDEN_COMPRA', false);
				}

			$this->dws['dw_orden_compra']->add_control(new edit_text_upper('MOTIVO_ANULA',110, 100));
			$this->b_delete_visible  = false;
		}
		else if ($COD_ESTADO_ORDEN_COMPRA== self::K_ESTADO_ANULADA) {
			$this->b_print_visible 	 = false;
			$this->b_no_save_visible = false;
			$this->b_save_visible 	 = false;
			$this->b_modify_visible  = false;
			$this->b_delete_visible  = false;	
			
		}
		else if ($COD_ESTADO_ORDEN_COMPRA== self::K_ESTADO_CERRADA) {
			$this->b_no_save_visible = false;
			$this->b_save_visible 	 = false;
			$this->b_modify_visible  = false;
			$this->b_delete_visible  = false;	
		}
		$this->dws['dw_item_orden_compra']->retrieve($cod_orden_compra);
		
		// PAGO ORDEN_COMPRA
		$this->dws['dw_pago_orden_compra']->retrieve($cod_orden_compra);

		// el nro NV nunca es modificable ESTO SE CAMBIA POR SOLICITUD DE SP y MH		
		//$this->dws['dw_orden_compra']->remove_control('COD_NOTA_VENTA');
		//$this->dws['dw_orden_compra']->add_control(new static_text('COD_NOTA_VENTA'));
		
		$autoriza_monto_compra = $this->dws['dw_orden_compra']->get_item(0, 'AUTORIZA_MONTO_COMPRA');
		if($autoriza_monto_compra == 'S')
			$this->dws['dw_orden_compra']->set_entrable('AUTORIZA_MONTO_COMPRA', false);

		$aut_monto_compra = $this->dws['dw_orden_compra']->get_item(0, 'AUTORIZA_MONTO_COMPRA');
			
		if($aut_monto_compra <> 'S'){
			$this->dws['dw_orden_compra']->set_item(0, 'AUT_MONTO_NOM_USUARIO', $this->nom_usuario);
			$this->dws['dw_orden_compra']->set_item(0, 'FECHA_AUTORIZA_MONTO_COMPRA', $this->current_date_time());
		}
		//$this->add_control(new static_text('AUT_MONTO_NOM_USUARIO'));
		//$this->add_control(new static_text('FECHA_AUTORIZA_MONTO_COMPRA'));
	}
	
	function get_key() {
		return $this->dws['dw_orden_compra']->get_item(0, 'COD_ORDEN_COMPRA');
	}
	
	function save_record($db) {	
		$cod_orden_compra 	= $this->get_key();		
		$cod_usuario 		= $this->dws['dw_orden_compra']->get_item(0, 'COD_USUARIO');
		$cod_usuario_sol 	= $this->dws['dw_orden_compra']->get_item(0, 'COD_USUARIO_SOLICITA');		
		$cod_moneda			= $this->dws['dw_orden_compra']->get_item(0, 'COD_MONEDA');		
		$cod_est_oc			= $this->dws['dw_orden_compra']->get_item(0, 'COD_ESTADO_ORDEN_COMPRA');
		$cod_nota_venta		= $this->dws['dw_orden_compra']->get_item(0, 'COD_NOTA_VENTA');
		$cod_nota_venta		= ($cod_nota_venta =='') ? "null" : "$cod_nota_venta";
		
		$cod_cta_cte		= $this->dws['dw_orden_compra']->get_item(0, 'COD_CUENTA_CORRIENTE');	 	
		$referencia			= $this->dws['dw_orden_compra']->get_item(0, 'REFERENCIA');
		$referencia 		= str_replace("'", "''", $referencia);
		
		$nro_orden_compra_4d		= $this->dws['dw_orden_compra']->get_item(0, 'NRO_ORDEN_COMPRA_4D');
		$nro_orden_compra_4d		= ($nro_orden_compra_4d =='') ? "null" : "$nro_orden_compra_4d";
		
		$cod_empresa		= $this->dws['dw_orden_compra']->get_item(0, 'COD_EMPRESA');
		$cod_suc_factura	= $this->dws['dw_orden_compra']->get_item(0, 'COD_SUCURSAL_FACTURA');
		$cod_persona		= $this->dws['dw_orden_compra']->get_item(0, 'COD_PERSONA');
		$cod_persona		= ($cod_persona =='') ? "null" : "$cod_persona";			
		$sub_total			= $this->dws['dw_orden_compra']->get_item(0, 'SUM_TOTAL');
		$sub_total      	= ($sub_total =='') ? 0 : "$sub_total";
		
		$porc_descto1		= $this->dws['dw_orden_compra']->get_item(0, 'PORC_DSCTO1');
		$porc_descto1		= ($porc_descto1 =='') ? "null" : "$porc_descto1";
				
		$monto_dscto1		= $this->dws['dw_orden_compra']->get_item(0, 'MONTO_DSCTO1');
		$monto_dscto1		= ($monto_dscto1 =='') ? 0 : "$monto_dscto1";
		
		$porc_descto2		= $this->dws['dw_orden_compra']->get_item(0, 'PORC_DSCTO2');
		$porc_descto2		= ($porc_descto2 =='') ? "null" : "$porc_descto2";
				
		$monto_dscto2		= $this->dws['dw_orden_compra']->get_item(0, 'MONTO_DSCTO2');
		$monto_dscto2		= ($monto_dscto2 =='') ? 0 : "$monto_dscto2";
		
		$total_neto			= $this->dws['dw_orden_compra']->get_item(0, 'TOTAL_NETO');
		$total_neto			= ($total_neto =='') ? 0 : "$total_neto";
		
		$porc_iva			= $this->dws['dw_orden_compra']->get_item(0, 'PORC_IVA');		
		
		$monto_iva			= $this->dws['dw_orden_compra']->get_item(0, 'MONTO_IVA');
		$monto_iva			= ($monto_iva =='') ? 0 : "$monto_iva";
		
		$total_con_iva		= $this->dws['dw_orden_compra']->get_item(0, 'TOTAL_CON_IVA');
		$total_con_iva		= ($total_con_iva =='') ? 0 : "$total_con_iva";
				
		$obs				= $this->dws['dw_orden_compra']->get_item(0, 'OBS');
		$obs		 		= str_replace("'", "''", $obs);
		$obs				= ($obs =='') ? "null" : "'$obs'";
							// NOTA: para el manejo de fecha se debe pasar un string dd/mm/yyyy y en el sp llamar a to_date ber eje en spi_orden_trabajo
		$motivo_anula		= $this->dws['dw_orden_compra']->get_item(0, 'MOTIVO_ANULA');
		$motivo_anula		= str_replace("'", "''", $motivo_anula);
		$motivo_anula		= ($motivo_anula =='') ? "null" : "'$motivo_anula'";
		
		$cod_user_anula		= $this->dws['dw_orden_compra']->get_item(0, 'COD_USUARIO_ANULA');
		$autorizada			= $this->dws['dw_orden_compra']->get_item(0, 'AUTORIZADA');
		$autorizada_20_proc	= $this->dws['dw_orden_compra']->get_item(0, 'AUTORIZADA_20_PROC');
		
		$autoriza_facturacion			= $this->dws['dw_orden_compra']->get_item(0, 'AUTORIZA_FACTURACION');
		$fecha_solicita_autorizacion	= $this->dws['dw_orden_compra']->get_item(0, 'FECHA_SOLICITA_FACTURACION');
		
		//si volvio a modificar la OC se vuelve a estado Por Autorizar
		/*if (!$this->is_new_record()) {
			$maximo_precio_oc_h = $this->dws['dw_orden_compra']->get_item(0, 'MAXIMO_PRECIO_OC_H');
			$maximo_precio_oc_h	= ($maximo_precio_oc_h =='') ? 0 : "$maximo_precio_oc_h";
			
			$sql = "select AUTORIZADA_20_PROC
							,TOTAL_NETO
						from orden_compra
						where cod_orden_compra = $cod_orden_compra";
			$result = $db->build_results($sql);		
			$autorizada_20_proc_old = $result[0]['AUTORIZADA_20_PROC'];
			$total_neto_old = $result[0]['TOTAL_NETO'];
			
			if($total_neto <> $total_neto_old){
				if($total_neto > $maximo_precio_oc_h){
					if($autorizada_20_proc_old == 'S'){
						$autorizada_20_proc = 'N';
					}
				}else{
					if($autorizada_20_proc_old == 'N'){
						$autorizada_20_proc = 'S';
					}
				}
			}
		}*/
		//si volvio a modificar la OC se vuelve a estado Por Autorizar
		
		if (($motivo_anula!= '') && ($cod_user_anula == '')) // se anula 
			$cod_user_anula			= $this->cod_usuario;
		else
			$cod_usuario_anula			= "null";
		
		$ingreso_usuario_dscto1 = $this->dws['dw_orden_compra']->get_item(0, 'INGRESO_USUARIO_DSCTO1');;
		$ingreso_usuario_dscto1 = ($ingreso_usuario_dscto1 =='') ? "null" : "'$ingreso_usuario_dscto1'";
		
		
		$ingreso_usuario_dscto2 = $this->dws['dw_orden_compra']->get_item(0, 'INGRESO_USUARIO_DSCTO2');;
		$ingreso_usuario_dscto2 = ($ingreso_usuario_dscto2 =='') ? "null" : "'$ingreso_usuario_dscto2'";
		
		$autoriza_facturacion			= ($autoriza_facturacion =='') ? "null" : "'$autoriza_facturacion'";
		$fecha_solicita_autorizacion	= ($fecha_solicita_autorizacion =='') ? "null" : $this->str2date($fecha_solicita_autorizacion);

		$tipo_orden_compra = $this->dws['dw_orden_compra']->get_item(0, 'TIPO_ORDEN_COMPRA');
		$cod_doc = $this->dws['dw_orden_compra']->get_item(0, 'COD_DOC');;
		$cod_doc = ($cod_doc=='') ? "null" : $cod_doc;
		
		$autoriza_monto_compra = $this->dws['dw_orden_compra']->get_item(0, 'AUTORIZA_MONTO_COMPRA');
		$autoriza_monto_compra = ($autoriza_monto_compra=='') ? "null" : $autoriza_monto_compra;		

		$cod_orden_compra = ($cod_orden_compra=='') ? "null" : $cod_orden_compra;		
    
		$sp = 'spu_orden_compra';
	    if ($this->is_new_record()) {
	    	$operacion = 'INSERT';

			$sql = "select COD_ESTADO_NOTA_VENTA
					from NOTA_VENTA
					where COD_NOTA_VENTA = $cod_nota_venta";
			$result = $db->build_results($sql);		
			$cod_estado_nota_venta = $result[0]['COD_ESTADO_NOTA_VENTA'];
			if ($cod_estado_nota_venta==self::K_NV_CERRADA)
				$tipo_orden_compra = 'BACKCHARGE';
			else 
				$tipo_orden_compra = 'NOTA_VENTA';
	    }
	    else
	    	$operacion = 'UPDATE';
	    
	
		$param	= "'$operacion'
					,$cod_orden_compra				
					,$cod_usuario 		
					,$cod_usuario_sol 									
					,$cod_moneda		
					,$cod_est_oc
					,$cod_nota_venta			
					,$cod_cta_cte
					,'$referencia'																						
					,$cod_empresa		
					,$cod_suc_factura	
					,$cod_persona			
					,$sub_total		
					,$porc_descto1		
					,$monto_dscto1		
					,$porc_descto2		
					,$monto_dscto2		
					,$total_neto		
					,$porc_iva		
					,$monto_iva		
					,$total_con_iva				
					,$obs
					,$motivo_anula
					,$cod_user_anula
					,$ingreso_usuario_dscto1
					,$ingreso_usuario_dscto2
					,$tipo_orden_compra
					,$cod_doc
					,$autorizada
					,$autorizada_20_proc
					,$nro_orden_compra_4d
					,NULL -- gf_plano
					,$autoriza_facturacion
					,$fecha_solicita_autorizacion
					,$autoriza_monto_compra
					,".$this->cod_usuario;
			
		if ($db->EXECUTE_SP($sp, $param)){
			if ($this->is_new_record()) {
				$cod_orden_compra = $db->GET_IDENTITY();
				$this->dws['dw_orden_compra']->set_item(0, 'COD_ORDEN_COMPRA', $cod_orden_compra);
			}
			 for ($i=0; $i<$this->dws['dw_item_orden_compra']->row_count(); $i++)
				$this->dws['dw_item_orden_compra']->set_item($i, 'COD_ORDEN_COMPRA', $this->dws['dw_orden_compra']->get_item(0, 'COD_ORDEN_COMPRA'), 'primary', false);				
			 if (!$this->dws['dw_item_orden_compra']->update($db))			
			 	return false;			
			
			$parametros_sp = "'RECALCULA',$cod_orden_compra";	
			if (!$db->EXECUTE_SP('spu_orden_compra', $parametros_sp))
				return false;

			$parametros_sp = "'item_orden_compra','orden_compra',$cod_orden_compra";
			if (!$db->EXECUTE_SP('sp_orden_no_parametricas', $parametros_sp))
				return false;
			
			return true;
		}	
		return false;				
	}
	
	function print_record() {
	$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
	$cod_orden_compra = $this->get_key();
	$sql= "SELECT OC.COD_ORDEN_COMPRA,
				OC.COD_NOTA_VENTA,
				OC.SUBTOTAL,
				OC.PORC_DSCTO1,
				OC.MONTO_DSCTO1,
				OC.PORC_DSCTO2,
				OC.MONTO_DSCTO2,
				OC.TOTAL_NETO,
				OC.PORC_IVA,
				OC.MONTO_IVA,
				OC.TOTAL_CON_IVA,
				OC.REFERENCIA,																
				OC.OBS,
				E.NOM_EMPRESA,
				E.RUT,
				E.DIG_VERIF,
				dbo.f_get_direccion('SUCURSAL', OC.COD_SUCURSAL, '[DIRECCION] [NOM_COMUNA] [NOM_CIUDAD]') DIRECCION,
				dbo.f_format_date(OC.FECHA_ORDEN_COMPRA, 3) FECHA_ORDEN_COMPRA,	
				S.TELEFONO,
				S.FAX,
				P.NOM_PERSONA,
				U.NOM_USUARIO,
				U.MAIL,
				IOC.NOM_PRODUCTO,
				case IOC.COD_PRODUCTO
					when 'T' then ''
					else IOC.COD_PRODUCTO
				end COD_PRODUCTO,
				case IOC.COD_PRODUCTO
					when 'T' then ''
					else IOC.ITEM
				end ITEM,
				case IOC.COD_PRODUCTO
					when 'T' then ''
					else IOC.CANTIDAD
				end CANTIDAD,
				case IOC.COD_PRODUCTO
					when 'T' then ''
					else IOC.PRECIO
				end PRECIO,
				case IOC.COD_PRODUCTO		
					when 'T' then ''
					else IOC.CANTIDAD * IOC.PRECIO
				end TOTAL_IOC,			
				M.SIMBOLO,
				dbo.f_get_parametro(".self::K_PARAM_NOM_EMPRESA.") NOM_EMPRESA_EMISOR,
				dbo.f_get_parametro(".self::K_PARAM_RUT_EMPRESA.") RUT_EMPRESA,
				dbo.f_get_parametro(".self::K_PARAM_DIR_EMPRESA.") DIR_EMPRESA,
				dbo.f_get_parametro(".self::K_PARAM_GIRO_EMPRESA.") GIRO_EMPRESA,
				dbo.f_get_parametro(".self::K_PARAM_TEL_EMPRESA.") TEL_EMPRESA,	
				dbo.f_get_parametro(".self::K_PARAM_FAX_EMPRESA.") FAX_EMPRESA,
				dbo.f_get_parametro(".self::K_PARAM_MAIL_EMPRESA.") MAIL_EMPRESA,
				dbo.f_get_parametro(".self::K_PARAM_CIUDAD_EMPRESA.") CIUDAD_EMPRESA,
				dbo.f_get_parametro(".self::K_PARAM_PAIS_EMPRESA.") PAIS_EMPRESA,
				dbo.f_get_parametro(".self::K_PARAM_SITIO_WEB_EMPRESA.") SITIO_WEB_EMPRESA
		FROM    ORDEN_COMPRA OC LEFT OUTER JOIN PERSONA P ON  OC.COD_PERSONA = P.COD_PERSONA,
				ITEM_ORDEN_COMPRA IOC, EMPRESA E, SUCURSAL S, USUARIO U, MONEDA M
		WHERE   OC.COD_ORDEN_COMPRA = $cod_orden_compra
		AND		E.COD_EMPRESA = OC.COD_EMPRESA 
		AND		S.COD_SUCURSAL = OC.COD_SUCURSAL 
		AND		U.COD_USUARIO = OC.COD_USUARIO_SOLICITA 
		AND		IOC.COD_ORDEN_COMPRA = OC.COD_ORDEN_COMPRA 
		AND		M.COD_MONEDA = OC.COD_MONEDA";
	$result_sql = $db->build_results($sql);
	//reporte
	$labels = array();
	$labels['strCOD_ORDEN_COMPRA'] = $cod_orden_compra;
	$labels['strFECHA_ORDEN_COMPRA'] = $result_sql[0]['FECHA_ORDEN_COMPRA'];
	$rpt = new print_reporte($sql, $this->root_dir.'appl/orden_compra/orden_compra.xml', $labels, "Orden de Compra ".$cod_orden_compra, 1);
	$this->redraw();
	}
}

/////////////////////////////////////////////////////////////
// Se separa por K_CLIENTE
$file_name = dirname(__FILE__)."/".K_CLIENTE."/class_wi_orden_compra.php";
if (file_exists($file_name)) 
	require_once($file_name);
else {
	class wi_orden_compra extends wi_orden_compra_base {
		function wi_orden_compra($cod_item_menu) {
			parent::wi_orden_compra_base($cod_item_menu); 
		}
	}
}?>