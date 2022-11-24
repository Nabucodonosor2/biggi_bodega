<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../common_appl/class_w_param_informe_biggi.php");
require_once(dirname(__FILE__)."/../common_appl/class_reporte_biggi.php");

class wi_inf_bodega_por_recibir extends w_param_informe_biggi {
	
	function wi_inf_bodega_por_recibir($cod_item_menu) {
		$xml = session::get('K_ROOT_DIR').'appl/inf_bodega_por_recibir/inf_bodega_por_recibir.xml';
		parent::w_param_informe_biggi('inf_bodega_por_recibir', $cod_item_menu, 'Bodega por Recibir.pdf', $xml, '', 'spi_bodega_por_recibir');

		// del 1ero del mes hasta hoy
		$sql = "select  '' R_SOLICITUD
						,'' TODAS
						,'' COD_SOLICITUD_COMPRA";
		$this->dws['dw_param'] = new datawindow($sql);
		$this->dws['dw_param']->add_control(new edit_text('COD_SOLICITUD_COMPRA',20,20));
		$this->dws['dw_param']->add_control($control = new edit_radio_button('R_SOLICITUD', '', '', 'Ingreso Solicitud', 'IMPRESION'));
		$control->set_onChange("checked_radio_button(this);");
		$this->dws['dw_param']->add_control($control = new edit_radio_button('TODAS', 'S', 'N', 'Todas', 'IMPRESION'));
		$control->set_onChange("checked_radio_button(this);");
	}
	
	function make_filtro() {
		$solicitud_compra = $this->dws['dw_param']->get_item(0, 'COD_SOLICITUD_COMPRA');
		$param_solicitud = '';
		
		// Arma los paramaetros para el SP
		if($solicitud_compra == ''){
			$solicitud_compra = 0;
			$param_solicitud =  'Todas';
		}
		else{
			$param_solicitud = $solicitud_compra;
		}
		
		//FILTRO
		$this->filtro .= "Fecha impresión = ".$this->current_date()."; ";		
		$this->filtro .= "Cod. Solicitud Compra = $param_solicitud";		

		$this->param = "$solicitud_compra";
	}
}
?>