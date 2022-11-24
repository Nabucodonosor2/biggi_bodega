<?php
//////////////////////////
///////BODEGA
///////////////////////////
class wi_inf_bodega_stock extends wi_inf_bodega_stock_base {
	function wi_inf_bodega_stock($cod_item_menu) {
		$xml = session::get('K_ROOT_DIR').'appl/inf_bodega_stock/inf_bodega_stock.xml';
		parent::w_param_informe_biggi('inf_bodega_stock', $cod_item_menu, 'Inventario Valorizado.pdf', $xml, '', 'spi_bodega_stock');

		// del 1ero del mes hasta hoy
		$sql = "select  '' COD_BODEGA
						,convert(varchar, getdate(), 103) FECHA";
		$this->dws['dw_param'] = new datawindow($sql);
		$sql_bodega="SELECT COD_BODEGA
							,NOM_BODEGA
					FROM BODEGA
					where COD_BODEGA = 2	-- eq terminado
					ORDER BY COD_BODEGA ASC";
		$this->dws['dw_param']->add_control(new drop_down_dw('COD_BODEGA', $sql_bodega, 0, '', false));
		$this->dws['dw_param']->add_control(new edit_date('FECHA'));
		
		// mandatorys		
		$this->dws['dw_param']->set_mandatory('COD_BODEGA', 'Bodega');	
		$this->dws['dw_param']->set_mandatory('FECHA', 'Fecha');
	}

}
?>