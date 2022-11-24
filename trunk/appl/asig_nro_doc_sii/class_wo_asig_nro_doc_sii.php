<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../common_appl/class_w_output_biggi.php");

class wo_asig_nro_doc_sii extends w_output_biggi{
   
	function wo_asig_nro_doc_sii()   {   	
      
		$sql = "SELECT COD_ASIG_NRO_DOC_SII
				      ,convert(nvarchar, FECHA_ASIG, 103) FECHA_ASIG
				      ,FECHA_ASIG DATE_ASIG 	
				  	  ,NOM_TIPO_DOC_SII
				      ,NOM_USUARIO
				      ,TD.COD_TIPO_DOC_SII
				      ,U.COD_USUARIO
				      ,dbo.f_asig_cant_disponible(COD_ASIG_NRO_DOC_SII) CANT_DISPONIBLE	
				FROM ASIG_NRO_DOC_SII AN, TIPO_DOC_SII TD, USUARIO U
				WHERE AN.COD_TIPO_DOC_SII = TD.COD_TIPO_DOC_SII and
					AN.COD_USUARIO_RECEPTOR = U.COD_USUARIO
				ORDER BY COD_ASIG_NRO_DOC_SII DESC"; 
			
      parent::w_output_biggi('asig_nro_doc_sii', $sql, $_REQUEST['cod_item_menu']);
      
      // headers
      $this->add_header(new header_num('COD_ASIG_NRO_DOC_SII', 'COD_ASIG_NRO_DOC_SII', 'Código'));
      $this->add_header($control= new header_date('FECHA_ASIG', 'FECHA_ASIG', 'Fecha'));
      $control->field_bd_order = 'DATE_ASIG';
      $sql_tipo_doc = "select COD_TIPO_DOC_SII, NOM_TIPO_DOC_SII from TIPO_DOC_SII order by ORDEN";
      $this->add_header(new header_drop_down('NOM_TIPO_DOC_SII', 'TD.COD_TIPO_DOC_SII', 'Tipo Documento', $sql_tipo_doc));
      $sql_responsable = "select COD_USUARIO, NOM_USUARIO from USUARIO where AUTORIZA_INGRESO = 'S' order by COD_USUARIO";
      $this->add_header(new header_drop_down('NOM_USUARIO', 'U.COD_USUARIO', 'Responsable', $sql_responsable));
      $this->add_header($control = new header_num('CANT_DISPONIBLE', '(dbo.f_asig_cant_disponible(COD_ASIG_NRO_DOC_SII))', 'Disponible'));
   	  $control->field_bd_order = 'CANT_DISPONIBLE';
     
   }
}
?>