<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

class wo_inf_facturas_por_mes extends w_informe_pantalla {
   function wo_inf_facturas_por_mes() {
   		$this->b_print_visible = false;
		$ano = session::get("inf_facturas_por_mes.ANO");
		session::un_set("inf_facturas_por_mes.ANO");
   		/* El cod_usuario debe leerese desde la sesion porque no se puede usar $this->cod_usuario
   		   hasta despues de llamar al ancestro
   		 */  
   		$cod_usuario =  session::get("COD_USUARIO");
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
   		$db->EXECUTE_SP("spi_facturas_por_mes", "$cod_usuario");
   		/* 
		$sql = "select	F.COD_FACTURA
						,F.NRO_FACTURA
						,MONTH(F.FECHA_FACTURA) MES
						,year(F.FECHA_FACTURA) ANO
						,convert(varchar, F.FECHA_FACTURA, 103) FECHA_FACTURA
						,F.FECHA_FACTURA DATE_FACTURA
						,F.NOM_EMPRESA
						,F.TOTAL_NETO
						,F.MONTO_IVA
						,F.TOTAL_CON_IVA
						,dbo.f_fa_saldo(F.COD_FACTURA) SALDO
						,1 CANTIDAD_FA
						,U.INI_USUARIO
				FROM FACTURA F, USUARIO U
				WHERE dbo.f_get_tiene_acceso(".$cod_usuario.", 'FACTURA',F.COD_USUARIO_VENDEDOR1, F.COD_USUARIO_VENDEDOR2) = 1 
				  and F.COD_ESTADO_DOC_SII in (2,3)
				  and year(F.FECHA_FACTURA) = $ano
				  and U.COD_USUARIO = F.COD_USUARIO_VENDEDOR1
				ORDER BY DATE_FACTURA";
		*/

		$sql = "select	I.MES
					    ,I.TIPO_DOC                     
					    ,I.COD_DOC                      
					    ,I.NRO_DOC                      
						,convert(varchar, I.FECHA_DOC, 103) FECHA_DOC
						,I.FECHA_DOC DATE_DOC
						,I.NOM_EMPRESA
						,I.TOTAL_NETO
						,I.MONTO_IVA
						,I.TOTAL_CON_IVA
						,1 CANTIDAD
					    ,case I.TIPO_DOC when 'FA' then 1 else 0 end CANT_FA                      
					    ,case I.TIPO_DOC when 'NC' then 1 else 0 end CANT_NC                      
					    ,case I.TIPO_DOC when 'FA' then I.TOTAL_NETO else 0 end TOT_FA                      
					    ,case I.TIPO_DOC when 'NC' then I.TOTAL_NETO else 0 end TOT_NC                      
				FROM INF_FACTURAS_POR_MES I
				where I.COD_USUARIO = $cod_usuario
				  and I.ANO = $ano
				order by DATE_DOC, I.NRO_DOC";  
		
		parent::w_informe_pantalla('inf_facturas_por_mes', $sql, $_REQUEST['cod_item_menu']);
		
		// headers
		$this->add_header($h_mes = new header_mes('MES', 'I.MES', 'Mes'));
		$this->add_header($h_tipo_doc = new header_text('TIPO_DOC', 'I.TIPO_DOC', 'Tipo Doc'));
		$this->add_header(new header_num('NRO_DOC', 'I.NRO_DOC', 'Nro Doc'));
		$this->add_header($control = new header_date('FECHA_DOC', 'I.FECHA_DOC', 'Fecha'));
		$control->field_bd_order = 'I.DATE_DOC';
		$this->add_header(new header_text('NOM_EMPRESA', 'I.NOM_EMPRESA', 'Cliente'));
		$this->add_header(new header_num('TOTAL_NETO', 'I.TOTAL_NETO', 'Neto', 0, true, 'SUM'));
		$this->add_header(new header_num('MONTO_IVA', 'I.MONTO_IVA', 'Iva', 0, true, 'SUM'));
		$this->add_header(new header_num('TOTAL_CON_IVA', 'I.TOTAL_CON_IVA', 'Total', 0, true, 'SUM'));
		$this->add_header(new header_num('CANTIDAD', '1', 'Cant', 0, true, 'SUM'));

		$this->add_header(new header_num('CANT_FA', "case I.TIPO_DOC when 'FA' then 1 else 0 end", 'CANT_FA', 0, true, 'SUM'));
		$this->add_header(new header_num('CANT_NC', "case I.TIPO_DOC when 'NC' then 1 else 0 end", 'CANT_NC', 0, true, 'SUM'));
		$this->add_header(new header_num('TOT_FA', "case I.TIPO_DOC when 'FA' then I.TOTAL_NETO else 0 end", 'TOT_FA', 0, true, 'SUM'));
		$this->add_header(new header_num('TOT_NC', "case I.TIPO_DOC when 'NC' then I.TOTAL_NETO else 0 end", 'TOT_NC', 0, true, 'SUM'));


		// controls
		$this->dw->add_control(new static_num('TOTAL_NETO'));
		$this->dw->add_control(new static_num('MONTO_IVA'));
		$this->dw->add_control(new static_num('TOTAL_CON_IVA'));

		// Filtro inicial
		$mes_desde = session::get("inf_facturas_por_mes.MES_DESDE");
		$mes_hasta = session::get("inf_facturas_por_mes.MES_HASTA");
		session::un_set("inf_facturas_por_mes.MES_DESDE");
		session::un_set("inf_facturas_por_mes.MES_HASTA");
		$h_mes->valor_filtro = $mes_desde;
		$h_mes->valor_filtro2 = $mes_hasta;
		
		$check_FA = session::get("inf_facturas_por_mes.CHECK_FA");
		$check_NC = session::get("inf_facturas_por_mes.CHECK_NC");
		if ($check_FA=='S' && $check_NC=='N')
			$h_tipo_doc->valor_filtro = 'FA';
		else if ($check_FA=='N' && $check_NC=='S')
			$h_tipo_doc->valor_filtro = 'NC';
		
		$this->make_filtros();	// filtro incial
   }
	function print_informe() {
		// reporte
		$sql = $this->dw->get_sql();
		$xml = session::get('K_ROOT_DIR').'appl/inf_facturas_por_cobrar/inf_facturas_por_cobrar_global.xml';
		$labels = array();
		$labels['str_fecha'] = $this->current_date();
		$labels['str_filtro'] = $this->nom_filtro;
		$rpt = new reporte($sql, $xml, $labels, "Facturas por cobrar".".pdf", true);

		$this->_redraw();
	}
}
?>