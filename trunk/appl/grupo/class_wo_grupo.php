<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

class wo_grupo extends w_parametrica
{
   function wo_grupo()
   {   	
      $sql = "select 	G.COD_GRUPO, 
						G.NOM_GRUPO, 
						U.NOM_USUARIO
			from 		GRUPO G, USUARIO U
			where 		U.COD_USUARIO = G.COD_USUARIO	
			order by 	COD_GRUPO";
			
      parent::w_parametrica('grupo', $sql, $_REQUEST['cod_item_menu'], '1025');
      
      // headers
      $this->add_header(new header_num('COD_GRUPO', 'COD_GRUPO', 'Código'));
      $this->add_header(new header_text('NOM_GRUPO', 'NOM_GRUPO', 'Grupo'));
      $sql_usuario = "select COD_USUARIO ,NOM_USUARIO from USUARIO order by	COD_USUARIO";
      $this->add_header(new header_drop_down('NOM_USUARIO', 'G.COD_USUARIO', 'Jefe', $sql_usuario));
      
   }
}
?>