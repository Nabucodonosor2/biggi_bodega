<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

class wo_clasif_empresa extends w_parametrica
{
   function wo_clasif_empresa()
   {   	
      $sql = "select 	COD_CLASIF_EMPRESA,
              			NOM_CLASIF_EMPRESA,
			   			ORDEN 
	        from 		CLASIF_EMPRESA 
	        order by 	ORDEN"; 
				
      parent::w_parametrica('clasif_empresa', $sql, $_REQUEST['cod_item_menu'], '1025');
      
      // headers
      $this->add_header(new header_num('COD_CLASIF_EMPRESA', 'COD_CLASIF_EMPRESA', 'Código'));
      $this->add_header(new header_text('NOM_CLASIF_EMPRESA', 'NOM_CLASIF_EMPRESA', 'Descripción'));
      $this->add_header(new header_num('ORDEN', 'ORDEN', 'Orden'));
      
   }
}
?>