<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

class wo_familia_producto extends w_parametrica
{
   function wo_familia_producto()
   {   	
      $sql = "select 	COD_FAMILIA_PRODUCTO,
	               		NOM_FAMILIA_PRODUCTO,
				   		ORDEN 
	        from 		FAMILIA_PRODUCTO 
	        order by 	ORDEN";
			
      parent::w_parametrica('familia_producto', $sql, $_REQUEST['cod_item_menu'], '1025');
      
      // headers
      $this->add_header(new header_num('COD_FAMILIA_PRODUCTO', 'COD_FAMILIA_PRODUCTO', 'Código'));
      $this->add_header(new header_text('NOM_FAMILIA_PRODUCTO', 'NOM_FAMILIA_PRODUCTO', 'Descripción'));
      $this->add_header(new header_num('ORDEN', 'ORDEN', 'Orden'));
   }
}
?>