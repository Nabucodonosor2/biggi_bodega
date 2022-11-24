<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../common_appl/class_w_param_informe_biggi.php");
require_once(dirname(__FILE__)."/../common_appl/class_reporte_biggi.php");
require_once(dirname(__FILE__)."/../../appl.ini");

class wi_inf_bodega_inventario_base extends w_param_informe_biggi {
	
	function wi_inf_bodega_inventario_base($cod_item_menu) {
		$xml = session::get('K_ROOT_DIR').'appl/inf_bodega_inventario/inf_bodega_inventario.xml';
		parent::w_param_informe_biggi('inf_bodega_inventario', $cod_item_menu, 'Inventario Sala Venta.pdf', $xml, '', 'spi_bodega_inventario');

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
	
	function make_filtro() {
		$fecha 				= $this->dws['dw_param']->get_item(0, 'FECHA'); 
		$cod_bodega			= $this->dws['dw_param']->get_item(0, 'COD_BODEGA');  
		$nom_bodega 		= $this->dws['dw_param']->controls['COD_BODEGA']->get_label_from_value($cod_bodega);
		
		//FILTRO
		$this->filtro = "Fecha = $fecha \n";
		$this->filtro .= "Bodega = $nom_bodega";	
		

		// Arma los paramaetros para el SP
		$fecha = $this->str2date($fecha, '23:59:59');
		$this->param = "$cod_bodega, $fecha";	
	}
}


$file_name = dirname(__FILE__)."/".K_CLIENTE."/class_wi_inf_bodega_inventario.php";
if (file_exists($file_name)) {
	require_once($file_name);
}
else {	
	class wi_inf_bodega_inventario extends wi_inf_bodega_inventario_base {
		function wi_inf_bodega_inventario($cod_item_menu) {
			parent::wi_inf_bodega_inventario_base($cod_item_menu); 
		}
	}
}
?>