<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../inf_ventas_por_mes/class_wi_inf_ventas_por_mes.php");

class wi_inf_por_despachar extends wi_inf_ventas_por_mes {
	function wi_inf_por_despachar($cod_item_menu) {
		parent::w_cot_nv('inf_por_despachar', $cod_item_menu);
		$this->dw_tabla = 'dw_nota_venta';
		$this->dw_tabla_item = 'dw_item_nota_venta';
		$this->ruta_menu = 'Nota de Venta: ';

		$this->nom_template = "../inf_ventas_por_mes/wi_inf_ventas_por_mes.htm";
		
		$this->constructor_base();

		// Redirecciona los links para que el modulo de origen sea "inf_por_despachar"
		$this->dws['dw_orden_compra']->remove_control('COD_ORDEN_COMPRA');
		$this->dws['dw_orden_compra']->add_control(new static_link('COD_ORDEN_COMPRA', '../../../../commonlib/trunk/php/link_wi.php?modulo_origen=inf_por_despachar&modulo_destino=orden_compra&cod_modulo_destino=[COD_ORDEN_COMPRA]&cod_item_menu=1520&current_tab_page=2'));
		
		$this->dws['dw_lista_guia_despacho']->remove_control('NRO_GUIA_DESPACHO');
		$this->dws['dw_lista_guia_despacho']->add_control(new static_link('NRO_GUIA_DESPACHO', '../../../../commonlib/trunk/php/link_wi.php?modulo_origen=inf_por_despachar&modulo_destino=guia_despacho&cod_modulo_destino=[NRO_GUIA_DESPACHO]&cod_item_menu=1525'));
		
		$this->dws['dw_lista_factura']->remove_control('NRO_FACTURA');
		$this->dws['dw_lista_factura']->add_control(new static_link('NRO_FACTURA', '../../../../commonlib/trunk/php/link_wi.php?modulo_origen=inf_por_despachar&modulo_destino=factura&cod_modulo_destino=[NRO_FACTURA]&cod_item_menu=1535'));
		
		$this->dws['dw_lista_pago']->remove_control('COD_INGRESO_PAGO');
		$this->dws['dw_lista_pago']->add_control(new static_link('COD_INGRESO_PAGO', '../../../../commonlib/trunk/php/link_wi.php?modulo_origen=inf_por_despachar&modulo_destino=ingreso_pago&cod_modulo_destino=[COD_INGRESO_PAGO]&cod_item_menu=2505'));
	}
}
?>