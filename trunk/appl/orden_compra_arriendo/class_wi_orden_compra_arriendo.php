<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../orden_compra/class_wi_orden_compra.php");

class wi_orden_compra_arriendo extends wi_orden_compra {
	function wi_orden_compra_arriendo($cod_item_menu) {
		parent::wi_orden_compra($cod_item_menu);
		$this->nom_tabla = 'orden_compra_arriendo';
		$this->nom_template = "wi_".$this->nom_tabla.".htm";

		/*
		$this->dws['dw_orden_compra'] = new dw_orden_compra();
		$this->add_controls_cot_nv();
		
		// DATAWINDOWS NCPROV_FAPROV
		$this->dws['dw_item_orden_compra'] = new dw_item_orden_compra();
		
		//PAGO ORDEN_COMPRA
		$this->dws['dw_pago_orden_compra'] = new dw_pago_orden_compra();
		
		$this->add_auditoria_relacionada('ITEM_ORDEN_COMPRA', 'PRECIO');		
		
		//auditoria Solicitado por IS.
		$this->add_auditoria('COD_EMPRESA');
		$this->add_auditoria('COD_USUARIO_SOLICITA');
		$this->add_auditoria('COD_ESTADO_ORDEN_COMPRA');
		$this->add_auditoria('COD_NOTA_VENTA');
		$this->add_auditoria('COD_SUCURSAL');
		$this->add_auditoria('COD_PERSONA');
		$this->add_auditoria('COD_CUENTA_CORRIENTE');
	
		// VMC, 23-01-2011 se hace obligatorio el nro de NV
		// asigna los mandatorys
		$this->dws['dw_orden_compra']->set_mandatory('COD_NOTA_VENTA', 'Nota de Venta');	
		
		//al momento de crear un nuevo registro se iniciara en rut de empresa proveedor
		$this->set_first_focus('RUT');
*/
	}
	function new_record() {
		parent::new_record();

		$cod_arriendo = session::get('ORDEN_COMPRA.CREAR_DESDE_ARRIENDO');
		session::un_set('ORDEN_COMPRA.CREAR_DESDE_ARRIENDO');
		
		$this->dws['dw_orden_compra']->set_item(0, 'COD_DOC', $cod_arriendo);
	}
	function load_wo() {
		if ($this->tiene_wo)
			$this->wo = session::get("wo_orden_compra_arriendo");
	}
	function make_sql_auditoria() {
		$nom_tabla = $this->nom_tabla;
		$this->nom_tabla = 'orden_compra';
		$sql = parent::make_sql_auditoria();
		$this->nom_tabla = $nom_tabla;
		return $sql;
	}
	function make_sql_auditoria_relacionada($tabla) {
		$nom_tabla = $this->nom_tabla;
		$this->nom_tabla = 'orden_compra';
		$sql = parent::make_sql_auditoria_relacionada($tabla);
		$this->nom_tabla = $nom_tabla;
		return $sql;
	}
	function delete_record($db) {
		return $db->EXECUTE_SP("spu_orden_compra", "'DELETE', ".$this->get_key());
	}
}
?>