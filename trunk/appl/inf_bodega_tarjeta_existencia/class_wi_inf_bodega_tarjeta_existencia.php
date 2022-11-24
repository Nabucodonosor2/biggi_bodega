<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../common_appl/class_w_param_informe_biggi.php");
require_once(dirname(__FILE__)."/../common_appl/class_reporte_biggi.php");
require_once(dirname(__FILE__)."/../../appl.ini");

class wi_inf_bodega_tarjeta_existencia_base extends w_param_informe_biggi {	
	function wi_inf_bodega_tarjeta_existencia_base($cod_item_menu) {
		$xml = session::get('K_ROOT_DIR').'appl/inf_bodega_tarjeta_existencia/inf_bodega_tarjeta_existencia.xml';
		parent::w_param_informe_biggi('inf_bodega_tarjeta_existencia', $cod_item_menu, 'Tarjeta.pdf', $xml, '', 'spi_bodega_tarjeta_existencia');

		// del 1ero del mes hasta hoy
		$sql = "select  null COD_BODEGA
						,null COD_PRODUCTO
						,convert(varchar, dbo.f_makedate(1, 1, year(getdate())), 103) FECHA_INICIO
						,convert(varchar, getdate(), 103) FECHA_TERMINO";
		$this->dws['dw_param'] = new datawindow($sql);
		$sql_bodega="SELECT COD_BODEGA
							,NOM_BODEGA
					FROM BODEGA
					where COD_BODEGA = 2	-- eq terminado
					ORDER BY COD_BODEGA ASC";
		$this->dws['dw_param']->add_control(new drop_down_dw('COD_BODEGA', $sql_bodega, 0, '', false));
		$this->dws['dw_param']->add_control(new edit_text_upper('COD_PRODUCTO', 20, 20));
		$this->dws['dw_param']->add_control(new edit_date('FECHA_INICIO'));
		$this->dws['dw_param']->add_control(new edit_date('FECHA_TERMINO'));
		
		// mandatorys		
		$this->dws['dw_param']->set_mandatory('COD_BODEGA', 'Bodega');	
		$this->dws['dw_param']->set_mandatory('FECHA_INICIO', 'Fecha Inicio');	
		$this->dws['dw_param']->set_mandatory('FECHA_TERMINO', 'Fecha Termino');	
	}
	
	function make_filtro() {
		$cod_bodega			= $this->dws['dw_param']->get_item(0, 'COD_BODEGA');  
		$nom_bodega 		= $this->dws['dw_param']->controls['COD_BODEGA']->get_label_from_value($cod_bodega);
		$cod_producto		= $this->dws['dw_param']->get_item(0, 'COD_PRODUCTO');  
		$fecha_inicio		= $this->dws['dw_param']->get_item(0, 'FECHA_INICIO'); 
		$fecha_termino		= $this->dws['dw_param']->get_item(0, 'FECHA_TERMINO'); 
		
		//FILTRO
		$this->filtro = "Bodega = $nom_bodega; Equipo = $cod_producto \n";	
		$this->filtro .= "Fecha Inicio = $fecha_inicio; Fecha termino =  $fecha_termino";
		

		// Arma los paramaetros para el SP
		$fecha_inicio = $this->str2date($fecha_inicio);
		$fecha_termino = $this->str2date($fecha_termino, "23:59:59");
		$this->param = "$cod_bodega, '$cod_producto', $fecha_inicio, $fecha_termino";	
	}
}


$file_name = dirname(__FILE__)."/".K_CLIENTE."/class_wi_inf_bodega_tarjeta_existencia.php";
if (file_exists($file_name)) {
	require_once($file_name);
}
else {	
	class wi_inf_bodega_tarjeta_existencia extends wi_inf_bodega_tarjeta_existencia_base {
		function wi_inf_bodega_tarjeta_existencia($cod_item_menu) {
			parent::wi_inf_bodega_tarjeta_existencia_base($cod_item_menu); 
		}
	}
}
?>