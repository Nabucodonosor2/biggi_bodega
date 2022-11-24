<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

class wo_plaza extends w_parametrica
{
   function wo_plaza()
   {   	
      $sql = "select 		COD_PLAZA,
	               			NOM_PLAZA			   			   
	        from 			PLAZA
			order by 		COD_PLAZA";
			
      parent::w_parametrica('plaza', $sql, $_REQUEST['cod_item_menu'], '1025');
      
      // headers
      $this->add_header(new header_num('COD_PLAZA', 'COD_PLAZA', 'Código'));
      $this->add_header(new header_text('NOM_PLAZA', 'NOM_PLAZA', 'Plaza'));
      
   }
}
?>