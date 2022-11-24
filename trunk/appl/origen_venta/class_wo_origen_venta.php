<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

class wo_origen_venta extends w_parametrica
{
   function wo_origen_venta()
   {   	
      $sql = "select 	COD_ORIGEN_VENTA,
			            NOM_ORIGEN_VENTA,
						ORDEN 
	        from 		ORIGEN_VENTA 
	        order by 	ORDEN";
			
      parent::w_parametrica('origen_venta', $sql, $_REQUEST['cod_item_menu'], '1025');
      
      // headers
      $this->add_header(new header_num('COD_ORIGEN_VENTA', 'COD_ORIGEN_VENTA', 'Código'));
      $this->add_header(new header_text('NOM_ORIGEN_VENTA', 'NOM_ORIGEN_VENTA', 'Origen Nota Venta'));
      $this->add_header(new header_num('ORDEN', 'ORDEN', 'Orden'));
   }
}
?>
