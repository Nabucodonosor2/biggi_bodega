<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

class wo_cuenta_contable extends w_parametrica
{
   function wo_cuenta_contable()
   {   	      
		$sql = "select 	COD_CUENTA_CONTABLE,
               			NOM_CUENTA_CONTABLE 
        		from 	CUENTA_CONTABLE 
        	order by 	COD_CUENTA_CONTABLE"; 
			
      parent::w_parametrica('cuenta_contable', $sql, $_REQUEST['cod_item_menu'], '1025');
      
      // headers
      $this->add_header(new header_num('COD_CUENTA_CONTABLE', 'COD_CUENTA_CONTABLE', 'Código'));
      $this->add_header(new header_text('NOM_CUENTA_CONTABLE', 'NOM_CUENTA_CONTABLE', 'Descripción'));
      
   }
}
?>