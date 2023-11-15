<?php
require_once(dirname(__FILE__)."/../../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../../ws_client_biggi/class_client_biggi.php");

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
			$sql = "SELECT COD_EMPRESA 
					FROM ORDEN_COMPRA 
					WHERE COD_ORDEN_COMPRA = $cod_orden_compra
						and TIPO_ORDEN_COMPRA = 'SOLICITUD_COMPRA'";
						
			$result = $db->build_results($sql);
			$cod_empresa = $result[0]['COD_EMPRESA'];
			
			if (count($result) == 0){
				$this->_redraw();
				$this->alert('La procedencia de la OC Nº'.$cod_orden_compra.' no es de solicitud');								
				return;
			}else{
				$sql = "SELECT COUNT(*) COUNT
						FROM ENTRADA_BODEGA EE
							,FAPROV F
						WHERE NRO_FACTURA_PROVEEDOR = $nro_fa_proveedor
						AND F.COD_EMPRESA = $cod_empresa
						AND EE.NRO_FACTURA_PROVEEDOR = F.COD_FAPROV";
						
				$result = $db->build_results($sql);
				if($result[0]['COUNT'] > 0){
					$this->_redraw();
					$this->alert('Esta fa provedor ya esta aplicada');								
					return;
				}
				
				$sql_emp = "SELECT COD_EMPRESA
								  ,NOM_EMPRESA
								  ,COD_ORDEN_COMPRA
							FROM ORDEN_COMPRA
							WHERE COD_ORDEN_COMPRA = $cod_orden_compra";
				$result_emp = $db->build_results($sql_emp);

				/////////////////////////////////////////////////////////////////////
				$cod_empresa_emp = $result_emp[0]['COD_EMPRESA'];
				$nom_empresa_emp = $result_emp[0]['NOM_EMPRESA'];

				$sql = "SELECT COUNT(*) COUNT
						FROM FAPROV
						WHERE COD_EMPRESA = $cod_empresa_emp
						AND NRO_FAPROV = $nro_fa_proveedor
						AND COD_ESTADO_FAPROV <> 5"; //anulada
				$result = $db->build_results($sql);

				if($result[0]['COUNT'] > 0){
					$this->_redraw();
					$this->alert('La Factura proveedor Nro '.$nro_fa_proveedor.', ya se encuentra registrada para el proveedor '.$nom_empresa_emp.'.');								
					return;
				}

				/////////////////////////////////////////////////////////////////////
				
				if($result_emp[0]['COD_EMPRESA'] == 4 && $result_emp[0]['COD_ORDEN_COMPRA'] > 57130){
					$sql = "select SISTEMA, URL_WS, USER_WS,PASSWROD_WS  from PARAMETRO_WS
							where SISTEMA = 'TODOINOX' ";
					$result = $db->build_results($sql);
					
					$user_ws		= $result[0]['USER_WS'];
					$passwrod_ws	= $result[0]['PASSWROD_WS'];
					$url_ws			= $result[0]['URL_WS'];
					
					$biggi = new client_biggi("$user_ws", "$passwrod_ws", "$url_ws");
					$result = $biggi->cli_entrada_bodega($cod_orden_compra, $nro_fa_proveedor);
					
					if($result == 'NO_EXISTE'){
						$this->_redraw();
						$this->alert('No existe factura');								
						return;
					}else if($result == 'OTRA_EMPRESA'){
						$this->_redraw();
						$this->alert('La factura esta para otra empresa');								
						return;
					}else if($result == 'DISTINTO_OC'){
						$this->_redraw();
						$this->alert('La factura tiene una oc distinta');								
						return;
					}
				}	
				
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