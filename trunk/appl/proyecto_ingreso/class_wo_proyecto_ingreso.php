<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../common_appl/class_w_parametrica_biggi.php");

class wo_proyecto_ingreso extends w_parametrica_biggi{
	function wo_proyecto_ingreso(){   	
		$sql = "SELECT	PIN.COD_PROYECTO_INGRESO
      					,PIN.NOM_PROYECTO_INGRESO
      					,PIN.ORDEN 	
      			FROM	PROYECTO_INGRESO PIN
				ORDER BY PIN.COD_PROYECTO_INGRESO DESC";
			
		parent::w_parametrica_biggi('proyecto_ingreso', $sql, $_REQUEST['cod_item_menu'], '1025');

      	// headers
		$this->add_header(new header_num('COD_PROYECTO_INGRESO', 'COD_PROYECTO_INGRESO', 'Código'));
		$this->add_header(new header_text('NOM_PROYECTO_INGRESO', 'NOM_PROYECTO_INGRESO', 'Proyecto Ingreso'));
		$this->add_header(new header_num('ORDEN', 'ORDEN', 'Orden'));
	}
}
?>