<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../common_appl/class_w_output_biggi.php");

class wo_empresa extends w_output_biggi
{
	function wo_empresa() {		
		$this->b_add_visible  = true;
		
		$sql="select 	E.COD_EMPRESA,
						E.RUT,
						E.DIG_VERIF,
						E.ALIAS,
						E.NOM_EMPRESA,
						E.GIRO,
						dbo.f_emp_nom_ciudad(E.COD_EMPRESA) NOM_CIUDAD
			from 		EMPRESA E 
			order by 	E.COD_EMPRESA";
			
		parent::w_output_biggi('empresa', $sql, $_REQUEST['cod_item_menu']);
		
		//formato numeros
		$this->dw->add_control(new static_num('RUT'));
		
		// headers
		$this->add_header(new header_num('COD_EMPRESA', 'COD_EMPRESA', 'Código'));
		$this->add_header(new header_text('NOM_EMPRESA', 'NOM_EMPRESA', 'Razón Social'));
		$this->add_header(new header_rut('RUT', 'E', 'Rut'));
		$this->add_header(new header_text('ALIAS', 'ALIAS', 'Alias'));
		$this->add_header(new header_text('GIRO', 'GIRO', 'Giro'));     
		$this->add_header($control = new header_text('NOM_CIUDAD', 'dbo.f_emp_nom_ciudad(E.COD_EMPRESA)', 'Ciudad'));
		$control->field_bd_order = 'NOM_CIUDAD';
	}
}
?>