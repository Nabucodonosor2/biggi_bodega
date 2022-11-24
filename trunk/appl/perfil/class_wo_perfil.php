<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

class wo_perfil extends w_output
{
   function wo_perfil()
   {   	
      $sql = "select 	COD_PERFIL,
	              		NOM_PERFIL
	        from 		PERFIL
	        order by 	COD_PERFIL";
			
      parent::w_output('perfil', $sql, $_REQUEST['cod_item_menu']);
      
      // headers
      $this->add_header(new header_num('COD_PERFIL', 'COD_PERFIL', 'Código'));
      $this->add_header(new header_text('NOM_PERFIL', 'NOM_PERFIL', 'Perfil'));      
   }	
}
?>
