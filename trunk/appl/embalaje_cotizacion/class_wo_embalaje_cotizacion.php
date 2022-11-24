<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

class wo_embalaje_cotizacion extends w_parametrica
{
   function wo_embalaje_cotizacion()
   {   	
      $sql = "select 	COD_EMBALAJE_COTIZACION,
               			NOM_EMBALAJE_COTIZACION,
			   			ORDEN 
	        from 		EMBALAJE_COTIZACION 
	        order by 	ORDEN";
			
      parent::w_parametrica('embalaje_cotizacion', $sql, $_REQUEST['cod_item_menu'], '1025');
      
      // headers
      $this->add_header(new header_num('COD_EMBALAJE_COTIZACION', 'COD_EMBALAJE_COTIZACION', 'Código'));
      $this->add_header(new header_text('NOM_EMBALAJE_COTIZACION', 'NOM_EMBALAJE_COTIZACION', 'Embalaje Cotización '));
      $this->add_header(new header_num('ORDEN', 'ORDEN', 'Orden'));
   }
}
?>