<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

class wo_ciudad extends w_parametrica
{
   function wo_ciudad()
   {   	
      $sql = "select	COD_CIUDAD,
        				NOM_CIUDAD
				from 	CIUDAD
				ORDER BY COD_CIUDAD";
			
      parent::w_parametrica('ciudad', $sql, $_REQUEST['cod_item_menu'], '1025');
      
      // headers
      $this->add_header(new header_num('COD_CIUDAD', 'COD_CIUDAD', 'Código'));
      $this->add_header(new header_text('NOM_CIUDAD', 'NOM_CIUDAD', 'Descripción'));      
   }	
}
?>