<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../guia_recepcion/class_wi_guia_recepcion.php");

class wi_guia_recepcion_arriendo extends wi_guia_recepcion {
	const K_TIPO_GR_ARRIENDO		= 4;
	
	function wi_guia_recepcion_arriendo($cod_item_menu) {		
		parent::wi_guia_recepcion($cod_item_menu);
		$this->nom_tabla = 'guia_recepcion_arriendo';
		$this->nom_template = "wi_".$this->nom_tabla.".htm";

		$sql = "select 	 COD_TIPO_GUIA_RECEPCION
						,NOM_TIPO_GUIA_RECEPCION
				from 	 TIPO_GUIA_RECEPCION
				where	COD_TIPO_GUIA_RECEPCION = ".self::K_TIPO_GR_ARRIENDO."
				order by ORDEN";
		$this->dws['dw_guia_recepcion']->controls['COD_TIPO_GUIA_RECEPCION']->set_sql($sql);
		$this->dws['dw_guia_recepcion']->controls['COD_TIPO_GUIA_RECEPCION']->retrieve();
		
		$this->dws['dw_guia_recepcion']->controls['TIPO_DOC']->aValues = array('', 'ARRIENDO');
		$this->dws['dw_guia_recepcion']->controls['TIPO_DOC']->aLabels = array('', 'CONTRATO ARRIENDO');
	
		// no se pueden agregar o eliminar items
		$this->dws['dw_item_guia_recepcion']->b_add_line_visible = false;
		$this->dws['dw_item_guia_recepcion']->b_del_line_visible = false;
		
		$this->set_first_focus('NRO_DOC');
	}
	function new_record() {
		parent::new_record();
		$this->dws['dw_guia_recepcion']->set_item(0, 'COD_TIPO_GUIA_RECEPCION', self::K_TIPO_GR_ARRIENDO);
		$this->dws['dw_guia_recepcion']->set_item(0, 'TR_DISPLAY_TIPO_DOC', '');
		$this->dws['dw_guia_recepcion']->set_item(0, 'TIPO_DOC', 'ARRIENDO');
		$this->dws['dw_guia_recepcion']->set_item(0, 'VISIBLE_TAB', '');
		$this->dws['dw_guia_recepcion']->set_item(0, 'TD_DISPLAY_ELIMINAR', 'none');
	}
	function load_wo() {
		if ($this->tiene_wo)
			$this->wo = session::get("wo_guia_recepcion_arriendo");
	}
	function make_sql_auditoria() {
		$nom_tabla = $this->nom_tabla;
		$this->nom_tabla = 'guia_recepcion';
		$sql = parent::make_sql_auditoria();
		$this->nom_tabla = $nom_tabla;
		return $sql;
	}
	function make_sql_auditoria_relacionada($tabla) {
		$nom_tabla = $this->nom_tabla;
		$this->nom_tabla = 'guia_recepcion';
		$sql = parent::make_sql_auditoria_relacionada($tabla);
		$this->nom_tabla = $nom_tabla;
		return $sql;
	}
	function delete_record($db) {
		return $db->EXECUTE_SP("spu_guia_recepcion", "'DELETE', ".$this->get_key());
	}
}
?>