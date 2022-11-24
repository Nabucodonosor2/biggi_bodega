<?php
require_once(dirname(__FILE__)."/../../appl.ini");
require_once(dirname(__FILE__)."/class_database.php");
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

class server_biggi{
	
	function svr_add_faprov_serv($array_datos, $sistema){

		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		//Valida si es que la factura provedor ya haya sido creada
		$sql = "SELECT COD_FAPROV
				FROM FAPROV
				WHERE NRO_FAPROV = ".$array_datos[0]['NRO_FACTURA']."
				AND WS_ORIGEN = 'SERVINDUS";
		
		$result = $db->build_results($sql);
		if(count($result) > 0)
			return 'MSJ_REGISTRO';
		
		if($sistema === 'BODEGA')
			$K_COD_EMPRESA = 5;
		else if($sistema == 'COMERCIAL')
			$K_COD_EMPRESA = 1899;
		else if($sistema == 'RENTAL')
			$K_COD_EMPRESA = 32;
		else if($sistema == 'TODOINOX')
			$K_COD_EMPRESA = 38;	
			
		//Valida que exista la OC y que sea para SERVINDUS
		$sql = "SELECT COD_ORDEN_COMPRA
				FROM ORDEN_COMPRA
				WHERE COD_ORDEN_COMPRA = ".$array_datos[0]['NRO_ORDEN_COMPRA']."
				AND COD_EMPRESA = $K_COD_EMPRESA"; //cod_empresa  => Servindus
				
		$result = $db->build_results($sql);
		if(count($result) == 0)
			return 'NO_REGISTRO_OC';	
		
		$cod_cuenta_compra = "NULL";	
		if($sistema == 'COMERCIAL'){
			$sql_cc = "SELECT NV.COD_EMPRESA
					   FROM NOTA_VENTA NV
					 	   ,ORDEN_COMPRA OC
					   WHERE OC.COD_ORDEN_COMPRA = ".$array_datos[0]['NRO_ORDEN_COMPRA']."
					   AND OC.COD_NOTA_VENTA = NV.COD_NOTA_VENTA";
			$result_cc = $db->build_results($sql_cc);
			
			$sql_cce = "SELECT COD_PROYECTO_COMPRA
						FROM CENTRO_COSTO_EMPRESA
						WHERE COD_EMPRESA =".$result_cc[0]['COD_EMPRESA'];
			$result_cce = $db->build_results($sql_cce);
			
			if($result_cce[0]['COD_PROYECTO_COMPRA'] == '' || count(result_cce) == 0){
				$cod_cuenta_compra = "NULL";
			}else{
				$cod_cuenta_compra = $result_cce[0]['COD_PROYECTO_COMPRA'];
			}
		}
		
		//Se hace la insercion de datos
		/**************FA_PROV**************/
		$sp = "spu_faprov";
		$param = "'INSERT'
				  ,NULL
				  ,1				--A definir
				  ,$K_COD_EMPRESA	--Servindus
				  ,4				--Factura Electronica
				  ,1				--Ingresada
				  ,".$array_datos[0]['NRO_FACTURA']."
				  ,'".$array_datos[0]['FECHA_FACTURA']."'
				  ,".$array_datos[0]['TOTAL_NETO']."
				  ,".$array_datos[0]['MONTO_IVA']."
				  ,".$array_datos[0]['TOTAL_CON_IVA']."
				  ,NULL
				  ,NULL
				  ,'ORDEN_COMPRA'
				  ,$cod_cuenta_compra
				  ,'SERVINDUS'";

		$db->query("exec ".$sp.' '.$param);
		/***********************************/
		
		/**************ITEM_FA_PROV**************/
		$COD_FAPROV = $db->GET_IDENTITY();
		$sp = "spu_item_faprov";
		
		$param2 = "'INSERT'
				  ,NULL
				  ,$COD_FAPROV
				  ,".$array_datos[0]['NRO_ORDEN_COMPRA']."
				  ,".$array_datos[0]['TOTAL_CON_IVA'];
		
		$db->query("exec ".$sp.' '.$param2);	
		/****************************************/
		
		return 'HECHO';
	}
	
	function svr_add_faprov($array_datos, $sistema){

		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		//Valida si es que la factura provedor ya haya sido creada
		$sql = "SELECT COD_FAPROV
				FROM FAPROV
				WHERE NRO_FAPROV = ".$array_datos[0]['NRO_FACTURA']."
				AND WS_ORIGEN = 'TODOINOX'";
		
		$result = $db->build_results($sql);
		if(count($result) > 0)
			return 'MSJ_REGISTRO';
		
		if($sistema === 'BODEGA')
			$K_COD_EMPRESA = 4;
		else if($sistema == 'COMERCIAL')
			$K_COD_EMPRESA = 1302;
			
		else if($sistema == 'RENTAL')
			$K_COD_EMPRESA = 4;

		//Valida que exista la OC y que sea para TODOINOX
		$sql = "SELECT COD_ORDEN_COMPRA
				FROM ORDEN_COMPRA
				WHERE COD_ORDEN_COMPRA = ".$array_datos[0]['NRO_ORDEN_COMPRA']."
				AND COD_EMPRESA = $K_COD_EMPRESA"; //cod_empresa  => Todoinox
				
		$result = $db->build_results($sql);
		if(count($result) == 0)
			return 'NO_REGISTRO_OC';	
		
		$cod_cuenta_compra = "NULL";	
		if($sistema == 'COMERCIAL'){
			$sql_cc = "SELECT NV.COD_EMPRESA
					   FROM NOTA_VENTA NV
					 	   ,ORDEN_COMPRA OC
					   WHERE OC.COD_ORDEN_COMPRA = ".$array_datos[0]['NRO_ORDEN_COMPRA']."
					   AND OC.COD_NOTA_VENTA = NV.COD_NOTA_VENTA";
			$result_cc = $db->build_results($sql_cc);
			
			$sql_cce = "SELECT COD_PROYECTO_COMPRA
						FROM CENTRO_COSTO_EMPRESA
						WHERE COD_EMPRESA =".$result_cc[0]['COD_EMPRESA'];
			$result_cce = $db->build_results($sql_cce);
			
			if($result_cce[0]['COD_PROYECTO_COMPRA'] == '' || count(result_cce) == 0){
				$cod_cuenta_compra = "NULL";
			}else{
				$cod_cuenta_compra = $result_cce[0]['COD_PROYECTO_COMPRA'];
			}
		}	
			
		//Se hace la insercion de datos
		/**************FA_PROV**************/
		$sp = "spu_faprov";
		$param = "'INSERT'
				  ,NULL
				  ,1		--Piero Silva
				  ,$K_COD_EMPRESA	--Todoinox
				  ,4		--Factura Electronica
				  ,1		--Ingresada
				  ,".$array_datos[0]['NRO_FACTURA']."
				  ,'".$array_datos[0]['FECHA_FACTURA']."'
				  ,".$array_datos[0]['TOTAL_NETO']."
				  ,".$array_datos[0]['MONTO_IVA']."
				  ,".$array_datos[0]['TOTAL_CON_IVA']."
				  ,NULL
				  ,NULL
				  ,'ORDEN_COMPRA'
				  ,$cod_cuenta_compra
				  ,'TODOINOX'"; //PREGUNTAR A MH

		$db->query("exec ".$sp.' '.$param);
		/***********************************/
		
		/**************ITEM_FA_PROV**************/
		$COD_FAPROV = $db->GET_IDENTITY();
		$sp = "spu_item_faprov";
		
		$param2 = "'INSERT'
				  ,NULL
				  ,$COD_FAPROV
				  ,".$array_datos[0]['NRO_ORDEN_COMPRA']."
				  ,".$array_datos[0]['TOTAL_CON_IVA'];
		
		$db->query("exec ".$sp.' '.$param2);	
		/****************************************/
		
		return 'HECHO';
	}
	function svr_add_faprov_bodega($array_datos, $sistema){

		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		//Valida si es que la factura provedor ya haya sido creada
		$sql = "SELECT COD_FAPROV
				FROM FAPROV
				WHERE NRO_FAPROV = ".$array_datos[0]['NRO_FACTURA']."
				AND WS_ORIGEN = 'BODEGA'";
		
		$result = $db->build_results($sql);
		if(count($result) > 0)
			return 'MSJ_REGISTRO';
	
		if($sistema === 'TODOINOX')
			$K_COD_EMPRESA = 37;
		else if($sistema == 'COMERCIAL')
			$K_COD_EMPRESA = 1138;
			
		else if($sistema == 'RENTAL')
			$K_COD_EMPRESA = 28;
			
		//Valida que exista la OC y que sea para BODEGA
		$sql = "SELECT COD_ORDEN_COMPRA
				FROM ORDEN_COMPRA
				WHERE COD_ORDEN_COMPRA = ".$array_datos[0]['NRO_ORDEN_COMPRA']."
				AND COD_EMPRESA = $K_COD_EMPRESA"; //cod_empresa = > BODEGA
				
		$result = $db->build_results($sql);
		if(count($result) == 0)
			return 'NO_REGISTRO_OC';	
		
		$cod_cuenta_compra = "NULL";	
		if($sistema == 'COMERCIAL'){
			$sql_cc = "SELECT NV.COD_EMPRESA
					   FROM NOTA_VENTA NV
					 	   ,ORDEN_COMPRA OC
					   WHERE OC.COD_ORDEN_COMPRA = ".$array_datos[0]['NRO_ORDEN_COMPRA']."
					   AND OC.COD_NOTA_VENTA = NV.COD_NOTA_VENTA";
			$result_cc = $db->build_results($sql_cc);
			
			$sql_cce = "SELECT COD_PROYECTO_COMPRA
						FROM CENTRO_COSTO_EMPRESA
						WHERE COD_EMPRESA =".$result_cc[0]['COD_EMPRESA'];
			$result_cce = $db->build_results($sql_cce);
			
			if($result_cce[0]['COD_PROYECTO_COMPRA'] == '' || count(result_cce) == 0){
				$cod_cuenta_compra = "NULL";
			}else{
				$cod_cuenta_compra = $result_cce[0]['COD_PROYECTO_COMPRA'];
			}
		}	
			
		//Se hace la insercion de datos
		$sp = "spu_faprov";
		$param = "'INSERT'
				  ,NULL
				  ,1		--Piero Silva
				  ,$K_COD_EMPRESA		--Todoinox
				  ,4		--Factura Electronica
				  ,1		--Ingresada
				  ,".$array_datos[0]['NRO_FACTURA']."
				  ,'".$array_datos[0]['FECHA_FACTURA']."'
				  ,".$array_datos[0]['TOTAL_NETO']."
				  ,".$array_datos[0]['MONTO_IVA']."
				  ,".$array_datos[0]['TOTAL_CON_IVA']."
				  ,NULL
				  ,NULL
				  ,'ORDEN_COMPRA'
				  ,$cod_cuenta_compra
				  ,'BODEGA'"; //PREGUNTAR A MH

		$result = $db->query("exec ".$sp.' '.$param);
		$COD_FAPROV = $db->GET_IDENTITY();
		$sp = "spu_item_faprov";
		
		$param2 = "'INSERT'
				  ,NULL
				  ,$COD_FAPROV
				  ,".$array_datos[0]['NRO_ORDEN_COMPRA']."
				  ,".$array_datos[0]['TOTAL_CON_IVA'];
		
		$result = $db->query("exec ".$sp.' '.$param2);	
		
		return 'HECHO';
	}
	function svr_oc_facturada($lista_item_oc,$origen){
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$result_ws = $db->build_results("exec spi_oc_facturada'$lista_item_oc','$origen'");
        
		$result = array();
		$result['ORDEN_COMPRA_ARRAY'] = $result_ws;
		
		$j = json_encode($result_ws);
        return $j;
	}
	function svr_update_costo_producto($empresa,$cod_producto,$precio){
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$result_ws = $db->build_results("exec spx_update_costo_producto '$empresa','$cod_producto',$precio");
        
		$result = array();
		$result['ORDEN_COMPRA_ARRAY'] = $result_ws;
		
		$j = json_encode($result_ws);
        return $j;
	}
	function svr_oc_por_facturar($cod_usuario,$inventario,$origen){
		
		$array_origen = explode('|', $origen);
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$sp = '';
		if($array_origen[0] == 'BODEGA')
		{
			$sp = "exec spi_inf_oc_por_facturar_tdnx '$cod_usuario','$inventario'";
		}
		else
		{
			if($array_origen[1] == 'BODEGA')
				$sp = "exec spi_inf_oc_por_facturar_bodega '$cod_usuario'";
			else
				$sp = "exec spi_inf_oc_por_facturar_tdnx '$cod_usuario'";
		}		
		
		$result_ws = $db->build_results($sp);
		
		for ($i=0; $i<count($result_ws); $i++) {
			$result_ws[$i]['NOM_PRODUCTO']			= utf8_encode ($result_ws[$i]['NOM_PRODUCTO']);
			$result_ws[$i]['NOM_USUARIO']			= utf8_encode ($result_ws[$i]['NOM_USUARIO']);
		}
		
		$result = array();
		$result['ORDEN_COMPRA_ARRAY'] = $result_ws;
		
		$j = json_encode($result_ws);
        return $j;
	}
	
	function svr_oc_por_facturar_indv($cod_usuario,$inventario,$origen,$cod_orden_compra){
		
		$array_origen = explode('|', $origen);
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$sp = '';
		if($array_origen[0] == 'BODEGA')
		{
			$sp = "exec spi_inf_oc_por_facturar_tdnx '$cod_usuario','$inventario'";
		}
		else
		{
			if($array_origen[1] == 'BODEGA')
				$sp = "exec spi_inf_oc_por_facturar_bodega '$cod_usuario'";
			else
				$sp = "exec spi_inf_oc_por_facturar_tdnx_indv '$cod_usuario', $cod_orden_compra";
		}		
		
		$result_ws = $db->build_results($sp);
		
		for ($i=0; $i<count($result_ws); $i++) {
			$result_ws[$i]['NOM_PRODUCTO']			= utf8_encode ($result_ws[$i]['NOM_PRODUCTO']);
			$result_ws[$i]['NOM_USUARIO']			= utf8_encode ($result_ws[$i]['NOM_USUARIO']);
		}
		
		$result = array();
		$result['ORDEN_COMPRA_ARRAY'] = $result_ws;
		
		$j = json_encode($result_ws);
        return $j;
	}
	
	function svr_orden_compra($cod_orden_compra){
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		
		// OC
 		$sql_ws = "SELECT COD_ORDEN_COMPRA
 						,OC.REFERENCIA
 						,CONVERT(VARCHAR,FECHA_ORDEN_COMPRA,103) FECHA_ORDEN_COMPRA
 						,OC.SUBTOTAL
 						,OC.PORC_DSCTO1
 						,OC.MONTO_DSCTO1
 						,OC.PORC_DSCTO2
 						,OC.MONTO_DSCTO2
 						,OC.TOTAL_NETO
 						,OC.TOTAL_CON_IVA
 						,OC.MONTO_IVA
 						,OC.COD_NOTA_VENTA
 						,RUT
 						,(SELECT NOM_EMPRESA FROM EMPRESA WHERE COD_EMPRESA = NV.COD_EMPRESA) NV_NOM_EMPRESA
 						,(SELECT NOM_EMPRESA FROM EMPRESA WHERE COD_EMPRESA = A.COD_EMPRESA) A_NOM_EMPRESA
 						,(SELECT NOM_USUARIO FROM USUARIO WHERE COD_USUARIO = OC.COD_USUARIO_SOLICITA) OC_NOM_USUARIO
 						,(SELECT NOM_MONEDA FROM MONEDA WHERE COD_MONEDA = OC.COD_MONEDA) OC_NOM_MONEDA
 						,(SELECT NOM_ESTADO_ORDEN_COMPRA 
 						  FROM ESTADO_ORDEN_COMPRA 
 						  WHERE COD_ESTADO_ORDEN_COMPRA = OC.COD_ESTADO_ORDEN_COMPRA) ESTADO_OC
 						,OC.TIPO_ORDEN_COMPRA
 						,OC.COD_DOC
 						,E.COD_FORMA_PAGO_CLIENTE
 						,OC.OBS
 						,COD_ESTADO_ORDEN_COMPRA
				   FROM ORDEN_COMPRA OC LEFT OUTER JOIN NOTA_VENTA NV ON OC.COD_NOTA_VENTA = NV.COD_NOTA_VENTA
										LEFT OUTER JOIN ARRIENDO A ON OC.COD_DOC = A.COD_ARRIENDO
					   ,EMPRESA E
				   WHERE COD_ORDEN_COMPRA = $cod_orden_compra
				   AND OC.COD_EMPRESA = E.COD_EMPRESA";
				
		$result_ws = $db->build_results($sql_ws);
		for ($i=0; $i<count($result_ws); $i++) {
			$result_ws[$i]['REFERENCIA']			= utf8_encode ($result_ws[$i]['REFERENCIA']);
			$result_ws[$i]['FECHA_ORDEN_COMPRA']	= utf8_encode ($result_ws[$i]['FECHA_ORDEN_COMPRA']);
			$result_ws[$i]['NV_NOM_EMPRESA']		= utf8_encode ($result_ws[$i]['NV_NOM_EMPRESA']);
			$result_ws[$i]['OC_NOM_USUARIO']		= utf8_encode ($result_ws[$i]['OC_NOM_USUARIO']);
			$result_ws[$i]['TIPO_ORDEN_COMPRA']		= utf8_encode ($result_ws[$i]['TIPO_ORDEN_COMPRA']);
			$result_ws[$i]['A_NOM_EMPRESA']			= utf8_encode ($result_ws[$i]['A_NOM_EMPRESA']);
			$result_ws[$i]['OC_NOM_MONEDA']			= utf8_encode ($result_ws[$i]['OC_NOM_MONEDA']);
			$result_ws[$i]['ESTADO_OC']				= utf8_encode ($result_ws[$i]['ESTADO_OC']);
			$result_ws[$i]['OBS']					= utf8_encode ($result_ws[$i]['OBS']);
		}
		$result = array();
		$result['ORDEN_COMPRA'] = $result_ws;		

		// items OC
 		$sql_ws = "SELECT COD_ITEM_ORDEN_COMPRA
 						,ORDEN
 						,ITEM
 						,COD_PRODUCTO
 						,NOM_PRODUCTO
 						,CANTIDAD
 						,PRECIO
				   FROM ITEM_ORDEN_COMPRA 
				   WHERE COD_ORDEN_COMPRA = $cod_orden_compra
				   AND FACTURADO_SIN_WS = 'N'";
				
		$result_ws = $db->build_results($sql_ws);
		for ($i=0; $i<count($result_ws); $i++) {
			$result_ws[$i]['COD_PRODUCTO'] 	= utf8_encode($result_ws[$i]['COD_PRODUCTO']);
			$result_ws[$i]['NOM_PRODUCTO'] 	= utf8_encode($result_ws[$i]['NOM_PRODUCTO']);
		}

		$result['ITEM_ORDEN_COMPRA'] = $result_ws;
		
		$j = json_encode($result);
		return $j;
	}
	function svr_consulta_tabla($nom_tabla, $cod_tabla){
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		
		$sql_ws= "SELECT *
				  FROM ".$nom_tabla."
				  WHERE COD_".$nom_tabla."=".$cod_tabla;
		
		$result_ws = $db->build_results($sql_ws);
		if(count($result_ws) > 0){
			$array = array_keys($result_ws[0]);
			
			for($i=0; $i < count($result_ws) ; $i++)
				for($j=0; $j < count($array) ; $j++)
					$result_ws[$i][$array[$j]]	= utf8_encode($result_ws[$i][$array[$j]]);
			
			$result = array();
			$result['TABLA'] = $result_ws;
			
			$j = json_encode($result);
			return $j;
		}else{
			return 'NO_REGISTRO';
		}	
	}
	function svr_cli_orden_compra_serv($cod_orden_compra){
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		
		// OC
 		$sql_ws = "SELECT COD_ORDEN_COMPRA
 						,OC.REFERENCIA
 						,CONVERT(VARCHAR,FECHA_ORDEN_COMPRA,103) FECHA_ORDEN_COMPRA
 						,OC.SUBTOTAL
 						,OC.PORC_DSCTO1
 						,OC.MONTO_DSCTO1
 						,OC.PORC_DSCTO2
 						,OC.MONTO_DSCTO2
 						,OC.TOTAL_NETO
 						,OC.TOTAL_CON_IVA
 						,OC.MONTO_IVA
 						,OC.COD_NOTA_VENTA
 						,RUT
 						,(SELECT NOM_EMPRESA FROM EMPRESA WHERE COD_EMPRESA = NV.COD_EMPRESA) NV_NOM_EMPRESA
 						,(SELECT NOM_EMPRESA FROM EMPRESA WHERE COD_EMPRESA = A.COD_EMPRESA) A_NOM_EMPRESA
 						,(SELECT NOM_USUARIO FROM USUARIO WHERE COD_USUARIO = OC.COD_USUARIO_SOLICITA) OC_NOM_USUARIO
 						,OC.TIPO_ORDEN_COMPRA
 						,OC.COD_DOC
 						,E.COD_FORMA_PAGO_CLIENTE
 						,OC.INGRESO_USUARIO_DSCTO1
 						,OC.INGRESO_USUARIO_DSCTO2
 						,OC.COD_USUARIO
 						,COD_ESTADO_ORDEN_COMPRA
				   FROM ORDEN_COMPRA OC LEFT OUTER JOIN NOTA_VENTA NV ON OC.COD_NOTA_VENTA = NV.COD_NOTA_VENTA
										LEFT OUTER JOIN ARRIENDO A ON OC.COD_DOC = A.COD_ARRIENDO
					   ,EMPRESA E
				   WHERE COD_ORDEN_COMPRA = $cod_orden_compra
				   AND OC.COD_EMPRESA = E.COD_EMPRESA";
				
		$result_ws = $db->build_results($sql_ws);
		for ($i=0; $i<count($result_ws); $i++) {
			$result_ws[$i]['REFERENCIA']				= utf8_encode ($result_ws[$i]['REFERENCIA']);
			$result_ws[$i]['FECHA_ORDEN_COMPRA']		= utf8_encode ($result_ws[$i]['FECHA_ORDEN_COMPRA']);
			$result_ws[$i]['NV_NOM_EMPRESA']			= utf8_encode ($result_ws[$i]['NV_NOM_EMPRESA']);
			$result_ws[$i]['OC_NOM_USUARIO']			= utf8_encode ($result_ws[$i]['OC_NOM_USUARIO']);
			$result_ws[$i]['TIPO_ORDEN_COMPRA']			= utf8_encode ($result_ws[$i]['TIPO_ORDEN_COMPRA']);
			$result_ws[$i]['A_NOM_EMPRESA']				= utf8_encode ($result_ws[$i]['A_NOM_EMPRESA']);
			$result_ws[$i]['INGRESO_USUARIO_DSCTO1']	= utf8_encode ($result_ws[$i]['INGRESO_USUARIO_DSCTO1']);
			$result_ws[$i]['INGRESO_USUARIO_DSCTO2']	= utf8_encode ($result_ws[$i]['INGRESO_USUARIO_DSCTO2']);
		}
		$result = array();
		$result['ORDEN_COMPRA'] = $result_ws;		

		// items OC
 		$sql_ws = "SELECT COD_ITEM_ORDEN_COMPRA
 						,ORDEN
 						,ITEM
 						,COD_PRODUCTO
 						,NOM_PRODUCTO
 						,CANTIDAD
 						,PRECIO
				   FROM ITEM_ORDEN_COMPRA 
				   WHERE COD_ORDEN_COMPRA = $cod_orden_compra
				   AND FACTURADO_SIN_WS = 'N'";
				
		$result_ws = $db->build_results($sql_ws);
		for ($i=0; $i<count($result_ws); $i++) {
			$result_ws[$i]['ORDEN'] 		= utf8_encode($result_ws[$i]['ORDEN']);
			$result_ws[$i]['ITEM'] 			= utf8_encode($result_ws[$i]['ITEM']);
			$result_ws[$i]['COD_PRODUCTO'] 	= utf8_encode($result_ws[$i]['COD_PRODUCTO']);
			$result_ws[$i]['NOM_PRODUCTO'] 	= utf8_encode($result_ws[$i]['NOM_PRODUCTO']);
			$result_ws[$i]['CANTIDAD'] 		= utf8_encode($result_ws[$i]['CANTIDAD']);
			$result_ws[$i]['PRECIO'] 		= utf8_encode($result_ws[$i]['PRECIO']);
		}

		$result['ITEM_ORDEN_COMPRA'] = $result_ws;
		
		$j = json_encode($result);
		return $j;	
	}
	function svr_oc_por_facturar_serv($origen, $cod_usuario){
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

		if($origen == 'BODEGA')
			$sql = "SELECT CONVERT(VARCHAR, GETDATE(), 103) FECHA_INF_OC_POR_FACTURAR_SERV
							,$cod_usuario COD_USUARIO
							,O.COD_ORDEN_COMPRA
							,CONVERT(VARCHAR, FECHA_ORDEN_COMPRA, 103) FECHA_ORDEN_COMPRA
							,COD_DOC COD_NOTA_VENTA
							,(SELECT INI_USUARIO FROM USUARIO WHERE COD_USUARIO = o.COD_USUARIO_SOLICITA) COD_USUARIO_VENDEDOR
							,COD_ITEM_ORDEN_COMPRA
							,COD_PRODUCTO
							,NOM_PRODUCTO
							,CANTIDAD CANTIDAD_OC
							,(SELECT NOM_USUARIO FROM USUARIO WHERE COD_USUARIO = o.COD_USUARIO_SOLICITA) NOM_USUARIO
					from ITEM_ORDEN_COMPRA i, ORDEN_COMPRA o
					where o.COD_ORDEN_COMPRA > 56724
					and o.COD_EMPRESA = 5    -- servindus
					and i.COD_ORDEN_COMPRA = o.COD_ORDEN_COMPRA
					and O.COD_ESTADO_ORDEN_COMPRA = 1
					and o.TIPO_ORDEN_COMPRA = 'SOLICITUD_COMPRA'
					and dbo.f_oc_por_llegar (i.cod_item_orden_compra) > 0
					AND i.FACTURADO_SIN_WS = 'N'";
		else if($origen == 'TODOINOX')
			$sql = "SELECT CONVERT(VARCHAR, GETDATE(), 103) FECHA_INF_OC_POR_FACTURAR_SERV
							,$cod_usuario COD_USUARIO
							,O.COD_ORDEN_COMPRA
							,CONVERT(VARCHAR, FECHA_ORDEN_COMPRA, 103) FECHA_ORDEN_COMPRA
							,nv.COD_NOTA_VENTA
							,(SELECT INI_USUARIO FROM USUARIO WHERE COD_USUARIO = o.COD_USUARIO_SOLICITA) COD_USUARIO_VENDEDOR
							,COD_ITEM_ORDEN_COMPRA
							,COD_PRODUCTO
							,NOM_PRODUCTO
							,CANTIDAD CANTIDAD_OC
							,(SELECT NOM_USUARIO FROM USUARIO WHERE COD_USUARIO = o.COD_USUARIO_SOLICITA) NOM_USUARIO
					from ITEM_ORDEN_COMPRA i, ORDEN_COMPRA o left outer join NOTA_VENTA nv on o.COD_NOTA_VENTA = nv.COD_NOTA_VENTA
					where o.COD_ORDEN_COMPRA > 23344
					and o.COD_EMPRESA = 38 -- Servindus
					and i.COD_ORDEN_COMPRA = o.COD_ORDEN_COMPRA
					and O.COD_ESTADO_ORDEN_COMPRA = 1
					and dbo.f_oc_get_saldo_sin_faprov(O.COD_ORDEN_COMPRA) > 0
					AND i.FACTURADO_SIN_WS = 'N'";
		else if($origen == 'COMERCIAL')
			$sql = "SELECT CONVERT(VARCHAR, GETDATE(), 103) FECHA_INF_OC_POR_FACTURAR_SERV
							,$cod_usuario COD_USUARIO
							,O.COD_ORDEN_COMPRA
							,CONVERT(VARCHAR, FECHA_ORDEN_COMPRA, 103) FECHA_ORDEN_COMPRA
							,nv.COD_NOTA_VENTA
							,(SELECT U.INI_USUARIO FROM USUARIO U WHERE U.COD_USUARIO = nv.COD_USUARIO_VENDEDOR1) COD_USUARIO_VENDEDOR
							,COD_ITEM_ORDEN_COMPRA
							,COD_PRODUCTO
							,NOM_PRODUCTO
							,CANTIDAD CANTIDAD_OC
							,(SELECT U.NOM_USUARIO FROM USUARIO U WHERE U.COD_USUARIO = nv.COD_USUARIO_VENDEDOR1) NOM_USUARIO
					 from ITEM_ORDEN_COMPRA i, ORDEN_COMPRA o,NOTA_VENTA nv
					 where o.COD_ORDEN_COMPRA > 185565
					 and o.COD_EMPRESA = 1899    --servindus
					 and i.COD_ORDEN_COMPRA = o.COD_ORDEN_COMPRA
					 and o.COD_NOTA_VENTA = nv.COD_NOTA_VENTA
					 and O.COD_ESTADO_ORDEN_COMPRA = 1
					 and dbo.f_oc_get_saldo_sin_faprov(O.COD_ORDEN_COMPRA) > 0
					 AND i.FACTURADO_SIN_WS = 'N'";
		else if($origen == 'RENTAL')
			$sql = "SELECT CONVERT(VARCHAR, GETDATE(), 103) FECHA_INF_OC_POR_FACTURAR_SERV
							,$cod_usuario COD_USUARIO
							,O.COD_ORDEN_COMPRA
							,CONVERT(VARCHAR, FECHA_ORDEN_COMPRA, 103) FECHA_ORDEN_COMPRA
							,case 
								when TIPO_ORDEN_COMPRA = 'ARRIENDO' then COD_DOC
								when TIPO_ORDEN_COMPRA = 'NOTA_VENTA' then nv.COD_NOTA_VENTA
							end COD_NOTA_VENTA
							,(SELECT U.INI_USUARIO FROM USUARIO U WHERE U.COD_USUARIO = nv.COD_USUARIO_VENDEDOR1) COD_USUARIO_VENDEDOR
							,COD_ITEM_ORDEN_COMPRA
							,COD_PRODUCTO
							,NOM_PRODUCTO
							,CANTIDAD CANTIDAD_OC
							,(SELECT U.NOM_USUARIO FROM USUARIO U WHERE U.COD_USUARIO = nv.COD_USUARIO_VENDEDOR1) NOM_USUARIO
					from ITEM_ORDEN_COMPRA i, ORDEN_COMPRA o left outer join NOTA_VENTA nv on o.COD_NOTA_VENTA = nv.COD_NOTA_VENTA
					where o.COD_ORDEN_COMPRA > 66208
					and o.COD_EMPRESA = 32 --servindus
					and i.COD_ORDEN_COMPRA = o.COD_ORDEN_COMPRA
					and O.COD_ESTADO_ORDEN_COMPRA = 4
					and dbo.f_oc_get_saldo_sin_faprov(O.COD_ORDEN_COMPRA) > 0
					AND i.FACTURADO_SIN_WS = 'N'";
					
		$result_ws = $db->build_results($sql);
		
		for ($i=0; $i<count($result_ws); $i++){
			$result_ws[$i]['NOM_PRODUCTO']			= utf8_encode($result_ws[$i]['NOM_PRODUCTO']);
			$result_ws[$i]['NOM_USUARIO']			= utf8_encode($result_ws[$i]['NOM_USUARIO']);
			$result_ws[$i]['COD_USUARIO_VENDEDOR']	= utf8_encode($result_ws[$i]['COD_USUARIO_VENDEDOR']);
		}
		
		$result = array();
		$result['ORDEN_COMPRA_SERV'] = $result_ws;
		
		$j = json_encode($result_ws);
        return $j;
	}
	
	function svr_consulta_stock($cod_producto, $sistema){
		if($sistema == 'TODOINOX')
			$cod_bodega = 1;
		else if($sistema == 'BODEGA')
			$cod_bodega = 2;
		
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$sql = "SELECT dbo.f_bodega_stock(COD_PRODUCTO, $cod_bodega, GETDATE()) STOCK
					  ,MANEJA_INVENTARIO
				FROM PRODUCTO
				WHERE COD_PRODUCTO = '$cod_producto'";
				
		$result_ws = $db->build_results($sql);
		
		$result = array();
		$result['ORDEN_COMPRA_SERV'] = $result_ws;
		
		$j = json_encode($result_ws);
        return $j;
	}
	
	function svr_oc_x_facturar_tipo_a($cod_usuario,$inventario,$origen){

		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		if($origen == 'COMERCIAL'){
			$sql= "SELECT COD_ORDEN_COMPRA
						  ,NV.COD_NOTA_VENTA
						  ,CONVERT(VARCHAR, NV.FECHA_ENTREGA, 103) FECHA_ENTREGA
						  ,(SELECT NOM_EMPRESA FROM EMPRESA WHERE COD_EMPRESA = NV.COD_EMPRESA) NOM_CLIENTE_NV
						  ,(SELECT U.NOM_USUARIO FROM USUARIO U WHERE U.COD_USUARIO = nv.COD_USUARIO_VENDEDOR1) NOM_USUARIO
						  ,O.TOTAL_NETO
						  ,O.AUTORIZA_FACTURACION
						  ,CONVERT(VARCHAR, O.FECHA_SOLICITA_FACTURACION, 103) FECHA_SOLICITA_FACTURACION
						  ,(SELECT INI_USUARIO FROM USUARIO WHERE COD_USUARIO = nv.COD_USUARIO_VENDEDOR1) COD_USUARIO_VENDEDOR
						  ,(SELECT COUNT(*) FROM ITEM_ORDEN_COMPRA IIO WHERE IIO.COD_ORDEN_COMPRA = O.COD_ORDEN_COMPRA AND IIO.FACTURADO_SIN_WS <> 'S') CANTIDAD_ITEM
				   FROM ORDEN_COMPRA O,NOTA_VENTA NV
				   WHERE O.COD_ORDEN_COMPRA > 175780
				   AND O.COD_EMPRESA = 1302    --todoinox
				   AND O.COD_NOTA_VENTA = NV.COD_NOTA_VENTA
				   AND O.COD_ESTADO_ORDEN_COMPRA = 1
				   AND dbo.f_oc_get_saldo_sin_faprov(O.COD_ORDEN_COMPRA) > 0
				   AND (SELECT COUNT(*) FROM ITEM_ORDEN_COMPRA IIO WHERE IIO.COD_ORDEN_COMPRA = O.COD_ORDEN_COMPRA AND IIO.FACTURADO_SIN_WS <> 'S') <> 0";
		}
		
		$result_ws = $db->build_results($sql);
		
		for ($i=0; $i<count($result_ws); $i++){
			$result_ws[$i]['NOM_CLIENTE_NV']		= utf8_encode($result_ws[$i]['NOM_CLIENTE_NV']);
			$result_ws[$i]['NOM_USUARIO']			= utf8_encode($result_ws[$i]['NOM_USUARIO']);
			$result_ws[$i]['COD_USUARIO_VENDEDOR']	= utf8_encode($result_ws[$i]['COD_USUARIO_VENDEDOR']);
		}
		
		$j = json_encode($result_ws);
        return $j;
	}
	
	function svr_print_oc_sistema($cod_orden_compra, $sistema){
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		if($sistema == 'COMERCIAL' || $sistema == 'BODEGA'){
			$sql = "SELECT  OC.COD_ORDEN_COMPRA,
							OC.COD_NOTA_VENTA,
							OC.SUBTOTAL,
							OC.PORC_DSCTO1,
							OC.MONTO_DSCTO1,
							OC.PORC_DSCTO2,
							OC.MONTO_DSCTO2,
							OC.TOTAL_NETO,
							OC.PORC_IVA,
							OC.MONTO_IVA,
							OC.TOTAL_CON_IVA,
							OC.REFERENCIA,																
							OC.OBS,
							E.NOM_EMPRESA,
							E.RUT,
							E.DIG_VERIF,
							dbo.f_get_direccion('SUCURSAL', OC.COD_SUCURSAL, '[DIRECCION] [NOM_COMUNA] [NOM_CIUDAD]') DIRECCION,
							dbo.f_format_date(OC.FECHA_ORDEN_COMPRA, 3) FECHA_ORDEN_COMPRA,	
							S.TELEFONO,
							S.FAX,
							P.NOM_PERSONA,
							U.NOM_USUARIO,
							U.MAIL,
							IOC.NOM_PRODUCTO,
							case IOC.COD_PRODUCTO
								when 'T' then ''
								else IOC.COD_PRODUCTO
							end COD_PRODUCTO,
							case IOC.COD_PRODUCTO
								when 'T' then ''
								else IOC.ITEM
							end ITEM,
							IOC.CANTIDAD,
							IOC.PRECIO,
							IOC.CANTIDAD * IOC.PRECIO TOTAL_IOC,			
							M.SIMBOLO,
							dbo.f_get_parametro(6) NOM_EMPRESA_EMISOR,
							dbo.f_get_parametro(20) RUT_EMPRESA,
							dbo.f_get_parametro(10) DIR_EMPRESA,
							dbo.f_get_parametro(21) GIRO_EMPRESA,
							dbo.f_get_parametro(11) TEL_EMPRESA,	
							dbo.f_get_parametro(12) FAX_EMPRESA,
							dbo.f_get_parametro(13) MAIL_EMPRESA,
							dbo.f_get_parametro(14) CIUDAD_EMPRESA,
							dbo.f_get_parametro(15) PAIS_EMPRESA,
							dbo.f_get_parametro(25) SITIO_WEB_EMPRESA,
							dbo.f_emp_get_cc(NV.COD_EMPRESA) CC_EMPRESA
					FROM    ORDEN_COMPRA OC LEFT OUTER JOIN PERSONA P ON  OC.COD_PERSONA = P.COD_PERSONA
											LEFT OUTER JOIN NOTA_VENTA NV ON NV.COD_NOTA_VENTA = OC.COD_NOTA_VENTA,
							ITEM_ORDEN_COMPRA IOC, EMPRESA E, SUCURSAL S, USUARIO U, MONEDA M
					WHERE   OC.COD_ORDEN_COMPRA = $cod_orden_compra
					AND		E.COD_EMPRESA = OC.COD_EMPRESA 
					AND		S.COD_SUCURSAL = OC.COD_SUCURSAL 
					AND		U.COD_USUARIO = OC.COD_USUARIO_SOLICITA 
					AND		IOC.COD_ORDEN_COMPRA = OC.COD_ORDEN_COMPRA 
					AND		M.COD_MONEDA = OC.COD_MONEDA";
		}
		
		$result_ws = $db->build_results($sql);
		
		for ($i=0; $i<count($result_ws); $i++){
			$result_ws[$i]['REFERENCIA']			= utf8_encode($result_ws[$i]['REFERENCIA']);
			$result_ws[$i]['OBS']					= utf8_encode($result_ws[$i]['OBS']);
			$result_ws[$i]['NOM_EMPRESA']			= utf8_encode($result_ws[$i]['NOM_EMPRESA']);
			$result_ws[$i]['DIRECCION']				= utf8_encode($result_ws[$i]['DIRECCION']);
			$result_ws[$i]['FECHA_ORDEN_COMPRA']	= utf8_encode($result_ws[$i]['FECHA_ORDEN_COMPRA']);
			$result_ws[$i]['NOM_PERSONA']			= utf8_encode($result_ws[$i]['NOM_PERSONA']);
			$result_ws[$i]['NOM_USUARIO']			= utf8_encode($result_ws[$i]['NOM_USUARIO']);
			$result_ws[$i]['MAIL']					= utf8_encode($result_ws[$i]['MAIL']);
			$result_ws[$i]['NOM_PRODUCTO']			= utf8_encode($result_ws[$i]['NOM_PRODUCTO']);
			$result_ws[$i]['COD_PRODUCTO']			= utf8_encode($result_ws[$i]['COD_PRODUCTO']);
			$result_ws[$i]['SIMBOLO']				= utf8_encode($result_ws[$i]['SIMBOLO']);
			$result_ws[$i]['NOM_EMPRESA_EMISOR']	= utf8_encode($result_ws[$i]['NOM_EMPRESA_EMISOR']);
			$result_ws[$i]['RUT_EMPRESA']			= utf8_encode($result_ws[$i]['RUT_EMPRESA']);
			$result_ws[$i]['DIR_EMPRESA']			= utf8_encode($result_ws[$i]['DIR_EMPRESA']);
			$result_ws[$i]['GIRO_EMPRESA']			= utf8_encode($result_ws[$i]['GIRO_EMPRESA']);
			$result_ws[$i]['CIUDAD_EMPRESA']		= utf8_encode($result_ws[$i]['CIUDAD_EMPRESA']);
			$result_ws[$i]['PAIS_EMPRESA']			= utf8_encode($result_ws[$i]['PAIS_EMPRESA']);
			$result_ws[$i]['SITIO_WEB_EMPRESA']		= utf8_encode($result_ws[$i]['SITIO_WEB_EMPRESA']);
		}
		
		$result = array();
		$result['ORDEN_COMPRA_SERV'] = $result_ws;
		
		$j = json_encode($result);
        return $j;
	}
	
	function svr_wo_pago_faprov($sistema_pf, $sistema){
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		if($sistema_pf == 'COMERCIAL'){
			$cod_empresa = 1337; //codigo COMERCIAL BIGGI en BD BIGGI 
			
			if($sistema == 'TODOINOX'){
				$pf_empresa = 1302; //codigo TODOINOX en BD BIGGI
				$limite_cod_pf = 11068;
			}else if($sistema == 'BODEGA'){
				$pf_empresa = 1138; //codigo BODEGA en BD BIGGI
				$limite_cod_pf = 11160;
			}else if($sistema == 'RENTAL'){
				$pf_empresa = 0; //codigo RENTAL en BD BIGGI
				$limite_cod_pf = 0;
			}
		}else if($sistema_pf == 'BODEGA'){
			$cod_empresa = 9; //codigo BODEGA en BD BODEGA
			
			if($sistema == 'TODOINOX'){
				$pf_empresa = 4; //codigo TODOINOX en BD BODEGA
				$limite_cod_pf = 1084;
			}else if($sistema == 'COMERCIAL'){
				$pf_empresa = 0; //codigo COMERCIAL en BD BODEGA
				$limite_cod_pf = 0;
			}else if($sistema == 'RENTAL'){
				$pf_empresa = 0; //codigo RENTAL en BD BODEGA
				$limite_cod_pf = 0;
			}	
		}else if($sistema_pf == 'RENTAL'){
			$cod_empresa = 29; //codigo RENTAL en BD RENTAL
			
			if($sistema == 'TODOINOX'){
				$pf_empresa = 4; //codigo TODOINOX en BD RENTAL
				$limite_cod_pf = 439;
			}else if($sistema == 'COMERCIAL'){
				$pf_empresa = 0; //codigo COMERCIAL en BD RENTAL
				$limite_cod_pf = 0;
			}else if($sistema == 'BODEGA'){
				$pf_empresa = 28; //codigo BODEGA en BD RENTAL
				$limite_cod_pf = 443;
			}
		}else if($sistema_pf == 'TODOINOX'){
			$cod_empresa = 7; //codigo TODOINOX en BD TODOINOX
			
			if($sistema == 'BODEGA'){
				$pf_empresa = 37; //codigo BODEGA en BD TODOINOX
				$limite_cod_pf = 1775;
			}else if($sistema == 'COMERCIAL'){
				$pf_empresa = 0; //codigo COMERCIAL en BD TODOINOX
				$limite_cod_pf = 0;
			}else if($sistema == 'RENTAL'){
				$pf_empresa = 0; //codigo RENTAL en BD TODOINOX
				$limite_cod_pf = 0;
			}
		}
					
		$sql = "SELECT COD_EMPRESA
					  ,NOM_EMPRESA
					  ,CONVERT(VARCHAR, RUT) +'-'+DIG_VERIF RUT
					  ,ALIAS 
				FROM EMPRESA
				WHERE COD_EMPRESA = $cod_empresa";	
		$result = $db->build_results($sql);
		
		$sql = "SELECT COD_PAGO_FAPROV
							,CONVERT(VARCHAR, FECHA_PAGO_FAPROV, 103) FECHA_PAGO_FAPROV
							,U.NOM_USUARIO
							,NRO_DOCUMENTO
							,CONVERT(VARCHAR, FECHA_DOCUMENTO, 103) FECHA_DOCUMENTO
							,MONTO_DOCUMENTO
							,".$result[0]['COD_EMPRESA']." COD_EMPRESA
							,'".$result[0]['NOM_EMPRESA']."' NOM_EMPRESA
							,'".$result[0]['RUT']."' RUT
							,'".$result[0]['ALIAS']."' ALIAS
							,NOM_TIPO_PAGO_FAPROV
				FROM	PAGO_FAPROV PF
					   ,USUARIO U
					   ,TIPO_PAGO_FAPROV TPF
				WHERE	PF.COD_EMPRESA = $pf_empresa
				AND PF.TRASPASADO_WS <> 'S'
				AND PF.COD_ESTADO_PAGO_FAPROV = 2
				AND PF.COD_PAGO_FAPROV > $limite_cod_pf
				AND PF.COD_USUARIO = U.COD_USUARIO
				AND PF.COD_TIPO_PAGO_FAPROV = TPF.COD_TIPO_PAGO_FAPROV
				ORDER BY COD_PAGO_FAPROV DESC";
				
		$result_ws = $db->build_results($sql);
		
		for ($i=0; $i<count($result_ws); $i++){
			$result_ws[$i]['NOM_USUARIO']			= utf8_encode($result_ws[$i]['NOM_USUARIO']);
			$result_ws[$i]['NOM_EMPRESA']			= utf8_encode($result_ws[$i]['NOM_EMPRESA']);
			$result_ws[$i]['RUT']					= utf8_encode($result_ws[$i]['RUT']);
			$result_ws[$i]['ALIAS']					= utf8_encode($result_ws[$i]['ALIAS']);
			$result_ws[$i]['NOM_TIPO_PAGO_FAPROV']	= utf8_encode($result_ws[$i]['NOM_TIPO_PAGO_FAPROV']);
		}
		
		$result = array();
		$result['PAGO_FAPROV_COMERCIAL'] = $result_ws;		
		
		$j = json_encode($result);
		return $j;
	}
	
	function svr_wi_pago_faprov($cod_pago_faprov){
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$sql = "SELECT PFF.COD_PAGO_FAPROV_FAPROV
						,PFF.COD_PAGO_FAPROV
						,PFF.COD_FAPROV
						,F.NRO_FAPROV
						,CONVERT(VARCHAR, F.FECHA_FAPROV, 103) FECHA_FAPROV
						,F.TOTAL_CON_IVA TOTAL_CON_IVA_FA
						,dbo.f_pago_faprov_get_monto_ncprov(F.COD_FAPROV) MONTO_NCPROV
						,dbo.f_pago_faprov_get_por_asignar(F.COD_FAPROV) + PFF.MONTO_ASIGNADO SALDO_SIN_PAGO_FAPROV
						,PFF.MONTO_ASIGNADO
						,dbo.f_pago_faprov_get_pago_ant(F.COD_FAPROV) PAGO_ANTERIOR
						,CC.NOM_CUENTA_CORRIENTE
				FROM 	PAGO_FAPROV_FAPROV PFF
						, FAPROV F left outer join CUENTA_COMPRA c on c.COD_CUENTA_COMPRA = f.COD_CUENTA_COMPRA
								   left outer join CUENTA_CORRIENTE cc on c.COD_CUENTA_CORRIENTE = cc.COD_CUENTA_CORRIENTE
						, PAGO_FAPROV PF
				WHERE 	PFF.COD_PAGO_FAPROV = $cod_pago_faprov AND
						F.COD_FAPROV = PFF.COD_FAPROV AND
						PF.COD_PAGO_FAPROV = PFF.COD_PAGO_FAPROV
				ORDER BY F.NRO_FAPROV";
		
		$result_ws = $db->build_results($sql);

		$result = array();
		$result['PAGO_FAPROV_FAPROV_COMERCIAL'] = $result_ws;		
		
		$j = json_encode($result);
		return $j;
	}
	
	function svr_cambio_estado_traspaso($cod_pago_faprov, $estado){
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$sp = "spu_pago_faprov";
		
		if($estado <> 'ANULADO')
			$operacion = 'CAMBIO_TRASPASADO';
		else
			$operacion = 'TRASPASO_ANULADO';
		
		$param = "'$operacion'
				  ,$cod_pago_faprov";

		$db->query("exec ".$sp.' '.$param);	
	}
	
	function svr_entrada_bodega($nro_orden_compra, $nro_factura){
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$sql = "SELECT COD_EMPRESA
					  ,NRO_ORDEN_COMPRA
				FROM FACTURA F
					,ITEM_FACTURA IFA
				WHERE NRO_FACTURA = $nro_factura
				AND IFA.COD_FACTURA = F.COD_FACTURA";
		$result = $db->build_results($sql);
		if(count($result) == 0)
			return 'NO_EXISTE';
		
		if($result[0]['COD_EMPRESA'] <> 37)
			return 'OTRA_EMPRESA';
			
		if($result[0]['NRO_ORDEN_COMPRA'] <> $nro_orden_compra)
			return 'DISTINTO_OC';

		$sql = "SELECT TOP 1 CONVERT(VARCHAR, FECHA_FACTURA, 103) FECHA_FACTURA
							,CANTIDAD
				FROM FACTURA F
					,ITEM_FACTURA IFA
				WHERE NRO_ORDEN_COMPRA = '$nro_orden_compra'
				AND F.NRO_FACTURA = $nro_factura
				AND F.COD_FACTURA = IFA.COD_FACTURA
				ORDER BY FECHA_REGISTRO DESC";
		$result_ws = $db->build_results($sql);
		
		if(count($result_ws) <= 0){
			return 'NO_COINCIDE';
		}
		 
		$result = array();
		$result['FACTURA'] = $result_ws;		
		
		$j = json_encode($result);
		return $j;
	}
	
	function svr_add_nota_credito($cod_pago_faprov){
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$sql = "SELECT N.NRO_NCPROV
						,CONVERT(VARCHAR, N.FECHA_NCPROV, 103) FECHA_NCPROV
						,NPF.MONTO_ASIGNADO
				FROM NCPROV_PAGO_FAPROV NPF, NCPROV N
				WHERE NPF.COD_PAGO_FAPROV = $cod_pago_faprov
				AND N.COD_NCPROV = NPF.COD_NCPROV";
		$result_ws = $db->build_results($sql);
		
		$result = array();
		$result['NCPROV'] = $result_ws;		
		
		$j = json_encode($result);
		return $j;
	}
	
	function svr_factura_arriendo($array_datos, $cliente){
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		/*Preguntar validaciones*/
		$sql = "SELECT NRO_FACTURA
				FROM FACTURA
				WHERE WS_ORIGEN = 'RENTAL_ARRIENDO|".$array_datos[0]['COD_FACTURA']."'";
		$result = $db->build_results($sql);
		
		if(count($result) <> 0){
			if($result[0]['NRO_FACTURA'] == '')
				return 'EXISTENTE_SIN_NRO_FA';
			else
				return $result[0]['NRO_FACTURA'];
		}
		
		if($cliente == 'SODEXO_CHILE'){
			$cod_empresa = "772";
			$cod_sucursal_factura = "2524";
			$cod_persona = "1567";	
		}else if($cliente == 'SODEXO_SERV'){
			$cod_empresa = "62";
			$cod_sucursal_factura = "2526";
			$cod_persona = "71";
		}
		
		$sql = "SELECT COD_SUCURSAL
				FROM SUCURSAL
				WHERE COD_SUCURSAL = $cod_sucursal_factura";
		$result = $db->build_results($sql);
		
		if(count($result) == 0)
			return 'NO_REGISTRO_SUCURSAL';
			
		$sql = "SELECT COD_PERSONA
				FROM PERSONA
				WHERE COD_PERSONA = $cod_persona";
		$result = $db->build_results($sql);
		
		if(count($result) == 0)
			return 'NO_REGISTRO_PERSONA';	
		
		$OBS					= ($array_datos[0]['OBS'] =='') ? "null" : "'".$array_datos[0]['OBS']."'";
		$RETIRADO_POR			= ($array_datos[0]['RETIRADO_POR'] =='') ? "null" : "'".$array_datos[0]['RETIRADO_POR']."'";
		$RUT_RETIRADO_POR		= ($array_datos[0]['RUT_RETIRADO_POR'] =='') ? "null" : $array_datos[0]['RUT_RETIRADO_POR'];
		$DIG_VERIF_RETIRADO_POR	= ($array_datos[0]['DIG_VERIF_RETIRADO_POR'] =='') ? "null" : "'".$array_datos[0]['DIG_VERIF_RETIRADO_POR']."'";
		$GUIA_TRANSPORTE		= ($array_datos[0]['GUIA_TRANSPORTE'] =='') ? "null" : "'".$array_datos[0]['GUIA_TRANSPORTE']."'";
		$PATENTE				= ($array_datos[0]['PATENTE'] =='') ? "null" : "'".$array_datos[0]['PATENTE']."'";
		
		//Se hace la insercion de datos
		/**************FACTURA**************/
		$sp = "spu_factura";
		$param = "'INSERT'
				  ,NULL													-- COD_FACTURA
				  ,7													-- COD_USUARIO_IMPRESION (MARGARITA SCIANCA)
				  ,7													-- COD_USUARIO (MARGARITA SCIANCA)
				  ,NULL													-- NRO_FACTURA
				  ,NULL													-- FECHA_FACTURA
				  ,1													-- COD_ESTADO_DOC_SII (Emitida)
				  ,$cod_empresa											-- COD_EMPRESA
				  ,$cod_sucursal_factura								-- COD_SUCURSAL_FACTURA
				  ,$cod_persona											-- COD_PERSONA
				  ,'".$array_datos[0]['REFERENCIA']."'					-- REFERENCIA
				  ,NULL													-- NRO_ORDEN_COMPRA
				  ,NULL													-- FECHA_ORDEN_COMPRA_CLIENTE
				  ,".$OBS."												-- OBS
				  ,".$RETIRADO_POR."									-- RETIRADO_POR
				  ,".$RUT_RETIRADO_POR."								-- RUT_RETIRADO_POR
				  ,".$DIG_VERIF_RETIRADO_POR."							-- DIG_VERIF_RETIRADO_POR
				  ,".$GUIA_TRANSPORTE."									-- GUIA_TRANSPORTE
				  ,".$PATENTE."											-- PATENTE
				  ,1													-- COD_BODEGA (BODEGA TODOINOX)
				  ,2													-- COD_TIPO_FACTURA (Arriendo)
				  ,NULL													-- COD_DOC
				  ,NULL													-- MOTIVO_ANULA
				  ,NULL													-- COD_USUARIO_ANULA
				  ,7													-- COD_USUARIO_VENDEDOR1 (MARGARITA SCIANCA)
				  ,0													-- PORC_VENDEDOR1 (MARGARITA SCIANCA)
				  ,NULL													-- COD_USUARIO_VENDEDOR2
				  ,NULL													-- PORC_VENDEDOR2
				  ,7													-- COD_FORMA_PAGO (CONTRA FACTURA 30 DIAS)
				  ,NULL													-- COD_ORIGEN_VENTA
				  ,0													-- SUBTOTAL
				  ,".$array_datos[0]['PORC_DSCTO1']."					-- PORC_DSCTO1
				  ,'P'													-- INGRESO_USUARIO_DESCTO1
				  ,0													-- MONTO_DSCTO1
				  ,".$array_datos[0]['PORC_DSCTO2']."					-- PORC_DSCTO2
				  ,'P'													-- INGRESO_USUARIO_DESCTO2
				  ,0													-- MONTO_DSCTO2
				  ,0													-- TOTAL_NETO
				  ,".$array_datos[0]['PORC_IVA']."						-- PORC_IVA
				  ,0													-- MONTO_IVA
				  ,0													-- TOTAL_CON_IVA
				  ,NULL													-- PORC_FACTURA_PARCIAL
				  ,NULL													-- NOM_FORMA_PAGO_OTRO
				  ,'N'													-- GENERA_SALIDA
				  ,NULL													-- TIPO_DOC
				  ,'N'													-- CANCELADA
				  ,'017'												-- COD_CENTRO_COSTO (VENTAS TODOINOX)
				  ,NULL													-- COD_VENDEDOR_SOFLAND (MARGARITA SCIANCA) => IC 26/05/2015:
				  ,'S'													-- NO_TIENE_OC
				  ,NULL													-- COD_COTIZACION
				  ,'RENTAL_ARRIENDO|".$array_datos[0]['COD_FACTURA']."'	-- WS_ORIGEN";
		
		$db->query("exec ".$sp.' '.$param);
		/***********************************/
		
		/**************ITEM_FACTURA**************/
		$COD_FACTURA = $db->GET_IDENTITY();
		$sp = "spu_item_factura";
		
		$param2 = "'INSERT'
				  ,NULL									-- COD_ITEM_FACTURA
				  ,$COD_FACTURA							-- COD_FACTURA
				  ,10									-- ORDEN
				  ,1									-- ITEM
				  ,'TE'									-- COD_PRODUCTO
				  ,'".$array_datos[0]['REFERENCIA']."'	-- NOM_PRODUCTO -- referencia de la factura en rental
				  ,1									-- CANTIDAD
				  ,".$array_datos[0]['SUBTOTAL']."		-- PRECIO -- subtotal de la factura en rental
				  ,NULL									-- COD_ITEM_DOC
				  ,7									-- COD_TIPO_TE (OTROS)
				  ,NULL									-- MOTIVO_TE
				  ,NULL									-- TIPO_DOC
				  ,NULL									-- COD_TIPO_GAS
				  ,NULL									-- COD_TIPO_ELECTRICIDAD";
		
		$db->query("exec ".$sp.' '.$param2);
		/****************************************/
		
		/**************RE-CALCULA**************/
		$sp = "spu_factura";
		
		$param3 = "'RECALCULA', $COD_FACTURA";
		
		$db->query("exec ".$sp.' '.$param3);
		/**************************************/
		return 'HECHO';
	}
	
	function svr_wo_pago_faprov_servindus($sistema){
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		if($sistema == 'COMERCIAL'){
			$cod_empresa_owner	= 1337;
			$cod_empresa_srv	= 1899;
			$limite_cod_pf		= 11587;
			//pagos faprov que estan hechas a mano
			$lista_cod_pago_faprov	= '11636, 11635, 11634';
		}else if($sistema == 'BODEGA'){
			$cod_empresa_owner	= 9;
			$cod_empresa_srv	= 5;
			$limite_cod_pf		= 1137;
			//pagos faprov que estan hechas a mano
			$lista_cod_pago_faprov	= '0';
		}else if($sistema == 'RENTAL'){
			$cod_empresa_owner	= 29;
			$cod_empresa_srv	= 32;
			$limite_cod_pf		= 505;
			$lista_cod_pago_faprov	= '0';
		}else if($sistema == 'TODOINOX'){
			$cod_empresa_owner	= 7;
			$cod_empresa_srv	= 38;
			$limite_cod_pf		= 1956;
			//pagos faprov que estan hechas a mano
			$lista_cod_pago_faprov	= '0';
		}
					
		$sql = "SELECT COD_EMPRESA
					  ,NOM_EMPRESA
					  ,CONVERT(VARCHAR, RUT) +'-'+DIG_VERIF RUT
					  ,ALIAS 
				FROM EMPRESA
				WHERE COD_EMPRESA = $cod_empresa_owner";	
		$result = $db->build_results($sql);
		
		$sql = "SELECT COD_PAGO_FAPROV
							,CONVERT(VARCHAR, FECHA_PAGO_FAPROV, 103) FECHA_PAGO_FAPROV
							,U.NOM_USUARIO
							,NRO_DOCUMENTO
							,CONVERT(VARCHAR, FECHA_DOCUMENTO, 103) FECHA_DOCUMENTO
							,MONTO_DOCUMENTO
							,".$result[0]['COD_EMPRESA']." COD_EMPRESA
							,'".$result[0]['NOM_EMPRESA']."' NOM_EMPRESA
							,'".$result[0]['RUT']."' RUT
							,'".$result[0]['ALIAS']."' ALIAS
							,NOM_TIPO_PAGO_FAPROV
				FROM	PAGO_FAPROV PF
					   ,USUARIO U
					   ,TIPO_PAGO_FAPROV TPF
				WHERE	PF.COD_EMPRESA = $cod_empresa_srv
				AND PF.TRASPASADO_WS <> 'S'
				AND PF.COD_ESTADO_PAGO_FAPROV = 2
				AND PF.COD_PAGO_FAPROV > $limite_cod_pf
				AND PF.COD_USUARIO = U.COD_USUARIO
				AND PF.COD_TIPO_PAGO_FAPROV = TPF.COD_TIPO_PAGO_FAPROV
				AND PF.COD_PAGO_FAPROV NOT IN ($lista_cod_pago_faprov)
				ORDER BY COD_PAGO_FAPROV DESC";
				
		$result_ws = $db->build_results($sql);
		
		for ($i=0; $i<count($result_ws); $i++){
			$result_ws[$i]['NOM_USUARIO']			= utf8_encode($result_ws[$i]['NOM_USUARIO']);
			$result_ws[$i]['NOM_EMPRESA']			= utf8_encode($result_ws[$i]['NOM_EMPRESA']);
			$result_ws[$i]['RUT']					= utf8_encode($result_ws[$i]['RUT']);
			$result_ws[$i]['ALIAS']					= utf8_encode($result_ws[$i]['ALIAS']);
			$result_ws[$i]['NOM_TIPO_PAGO_FAPROV']	= utf8_encode($result_ws[$i]['NOM_TIPO_PAGO_FAPROV']);
		}
		
		$result = array();
		$result['PAGO_FAPROV_SERVINDUS'] = $result_ws;		
		
		$j = json_encode($result);
		return $j;
	}
}
?>