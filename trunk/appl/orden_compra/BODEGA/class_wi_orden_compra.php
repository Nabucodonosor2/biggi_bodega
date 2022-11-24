<?php
/////////////////////////////////////////
/// BODEGA
/////////////////////////////////////////

class wi_orden_compra extends wi_orden_compra_base {
	function wi_orden_compra($cod_item_menu) {
		parent::wi_orden_compra_base($cod_item_menu);
		$this->dws['dw_orden_compra']-> unset_mandatory('COD_NOTA_VENTA');	
	}
	
	function new_record(){
		parent::new_record();
		
		if (session::is_set('CREADO_DESDE_SOL_OC')){
			$cod_solicitud_oc = session::get("CREADO_DESDE_SOL_OC");
			session::un_set('CREADO_DESDE_SOL_OC');
			
			$this->dws['dw_orden_compra']->set_item(0, 'COD_DOC', $cod_solicitud_oc);
			$this->dws['dw_orden_compra']->set_item(0, 'TIPO_ORDEN_COMPRA', 'SOLICITUD_COMPRA');
		}
	}
}
?>