<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../common_appl/class_w_output_biggi.php");

class wo_bodega extends w_output_biggi{
   
	function wo_bodega(){   	
      	$sql = "SELECT	B.COD_BODEGA
						,B.NOM_BODEGA
						,TB.NOM_TIPO_BODEGA
						,A.COD_ARRIENDO
				FROM BODEGA B left outer join ARRIENDO A on A.COD_BODEGA = B.COD_BODEGA, TIPO_BODEGA TB
				WHERE B.COD_TIPO_BODEGA = TB.COD_TIPO_BODEGA
				ORDER BY COD_BODEGA"; 
			
      parent::w_output_biggi('bodega', $sql, $_REQUEST['cod_item_menu']);
      
      // headers
      $this->add_header(new header_num('COD_BODEGA', 'COD_BODEGA', 'Código'));
      $this->add_header(new header_text('NOM_BODEGA', 'NOM_BODEGA', 'Bodega'));
      $this->add_header(new header_num('COD_ARRIENDO', 'COD_ARRIENDO', 'Código Arriendo'));
      $sql_tipo_bodega = "select COD_TIPO_BODEGA, NOM_TIPO_BODEGA from TIPO_BODEGA order by COD_TIPO_BODEGA";
      $this->add_header($header = new header_drop_down('NOM_TIPO_BODEGA', 'TB.COD_TIPO_BODEGA', 'Tipo', $sql_tipo_bodega));

		// Filtro inicial
		$header->valor_filtro = '1';	// bodega NORMAL
		$this->make_filtros();
	}
}
?>