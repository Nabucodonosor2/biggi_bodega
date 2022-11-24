<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../common_appl/class_w_parametrica_biggi.php");

class wo_tipo_doc_pago extends w_parametrica_biggi
{
   function wo_tipo_doc_pago()
   {   	
      $sql = "select 	COD_TIPO_DOC_PAGO,
	               		NOM_TIPO_DOC_PAGO,			    
				   		ORDEN
				   		,TIPO_DOCUMENTO
			from 		TIPO_DOC_PAGO
			order by 	ORDEN";
			
      parent::w_parametrica_biggi('tipo_doc_pago', $sql, $_REQUEST['cod_item_menu'], '1025');
      
      // headers
      $this->add_header(new header_num('COD_TIPO_DOC_PAGO', 'COD_TIPO_DOC_PAGO', 'Código'));
      $this->add_header(new header_text('NOM_TIPO_DOC_PAGO', 'NOM_TIPO_DOC_PAGO', 'Nombre Documento'));
      $this->add_header(new header_num('ORDEN', 'ORDEN', 'Orden'));
      $this->add_header(new header_text('TIPO_DOCUMENTO', 'TIPO_DOCUMENTO', 'Nombre Corto Softland'));
   }
}
?>
