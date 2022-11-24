<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../common_appl/class_w_parametrica_biggi.php");

class wo_cuenta_corriente extends w_parametrica_biggi
{
   function wo_cuenta_corriente()
   {   	
      $sql = "select 		CT.COD_CUENTA_CORRIENTE,
							CT.NOM_CUENTA_CORRIENTE, 
							CT.NRO_CUENTA_CORRIENTE,
							ORDEN																				 						
				from 		CUENTA_CORRIENTE CT				
				order by 	ORDEN";
			
      parent::w_parametrica_biggi('cuenta_corriente', $sql, $_REQUEST['cod_item_menu'], '1025');
      
      // headers      
      $this->add_header(new header_num('COD_CUENTA_CORRIENTE', 'COD_CUENTA_CORRIENTE', 'Código'));
      $this->add_header(new header_text('NOM_CUENTA_CORRIENTE', 'NOM_CUENTA_CORRIENTE', 'Nombre'));
      $this->add_header(new header_num('NRO_CUENTA_CORRIENTE', 'NRO_CUENTA_CORRIENTE', 'Número'));
      $this->add_header(new header_num('ORDEN', 'ORDEN', 'Orden'));	     
   }
}
?>