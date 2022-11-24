<?php
class header_modelo extends  header_text {
	function header_modelo($field, $field_bd, $nom_header) {
		parent::header_text($field, $field_bd, $nom_header);
	}
	function make_java_script() {
		return '"return dlg_find_modelo(\''.$this->nom_header.'\', \''.$this->valor_filtro.'\', this);"';		
	}
	function make_filtro() {
		if ($this->valor_filtro=='')
			return '';
		
		$res = explode('|', $this->valor_filtro);
		$valor = $res[0];
		if ($res[1]=='S')
			// busqueda exacta
			return "(Upper(".$this->field_bd.") like '".strtoupper($valor)."') and ";		
		else
			// comienza por 	
			return "(Upper(".$this->field_bd.") like '".strtoupper($valor)."%') and ";		
	}
}
?>