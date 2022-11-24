<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

class wo_tipo_gas extends w_parametrica
{
   function wo_tipo_gas()
   {   	
      $sql = "select 	COD_TIPO_GAS,
		               	NOM_TIPO_GAS,
					   	ORDEN 
		      from 		TIPO_GAS 
		      order by 	ORDEN";
			
      parent::w_parametrica('tipo_gas', $sql, $_REQUEST['cod_item_menu'], '1025');
      
      // headers
      $this->add_header(new header_num('COD_TIPO_GAS', 'COD_TIPO_GAS', 'Código'));
      $this->add_header(new header_text('NOM_TIPO_GAS', 'NOM_TIPO_GAS', 'Tipo Gas'));
      $this->add_header(new header_num('ORDEN', 'ORDEN', 'Orden'));
   }
}
?>