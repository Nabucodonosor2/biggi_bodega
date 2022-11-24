<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

class w_parametrica_biggi extends w_parametrica {
	private 	$cod_item_menu_parametro;
	
	function w_parametrica_biggi($nom_tabla, $sql, $cod_item_menu, $cod_item_menu_parametro) {
		parent::w_parametrica($nom_tabla, $sql, $cod_item_menu, $cod_item_menu_parametro);
	}
	function procesa_event() {
		if(isset($_POST['b_back_x'])) {
			$cod_usuario = session::get("COD_USUARIO");;
			if ($cod_usuario == 1)
				header('Location:' . $this->root_url . 'appl/parametro/wi_parametro.php?cod_item_menu='.$this->cod_item_menu_parametro);
			else
				header('Location:' . $this->root_url . 'appl/parametro_adm/wi_parametro_adm.php?cod_item_menu='.$this->cod_item_menu_parametro);
		}			
		else
			parent::procesa_event();	
	}
}
?>