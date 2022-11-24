<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

class wo_flete_cotizacion extends w_parametrica
{
   function wo_flete_cotizacion()
   {   	
      $sql = "select 	COD_FLETE_COTIZACION,
	               		NOM_FLETE_COTIZACION,
				   		ORDEN
	        from 		FLETE_COTIZACION 
	        order by 	ORDEN";
			
      parent::w_parametrica('flete_cotizacion', $sql, $_REQUEST['cod_item_menu'], '1025');
      
      // headers
      $this->add_header(new header_num('COD_FLETE_COTIZACION', 'COD_FLETE_COTIZACION', 'Código'));
      $this->add_header(new header_text('NOM_FLETE_COTIZACION', 'NOM_FLETE_COTIZACION', 'Flete Cotización'));
      $this->add_header(new header_num('ORDEN', 'ORDEN', 'Orden'));
   }
}
?>