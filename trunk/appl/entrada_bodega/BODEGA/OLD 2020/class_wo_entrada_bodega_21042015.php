<?php
require_once(dirname(__FILE__)."/../../../../../commonlib/trunk/php/auto_load.php");

class wo_entrada_bodega extends wo_entrada_bodega_base {
	function wo_entrada_bodega() {      
		parent::wo_entrada_bodega_base(); 
	}
   
   	//function entrada_from_oc($cod_orden_compra) {
	function entrada_from_oc($valor_devuelto) {
		list($cod_orden_compra, $nro_fa_proveedor, $fecha_fa_proveedor)=split('[|]', $valor_devuelto);
		
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$sql = "SELECT * FROM ORDEN_COMPRA WHERE COD_ORDEN_COMPRA = $cod_orden_compra";
		$result = $db->build_results($sql);
		if (count($result) == 0){
			$this->_redraw();
			$this->alert('La OC Nº'.$cod_orden_compra.' no existe.');								
			return;
		}else{
			//si existe la Oc determina el tipo
			$sql = "SELECT * 
					FROM ORDEN_COMPRA 
					WHERE COD_ORDEN_COMPRA = $cod_orden_compra
						and TIPO_ORDEN_COMPRA = 'SOLICITUD_COMPRA'";
						
			$result = $db->build_results($sql);
			if (count($result) == 0){
				$this->_redraw();
				$this->alert('La procedencia de la OC Nº'.$cod_orden_compra.' no es de solicitud');								
				return;
			}else{
				session::set('ENTRADA_CREADA_DESDE', $valor_devuelto);
				$this->add();
	   		}				
		}
	}

	function procesa_event() {		
		if(isset($_POST['b_create_x']))
			$this->entrada_from_oc($_POST['wo_hidden']);
		else
			parent::procesa_event();
	}
}
?>