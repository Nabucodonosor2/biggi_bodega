<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

class wo_inf_facturas_por_mes extends w_informe_pantalla {
   function wo_inf_facturas_por_mes() {
   		$this->b_print_visible = false;
   		/* El cod_usuario debe leerese desde la sesion porque no se puede usar $this->cod_usuario
   		   hasta despues de llamar al ancestro
   		 */  
   		$cod_usuario =  session::get("COD_USUARIO");
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
				FROM FACTURA F
				WHERE dbo.f_get_tiene_acceso(".$cod_usuario.", 'FACTURA',F.COD_USUARIO_VENDEDOR1, F.COD_USUARIO_VENDEDOR2) = 1 
				ORDER BY F.NRO_FACTURA";
		
		parent::w_informe_pantalla('inf_facturas_por_mes', $sql, $_REQUEST['cod_item_menu']);
		
		// headers
		$this->add_header($h_mes = new header_mes('MES', 'MONTH(F.FECHA_FACTURA)', 'Mes'));
		$h_mes->field_bd_order = 'MES';
		$this->add_header($h_ano = new header_num('ANO', 'year(F.FECHA_FACTURA)', 'Año'));
		$this->add_header(new header_num('NRO_FACTURA', 'F.NRO_FACTURA', 'Número'));
		$this->add_header($control = new header_date('FECHA_FACTURA', 'F.FECHA_FACTURA', 'Fecha'));
		$control->field_bd_order = 'DATE_FACTURA';
		$this->add_header(new header_text('NOM_EMPRESA', "F.NOM_EMPRESA", 'Cliente'));
		$this->add_header(new header_num('TOTAL_NETO', 'F.TOTAL_NETO', 'Neto', 0, true, 'SUM'));
		$this->add_header(new header_num('MONTO_IVA', 'F.MONTO_IVA', 'Iva', 0, true, 'SUM'));
		$this->add_header(new header_num('TOTAL_CON_IVA', 'F.TOTAL_CON_IVA', 'Total', 0, true, 'SUM'));
		$this->add_header(new header_num('SALDO', 'dbo.f_fa_saldo(F.COD_FACTURA)', 'Saldo', 0, true, 'SUM'));
		$this->add_header(new header_num('CANTIDAD_FA', '1', '', 0, true, 'SUM'));

		// controls
		$this->dw->add_control(new static_num('TOTAL_NETO'));
		$this->dw->add_control(new static_num('MONTO_IVA'));
		$this->dw->add_control(new static_num('TOTAL_CON_IVA'));
		$this->dw->add_control(new static_num('SALDO'));

		// Filtro inicial
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$h_mes->valor_filtro = $db->current_month();
		$h_ano->valor_filtro = $db->current_year();
		
		$this->make_filtros();	// filtro incial
   }
	function print_informe() {
		// reporte
		$sql = $this->dw->get_sql();
		$xml = session::get('K_ROOT_DIR').'appl/inf_facturas_por_cobrar/inf_facturas_por_cobrar_global.xml';
		$labels = array();
		$labels['str_fecha'] = $this->current_date();
		$labels['str_filtro'] = $this->nom_filtro;
		$rpt = new reporte($sql, $xml, $labels, "Facturas por cobrar", true);

		$this->_redraw();
	}
	function detalle_record($rec_no) {
		session::set('DESDE_wo_factura', 'desde output');	// para indicar que viene del output
		session::set('DESDE_wo_inf_facturas_por_mes', 'true');
		$ROOT = $this->root_url;
		$url = $ROOT.'appl/factura';
		header ('Location:'.$url.'/wi_factura.php?rec_no='.$rec_no.'&cod_item_menu=1535');
	}
}
?>