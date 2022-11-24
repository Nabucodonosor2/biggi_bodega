<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

class wo_region extends w_parametrica
{
   function wo_region()
   {   	
      $sql = "select 	COD_REGION,						
	               		NOM_REGION			   			   
	        from 		REGION
	        order by 	COD_REGION";
			
      parent::w_parametrica('region', $sql, $_REQUEST['cod_item_menu'], '1025');
      
      // headers
      $this->add_header(new header_num('COD_REGION', 'COD_REGION', 'Código'));
      $this->add_header(new header_text('NOM_REGION', 'NOM_REGION', 'Región'));
      
   }
}
?>