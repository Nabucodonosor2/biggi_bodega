<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../common_appl/class_w_output_biggi.php");

class wo_guia_recepcion extends w_output_biggi {
   	function wo_guia_recepcion() {
		$sql = "SELECT	GR.COD_GUIA_RECEPCION
						,convert(varchar(20),GR.FECHA_GUIA_RECEPCION, 103)FECHA_GUIA_RECEPCION
						,GR.FECHA_GUIA_RECEPCION DATE_GUIA_RECEPCION
						,E.RUT
						,E.DIG_VERIF
						,E.NOM_EMPRESA
						,EGR.COD_ESTADO_GUIA_RECEPCION
						,EGR.NOM_ESTADO_GUIA_RECEPCION
						,TGR.COD_TIPO_GUIA_RECEPCION
						,TGR.NOM_TIPO_GUIA_RECEPCION
				FROM	GUIA_RECEPCION GR, EMPRESA E, ESTADO_GUIA_RECEPCION EGR, TIPO_GUIA_RECEPCION TGR
				WHERE	GR.COD_EMPRESA = E.COD_EMPRESA AND
						isnull(GR.TIPO_DOC,'') NOT IN ('ARRIENDO', 'MOD_ARRIENDO') AND
						EGR.COD_ESTADO_GUIA_RECEPCION = GR.COD_ESTADO_GUIA_RECEPCION AND
						TGR.COD_TIPO_GUIA_RECEPCION = GR.COD_TIPO_GUIA_RECEPCION
						order by COD_GUIA_RECEPCION desc";		
	
   		parent::w_output_biggi('guia_recepcion', $sql, $_REQUEST['cod_item_menu']);

		$this->dw->add_control(new edit_precio('MONTO_DOCUMENTO'));
		$this->dw->add_control(new static_num('RUT'));
		
		// headers
		$this->add_header(new header_num('COD_GUIA_RECEPCION', 'COD_GUIA_RECEPCION', 'Código'));
		$this->add_header($control = new header_date('FECHA_GUIA_RECEPCION', 'FECHA_GUIA_RECEPCION', 'Fecha'));
		$control->field_bd_order = 'DATE_GUIA_RECEPCION';
		$this->add_header(new header_rut('RUT', 'E', 'Rut'));
		$this->add_header(new header_text('NOM_EMPRESA', 'E.NOM_EMPRESA', 'Proveedor'));
		$sql_estado_gr = "select COD_ESTADO_GUIA_RECEPCION, NOM_ESTADO_GUIA_RECEPCION from ESTADO_GUIA_RECEPCION order by COD_ESTADO_GUIA_RECEPCION";
		$this->add_header(new header_drop_down('NOM_ESTADO_GUIA_RECEPCION', 'EGR.COD_ESTADO_GUIA_RECEPCION', 'Estado', $sql_estado_gr));
		$sql_tipo_gr = "select COD_TIPO_GUIA_RECEPCION, NOM_TIPO_GUIA_RECEPCION from TIPO_GUIA_RECEPCION order by COD_TIPO_GUIA_RECEPCION";
		$this->add_header(new header_drop_down('NOM_TIPO_GUIA_RECEPCION', 'TGR.COD_TIPO_GUIA_RECEPCION', 'Tipo Doc.', $sql_tipo_gr));
		}
}
?>