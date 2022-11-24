<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

class wo_origen_cotizacion extends w_parametrica
{
   function wo_origen_cotizacion()
   {   	
      $sql = "select 	COD_ORIGEN_COTIZACION,
	               		NOM_ORIGEN_COTIZACION,
				   		ORDEN 
	        from 		ORIGEN_COTIZACION 
	        order by 	ORDEN";
			
      parent::w_parametrica('origen_cotizacion', $sql, $_REQUEST['cod_item_menu'], '1025');
      
      // headers
      $this->add_header(new header_num('COD_ORIGEN_COTIZACION', 'COD_ORIGEN_COTIZACION', 'Código'));
      $this->add_header(new header_text('NOM_ORIGEN_COTIZACION', 'NOM_ORIGEN_COTIZACION', 'Origen Cotizacion'));
      $this->add_header(new header_num('ORDEN', 'ORDEN', 'Orden'));
   }
}
?>