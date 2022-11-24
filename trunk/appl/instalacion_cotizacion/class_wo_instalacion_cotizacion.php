<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

class wo_instalacion_cotizacion extends w_parametrica
{
   function wo_instalacion_cotizacion()
   {   	
      $sql = "select 	COD_INSTALACION_COTIZACION,
	               		NOM_INSTALACION_COTIZACION,
				   		ORDEN 
	        from 		INSTALACION_COTIZACION 
	        order by 	ORDEN";
			
      parent::w_parametrica('instalacion_cotizacion', $sql, $_REQUEST['cod_item_menu'], '1025');
      
      // headers
      $this->add_header(new header_num('COD_INSTALACION_COTIZACION', 'COD_INSTALACION_COTIZACION', 'Código'));
      $this->add_header(new header_text('NOM_INSTALACION_COTIZACION', 'NOM_INSTALACION_COTIZACION', 'Instalación Cotización'));
      $this->add_header(new header_num('ORDEN', 'ORDEN', 'Orden'));
   }
}
?>