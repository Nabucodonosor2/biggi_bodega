<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

class wo_accion_cobranza extends w_parametrica
{
   function wo_accion_cobranza()
   {   	
      $sql	=	"SELECT	COD_ACCION_COBRANZA
      					,NOM_ACCION_COBRANZA
				FROM	ACCION_COBRANZA
				ORDER BY COD_ACCION_COBRANZA"; 
			
      parent::w_parametrica('accion_cobranza', $sql, $_REQUEST['cod_item_menu'], '1025');
      
      // headers
      $this->add_header(new header_num('COD_ACCION_COBRANZA', 'COD_ACCION_COBRANZA', 'Código'));
      $this->add_header(new header_text('NOM_ACCION_COBRANZA', 'NOM_ACCION_COBRANZA', 'Accion Cobranza'));
   }
}
?>