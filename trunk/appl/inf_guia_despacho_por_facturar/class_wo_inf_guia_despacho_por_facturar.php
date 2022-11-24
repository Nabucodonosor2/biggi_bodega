<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

class wo_inf_guia_despacho_por_facturar extends w_informe_pantalla {
   function wo_inf_guia_despacho_por_facturar() {
   		// Construye el resultado del informe en un tabla AUXILIA de INFORME
   		$cod_usuario = session::get("COD_USUARIO");;
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
   		$db->EXECUTE_SP("spi_gd_por_facturar", "$cod_usuario"); 
   		
   		$sql = "select I.NRO_GUIA_DESPACHO
   						,I.FECHA_GUIA_DESPACHO
						,convert(varchar, I.FECHA_GUIA_DESPACHO, 103) FECHA_GUIA_DESPACHO_STR
						,I.RUT
						,I.DIG_VERIF
						,I.NOM_EMPRESA
						,I.COD_NOTA_VENTA
						,I.COD_NOTA_VENTA COD_NOTA_VENTA_H
						,I.FECHA_NOTA_VENTA
						,convert(varchar, I.FECHA_NOTA_VENTA, 103) FECHA_NOTA_VENTA_STR
						,GD.COD_TIPO_GUIA_DESPACHO
						,TGD.NOM_TIPO_GUIA_DESPACHO
				from INF_GD_POR_FACTURAR I, GUIA_DESPACHO GD, TIPO_GUIA_DESPACHO TGD
				where I.COD_USUARIO = $cod_usuario
				and  I.FECHA_GUIA_DESPACHO >= '01/01/2013'
				and I.COD_GUIA_DESPACHO = GD.COD_GUIA_DESPACHO
				and GD.COD_TIPO_GUIA_DESPACHO = TGD.COD_TIPO_GUIA_DESPACHO
				order by I.FECHA_GUIA_DESPACHO";
		
		parent::w_informe_pantalla('inf_guia_despacho_por_facturar', $sql, $_REQUEST['cod_item_menu']);
		
		// headers
		$this->add_header(new header_num('NRO_GUIA_DESPACHO', 'I.NRO_GUIA_DESPACHO', 'Número'));
		$this->add_header(new header_date('FECHA_GUIA_DESPACHO_STR', 'I.FECHA_GUIA_DESPACHO', 'Fecha'));
		$this->add_header(new header_rut('RUT', 'I', 'Rut'));
		$this->add_header(new header_text('NOM_EMPRESA', "I.NOM_EMPRESA", 'Cliente'));
		$this->add_header(new header_num('COD_NOTA_VENTA', 'I.COD_NOTA_VENTA', 'NV'));
		$this->add_header(new header_date('FECHA_NOTA_VENTA_STR', 'I.FECHA_NOTA_VENTA', 'Fecha NV'));
		$sql_tipo_guia_despacho = "select COD_TIPO_GUIA_DESPACHO ,NOM_TIPO_GUIA_DESPACHO from TIPO_GUIA_DESPACHO order by	COD_TIPO_GUIA_DESPACHO";
		$this->add_header(new header_drop_down('NOM_TIPO_GUIA_DESPACHO', 'TGD.COD_TIPO_GUIA_DESPACHO', 'Tipo Guía', $sql_tipo_guia_despacho));

		// controls
		$this->dw->add_control(new static_num('RUT'));
		$this->dw->add_control(new static_link('COD_NOTA_VENTA', '../../../../commonlib/trunk/php/link_wi.php?modulo_origen=inf_guia_despacho_por_facturar&modulo_destino=nota_venta&cod_modulo_destino=[COD_NOTA_VENTA]&cod_item_menu=1510'));
		$this->dw->add_control(new edit_text_hidden('COD_NOTA_VENTA_H'));
		
		$this->row_per_page = 500;
   }
	function print_informe() {
		// reporte
		$sql = $this->dw->get_sql();
		$xml = session::get('K_ROOT_DIR').'appl/inf_guia_despacho_por_facturar/inf_guia_despacho_por_facturar.xml';
		$labels = array();
		$labels['str_fecha'] = $this->current_date();
		$labels['str_filtro'] = $this->nom_filtro;
		$rpt = new reporte($sql, $xml, $labels, "GD por facturar.pdf", true);

		$this->_redraw();
	}
	function detalle_record($rec_no) {
		session::set('DESDE_wo_factura', 'desde output');	// para indicar que viene del output
		session::set('DESDE_wo_inf_facturas_por_cobrar', 'true');
		$ROOT = $this->root_url;
		$url = $ROOT.'appl/factura';
		header ('Location:'.$url.'/wi_factura.php?rec_no='.$rec_no.'&cod_item_menu=1535');
	}
   	function _redraw() {
		parent::_redraw();
		
		if (session::is_set('ULTIMA_NV_CONSULTADA')) {
			$cod_nota_venta = session::get('ULTIMA_NV_CONSULTADA');
			session::un_set('ULTIMA_NV_CONSULTADA');
			print '<script type="text/javascript">haga_scroll('.$cod_nota_venta.');</script>';
		}
	}
}
?>