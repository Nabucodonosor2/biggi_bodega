<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

class wo_forma_pago extends w_parametrica
{
   function wo_forma_pago()
   {   	
      $sql = "select 		COD_FORMA_PAGO,
               				NOM_FORMA_PAGO,			    
			   				ORDEN,
			   				CANTIDAD_DOC
		        from 		FORMA_PAGO
				order by 	ORDEN"; 
			
      parent::w_parametrica('forma_pago', $sql, $_REQUEST['cod_item_menu'], '1025');
      
      // headers
      $this->add_header(new header_num('COD_FORMA_PAGO', 'COD_FORMA_PAGO', 'Código'));
      $this->add_header(new header_text('NOM_FORMA_PAGO', 'NOM_FORMA_PAGO', 'Forma de Pago'));
      $this->add_header(new header_num('CANTIDAD_DOC', 'CANTIDAD_DOC', 'Cantidad Documentos'));
      $this->add_header(new header_num('ORDEN', 'ORDEN', 'Orden'));
   }
}
?>