<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

class wo_tipo_producto extends w_parametrica
{
   function wo_tipo_producto()
   {   	
      $sql = "select 	COD_TIPO_PRODUCTO,
		               	NOM_TIPO_PRODUCTO,			    
					   	ORDEN
			from 		TIPO_PRODUCTO
			order by 	ORDEN";
			
      parent::w_parametrica('tipo_producto', $sql, $_REQUEST['cod_item_menu'], '1025');
      
      // headers
      $this->add_header(new header_num('COD_TIPO_PRODUCTO', 'COD_TIPO_PRODUCTO', 'Código'));
      $this->add_header(new header_text('NOM_TIPO_PRODUCTO', 'NOM_TIPO_PRODUCTO', 'Tipo de Producto'));
      $this->add_header(new header_num('ORDEN', 'ORDEN', 'Orden'));
   }
}
?>