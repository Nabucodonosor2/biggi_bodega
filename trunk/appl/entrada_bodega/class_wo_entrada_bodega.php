<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../common_appl/class_w_output_biggi.php");
require_once(dirname(__FILE__)."/../../appl.ini");

class wo_entrada_bodega_base extends w_output_biggi {
   function wo_entrada_bodega_base() {      
		$sql = "SELECT	EB.COD_ENTRADA_BODEGA,
						convert(varchar(20), EB.FECHA_ENTRADA_BODEGA, 103) FECHA_ENTRADA_BODEGA,
						EB.FECHA_ENTRADA_BODEGA DATE_ENTADA_BODEGA,
						EB.COD_BODEGA,
						U.NOM_USUARIO,
						B.NOM_BODEGA,
						EB.TIPO_DOC,
						dbo.f_get_nro_doc(EB.TIPO_DOC, EB.COD_DOC) NRO_DOC,
						EB.REFERENCIA
				FROM	ENTRADA_BODEGA EB, USUARIO U, BODEGA B
				WHERE	EB.COD_USUARIO = U.COD_USUARIO
				AND		EB.COD_BODEGA = B.COD_BODEGA
				ORDER BY EB.COD_ENTRADA_BODEGA DESC";
			
		parent::w_output_biggi('entrada_bodega', $sql, $_REQUEST['cod_item_menu']);
		      
		// headers
		$this->add_header(new header_num('COD_ENTRADA_BODEGA', 'COD_ENTRADA_BODEGA', 'Código'));
		$this->add_header($control = new header_date('FECHA_ENTRADA_BODEGA', 'FECHA_ENTRADA_BODEGA', 'Fecha'));
		$control->field_bd_order = "DATE_ENTADA_BODEGA";
		$this->add_header(new header_text('NOM_USUARIO', 'NOM_USUARIO', 'Usuario'));
		$this->add_header(new header_text('NOM_BODEGA', 'NOM_BODEGA', 'Bodega'));
		$this->add_header(new header_text('TIPO_DOC', 'TIPO_DOC', 'Tipo Docto.'));
		$this->add_header($control = new header_num('NRO_DOC', 'dbo.f_get_nro_doc(EB.TIPO_DOC, EB.COD_DOC)', 'N° Docto.'));
		$control->field_bd_order = "NRO_DOC";
   }
}

// Se separa por K_CLIENTE
$file_name = dirname(__FILE__)."/".K_CLIENTE."/class_wo_entrada_bodega.php";
if (file_exists($file_name)) 
	require_once($file_name);
else {
	class wo_entrada_bodega extends wo_entrada_bodega_base {
		function wo_entrada_bodega() {
			parent::wo_entrada_bodega_base(); 
		}
	}
}
?>