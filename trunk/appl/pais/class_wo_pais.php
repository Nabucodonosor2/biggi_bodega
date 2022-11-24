<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

class wo_pais extends w_parametrica
{
   function wo_pais()
   {   	
      $sql = "select 	COD_PAIS,
               			NOM_PAIS			   			   
        	from 		PAIS
        	order by 	COD_PAIS"; 
			
      parent::w_parametrica('pais', $sql, $_REQUEST['cod_item_menu'], '1025');
      
      // headers
      $this->add_header(new header_num('COD_PAIS', 'COD_PAIS', 'Código'));
      $this->add_header(new header_text('NOM_PAIS', 'NOM_PAIS', 'País'));
      
   }
}
?>