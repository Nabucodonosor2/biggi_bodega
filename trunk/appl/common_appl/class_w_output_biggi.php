<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

class w_output_biggi extends w_output {
   	function w_output_biggi($nom_tabla, $sql, $cod_item_menu) {
   		parent::w_output($nom_tabla, $sql, $cod_item_menu);
   		
   		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$sql = "select A.EXPORTAR
				from   AUTORIZA_MENU A, USUARIO U
		        where  U.COD_USUARIO = $this->cod_usuario
		         and	A.COD_PERFIL = U.COD_PERFIL
		         and 	 A.COD_ITEM_MENU = '".$cod_item_menu."'";
		$result = $db->build_results($sql);
		$exportar = $result[0]['EXPORTAR'];
	
		if ($exportar == 'S') {
			$this->b_export_visible = true;
	  	}else{
			$this->b_export_visible = false;
      	}
	}
	function redraw(&$temp) {
		parent::redraw($temp);
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
        $cod_usuario = $this->cod_usuario;
		
        $sql = "SELECT COD_PERFIL 
				FROM USUARIO
				WHERE COD_USUARIO = $cod_usuario";
		$result = $db->build_results($sql);
		$cod_perfil = $result[0]['COD_PERFIL'];

		$item_menu_autoriza_oc = '994010';
		
		$sql_autoriza = "SELECT AUTORIZA_MENU
						   FROM AUTORIZA_MENU
						  WHERE COD_ITEM_MENU = $item_menu_autoriza_oc
						    AND COD_PERFIL = $cod_perfil";
		
		$result = $db->build_results($sql_autoriza);				    
		$autoriza = $result[0]['AUTORIZA_MENU'];	
		if($autoriza == 'E'){					    
			$sql = "exec sp_alerta 4"; // COD_USUARIO=4 MUESTRA EL MENSAJE 
		}
		$result = $db->build_results($sql);
		$mensaje = "";
		for ($i=0; $i < count($result); $i++) {
			$mensaje .= $result[$i]['ALERTA_MENSAJE']."<br>";
		}
		$temp->setVar('WO_ALERTA_MENSAJE',$mensaje);
	}
}
?>