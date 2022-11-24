<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../common_appl/class_w_output_biggi.php");

class wo_ncprov extends w_output_biggi {
   	function wo_ncprov() {
		$sql = "SELECT	N.COD_NCPROV
						,convert(varchar(20), FECHA_REGISTRO, 103) FECHA_REGISTRO
						,FECHA_REGISTRO DATE_REGISTRO
						,E.NOM_EMPRESA
						,RUT
						,DIG_VERIF
						,EN.NOM_ESTADO_NCPROV
						,dbo.f_ncprov_get_oc(N.COD_NCPROV) OC_PART
						,NRO_NCPROV
						,convert(varchar(20), FECHA_NCPROV, 103) FECHA_NCPROV
						,FECHA_NCPROV DATE_NC
						,TOTAL_NETO	
				FROM	NCPROV N, ESTADO_NCPROV EN, EMPRESA E
				WHERE	EN.COD_ESTADO_NCPROV = N.COD_ESTADO_NCPROV AND
	 					E.COD_EMPRESA = N.COD_EMPRESA
						order by COD_NCPROV desc";
			
   		parent::w_output_biggi('ncprov', $sql, $_REQUEST['cod_item_menu']);
	
		$this->dw->add_control(new edit_precio('TOTAL_NETO'));
		$this->dw->add_control(new static_num('RUT'));
		
		// headers
		$this->add_header(new header_num('COD_NCPROV', 'N.COD_NCPROV', 'Código'));
		$this->add_header($control = new header_date('FECHA_REGISTRO','FECHA_REGISTRO', 'Fecha'));
		$control->field_bd_order = 'DATE_REGISTRO';
		$this->add_header(new header_rut('RUT', 'E', 'Rut'));
		$this->add_header(new header_text('NOM_EMPRESA', 'E.NOM_EMPRESA', 'Proveedor'));
		
		$this->add_header($control = new header_text('OC_PART', '(SELECT dbo.f_ncprov_get_oc(N.COD_NCPROV))', 'OC/PART'));
		$control->field_bd_order = "OC_PART";		
		
		$sql_estado_ncprov = "select COD_ESTADO_NCPROV, NOM_ESTADO_NCPROV from ESTADO_NCPROV order by COD_ESTADO_NCPROV";
		$this->add_header($control =  new header_drop_down('NOM_ESTADO_NCPROV', 'EN.COD_ESTADO_NCPROV', 'Estado', $sql_estado_ncprov));
		$control->field_bd_order = "NOM_ESTADO_NCPROV";	
		$this->add_header(new header_num('NRO_NCPROV', 'NRO_NCPROV', 'Nº Doc.')); 
		$this->add_header($control = new header_date('FECHA_NCPROV', 'FECHA_NCPROV', 'Fecha Doc.'));
		$control->field_bd_order = 'DATE_NC';
		$this->add_header(new header_num('TOTAL_NETO', 'TOTAL_NETO', 'Total Neto'));  
   	}
}
?>