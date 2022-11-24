<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../common_appl/class_w_param_informe_biggi.php");
require_once(dirname(__FILE__)."/../common_appl/class_reporte_biggi.php");


class wi_inf_mantencion_sw extends w_param_informe_biggi {
	const K_APROBADO = 90;
	
	function wi_inf_mantencion_sw($cod_item_menu) {
		$xml = session::get('K_ROOT_DIR').'appl/inf_mantencion_sw/inf_mantencion_sw.xml';
		parent::w_param_informe_biggi('inf_mantencion_sw', $cod_item_menu, 'Mantenciones'.".pdf", $xml, '', 'spi_mantencion_sw');

		// del 1ero del mes hasta hoy
		$sql = "select  convert(varchar, dateadd(day, 1 - day(getdate()), getdate()), 103) FECHA_INICIO
						,convert(varchar, getdate(), 103) FECHA_TERMINO
						,0 COD_USUARIO
						,".self::K_APROBADO." COD_ESTADO_SOLUCION_SW";
		$this->dws['dw_param'] = new datawindow($sql);
		
		$this->dws['dw_param']->add_control(new edit_date('FECHA_INICIO'));
		$this->dws['dw_param']->add_control(new edit_date('FECHA_TERMINO'));
		$sql = "select 0 COD_USUARIO
						,'Todos' NOM_USUARIO
				union 
				select COD_USUARIO
						,NOM_USUARIO
				from USUARIO
				where COD_USUARIO <> 1
				order by COD_USUARIO";
		$this->dws['dw_param']->add_control(new drop_down_dw('COD_USUARIO', $sql));
		$sql = "select COD_ESTADO_SOLUCION_SW
						,NOM_ESTADO_SOLUCION_SW
				from ESTADO_SOLUCION_SW
				order by COD_ESTADO_SOLUCION_SW";
		$this->dws['dw_param']->add_control(new drop_down_dw('COD_ESTADO_SOLUCION_SW', $sql));
		
		// mandatorys		
		$this->dws['dw_param']->set_mandatory('FECHA_INICIO', 'Fecha Inicio');	
		$this->dws['dw_param']->set_mandatory('FECHA_TERMINO', 'Fecha Termino');	
		$this->dws['dw_param']->set_mandatory('COD_USUARIO', 'Solicitante');	
		$this->dws['dw_param']->set_mandatory('COD_ESTADO_SOLUCION_SW', 'Estado');	
	}
	function make_filtro() {
		$fecha_inicio 			= $this->dws['dw_param']->get_item(0, 'FECHA_INICIO'); 
		$fecha_termino			= $this->dws['dw_param']->get_item(0, 'FECHA_TERMINO'); 
		$cod_usuario			= $this->dws['dw_param']->get_item(0, 'COD_USUARIO'); 
		$cod_estado_solucion_sw	= $this->dws['dw_param']->get_item(0, 'COD_ESTADO_SOLUCION_SW'); 
		
		//FILTRO
		$this->filtro .= "Fecha Inicio = $fecha_inicio; ";	
		$this->filtro .= "Fecha Termino = $fecha_termino\n";
		if ($cod_usuario==0)
			$nom_usuario = 'Todos';
		else
			$nom_usuario = $this->dws['dw_param']->controls['COD_USUARIO']->get_label_from_value($cod_usuario);
		$this->filtro .= "Solicitante = $nom_usuario\n";
		$nom_estado = $this->dws['dw_param']->controls['COD_ESTADO_SOLUCION_SW']->get_label_from_value($cod_estado_solucion_sw);
		$this->filtro .= "Estado = $nom_estado\n";

		// Arma los paramaetros para el SP
		$fecha_inicio = $this->str2date($fecha_inicio);
		$fecha_termino = $this->str2date($fecha_termino, '23:59:59');
		
		$this->param = "$fecha_inicio, $fecha_termino, $cod_usuario, $cod_estado_solucion_sw";	
	}
}
?>