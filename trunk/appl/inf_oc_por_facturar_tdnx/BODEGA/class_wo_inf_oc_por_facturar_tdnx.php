<?php
class wo_inf_oc_por_facturar_tdnx extends wo_inf_oc_por_facturar_tdnx_base {
	function procesa_event() {
		if(isset($_POST['b_back_x']))
			header('Location:' . $this->root_url . 'appl/inf_oc_por_facturar_tdnx/BODEGA/inf_oc_por_facturar_tdnx.php?cod_item_menu='.$this->cod_item_menu_parametro);
		else
			parent::procesa_event();	
	}
}
?>