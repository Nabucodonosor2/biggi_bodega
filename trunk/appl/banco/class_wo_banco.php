<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

class wo_banco extends w_parametrica
{
   function wo_banco()
   {
      $sql = "select 		COD_BANCO,
              				NOM_BANCO			   			   
        		from 		BANCO
				order by 	COD_BANCO";
			
      parent::w_parametrica('banco', $sql, $_REQUEST['cod_item_menu'], '1025');
      
      // headers
      $this->add_header(new header_num('COD_BANCO', 'COD_BANCO', 'Código'));
      $this->add_header(new header_text('NOM_BANCO', 'NOM_BANCO', 'Descripción'));
   }
	
    
}
?>