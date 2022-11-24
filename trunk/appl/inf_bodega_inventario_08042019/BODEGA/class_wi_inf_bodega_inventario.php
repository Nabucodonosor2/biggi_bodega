<?php
class wi_inf_bodega_inventario extends wi_inf_bodega_inventario_base {
	function wi_inf_bodega_inventario($cod_item_menu) {
		$xml = session::get('K_ROOT_DIR').'appl/inf_bodega_inventario/inf_bodega_inventario.xml';
		parent::w_param_informe_biggi('inf_bodega_inventario', $cod_item_menu, 'Inventario no Valorizado.pdf', $xml, '', 'spi_bodega_inventario');

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