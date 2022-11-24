<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

class wo_comuna extends w_parametrica
{
   function wo_comuna()
   {   	
      $sql = "select 	COD_COMUNA,
               			NOM_COMUNA			   			   
        		from 	COMUNA
        	order by 	COD_COMUNA";
			
      parent::w_parametrica('comuna', $sql, $_REQUEST['cod_item_menu'], '1025');
      
      // headers
      $this->add_header(new header_num('COD_COMUNA', 'COD_COMUNA', 'Código'));
      $this->add_header(new header_text('NOM_COMUNA', 'NOM_COMUNA', 'COMUNA'));
      
   }
}
?>