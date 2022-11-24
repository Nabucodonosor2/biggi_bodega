<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

class wo_tipo_electricidad extends w_parametrica
{
   function wo_tipo_electricidad()
   {   	
      $sql = "select 	COD_TIPO_ELECTRICIDAD,
	               		NOM_TIPO_ELECTRICIDAD,
				   		ORDEN 
	        from 		TIPO_ELECTRICIDAD 
	        order by 	ORDEN";
			
      parent::w_parametrica('tipo_electricidad', $sql, $_REQUEST['cod_item_menu'], '1025');
      
      // headers
      $this->add_header(new header_num('COD_TIPO_ELECTRICIDAD', 'COD_TIPO_ELECTRICIDAD', 'Código'));
      $this->add_header(new header_text('NOM_TIPO_ELECTRICIDAD', 'NOM_TIPO_ELECTRICIDAD', 'Tipo Electricidad'));
      $this->add_header(new header_num('ORDEN', 'ORDEN', 'Orden'));
   }
}
?>