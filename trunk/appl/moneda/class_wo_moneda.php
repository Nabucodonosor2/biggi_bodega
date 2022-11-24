<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

class wo_moneda extends w_parametrica
{
   function wo_moneda()
   {   	
      $sql = "select 		COD_MONEDA,
		               		NOM_MONEDA,
					   		SIMBOLO,
					 		ORDEN 
		        from 		MONEDA
				order by 	ORDEN";
			
      parent::w_parametrica('moneda', $sql, $_REQUEST['cod_item_menu'], '1025');
      
      // headers
      $this->add_header(new header_num('COD_MONEDA', 'COD_MONEDA', 'Código'));
      $this->add_header(new header_text('NOM_MONEDA', 'NOM_MONEDA', 'Moneda'));
      $this->add_header(new header_num('ORDEN', 'ORDEN', 'Orden'));
   }
}
?>