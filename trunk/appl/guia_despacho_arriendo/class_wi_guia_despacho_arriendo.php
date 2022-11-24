<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../guia_despacho/class_wi_guia_despacho.php");

class dw_item_guia_despacho_arriendo extends dw_item_guia_despacho  {
	const K_BODEGA_RENTAL = 1;
	
	function dw_item_guia_despacho_arriendo() {
		$sql = "SELECT IGD.COD_ITEM_GUIA_DESPACHO,
						IGD.COD_GUIA_DESPACHO,
						IGD.ORDEN,
						IGD.ITEM,
						IGD.COD_PRODUCTO,
						IGD.NOM_PRODUCTO,
						IGD.CANTIDAD,
						dbo.f_arr_cant_por_despachar(IGD.COD_ITEM_DOC, default) CANTIDAD_POR_DESPACHAR,
						dbo.f_bodega_stock_cero(IGD.COD_PRODUCTO, ".self::K_BODEGA_RENTAL.", getdate()) CANTIDAD_BODEGA,
						IGD.PRECIO,
						IGD.COD_ITEM_DOC,
						GD.COD_DOC,
						case
							when GD.COD_DOC IS not NULL and GD.COD_ESTADO_DOC_SII = ".self::K_ESTADO_SII_EMITIDA." then ''
							else 'none'
						end TD_DISPLAY_CANT_POR_DESP,
						case
							when IGD.COD_ITEM_DOC IS NULL then ''
							else 'none'
						end TD_DISPLAY_ELIMINAR,
						COD_TIPO_TE,
						MOTIVO_TE
				FROM    ITEM_GUIA_DESPACHO IGD, GUIA_DESPACHO GD
				WHERE   IGD.COD_GUIA_DESPACHO = {KEY1}
						AND GD.COD_GUIA_DESPACHO  = IGD.COD_GUIA_DESPACHO 
				ORDER BY ORDEN";
		
		parent::datawindow($sql, 'ITEM_GUIA_DESPACHO', true, true);
		
		$this->add_control(new edit_text_upper('COD_ITEM_GUIA_DESPACHO',10, 10, 'hidden'));
		$this->add_control(new edit_num('ORDEN',4, 10));
		$this->add_control(new edit_text_upper('ITEM',4, 5));
		$this->add_control($control = new edit_cantidad('CANTIDAD',12,10));
		$control->set_onChange("this.value = valida_ct_x_despachar(this);");
		$this->add_control(new static_num('CANTIDAD_POR_DESPACHAR',1));
		$this->add_control(new edit_text('CANTIDAD_BODEGA',10, 10, 'hidden'));
		$this->add_control(new computed('PRECIO', 0));
		$this->set_computed('TOTAL', '[CANTIDAD] * [PRECIO]');
		$this->add_controls_producto_help();		// Debe ir despues de los computed donde esta involucrado precio, porque add_controls_producto_help() afecta el campos PRECIO
		
		$this->set_first_focus('CANTIDAD');
	}
}
class wi_guia_despacho_arriendo extends wi_guia_despacho {
	function wi_guia_despacho_arriendo($cod_item_menu) {
		parent::w_input('guia_despacho_arriendo', $cod_item_menu);
		$this->add_FK_delete_cascada('ITEM_GUIA_DESPACHO');	
		$this->hide_menu_when_from = false;		// Cuando se crear desde no se debe esconder menu
		
		// tab guia despacho
		// DATAWINDOWS GUIA DESPACHO
		$this->dws['dw_guia_despacho'] = new dw_guia_despacho();
				
		// tab items
		// DATAWINDOWS ITEMS GUIA DESPACHO
		$this->dws['dw_item_guia_despacho'] = new dw_item_guia_despacho_arriendo();
		$this->set_first_focus('NRO_ORDEN_COMPRA');

		//auditoria Solicitado por IS.
		$this->add_auditoria('COD_ESTADO_DOC_SII');
		$this->add_auditoria('COD_EMPRESA');
		$this->add_auditoria('COD_SUCURSAL_DESPACHO');
		$this->add_auditoria('COD_PERSONA');		
	}
	function make_sql_auditoria() {
		// cambia de guia_despacho_arriendo a guia_despacho y luego lo devuelve
		$nom_tabla = $this->nom_tabla;
		$this->nom_tabla = 'guia_despacho';
		$sql = parent::make_sql_auditoria();
		$this->nom_tabla = $nom_tabla;
		return $sql;
	}
	function delete_record($db) {
		return $db->EXECUTE_SP("spu_guia_despacho", "'DELETE', ".$this->get_key());
	}
}
?>