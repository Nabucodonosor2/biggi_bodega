<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../common_appl/class_w_output_biggi.php");

class wo_usuario extends w_output_biggi{
   
	function wo_usuario(){   	
      $sql = "select 	U.COD_USUARIO,
	               		U.NOM_USUARIO,			  	   
	               		U.LOGIN,			  	   
	               		P.NOM_PERFIL		   			   
	        from 		USUARIO U, PERFIL P
			where 		P.COD_PERFIL = U.COD_PERFIL
			order by	U.COD_USUARIO"; 
			
      parent::w_output_biggi('usuario', $sql, $_REQUEST['cod_item_menu']);
      
      // headers
      $this->add_header(new header_num('COD_USUARIO', 'COD_USUARIO', 'Código'));
      $this->add_header(new header_text('NOM_USUARIO', 'U.NOM_USUARIO', 'Usuario'));      
      $this->add_header(new header_text('LOGIN', 'U.LOGIN', 'Login'));      
      $this->add_header(new header_text('NOM_PERFIL', 'P.NOM_PERFIL', 'Perfil'));
   }
}
?>