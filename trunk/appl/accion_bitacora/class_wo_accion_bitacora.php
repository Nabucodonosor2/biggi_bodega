<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

class wo_accion_bitacora extends w_parametrica
{
   function wo_accion_bitacora()
   {   	
      $sql = "select		COD_ACCION_BITACORA,
               				NOM_ACCION_BITACORA,
               				ORDEN 
	        	from 		ACCION_BITACORA						
				order by 	ORDEN";
			
      parent::w_parametrica('accion_bitacora', $sql, $_REQUEST['cod_item_menu'], '1025');
      
      // headers
      $this->add_header(new header_num('COD_ACCION_BITACORA', 'COD_ACCION_BITACORA', 'Código'));
      $this->add_header(new header_text('NOM_ACCION_BITACORA', 'NOM_ACCION_BITACORA', 'Descripción'));
      $this->add_header(new header_num('ORDEN', 'ORDEN', 'Orden'));
      
   }
}
?>