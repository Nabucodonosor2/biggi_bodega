<?php
class header_vendedor extends  header_drop_down {
	function header_vendedor($field, $field_bd, $nom_header) {
		$sql = "SELECT U.COD_USUARIO, U.NOM_USUARIO 
			  FROM USUARIO U 
			 WHERE U.VENDEDOR_VISIBLE_FILTRO = 1 
		  ORDER BY NOM_USUARIO ASC";
		parent::header_drop_down($field, $field_bd, $nom_header, $sql);
	}
	function make_java_script() {
		return '"return dlg_find_vendedor(\''.$this->nom_header.'\', \''.$this->valor_filtro.'\', \''.$this->sql.'\', this);"';		
	}
}
?>