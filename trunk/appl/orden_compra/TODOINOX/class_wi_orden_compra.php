<?php
/////////////////////////////////////////
/// TODOINOX
/////////////////////////////////////////

class wi_orden_compra extends wi_orden_compra_base {
	function wi_orden_compra($cod_item_menu) {
		parent::wi_orden_compra_base($cod_item_menu);
		$this->dws['dw_orden_compra']-> unset_mandatory('COD_NOTA_VENTA');	
	}

}
?>