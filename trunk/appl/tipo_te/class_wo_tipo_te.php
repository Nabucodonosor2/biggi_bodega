<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

class wo_tipo_te extends w_parametrica
{
   function wo_tipo_te()
   {   	
      $sql = "select 	COD_TIPO_TE,
		               	NOM_TIPO_TE,			    
					   	ORDEN
		    from 		TIPO_TE
			order by 	ORDEN";
			
      parent::w_parametrica('tipo_te', $sql, $_REQUEST['cod_item_menu'], '1025');
      
      // headers
      $this->add_header(new header_num('COD_TIPO_TE', 'COD_TIPO_TE', 'Código'));
      $this->add_header(new header_text('NOM_TIPO_TE', 'NOM_TIPO_TE', 'Tipo TE'));
      $this->add_header(new header_num('ORDEN', 'ORDEN', 'Orden'));
   }
}
?>