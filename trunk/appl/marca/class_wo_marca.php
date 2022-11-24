<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

class wo_marca extends w_parametrica
{
   function wo_marca()
   {   	
      $sql = "select 		COD_MARCA,
		               		NOM_MARCA,
		               		ORDEN			   			   
		        from 		MARCA
		        order by 	ORDEN"; 
			
      parent::w_parametrica('marca', $sql, $_REQUEST['cod_item_menu'], '1025');
      
      // headers
      $this->add_header(new header_num('COD_MARCA', 'COD_MARCA', 'Código'));
      $this->add_header(new header_text('NOM_MARCA', 'NOM_MARCA', 'Marca'));
      $this->add_header(new header_num('ORDEN', 'ORDEN', 'Orden'));
   }
}
?>