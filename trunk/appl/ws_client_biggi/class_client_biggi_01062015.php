<?php
require("simple_restclient.php");
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../../appl.ini");

function objectToArray($d) {
	if (is_object($d)) {
		// Gets the properties of the given object
		// with get_object_vars function
		$d = get_object_vars($d);
	}
	 
	if (is_array($d)) {
		/*
		* Return array converted to object
		* Using __FUNCTION__ (Magic constant)
		* for recursive call
		*/
		return array_map(__FUNCTION__, $d);
	}
	else {
		// Return array
		return $d;
	}
}
	
class client_biggi {
	var $uid;
	var $pwd;
	var $url;
	
	static function str2date($fecha_str, $hora_str='00:00:00') {
		if ($fecha_str=='')
			return 'null';
		// Entra la fecha en formato dd/mm/yyyy		
		if (K_TIPO_BD=='mssql') {
			$res = explode('/', $fecha_str);
			if (strlen($res[2])==2)
				$res[2] = '20'.$res[2];
			return sprintf("{ts '$res[2]-$res[1]-$res[0] $hora_str.000'}");
		}
		else if (K_TIPO_BD=='oci')
			return "to_date('$fecha_str $hora_str', 'dd/mm/yyyy hh24:mi:ss')";
		else
			base::error("base.str2date, no soportado para ".K_TIPO_BD);
	}
	function client_biggi($uid, $pwd, $url) {
		$this->uid	= $uid;
		$this->pwd	= $pwd;	
		$this->url 	= $url;
	}
	function cli_orden_compra($cod_orden_compra){
		$client =new simple_restclient($this->url);
		$client->SetClass("server_biggi");
		if($client->Service_Exists()){	# CHECK IF THE SERVICE EXISTS
			$client->SetAuth($this->uid, $this->pwd);	# AUTHENTICATE THE CONNECTION
			 
			$val = array('var1'=>$cod_orden_compra); # ARGUMENTS THAT WILL BE PASSED FOR THE METHOD
			$client->Call->Method("svr_orden_compra", $val,$return); 	# CALLING THE METHOD class->Morning('jeff','hi').

			$result = objectToArray(json_decode($return));
			for($i=0; $i<count($result['ORDEN_COMPRA']); $i++){
				$result['ORDEN_COMPRA'][$i]['REFERENCIA']			= utf8_decode($result['ORDEN_COMPRA'][$i]['REFERENCIA']);
				$result['ORDEN_COMPRA'][$i]['FECHA_ORDEN_COMPRA']	= utf8_decode($result['ORDEN_COMPRA'][$i]['FECHA_ORDEN_COMPRA']);
				$result['ORDEN_COMPRA'][$i]['NV_NOM_EMPRESA']		= utf8_decode($result['ORDEN_COMPRA'][$i]['NV_NOM_EMPRESA']);
				$result['ORDEN_COMPRA'][$i]['OC_NOM_USUARIO']		= utf8_decode($result['ORDEN_COMPRA'][$i]['OC_NOM_USUARIO']);
				$result['ORDEN_COMPRA'][$i]['TIPO_ORDEN_COMPRA']	= utf8_decode($result['ORDEN_COMPRA'][$i]['TIPO_ORDEN_COMPRA']);
				$result['ORDEN_COMPRA'][$i]['A_NOM_EMPRESA']		= utf8_decode($result['ORDEN_COMPRA'][$i]['A_NOM_EMPRESA']);
				$result['ORDEN_COMPRA'][$i]['OC_NOM_MONEDA']		= utf8_decode($result['ORDEN_COMPRA'][$i]['OC_NOM_MONEDA']);
				$result['ORDEN_COMPRA'][$i]['ESTADO_OC']			= utf8_decode($result['ORDEN_COMPRA'][$i]['ESTADO_OC']);
				$result['ORDEN_COMPRA'][$i]['OBS']					= utf8_decode($result['ORDEN_COMPRA'][$i]['OBS']);
			}
			for($i=0; $i<count($result['ITEM_ORDEN_COMPRA']); $i++){
				$result['ITEM_ORDEN_COMPRA'][$i]['COD_PRODUCTO']	= utf8_decode($result['ITEM_ORDEN_COMPRA'][$i]['COD_PRODUCTO']);
				$result['ITEM_ORDEN_COMPRA'][$i]['NOM_PRODUCTO']	= utf8_decode($result['ITEM_ORDEN_COMPRA'][$i]['NOM_PRODUCTO']);
			}	
				
			return $result;
		}
	}
	function cli_update_costo_producto($empresa,$cod_producto,$precio){				
		$client =new simple_restclient($this->url);
		$client->SetClass("server_biggi");
		if($client->Service_Exists()){	# CHECK IF THE SERVICE EXISTS
			$client->SetAuth($this->uid, $this->pwd);	# AUTHENTICATE THE CONNECTION
			 
			$val = array('var1'=>$empresa, 'var2'=>$cod_producto, 'var3'=>$precio); # ARGUMENTS THAT WILL BE PASSED FOR THE METHOD
			$client->Call->Method("svr_update_costo_producto", $val,$return); 	# CALLING THE METHOD class->Morning('jeff','hi').

			$result = objectToArray(json_decode($return));	
			
			return $result;
		}
	}
	function cli_oc_facturada($lista_item_oc,$origen){				
		$client =new simple_restclient($this->url);
		$client->SetClass("server_biggi");
		if($client->Service_Exists()){	# CHECK IF THE SERVICE EXISTS
			$client->SetAuth($this->uid, $this->pwd);	# AUTHENTICATE THE CONNECTION

			$val = array('var1'=>$lista_item_oc, 'var2'=>$origen); # ARGUMENTS THAT WILL BE PASSED FOR THE METHOD
			$client->Call->Method("svr_oc_facturada", $val,$return); 	# CALLING THE METHOD class->Morning('jeff','hi').

			$result = objectToArray(json_decode($return));	
			
			return $result;
		}
	}
	function cli_oc_facturada_serv($lista_item_oc,$origen){				
		$client =new simple_restclient($this->url);
		$client->SetClass("server_servindus");
		if($client->Service_Exists()){	# CHECK IF THE SERVICE EXISTS
			$client->SetAuth($this->uid, $this->pwd);	# AUTHENTICATE THE CONNECTION
			
			$val = array('var1'=>$lista_item_oc, 'var2'=>$origen); # ARGUMENTS THAT WILL BE PASSED FOR THE METHOD
			$client->Call->Method("svr_oc_facturada_serv", $val,$return); 	# CALLING THE METHOD class->Morning('jeff','hi').
			
			$result = objectToArray(json_decode($return));	
			
			return $result;
		}
	}
	function cli_oc_por_facturar($cod_usuario,$inventario,$origen){
		$client =new simple_restclient($this->url);
		$client->SetClass("server_biggi");
		if($client->Service_Exists()){	# CHECK IF THE SERVICE EXISTS
			$client->SetAuth($this->uid, $this->pwd);	# AUTHENTICATE THE CONNECTION
			 
			//se envia un usuario fictisio para diferenciar con usuarios
			$codusuario = 9999;
			$array_origen = explode('|', $origen);
			$val = array('var1'=>$codusuario, 'var2'=>$inventario, 'var3'=>$origen); # ARGUMENTS THAT WILL BE PASSED FOR THE METHOD
			$client->Call->Method("svr_oc_por_facturar", $val,$return); 	# CALLING THE METHOD class->Morning('jeff','hi').
			
			$result = objectToArray(json_decode($return));	
			
			for($i=0; $i<count($result['ORDEN_COMPRA_ARRAY']); $i++){
				$result['ORDEN_COMPRA_ARRAY'][$i]['NOM_PRODUCTO']	= utf8_decode($result['ORDEN_COMPRA_ARRAY'][$i]['NOM_PRODUCTO']);
				$result['ORDEN_COMPRA_ARRAY'][$i]['NOM_USUARIO']	= utf8_decode($result['ORDEN_COMPRA_ARRAY'][$i]['NOM_USUARIO']);
			}
			
			$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
			
			if($array_origen[1] == "BODEGA")
				$sp = "spi_inf_oc_por_facturar_bodega";
			else
				$sp = "spi_inf_oc_por_facturar_tdnx";

			$error = false;
			$db->BEGIN_TRANSACTION();
			for($i=0; $i<count($result); $i++) 
			{
				$fecha_inf_oc_por_facturar_tdnx = $result[$i]['FECHA_INF_OC_POR_FACTURAR_TDNX'];
				$cod_origen_compra				= $result[$i]['COD_ORDEN_COMPRA'];
				$fecha_orden_compra				= $result[$i]['FECHA_ORDEN_COMPRA'];
				$cod_item_orden_compra			= $result[$i]['COD_ITEM_ORDEN_COMPRA'];
				$cod_producto					= $result[$i]['COD_PRODUCTO'];
				$nom_producto					= $result[$i]['NOM_PRODUCTO'];
				$cantidad_oc					= $result[$i]['CANTIDAD_OC'];
				$cod_nota_venta					= $result[$i]['COD_NOTA_VENTA'];
				$cod_usuario_vendedor			= $result[$i]['COD_USUARIO_VENDEDOR'];
				$nom_usuario					= $result[$i]['NOM_USUARIO'];
				
				$cod_usuario_vendedor = ($cod_usuario_vendedor == "") ? "null":"'".$cod_usuario_vendedor."'";
				$nom_usuario 		  = ($nom_usuario == "") ? "null":"'".$nom_usuario."'";
				$cod_nota_venta	  = ($cod_nota_venta == "") ? "null": $cod_nota_venta;
				$fecha_inf_oc_por_facturar_tdnx = $this->str2date($fecha_inf_oc_por_facturar_tdnx);
				$fecha_orden_compra = $this->str2date($fecha_orden_compra);
				
				$param =   "'$array_origen[0]'
							,$fecha_inf_oc_por_facturar_tdnx
							,$cod_usuario
							,$cod_origen_compra
							,$fecha_orden_compra
							,$cod_item_orden_compra
							,'$cod_producto'
							,'$nom_producto'
							,$cantidad_oc
							,$cod_nota_venta
							,$cod_usuario_vendedor
							,$nom_usuario";
				
				if (!$db->EXECUTE_SP($sp, $param)){
					$error = true;	
				}
			}
			if($error){
				$db->ROLLBACK_TRANSACTION();						
			}else{
				$db->COMMIT_TRANSACTION();
			}
			return true;
		}
	}
	
	function cli_oc_por_facturar_indiv($cod_usuario,$inventario,$origen, $cod_orden_compra){
		$client =new simple_restclient($this->url);
		$client->SetClass("server_biggi");
		if($client->Service_Exists()){	# CHECK IF THE SERVICE EXISTS
			$client->SetAuth($this->uid, $this->pwd);	# AUTHENTICATE THE CONNECTION
			 
			//se envia un usuario fictisio para diferenciar con usuarios
			$codusuario = 9999;
			$array_origen = explode('|', $origen);
			$val = array('var1'=>$codusuario, 'var2'=>$inventario, 'var3'=>$origen, 'var4'=>$cod_orden_compra); # ARGUMENTS THAT WILL BE PASSED FOR THE METHOD
			$client->Call->Method("svr_oc_por_facturar_indv", $val,$return); 	# CALLING THE METHOD class->Morning('jeff','hi').
			
			$result = objectToArray(json_decode($return));	

			for($i=0; $i<count($result['ORDEN_COMPRA_ARRAY']); $i++){
				$result['ORDEN_COMPRA_ARRAY'][$i]['NOM_PRODUCTO']	= utf8_decode($result['ORDEN_COMPRA_ARRAY'][$i]['NOM_PRODUCTO']);
				$result['ORDEN_COMPRA_ARRAY'][$i]['NOM_USUARIO']	= utf8_decode($result['ORDEN_COMPRA_ARRAY'][$i]['NOM_USUARIO']);
			}
			
			$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
			
			if($array_origen[1] == "BODEGA")
				$sp = "spi_inf_oc_por_facturar_bodega";
			else
				$sp = "spi_inf_oc_por_facturar_tdnx";

			$error = false;
			$db->BEGIN_TRANSACTION();
			for($i=0; $i<count($result); $i++) 
			{
				$fecha_inf_oc_por_facturar_tdnx = $result[$i]['FECHA_INF_OC_POR_FACTURAR_TDNX'];
				$cod_origen_compra				= $result[$i]['COD_ORDEN_COMPRA'];
				$fecha_orden_compra				= $result[$i]['FECHA_ORDEN_COMPRA'];
				$cod_item_orden_compra			= $result[$i]['COD_ITEM_ORDEN_COMPRA'];
				$cod_producto					= $result[$i]['COD_PRODUCTO'];
				$nom_producto					= $result[$i]['NOM_PRODUCTO'];
				$cantidad_oc					= $result[$i]['CANTIDAD_OC'];
				$cod_nota_venta					= $result[$i]['COD_NOTA_VENTA'];
				$cod_usuario_vendedor			= $result[$i]['COD_USUARIO_VENDEDOR'];
				$nom_usuario					= $result[$i]['NOM_USUARIO'];
				
				$cod_usuario_vendedor = ($cod_usuario_vendedor == "") ? "null":"'".$cod_usuario_vendedor."'";
				$nom_usuario 		  = ($nom_usuario == "") ? "null":"'".$nom_usuario."'";
				$cod_nota_venta	  = ($cod_nota_venta == "") ? "null": $cod_nota_venta;
				$fecha_inf_oc_por_facturar_tdnx = $this->str2date($fecha_inf_oc_por_facturar_tdnx);
				$fecha_orden_compra = $this->str2date($fecha_orden_compra);
				
				$param =   "'$array_origen[0]'
							,$fecha_inf_oc_por_facturar_tdnx
							,$cod_usuario
							,$cod_origen_compra
							,$fecha_orden_compra
							,$cod_item_orden_compra
							,'$cod_producto'
							,'$nom_producto'
							,$cantidad_oc
							,$cod_nota_venta
							,$cod_usuario_vendedor
							,$nom_usuario";
				
				if (!$db->EXECUTE_SP($sp, $param)){
					$error = true;	
				}
			}
			if($error){
				$db->ROLLBACK_TRANSACTION();						
			}else{
				$db->COMMIT_TRANSACTION();
			}
			return true;
		}
	}
	
	function cli_tabla($nom_tabla, $cod_tabla){
		$client =new simple_restclient($this->url);
		$client->SetClass("server_biggi");
		if($client->Service_Exists()){	# CHECK IF THE SERVICE EXISTS
			$client->SetAuth($this->uid, $this->pwd);	# AUTHENTICATE THE CONNECTION
			 
			$val = array('var1'=>$nom_tabla, 'var2'=>$cod_tabla); # ARGUMENTS THAT WILL BE PASSED FOR THE METHOD
			$client->Call->Method("svr_consulta_tabla", $val, $return); 	# CALLING THE METHOD class->Morning('jeff','hi').
			if($return != 'NO_REGISTRO'){
				$result = objectToArray(json_decode($return));
				$array = array_keys($result['TABLA'][0]);
				
				for($i=0; $i < count($result['TABLA']) ; $i++)
					for($j=0; $j < count($array) ; $j++)
						$result['TABLA'][$i][$array[$j]]	= utf8_decode($result['TABLA'][$i][$array[$j]]);
	
				return $result;
			}else{
				return 'NO_REGISTRO';
			}
		}
	}
	
	function cli_add_faprov($array_datos, $sistema){
		$client =new simple_restclient($this->url);
		$client->SetClass("server_biggi");
		if($client->Service_Exists()){	# CHECK IF THE SERVICE EXISTS
			$client->SetAuth($this->uid, $this->pwd);	# AUTHENTICATE THE CONNECTION
			 
			$val = array('var1'=>$array_datos, 'var2'=>$sistema); # ARGUMENTS THAT WILL BE PASSED FOR THE METHOD
			$client->Call->Method("svr_add_faprov", $val, $return); 	# CALLING THE METHOD class->Morning('jeff','hi').
			
			return $return;
		}
	}
	function cli_add_faprov_bodega($array_datos, $sistema){
		$client =new simple_restclient($this->url);
		$client->SetClass("server_biggi");
		if($client->Service_Exists()){	# CHECK IF THE SERVICE EXISTS
			$client->SetAuth($this->uid, $this->pwd);	# AUTHENTICATE THE CONNECTION
			 
			$val = array('var1'=>$array_datos, 'var2'=>$sistema); # ARGUMENTS THAT WILL BE PASSED FOR THE METHOD
			$client->Call->Method("svr_add_faprov_bodega", $val, $return); 	# CALLING THE METHOD class->Morning('jeff','hi').
			
			return $return;
		}
	}
	function cli_wo_pago_faprov($cod_usuario, $sistema_pf, $sistema){
		$client =new simple_restclient($this->url);
		$client->SetClass("server_biggi");
		if($client->Service_Exists()){	# CHECK IF THE SERVICE EXISTS
			$client->SetAuth($this->uid, $this->pwd);	# AUTHENTICATE THE CONNECTION
			 
			$val = array('var1'=>$sistema_pf, 'var2'=>$sistema); # ARGUMENTS THAT WILL BE PASSED FOR THE METHOD
			$client->Call->Method("svr_wo_pago_faprov", $val, $return); 	# CALLING THE METHOD class->Morning('jeff','hi').
			
			$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
			$result = objectToArray(json_decode($return));
			
			for($i=0; $i<count($result['ORDEN_COMPRA_ARRAY']); $i++){
				$result['PAGO_FAPROV_COMERCIAL'][$i]['NOM_USUARIO']				= utf8_decode($result['PAGO_FAPROV_COMERCIAL'][$i]['NOM_USUARIO']);
				$result['PAGO_FAPROV_COMERCIAL'][$i]['NOM_EMPRESA']				= utf8_decode($result['PAGO_FAPROV_COMERCIAL'][$i]['NOM_EMPRESA']);
				$result['PAGO_FAPROV_COMERCIAL'][$i]['RUT']						= utf8_decode($result['PAGO_FAPROV_COMERCIAL'][$i]['RUT']);
				$result['PAGO_FAPROV_COMERCIAL'][$i]['ALIAS']					= utf8_decode($result['PAGO_FAPROV_COMERCIAL'][$i]['ALIAS']);
				$result['PAGO_FAPROV_COMERCIAL'][$i]['NOM_TIPO_PAGO_FAPROV']	= utf8_decode($result['PAGO_FAPROV_COMERCIAL'][$i]['NOM_TIPO_PAGO_FAPROV']);
			}
			
			$sp = "spi_pago_faprov_comercial";
			$error = false;
			$db->BEGIN_TRANSACTION();

			for($i=0 ; $i < count($result['PAGO_FAPROV_COMERCIAL']) ; $i++){
				$fecha_pago_faprov	= $this->str2date($result['PAGO_FAPROV_COMERCIAL'][$i]['FECHA_PAGO_FAPROV']);
				$fecha_documento	= $this->str2date($result['PAGO_FAPROV_COMERCIAL'][$i]['FECHA_DOCUMENTO']);
				
				$param = $result['PAGO_FAPROV_COMERCIAL'][$i]['COD_PAGO_FAPROV'].",".
						 $fecha_pago_faprov.",'".
						 $result['PAGO_FAPROV_COMERCIAL'][$i]['NOM_USUARIO']."',".
						 $result['PAGO_FAPROV_COMERCIAL'][$i]['NRO_DOCUMENTO'].",".
						 $fecha_documento.",".
						 $result['PAGO_FAPROV_COMERCIAL'][$i]['MONTO_DOCUMENTO'].",".
						 $cod_usuario.",".
						 $result['PAGO_FAPROV_COMERCIAL'][$i]['COD_EMPRESA'].",'".
						 $result['PAGO_FAPROV_COMERCIAL'][$i]['NOM_EMPRESA']."','".
						 $result['PAGO_FAPROV_COMERCIAL'][$i]['RUT']."','".
						 $result['PAGO_FAPROV_COMERCIAL'][$i]['ALIAS']."','".
						 $result['PAGO_FAPROV_COMERCIAL'][$i]['NOM_TIPO_PAGO_FAPROV']."'";
				
				if (!$db->EXECUTE_SP($sp, $param)){
					$error = true;	
				}
			}
			
			if($error){
				$db->ROLLBACK_TRANSACTION();						
			}else{
				$db->COMMIT_TRANSACTION();
			}
			
		}
	}
	
	function cli_oc_x_facturar_tipo_a($cod_usuario,$inventario,$origen){
		$client =new simple_restclient($this->url);
		$client->SetClass("server_biggi");
		if($client->Service_Exists()){	# CHECK IF THE SERVICE EXISTS
			$client->SetAuth($this->uid, $this->pwd);	# AUTHENTICATE THE CONNECTION
			 
			$val = array('var1'=>$cod_usuario, 'var2'=>$inventario, 'var3'=>$origen); # ARGUMENTS THAT WILL BE PASSED FOR THE METHOD
			$client->Call->Method("svr_oc_x_facturar_tipo_a", $val,$return); 	# CALLING THE METHOD class->Morning('jeff','hi').
			
			$result = objectToArray(json_decode($return));	
			$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
			
			for($i=0; $i<count($result); $i++){
				$result[$i]['NOM_CLIENTE_NV']		= utf8_decode($result[$i]['NOM_CLIENTE_NV']);
				$result[$i]['NOM_USUARIO']			= utf8_decode($result[$i]['NOM_USUARIO']);
				$result[$i]['COD_USUARIO_VENDEDOR']	= utf8_decode($result[$i]['COD_USUARIO_VENDEDOR']);
			}
			
			$sp = "spi_inf_oc_x_fact_tdnx";

			$error = false;
			$db->BEGIN_TRANSACTION();
			
			for($i=0 ; $i < count($result) ; $i++){
				$fecha_entrega				= $this->str2date($result[$i]['FECHA_ENTREGA']);
				
				$fecha_solicita_facturacion	= $result[$i]['FECHA_SOLICITA_FACTURACION'];
				$fecha_solicita_facturacion = ($fecha_solicita_facturacion =='') ? "null" : $this->str2date($fecha_solicita_facturacion);
				
				$autoriza_facturacion = $result[$i]['AUTORIZA_FACTURACION'];
				$autoriza_facturacion = ($autoriza_facturacion =='') ? "null" : "'$autoriza_facturacion'";
				
				$param =   "'$origen'
							,".$result[$i]['COD_ORDEN_COMPRA']."
							,$autoriza_facturacion
							,$fecha_solicita_facturacion
							,".$result[$i]['COD_NOTA_VENTA']."
							,$fecha_entrega
							,$cod_usuario
							,'".$result[$i]['NOM_CLIENTE_NV']."'
							,'".$result[$i]['COD_USUARIO_VENDEDOR']."'
							,".$result[$i]['TOTAL_NETO']."
							,'".$result[$i]['NOM_USUARIO']."'";
				
				if (!$db->EXECUTE_SP($sp, $param)){
					$error = true;	
				}
			}
				
		}
		if($error)
			$db->ROLLBACK_TRANSACTION();						
		else
			$db->COMMIT_TRANSACTION();
	}
	
	function cli_wi_pago_faprov($cod_usuario, $cod_pago_faprov){
		$client =new simple_restclient($this->url);
		$client->SetClass("server_biggi");
		if($client->Service_Exists()){	# CHECK IF THE SERVICE EXISTS
			$client->SetAuth($this->uid, $this->pwd);	# AUTHENTICATE THE CONNECTION
			 
			$val = array('var1'=>$cod_pago_faprov); # ARGUMENTS THAT WILL BE PASSED FOR THE METHOD
			$client->Call->Method("svr_wi_pago_faprov", $val,$return); 	# CALLING THE METHOD class->Morning('jeff','hi').
			
			$result = objectToArray(json_decode($return));	
			$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
			
			$sp = "spi_pago_faprov_faprov_com";

			$error = false;
			$db->BEGIN_TRANSACTION();
			
			for($i=0 ; $i < count($result['PAGO_FAPROV_FAPROV_COMERCIAL']) ; $i++){
				$fecha_faprov	= $this->str2date($result['PAGO_FAPROV_FAPROV_COMERCIAL'][$i]['FECHA_FAPROV']);
				
				$param =	$result['PAGO_FAPROV_FAPROV_COMERCIAL'][$i]['COD_PAGO_FAPROV_FAPROV'].",".
							$result['PAGO_FAPROV_FAPROV_COMERCIAL'][$i]['COD_PAGO_FAPROV'].",".
							$result['PAGO_FAPROV_FAPROV_COMERCIAL'][$i]['COD_FAPROV'].",".
							$result['PAGO_FAPROV_FAPROV_COMERCIAL'][$i]['MONTO_ASIGNADO'].",".
							$cod_usuario.",".
							$result['PAGO_FAPROV_FAPROV_COMERCIAL'][$i]['NRO_FAPROV'].",".
							$fecha_faprov.",".
							$result['PAGO_FAPROV_FAPROV_COMERCIAL'][$i]['TOTAL_CON_IVA_FA'].",".
							$result['PAGO_FAPROV_FAPROV_COMERCIAL'][$i]['MONTO_NCPROV'].",".
							$result['PAGO_FAPROV_FAPROV_COMERCIAL'][$i]['SALDO_SIN_PAGO_FAPROV'].",".
							$result['PAGO_FAPROV_FAPROV_COMERCIAL'][$i]['PAGO_ANTERIOR'].",'".
							$result['PAGO_FAPROV_FAPROV_COMERCIAL'][$i]['NOM_CUENTA_CORRIENTE']."'";

				if (!$db->EXECUTE_SP($sp, $param)){
					$error = true;	
				}
			}
				
		}
		if($error)
			$db->ROLLBACK_TRANSACTION();						
		else
			$db->COMMIT_TRANSACTION();
	}
	
	function cli_print_oc_sistema($cod_orden_compra, $sistema){
		$client =new simple_restclient($this->url);
		$client->SetClass("server_biggi");
		if($client->Service_Exists()){	# CHECK IF THE SERVICE EXISTS
			$client->SetAuth($this->uid, $this->pwd);	# AUTHENTICATE THE CONNECTION
			 
			$val = array('var1'=>$cod_orden_compra, 'var2'=>$sistema); # ARGUMENTS THAT WILL BE PASSED FOR THE METHOD
			$client->Call->Method("svr_print_oc_sistema", $val, $return); 	# CALLING THE METHOD class->Morning('jeff','hi').
			
			$result = objectToArray(json_decode($return));
			
			$sql = "";
			for($i=0; $i<count($result['ORDEN_COMPRA_SERV']); $i++){

				$result['ORDEN_COMPRA_SERV'][$i]['REFERENCIA']			= utf8_decode($result['ORDEN_COMPRA_SERV'][$i]['REFERENCIA']);
				$result['ORDEN_COMPRA_SERV'][$i]['OBS']					= utf8_decode($result['ORDEN_COMPRA_SERV'][$i]['OBS']);
				$result['ORDEN_COMPRA_SERV'][$i]['NOM_EMPRESA']			= utf8_decode($result['ORDEN_COMPRA_SERV'][$i]['NOM_EMPRESA']);
				$result['ORDEN_COMPRA_SERV'][$i]['DIRECCION']			= utf8_decode($result['ORDEN_COMPRA_SERV'][$i]['DIRECCION']);
				$result['ORDEN_COMPRA_SERV'][$i]['FECHA_ORDEN_COMPRA']	= utf8_decode($result['ORDEN_COMPRA_SERV'][$i]['FECHA_ORDEN_COMPRA']);
				$result['ORDEN_COMPRA_SERV'][$i]['NOM_PERSONA']			= utf8_decode($result['ORDEN_COMPRA_SERV'][$i]['NOM_PERSONA']);
				$result['ORDEN_COMPRA_SERV'][$i]['NOM_USUARIO']			= utf8_decode($result['ORDEN_COMPRA_SERV'][$i]['NOM_USUARIO']);
				$result['ORDEN_COMPRA_SERV'][$i]['MAIL']				= utf8_decode($result['ORDEN_COMPRA_SERV'][$i]['MAIL']);
				$result['ORDEN_COMPRA_SERV'][$i]['NOM_PRODUCTO']		= utf8_decode($result['ORDEN_COMPRA_SERV'][$i]['NOM_PRODUCTO']);
				$result['ORDEN_COMPRA_SERV'][$i]['COD_PRODUCTO']		= utf8_decode($result['ORDEN_COMPRA_SERV'][$i]['COD_PRODUCTO']);
				$result['ORDEN_COMPRA_SERV'][$i]['SIMBOLO']				= utf8_decode($result['ORDEN_COMPRA_SERV'][$i]['SIMBOLO']);
				$result['ORDEN_COMPRA_SERV'][$i]['NOM_EMPRESA_EMISOR']	= utf8_decode($result['ORDEN_COMPRA_SERV'][$i]['NOM_EMPRESA_EMISOR']);
				$result['ORDEN_COMPRA_SERV'][$i]['RUT_EMPRESA']			= utf8_decode($result['ORDEN_COMPRA_SERV'][$i]['RUT_EMPRESA']);
				$result['ORDEN_COMPRA_SERV'][$i]['DIR_EMPRESA']			= utf8_decode($result['ORDEN_COMPRA_SERV'][$i]['DIR_EMPRESA']);
				$result['ORDEN_COMPRA_SERV'][$i]['GIRO_EMPRESA']		= utf8_decode($result['ORDEN_COMPRA_SERV'][$i]['GIRO_EMPRESA']);
				$result['ORDEN_COMPRA_SERV'][$i]['CIUDAD_EMPRESA']		= utf8_decode($result['ORDEN_COMPRA_SERV'][$i]['CIUDAD_EMPRESA']);
				$result['ORDEN_COMPRA_SERV'][$i]['PAIS_EMPRESA']		= utf8_decode($result['ORDEN_COMPRA_SERV'][$i]['PAIS_EMPRESA']);
				$result['ORDEN_COMPRA_SERV'][$i]['SITIO_WEB_EMPRESA']	= utf8_decode($result['ORDEN_COMPRA_SERV'][$i]['SITIO_WEB_EMPRESA']);
				
				$COD_NOTA_VENTA	= ($result['ORDEN_COMPRA_SERV'][$i]['COD_NOTA_VENTA'] =='') ? "null" : $result['ORDEN_COMPRA_SERV'][$i]['COD_NOTA_VENTA'];
				$PORC_DSCTO1	= ($result['ORDEN_COMPRA_SERV'][$i]['PORC_DSCTO1'] =='') ? "null" : $result['ORDEN_COMPRA_SERV'][$i]['PORC_DSCTO1'];
				$PORC_DSCTO2	= ($result['ORDEN_COMPRA_SERV'][$i]['PORC_DSCTO2'] =='') ? "null" : $result['ORDEN_COMPRA_SERV'][$i]['PORC_DSCTO2'];
				$OBS			= ($result['ORDEN_COMPRA_SERV'][$i]['OBS'] =='') ? "null" : "'".$result['ORDEN_COMPRA_SERV'][$i]['OBS']."'";
				$TELEFONO		= ($result['ORDEN_COMPRA_SERV'][$i]['TELEFONO'] =='') ? "null" : $result['ORDEN_COMPRA_SERV'][$i]['TELEFONO'];
				$FAX			= ($result['ORDEN_COMPRA_SERV'][$i]['FAX'] =='') ? "null" : "'".$result['ORDEN_COMPRA_SERV'][$i]['FAX']."'";
				$NOM_PERSONA	= ($result['ORDEN_COMPRA_SERV'][$i]['NOM_PERSONA'] =='') ? "null" : "'".$result['ORDEN_COMPRA_SERV'][$i]['NOM_PERSONA']."'";
				
				$sql.= "SELECT ".$result['ORDEN_COMPRA_SERV'][$i]['COD_ORDEN_COMPRA']." COD_ORDEN_COMPRA
							  ,$COD_NOTA_VENTA COD_NOTA_VENTA
							  ,".$result['ORDEN_COMPRA_SERV'][$i]['SUBTOTAL']." SUBTOTAL
							  ,$PORC_DSCTO1 PORC_DSCTO1
							  ,".$result['ORDEN_COMPRA_SERV'][$i]['MONTO_DSCTO1']." MONTO_DSCTO1
							  ,$PORC_DSCTO2 PORC_DSCTO2
							  ,".$result['ORDEN_COMPRA_SERV'][$i]['MONTO_DSCTO2']." MONTO_DSCTO2
							  ,".$result['ORDEN_COMPRA_SERV'][$i]['TOTAL_NETO']." TOTAL_NETO
							  ,".$result['ORDEN_COMPRA_SERV'][$i]['PORC_IVA']." PORC_IVA
							  ,".$result['ORDEN_COMPRA_SERV'][$i]['MONTO_IVA']." MONTO_IVA
							  ,".$result['ORDEN_COMPRA_SERV'][$i]['TOTAL_CON_IVA']." TOTAL_CON_IVA
							  ,'".$result['ORDEN_COMPRA_SERV'][$i]['REFERENCIA']."' REFERENCIA
							  ,$OBS OBS
							  ,'".$result['ORDEN_COMPRA_SERV'][$i]['NOM_EMPRESA']."' NOM_EMPRESA
							  ,".$result['ORDEN_COMPRA_SERV'][$i]['RUT']." RUT
							  ,'".$result['ORDEN_COMPRA_SERV'][$i]['DIG_VERIF']."' DIG_VERIF
							  ,'".$result['ORDEN_COMPRA_SERV'][$i]['DIRECCION']."' DIRECCION
							  ,'".$result['ORDEN_COMPRA_SERV'][$i]['FECHA_ORDEN_COMPRA']."' FECHA_ORDEN_COMPRA
							  ,$TELEFONO TELEFONO
							  ,$FAX FAX
							  ,$NOM_PERSONA NOM_PERSONA
							  ,'".$result['ORDEN_COMPRA_SERV'][$i]['NOM_USUARIO']."' NOM_USUARIO
							  ,'".$result['ORDEN_COMPRA_SERV'][$i]['MAIL']."' MAIL
							  ,'".$result['ORDEN_COMPRA_SERV'][$i]['NOM_PRODUCTO']."' NOM_PRODUCTO
							  ,'".$result['ORDEN_COMPRA_SERV'][$i]['COD_PRODUCTO']."' COD_PRODUCTO
							  ,'".$result['ORDEN_COMPRA_SERV'][$i]['ITEM']."' ITEM
							  ,'".$result['ORDEN_COMPRA_SERV'][$i]['CANTIDAD']."' CANTIDAD
							  ,'".$result['ORDEN_COMPRA_SERV'][$i]['PRECIO']."' PRECIO
							  ,'".$result['ORDEN_COMPRA_SERV'][$i]['TOTAL_IOC']."' TOTAL_IOC
							  ,'".$result['ORDEN_COMPRA_SERV'][$i]['SIMBOLO']."' SIMBOLO
							  ,'".$result['ORDEN_COMPRA_SERV'][$i]['NOM_EMPRESA_EMISOR']."' NOM_EMPRESA_EMISOR
							  ,'".$result['ORDEN_COMPRA_SERV'][$i]['RUT_EMPRESA']."' RUT_EMPRESA
							  ,'".$result['ORDEN_COMPRA_SERV'][$i]['DIR_EMPRESA']."' DIR_EMPRESA
							  ,'".$result['ORDEN_COMPRA_SERV'][$i]['GIRO_EMPRESA']."' GIRO_EMPRESA
							  ,'".$result['ORDEN_COMPRA_SERV'][$i]['TEL_EMPRESA']."' TEL_EMPRESA
							  ,'".$result['ORDEN_COMPRA_SERV'][$i]['FAX_EMPRESA']."' FAX_EMPRESA
							  ,'".$result['ORDEN_COMPRA_SERV'][$i]['MAIL_EMPRESA']."' MAIL_EMPRESA
							  ,'".$result['ORDEN_COMPRA_SERV'][$i]['CIUDAD_EMPRESA']."' CIUDAD_EMPRESA
							  ,'".$result['ORDEN_COMPRA_SERV'][$i]['PAIS_EMPRESA']."' PAIS_EMPRESA
							  ,'".$result['ORDEN_COMPRA_SERV'][$i]['SITIO_WEB_EMPRESA']."' SITIO_WEB_EMPRESA
							  ,".$result['ORDEN_COMPRA_SERV'][$i]['CC_EMPRESA']." CC_EMPRESA
						UNION ";
			}
			$sql = trim($sql, 'UNION ');
			return $sql;
		}
	}
	
	function cli_cambio_estado_traspaso($cod_pago_faprov, $estado){
		$client =new simple_restclient($this->url);
		$client->SetClass("server_biggi");
		if($client->Service_Exists()){	# CHECK IF THE SERVICE EXISTS
			$client->SetAuth($this->uid, $this->pwd);	# AUTHENTICATE THE CONNECTION
			 
			$val = array('var1'=>$cod_pago_faprov, 'var2'=>$estado); # ARGUMENTS THAT WILL BE PASSED FOR THE METHOD
			$client->Call->Method("svr_cambio_estado_traspaso", $val, $return); 	# CALLING THE METHOD class->Morning('jeff','hi').
		}
	}
	
	function cli_entrada_bodega($cod_orden_compra, $nro_factura){
		$client =new simple_restclient($this->url);
		$client->SetClass("server_biggi");
		if($client->Service_Exists()){	# CHECK IF THE SERVICE EXISTS
			$client->SetAuth($this->uid, $this->pwd);	# AUTHENTICATE THE CONNECTION
			 
			$val = array('var1'=>$cod_orden_compra, 'var2'=>$nro_factura); # ARGUMENTS THAT WILL BE PASSED FOR THE METHOD
			$client->Call->Method("svr_entrada_bodega", $val, $return); 	# CALLING THE METHOD class->Morning('jeff','hi').

			if($return <> 'NO_COINCIDE' && $return <> 'NO_EXISTE' && $return <> 'OTRA_EMPRESA' && $return <> 'DISTINTO_OC')
				$return = objectToArray(json_decode($return));	
			
			return $return;
		}
	}
	
	function cli_entrada_bodega_serv($cod_orden_compra, $nro_factura){
		$client =new simple_restclient($this->url);
		$client->SetClass("server_servindus");
		if($client->Service_Exists()){	# CHECK IF THE SERVICE EXISTS
			$client->SetAuth($this->uid, $this->pwd);	# AUTHENTICATE THE CONNECTION
			 
			$val = array('var1'=>$cod_orden_compra, 'var2'=>$nro_factura); # ARGUMENTS THAT WILL BE PASSED FOR THE METHOD
			$client->Call->Method("svr_entrada_bodega_serv", $val, $return); 	# CALLING THE METHOD class->Morning('jeff','hi').

			if($return <> 'NO_COINCIDE' && $return <> 'NO_EXISTE' && $return <> 'OTRA_EMPRESA' && $return <> 'DISTINTO_OC')
				$return = objectToArray(json_decode($return));	
			
			return $return;
		}
	}
	
	function cli_add_nota_credito($cod_pago_faprov){
		$client =new simple_restclient($this->url);
		$client->SetClass("server_biggi");
		if($client->Service_Exists()){	# CHECK IF THE SERVICE EXISTS
			$client->SetAuth($this->uid, $this->pwd);	# AUTHENTICATE THE CONNECTION
			 
			$val = array('var1'=>$cod_pago_faprov); # ARGUMENTS THAT WILL BE PASSED FOR THE METHOD
			$client->Call->Method("svr_add_nota_credito", $val, $return); 	# CALLING THE METHOD class->Morning('jeff','hi').
			$return = objectToArray(json_decode($return));	
			
			return $return;
		}
	}
	
	function consulta_stock($cod_producto, $sistema){
		$client =new simple_restclient($this->url);
		$client->SetClass("server_biggi");
		if($client->Service_Exists()){	# CHECK IF THE SERVICE EXISTS
			$client->SetAuth($this->uid, $this->pwd);	# AUTHENTICATE THE CONNECTION
			 
			$val = array('var1'=>$cod_producto, 'var2'=>$sistema); # ARGUMENTS THAT WILL BE PASSED FOR THE METHOD
			$client->Call->Method("svr_consulta_stock", $val, $return); 	# CALLING THE METHOD class->Morning('jeff','hi').
			
			$result = objectToArray(json_decode($return));	
			
			return $result;
		}
	}
	
	function cli_factura_arriendo($array_datos, $cliente){
		$client =new simple_restclient($this->url);
		$client->SetClass("server_biggi");
		if($client->Service_Exists()){	# CHECK IF THE SERVICE EXISTS
			$client->SetAuth($this->uid, $this->pwd);	# AUTHENTICATE THE CONNECTION
			 
			$val = array('var1'=>$array_datos, 'var2'=>$cliente); # ARGUMENTS THAT WILL BE PASSED FOR THE METHOD
			$client->Call->Method("svr_factura_arriendo", $val, $return); 	# CALLING THE METHOD class->Morning('jeff','hi').
			
			return $return;
		}
	}
}
?>