<?php
////////////////////////////////////////
/////////// BODEGA_BIGGI ///////////////
////////////////////////////////////////
class wi_factura extends wi_factura_base {
	const K_BODEGA_TERMINADO = 2;
	const K_TIPO_FA_OC_COMERCIAL = 3;
	
	const K_AUTORIZA_ENVIAR_DTE = '992070';
	const K_AUTORIZA_IMPRIMIR_DTE = '992075';
	const K_AUTORIZA_CONSULTAR_DTE = '992080';
	const K_AUTORIZA_XML_DTE = '992085';
	const K_AUTORIZA_REENVIAR_DTE='992090';
	
	const K_PARAM_RUTEMISOR = 20;
	const K_PARAM_RZNSOC = 6;
	const K_PARAM_GIROEMIS = 21;
	const K_PARAM_DIRORIGEN = 10;
	const K_PARAM_CMNAORIGEN = 70;
	const K_TIPO_DOC = 33;//FA
	const K_ACTV_ECON = 292510;// FORJA, PRENSADO, ESTAMPADO Y LAMINADO DE METAL; INCLUYE PULVIMETALURGIA
	const K_PARAM_HASH = 200;
	const K_ESTADO_SII_EMITIDA = 1;
	
	function wi_factura($cod_item_menu) {
		parent::wi_factura_base($cod_item_menu);
		
		$js = $this->dws['dw_item_factura']->controls['CANTIDAD']->get_onChange();
		$js ="valida_cantidad(this);".$js;
		$this->dws['dw_item_factura']->controls['CANTIDAD']->set_onChange($js);
		$this->dws['dw_item_factura']->add_control(new edit_text('COD_ITEM_DOC',10, 10, 'hidden'));
		
	}
	function new_record() {
		parent::new_record();
		$this->dws['dw_factura']->set_item(0, 'COD_BODEGA', self::K_BODEGA_TERMINADO);
		$this->dws['dw_factura']->set_item(0, 'GENERA_SALIDA', 'S');
		$this->dws['dw_factura']->set_item(0, 'COD_FORMA_PAGO', 7); //CCF - 30 DIAS
		$this->dws['dw_factura']->set_item(0, 'COD_USUARIO_VENDEDOR1', 6); //PIERO SILVA
		$this->dws['dw_factura']->set_item(0, 'PORC_VENDEDOR1', 1); //PIERO SILVA
		
		$this->dws['dw_factura']->set_entrable('GENERA_SALIDA', false);
        if (session::is_set("FACTURA_DESDE_OC_COMERCIAL")) {
            $cod_orden_compra_comercial = session::get("FACTURA_DESDE_OC_COMERCIAL");
            session::un_set("FACTURA_DESDE_OC_COMERCIAL");
            $this->crear_desde_oc_comercial($cod_orden_compra_comercial);
        }
		if (session::is_set('FACTURA_DESDE_OC')) {
			
			$ws_origen = session::get('WS_ORIGEN');
			$array = session::get('FACTURA_DESDE_OC');	
			$this->creada_desde_oc($array, $ws_origen);
			session::un_set('FACTURA_DESDE_OC');
			session::un_set('WS_ORIGEN');
			return;
		}
	}
	
	
	function crear_desde_oc_comercial($cod_orden_compra_comercial) {
		
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$sql = "select O.REFERENCIA
    					,convert(varchar, O.FECHA_ORDEN_COMPRA, 103) FECHA_ORDEN_COMPRA           
        				,O.COD_NOTA_VENTA
				from BIGGI_dbo_ORDEN_COMPRA O
				where O.COD_ORDEN_COMPRA =  $cod_orden_compra_comercial";
        $result_oc = $db->build_results($sql);
        $cod_nota_venta_comercial = $result_oc[0]['COD_NOTA_VENTA']; 
        
        $sql = "select N.REFERENCIA
        				,U.INI_USUARIO
						,CC.NOM_CENTRO_COSTO 
				from BIGGI_dbo_NOTA_VENTA N,  BIGGI_dbo_USUARIO U, BIGGI_dbo_CENTRO_COSTO CC
				where N.COD_NOTA_VENTA = $cod_nota_venta_comercial
				  and U.COD_USUARIO = N.COD_USUARIO_VENDEDOR1
				  and CC.COD_CENTRO_COSTO = dbo.BIGGI_dbo_f_emp_get_cc(N.COD_EMPRESA)";
        $result_nv = $db->build_results($sql);
		$referencia_nv = $result_nv[0]['REFERENCIA'];
		$usuario_nv = $result_nv[0]['INI_USUARIO'];
		$nom_centro_costo_nv = $result_nv[0]['NOM_CENTRO_COSTO'];
		
		$sql = "select E.COD_EMPRESA
    					,E.ALIAS
    					,E.RUT
    					,E.DIG_VERIF
    					,E.NOM_EMPRESA
    					,E.GIRO
    					,S.COD_SUCURSAL
    					,dbo.f_get_direccion('SUCURSAL', S.COD_SUCURSAL, '[DIRECCION]  [NOM_COMUNA] [NOM_CIUDAD] - TELEFONO: [TELEFONO] - FAX: [FAX]') DIRECCION_FACTURA
    					,P.COD_PERSONA
		    			,dbo.f_emp_get_mail_cargo_persona(P.COD_PERSONA,  '[EMAIL] - [NOM_CARGO]') MAIL_CARGO_PERSONA
				from EMPRESA E, SUCURSAL S, PERSONA P
				where E.COD_EMPRESA = 1	-- COMERCIAL BIGGI
				  and S.COD_EMPRESA = E.COD_EMPRESA
				  and P.COD_PERSONA = 1";	//JJ
        $result_emp = $db->build_results($sql);

		$this->dws['dw_factura']->set_item(0, 'COD_EMPRESA', $result_emp[0]['COD_EMPRESA']);	
		$this->dws['dw_factura']->set_item(0, 'ALIAS', $result_emp[0]['ALIAS']);	
		$this->dws['dw_factura']->set_item(0, 'RUT', $result_emp[0]['RUT']);	
		$this->dws['dw_factura']->set_item(0, 'DIG_VERIF', $result_emp[0]['DIG_VERIF']);	
		$this->dws['dw_factura']->set_item(0, 'NOM_EMPRESA', $result_emp[0]['NOM_EMPRESA']);	
		$this->dws['dw_factura']->set_item(0, 'GIRO', $result_emp[0]['GIRO']);	

		$this->dws['dw_factura']->set_item(0, 'COD_SUCURSAL_FACTURA', $result_emp[0]['COD_SUCURSAL']);	
		$this->dws['dw_factura']->set_item(0, 'DIRECCION_FACTURA', $result_emp[0]['DIRECCION_FACTURA']);	
		$this->dws['dw_factura']->set_item(0, 'COD_PERSONA', $result_emp[0]['COD_PERSONA']);			
		$this->dws['dw_factura']->set_item(0, 'MAIL_CARGO_PERSONA', $result_emp[0]['MAIL_CARGO_PERSONA']);
		$referencia = "N/V $cod_nota_venta_comercial; $referencia_nv; $usuario_nv; CC: $nom_centro_costo_nv";	
		$this->dws['dw_factura']->set_item(0, 'REFERENCIA', $referencia);
		
		$this->dws['dw_factura']->set_item(0, 'NRO_ORDEN_COMPRA', $cod_orden_compra_comercial);
		$this->dws['dw_factura']->set_item(0, 'COD_DOC', $cod_orden_compra_comercial);
		$this->dws['dw_factura']->set_entrable('NRO_ORDEN_COMPRA', false);
		$this->dws['dw_factura']->set_item(0, 'FECHA_ORDEN_COMPRA_CLIENTE', $result_oc[0]['FECHA_ORDEN_COMPRA']);	
		$this->dws['dw_factura']->set_entrable('FECHA_ORDEN_COMPRA_CLIENTE', false);
		$this->dws['dw_factura']->set_item(0, 'COD_TIPO_FACTURA', self::K_TIPO_FA_OC_COMERCIAL);
		$this->dws['dw_factura']->set_item(0, 'TD_DISPLAY_CANT_POR_FACT', '');
		$this->dws['dw_factura']->set_item(0, 'COD_USUARIO_VENDEDOR1', 6);	//PIERO SILVA
		$this->dws['dw_factura']->set_item(0, 'PORC_VENDEDOR1', 0);	
		
		$this->dws['dw_factura']->controls['COD_SUCURSAL_FACTURA']->retrieve($result_emp[0]['COD_EMPRESA']);
		$this->dws['dw_factura']->controls['COD_PERSONA']->retrieve($result_emp[0]['COD_EMPRESA']);


		////////////////////
		// items		
		$sql = "select I.ORDEN
    					,I.ITEM
		    			,I.COD_PRODUCTO
    					,I.NOM_PRODUCTO           
					    ,dbo.f_fa_OC_Comercial_por_facturar(I.COD_ITEM_ORDEN_COMPRA) CANTIDAD               
					    ,dbo.f_bodega_stock_cero(I.COD_PRODUCTO, ".self::K_BODEGA_TERMINADO.", getdate()) CANTIDAD_STOCK
					    ,P.PRECIO_VENTA_INTERNO PRECIO                 
    					,I.COD_ITEM_ORDEN_COMPRA
				from BIGGI_dbo_ITEM_ORDEN_COMPRA I, PRODUCTO P
				where I.COD_ORDEN_COMPRA = $cod_orden_compra_comercial
    			  and P.COD_PRODUCTO = I.COD_PRODUCTO
    			  and dbo.f_fa_OC_Comercial_por_facturar(I.COD_ITEM_ORDEN_COMPRA) > 0
				order by ORDEN";
        $result = $db->build_results($sql);
        $sum_total = 0;
        for ($i=0; $i<count($result); $i++) {
			$this->dws['dw_item_factura']->insert_row();
			$this->dws['dw_item_factura']->set_item($i, 'ORDEN', $result[$i]['ORDEN']);
        	
			$this->dws['dw_item_factura']->set_item($i, 'ITEM', $result[$i]['ITEM']);
			$this->dws['dw_item_factura']->set_item($i, 'COD_PRODUCTO', $result[$i]['COD_PRODUCTO']);
			$this->dws['dw_item_factura']->set_item($i, 'COD_PRODUCTO_OLD', $result[$i]['COD_PRODUCTO']);
			$this->dws['dw_item_factura']->set_item($i, 'NOM_PRODUCTO', $result[$i]['NOM_PRODUCTO']);
			if ($result[$i]['CANTIDAD'] > $result[$i]['CANTIDAD_STOCK'])
				$this->dws['dw_item_factura']->set_item($i, 'CANTIDAD', $result[$i]['CANTIDAD_STOCK']);
			else
				$this->dws['dw_item_factura']->set_item($i, 'CANTIDAD', $result[$i]['CANTIDAD']);
			$this->dws['dw_item_factura']->set_item($i, 'CANTIDAD_POR_FACTURAR', $result[$i]['CANTIDAD']);
			$this->dws['dw_item_factura']->set_item($i, 'PRECIO', $result[$i]['PRECIO']);
			$this->dws['dw_item_factura']->set_item($i, 'COD_ITEM_DOC', $result[$i]['COD_ITEM_ORDEN_COMPRA']);
			$this->dws['dw_item_factura']->set_item($i, 'TIPO_DOC', 'ITEM_ORDEN_COMPRA_COMERCIAL');
			$this->dws['dw_item_factura']->set_item($i, 'TD_DISPLAY_CANT_POR_FACT', '');
			$total = $result[$i]['CANTIDAD'] * $result[$i]['PRECIO'];
			$this->dws['dw_item_factura']->set_item($i, 'TOTAL', $total);
			$sum_total += $total;
        }
		$this->dws['dw_item_factura']->controls['ORDEN']->size = 3;
		$this->dws['dw_item_factura']->controls['ITEM']->size = 3;
		$this->dws['dw_item_factura']->controls['COD_PRODUCTO']->size = 20;
		$this->dws['dw_item_factura']->controls['NOM_PRODUCTO']->size = 45;
		
		$this->dws['dw_item_factura']->calc_computed();
		
		$this->dws['dw_factura']->set_item(0, 'SUM_TOTAL', $sum_total);
		$this->dws['dw_factura']->set_item(0, 'PORC_IVA', $this->get_parametro(1));	// IVA
		
		$this->dws['dw_factura']->calc_computed();
		$this->dws['dw_factura']->set_item(0, 'ORIGEN_FACTURA', 'CREAR_DESDE');
	}
	function load_record() {
		parent::load_record();
		$this->dws['dw_factura']->set_entrable('GENERA_SALIDA', false);
		
		// cambia el COD_NOTA_VENTA por el nro de NV
		$this->dws['dw_factura']->add_field('COD_NOTA_VENTA');
		$cod_factura = $this->dws['dw_factura']->get_item(0, 'COD_FACTURA');
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$sql = "select dbo.f_fa_NV_COMERCIAL(COD_FACTURA) COD_NOTA_VENTA
				from FACTURA
				where COD_FACTURA = $cod_factura";
		$result = $db->build_results($sql);
		$this->dws['dw_factura']->set_item(0, 'COD_NOTA_VENTA', $result[0]['COD_NOTA_VENTA']);
	}
	
	function cant_para_stock($result, $cod_item_oc, $i) {
		//obtener el cod_producto de $cod_item_oc
		$cod_producto = $result['ITEM_ORDEN_COMPRA'][$i]['COD_PRODUCTO'];
		$cant_para_stock = 0;
		
		for($j=0 ; $j < count($result['ITEM_ORDEN_COMPRA']) ; $j++){
			if ($result['ITEM_ORDEN_COMPRA'][$j]['COD_PRODUCTO'] == $cod_producto){
				$cantidad = $result['ITEM_ORDEN_COMPRA'][$j]['CANTIDAD'];
				$cant_para_stock += $cantidad;
			}
		}
		
		return $cant_para_stock;
	}
	
	function creada_desde_oc($result, $ws_origen){
		$str_alert_aut = '';
		$str_alert_no_aut = '';
		$str_alert_elim = '';
		$str_alert_no_existe = '';
		$j = 0;	
		$cod_orden_compra = $result['ORDEN_COMPRA'][0]['COD_ORDEN_COMPRA'];
		$rut = $result['ORDEN_COMPRA'][0]['RUT'];
		
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);		
		$sql="SELECT E.COD_EMPRESA
					,ALIAS
					,DIG_VERIF
					,NOM_EMPRESA
					,GIRO
					,P.COD_PERSONA
					,S.COD_SUCURSAL
					,dbo.f_get_direccion('SUCURSAL', S.COD_SUCURSAL, '[DIRECCION]  [NOM_COMUNA] [NOM_CIUDAD] - TELEFONO: [TELEFONO] - FAX: [FAX]') DIRECCION_FACTURA						
					,dbo.f_emp_get_mail_cargo_persona(P.COD_PERSONA, '[EMAIL]') MAIL_CARGO_PERSONA
			  FROM EMPRESA E
				  ,SUCURSAL S
				  ,PERSONA P
			  WHERE RUT = $rut
			  AND E.COD_EMPRESA = S.COD_EMPRESA
			  AND P.COD_SUCURSAL = S.COD_SUCURSAL";
		$result_emp = $db->build_results($sql);
		$cod_empresa = $result_emp[0]['COD_EMPRESA'];
		
		$this->dws['dw_factura']->set_item(0, 'COD_EMPRESA',				$result_emp[0]['COD_EMPRESA']);	
		$this->dws['dw_factura']->set_item(0, 'ALIAS',						$result_emp[0]['ALIAS']);	
		$this->dws['dw_factura']->set_item(0, 'RUT',						$rut);	
		$this->dws['dw_factura']->set_item(0, 'DIG_VERIF',					$result_emp[0]['DIG_VERIF']);	
		$this->dws['dw_factura']->set_item(0, 'NOM_EMPRESA',				$result_emp[0]['NOM_EMPRESA']);	
		$this->dws['dw_factura']->set_item(0, 'GIRO',						$result_emp[0]['GIRO']);	
		$this->dws['dw_factura']->set_item(0, 'DIRECCION_FACTURA',			$result_emp[0]['DIRECCION_FACTURA']);
		$this->dws['dw_factura']->set_item(0, 'MAIL_CARGO_PERSONA',			$result_emp[0]['MAIL_CARGO_PERSONA']);
		$this->dws['dw_factura']->set_item(0, 'COD_PERSONA',				$result_emp[0]['COD_PERSONA']);
		$this->dws['dw_factura']->set_item(0, 'COD_SUCURSAL_FACTURA',		$result_emp[0]['COD_SUCURSAL']);
		$this->dws['dw_factura']->set_item(0, 'COD_USUARIO_VENDEDOR1',		6); //PIERO SILVA
		$this->dws['dw_factura']->set_item(0, 'PORC_VENDEDOR1', 			1);
		$this->dws['dw_factura']->set_item(0, 'ORIGEN_FACTURA', 			'CREAR_DESDE');
		
		$this->dws['dw_factura']->set_item(0, 'NRO_ORDEN_COMPRA',			$cod_orden_compra);
		$this->dws['dw_factura']->set_item(0, 'WS_ORIGEN',					$ws_origen);
		
		if($ws_origen == 'COMERCIAL'){
			$this->dws['dw_factura']->set_item(0, 'COD_CENTRO_COSTO', 001);
			$this->dws['dw_factura']->set_item(0, 'REFERENCIA', 'NV:'.$result['ORDEN_COMPRA'][0]['COD_NOTA_VENTA'].' / '.$result['ORDEN_COMPRA'][0]['NV_NOM_EMPRESA'].' / '.$result['ORDEN_COMPRA'][0]['OC_NOM_USUARIO']);
		}else if($ws_origen == 'TODOINOX'){
			$this->dws['dw_factura']->set_item(0, 'COD_CENTRO_COSTO', 002);
			$this->dws['dw_factura']->set_item(0, 'REFERENCIA', $result['ITEM_ORDEN_COMPRA'][0]['COD_PRODUCTO'].' / '.'BODEGA BIGGI STOCK');		
		}else{	//RENTAL
			$this->dws['dw_factura']->set_item(0, 'COD_CENTRO_COSTO', '');
			if($result['ORDEN_COMPRA'][0]['TIPO_ORDEN_COMPRA'] == 'ARRIENDO')
				$this->dws['dw_factura']->set_item(0, 'REFERENCIA', 'ARR: '.$result['ORDEN_COMPRA'][0]['COD_DOC'].' / '.$result['ORDEN_COMPRA'][0]['A_NOM_EMPRESA'].' / '.$result['ORDEN_COMPRA'][0]['OC_NOM_USUARIO']);
			else
				$this->dws['dw_factura']->set_item(0, 'REFERENCIA', 'NV:'.$result['ORDEN_COMPRA'][0]['COD_NOTA_VENTA'].' / '.$result['ORDEN_COMPRA'][0]['NV_NOM_EMPRESA'].' / '.$result['ORDEN_COMPRA'][0]['OC_NOM_USUARIO']);
		}	
		
		$this->dws['dw_factura']->set_item(0, 'FECHA_ORDEN_COMPRA_CLIENTE', $result['ORDEN_COMPRA'][0]['FECHA_ORDEN_COMPRA']);
		$this->dws['dw_factura']->set_item(0, 'RUT',						$result['ORDEN_COMPRA'][0]['RUT']);
		$this->dws['dw_factura']->set_item(0, 'PORC_DSCTO1',				$result['ORDEN_COMPRA'][0]['PORC_DSCTO1']);
		$this->dws['dw_factura']->set_item(0, 'MONTO_DSCTO1',				$result['ORDEN_COMPRA'][0]['MONTO_DSCTO1']);	
		$this->dws['dw_factura']->set_item(0, 'PORC_IVA',					$this->get_parametro(1));
		$this->dws['dw_factura']->set_item(0, 'COD_FORMA_PAGO',				7); //CCF - 30 DIAS
		
		$sql	= "SELECT COD_TIPO_FACTURA
	  					 ,NOM_TIPO_FACTURA
				   FROM TIPO_FACTURA 
				   WHERE COD_TIPO_FACTURA = 1";
		$result_f = $db->build_results($sql);
		$this->dws['dw_factura']->set_item(0, 'COD_TIPO_FACTURA', $result_f[0]['COD_TIPO_FACTURA']);
		$this->dws['dw_factura']->set_item(0, 'COD_TIPO_FACTURA_H', $result_f[0]['COD_TIPO_FACTURA']);
		
		$sql="SELECT COD_PERFIL
	  		  FROM USUARIO
	  		  WHERE COD_USUARIO = $this->cod_usuario";
		$result_usu	= $db->build_results($sql);

		$sql="SELECT AUTORIZA_MENU
			   		 FROM AUTORIZA_MENU
			   		 WHERE COD_PERFIL = ".$result_usu[0]['COD_PERFIL']."
			   		 AND COD_ITEM_MENU = '992050'";
		$result_aut	= $db->build_results($sql);
		
		if($ws_origen == 'TODOINOX')
			$tipo_doc = 'ITEM_ORDEN_COMPRA_TODOINOX';
		else if($ws_origen == 'COMERCIAL')
			$tipo_doc = 'ITEM_ORDEN_COMPRA_COMERCIAL';
		else //RENTAL
			$tipo_doc = 'ITEM_ORDEN_COMPRA_RENTAL';		
		
		$sum_total = 0;
		for ($i=0; $i < count($result['ITEM_ORDEN_COMPRA']); $i++){
			$alert_stk = false;
			
			$cod_item_oc = $result['ITEM_ORDEN_COMPRA'][$i]['COD_ITEM_ORDEN_COMPRA'];
			$cod_producto = $result['ITEM_ORDEN_COMPRA'][$i]['COD_PRODUCTO'];
			$nom_producto = $result['ITEM_ORDEN_COMPRA'][$i]['NOM_PRODUCTO'];
			
			//busca si el producto existe en bodega
			//si no existe ni un producto no se deja facturar 
			//si existe por lo menos uno los demas se elimina dejando este
			$sql_producto = "SELECT COUNT(*) CANTIDAD FROM PRODUCTO WHERE COD_TIPO_PRODUCTO = 1 AND COD_PRODUCTO = '$cod_producto'";
			$result_producto = $db->build_results($sql_producto);
			$cantidad = $result_producto[0]["CANTIDAD"];
			
			if($cantidad > 0)
			{
				
				$sql = "SELECT SUM(CANTIDAD) CANTIDAD
						FROM ITEM_FACTURA
						WHERE COD_ITEM_DOC = $cod_item_oc";
						
				if($ws_origen == 'TODOINOX')
					$sql.= " AND TIPO_DOC = 'ITEM_ORDEN_COMPRA_TODOINOX'";
				else if($ws_origen == 'COMERCIAL')
					$sql.= " AND TIPO_DOC = 'ITEM_ORDEN_COMPRA_COMERCIAL'";
				else //RENTAL
					$sql.= " AND TIPO_DOC = 'ITEM_ORDEN_COMPRA_RENTAL'";		
						
				$result_cant = $db->build_results($sql);
				
				$cantidad = $result['ITEM_ORDEN_COMPRA'][$i]['CANTIDAD'] - $result_cant[0]['CANTIDAD'];
				$cant_para_stock = $this->cant_para_stock($result, $cod_item_oc, $i);
				////////Manejo de precio publico e interno////////	
				if($cod_producto != 'E' && $cod_producto != 'TE' && $cod_producto != 'I' && $cod_producto != 'F'){
					$sql_emp = "SELECT COD_EMPRESA
					FROM PRECIO_INT_EMP
					WHERE COD_EMPRESA = $cod_empresa";
			
					$result_emp = $db->build_results($sql_emp);	
					if(count($result_emp) != 0){		
						$sql_precio_int = "SELECT  PRECIO_VENTA_INTERNO PRECIO_INT
										   FROM 	PRODUCTO
										   WHERE COD_PRODUCTO = '$cod_producto'";
							
						$result_precio = $db->build_results($sql_precio_int);
						$precio = $result_precio[0]['PRECIO_INT'];
						
						if($precio == 0.00){
							$sql_precio_pub = "SELECT  PRECIO_VENTA_PUBLICO PRECIO_PUB
											   FROM 	PRODUCTO
											   WHERE COD_PRODUCTO = '$cod_producto'";
								
							$result_prec_pub = $db->build_results($sql_precio_pub);
							$precio = $result_prec_pub[0]['PRECIO_PUB'];
						}
					}else{
						$sql_precio_pub = "SELECT  PRECIO_VENTA_PUBLICO PRECIO_PUB
										   FROM 	PRODUCTO
										   WHERE COD_PRODUCTO = '$cod_producto'";
								
						$result_prec_pub = $db->build_results($sql_precio_pub);
						$precio = $result_prec_pub[0]['PRECIO_PUB'];
					}
					///////Manejo Stock/////
					$sql_stock="SELECT dbo.f_bodega_stock(COD_PRODUCTO, 2, GETDATE()) STOCK
							  		  ,MANEJA_INVENTARIO
							    FROM PRODUCTO
							    WHERE COD_PRODUCTO = '$cod_producto'";
					$result_stock = $db->build_results($sql_stock);
	
					if($result_aut[0]['AUTORIZA_MENU'] == 'E'){
						if($result_stock[0]['MANEJA_INVENTARIO'] <> 'N'){
							if($cant_para_stock > $result_stock[0]['STOCK']){
								$pos = strpos($str_alert_aut, $cod_producto.', '.$nom_producto);
								if ($pos===false)
									$str_alert_aut .= $cod_producto.', '.$nom_producto.', CANTIDAD SOLICITADA '.$cant_para_stock.'|';
							}															
						}
					}else{
						if($result_stock[0]['MANEJA_INVENTARIO'] <> 'N'){
							if($cant_para_stock > $result_stock[0]['STOCK']){
								$pos = strpos($str_alert_no_aut, $cod_producto.', '.$nom_producto);
								if ($pos===false)
									$str_alert_no_aut .= $cod_producto.', '.$nom_producto.', CANTIDAD SOLICITADA '.$cant_para_stock.'|';
								
								$alert_stk = true;
							}		
						}		
					}
					//////////////////////
				}else
					$precio = $result['ITEM_ORDEN_COMPRA'][$i]['PRECIO'];	
				//////////////////////////////
				
				if($alert_stk == true)
					$cantidad = 0;	

				$this->dws['dw_item_factura']->insert_row();
				$this->dws['dw_item_factura']->set_item($j, 'ORDEN',			$result['ITEM_ORDEN_COMPRA'][$i]['ORDEN']);
				$this->dws['dw_item_factura']->set_item($j, 'ITEM',				$result['ITEM_ORDEN_COMPRA'][$i]['ITEM']);
				$this->dws['dw_item_factura']->set_item($j, 'COD_PRODUCTO',		$cod_producto);
				$this->dws['dw_item_factura']->set_item($j, 'COD_PRODUCTO_OLD', $cod_producto);
				$this->dws['dw_item_factura']->set_item($j, 'NOM_PRODUCTO',		$nom_producto);
				$this->dws['dw_item_factura']->set_item($j, 'CANTIDAD',			$cantidad);
				$this->dws['dw_item_factura']->set_item($j, 'PRECIO',			$precio);
				$this->dws['dw_item_factura']->set_item($j, 'COD_ITEM_DOC',		$cod_item_oc);
				$this->dws['dw_item_factura']->set_item($j, 'TIPO_DOC',			$tipo_doc);
				$j = $j+1;
				
				$total = $precio * $cantidad;
				$sum_total += $total;
				
			}
			else
			{
				$str_alert_elim .= $cod_producto.' - '.$nom_producto.'|';
			}
			
		}
		
		//Alerta productos eliminados
		if($str_alert_elim != ''){
			$message = 'Algunos productos incluidos en la OC no existen en Bodega Biggi, estos son:.\n\n';
			$arr_productos = explode('|',trim($str_alert_elim,'|'));
			
			for($i=0 ; $i < count($arr_productos) ; $i++){
				$message .=	$arr_productos[$i].'\n\n';
				$str_alert_no_existe .= $arr_productos[$i].'\n\n'; 
			}
			
			$this->alert($message);
		}
		//Alerta para sin stock autorizado
		if($str_alert_aut != ''){
			$message = 'Las cantidades solicitadas en la OC '.$cod_orden_compra.' de BIGGI CHILE SOCIEDAD LIMITADA exceden la cantidad de stock disponible en Bodega BIGGI.\n\n';
			$message .= 'Los productos son:\n\n';
			$arr_productos = explode('|',trim($str_alert_aut,'|'));
			
			for($i=0 ; $i < count($arr_productos) ; $i++)
				$message .=	$arr_productos[$i].'\n\n';
				
			$message .=	'Sin embargo, usted esta autorizado para facturar productos sin stock.';
			$this->alert($message);
		}
		//Alerta para sin stock no autorizado
		if($str_alert_no_aut != ''){
			$message = 'Las cantidades solicitadas en la OC '.$cod_orden_compra.' de BIGGI CHILE SOCIEDAD LIMITADA exceden la cantidad de stock disponible en Bodega BIGGI.\n\n';
			$message .=	'Los productos son:\n\n';
			$arr_productos = explode('|',trim($str_alert_no_aut,'|'));
			
			for($i=0 ; $i < count($arr_productos) ; $i++)
				$message .=	$arr_productos[$i].'\n\n';
			
			$message .=	'Usted no esta autorizado para facturar productos sin stock.';
			$this->alert($message);
		}
		
		$this->dws['dw_item_factura']->calc_computed();
		
		$total_neto = $sum_total - $monto_desc1;
		$monto_iva = $total_neto * ($this->get_parametro(1)/100);
		$total_con_iva = $total_neto + $monto_iva;
		
		$this->dws['dw_factura']->set_item(0, 'TOTAL_NETO',		$total_neto);
		$this->dws['dw_factura']->set_item(0, 'MONTO_IVA',		$monto_iva);
		$this->dws['dw_factura']->set_item(0, 'TOTAL_CON_IVA',	$total_con_iva);
		
		
		$this->dws['dw_factura']->controls['COD_SUCURSAL_FACTURA']->retrieve($cod_empresa);
		$this->dws['dw_factura']->controls['COD_PERSONA']->retrieve($cod_empresa);
		
		$this->dws['dw_factura']->set_entrable('RUT', false);
		$this->dws['dw_factura']->set_entrable('ALIAS', false);
		$this->dws['dw_factura']->set_entrable('NOM_EMPRESA', false);
		$this->dws['dw_factura']->set_entrable('COD_SUCURSAL_FACTURA', false);
		$this->dws['dw_factura']->set_entrable('COD_PERSONA', false);
		$this->dws['dw_factura']->set_entrable('COD_FORMA_PAGO', false);
		$this->dws['dw_factura']->set_entrable('NO_TIENE_OC', false);
		
		$this->dws['dw_item_factura']->b_add_line_visible = false;
		$this->dws['dw_item_factura']->b_del_line_visible = false;
		
		unset($this->dws['dw_factura']->controls['NRO_ORDEN_COMPRA']);
		$this->dws['dw_factura']->add_control(new static_text('NRO_ORDEN_COMPRA'));
		
		unset($this->dws['dw_factura']->controls['COD_EMPRESA']);
		$this->dws['dw_factura']->add_control(new static_text('COD_EMPRESA'));
		
		if (count($this->dws['dw_factura']) ==0 )
		{
			session::set("SIN_PRODUCTOS", $str_alert_no_existe);
			$this->goto_list();
		}
	}
	
	function procesa_event() {		
		if((isset($_POST['b_back_x']) && session::is_set('FACTURA_DESDE_INF_X_FAC')) 
			|| (isset($_POST['b_no_save_x']) && session::is_set('FACTURA_DESDE_INF_X_FAC'))
				|| (isset($_POST['b_delete_x']) && session::is_set('FACTURA_DESDE_INF_X_FAC'))) {
			session::un_set("FACTURA_DESDE_INF_X_FAC");
			$url = $this->root_url."../../commonlib/trunk/php/mantenedor.php?modulo=inf_oc_por_facturar_bodega&cod_item_menu=4097";
			header ('Location:'.$url);
		}else
			parent::procesa_event();
	}

	function habilita_boton(&$temp, $boton, $habilita) {
		parent::habilita_boton($temp, $boton, $habilita);
		
		if($boton == 'enviar_dte'){
			if($habilita){
				$control = '<input name="b_enviar_dte" id="b_enviar_dte" src="../../images_appl/b_enviar_dte.jpg" type="image" '.
							 'onMouseDown="MM_swapImage(\'b_enviar_dte\',\'\',\'../../images_appl/b_enviar_dte_click.jpg\',1)" '.
							 'onMouseUp="MM_swapImgRestore()" onMouseOut="MM_swapImgRestore()" '.
							 'onMouseOver="MM_swapImage(\'b_enviar_dte\',\'\',\'../../images_appl/b_enviar_dte_over.jpg\',1)" 
							 onClick="var vl_tab = document.getElementById(\'wi_current_tab_page\'); if (TabbedPanels1 && vl_tab) vl_tab.value =TabbedPanels1.getCurrentTabIndex();
									 if (document.getElementById(\'b_save\')) {
										 if (validate_save()) {
										 		document.getElementById(\'wi_hidden\').value = \'save_enviar_dte\';
										 		document.getElementById(\'b_save\').click();
										 		return true;
										 	}
										 	else
										 		return false;
									 }
								 	 else
								 	 		return true;"/>';
			}else{
				$control = '<img src="../../images_appl/b_enviar_dte_d.jpg">';
			}
			
			$temp->setVar("WSWAP_ENVIA_DTE", $control);
		}
		if($boton == 'consultar_dte'){
			if($habilita){
				$control = '<input name="b_consultar_dte" id="b_consultar_dte" src="../../images_appl/b_consultar_dte.jpg" type="image" '.
							 'onMouseDown="MM_swapImage(\'b_consultar_dte\',\'\',\'../../images_appl/b_consultar_dte_click.jpg\',1)" '.
							 'onMouseUp="MM_swapImgRestore()" onMouseOut="MM_swapImgRestore()" '.
							 'onMouseOver="MM_swapImage(\'b_consultar_dte\',\'\',\'../../images_appl/b_consultar_dte_over.jpg\',1)"
							 onClick="return true;"/>';
			}else{
				$control = '<img src="../../images_appl/b_consultar_dte_d.jpg">';
			}
			
			$temp->setVar("WSWAP_CONSULTAR_DTE", $control);
		}
		if($boton == 'imprimir_dte'){
			if($habilita){
				$ruta_over = "'../../images_appl/b_reimprime_dte_over.jpg'";
				$ruta_out = "'../../images_appl/b_reimprime_dte.jpg'";
				$ruta_click = "'../../images_appl/b_reimprime_dte_click.jpg'";
				$control =  '<input name="b_imprimir_dte" id="b_imprimir_dte" type="button" onmouseover="entrada(this, '.$ruta_over.')" onmouseout="salida(this, '.$ruta_out.')" onmousedown="down(this, '.$ruta_click.')"'.
				   			'style="cursor:pointer;height:68px;width:66px;border: 0;background-image:url(../../images_appl/b_reimprime_dte.jpg);background-repeat:no-repeat;background-position:center;border-radius: 15px;"'.
				   			'onClick="return dlg_print_dte();" />';
			
			}else{
				$control = '<img src="../../images_appl/b_reimprime_dte_d.jpg">';
			}
			
			$temp->setVar("WSWAP_IMPRIMIR_DTE", $control);
		}
		if($boton == 'reenviar_dte'){
			if($habilita){
				$control = '<input name="b_reenviar_dte" id="b_reenviar_dte" src="../../images_appl/b_reenviar.jpg" type="image" '.
							 'onMouseDown="MM_swapImage(\'b_reenviar_dte\',\'\',\'../../images_appl/b_reenviar_click.jpg\',1)" '.
							 'onMouseUp="MM_swapImgRestore()" onMouseOut="MM_swapImgRestore()" '.
							 'onMouseOver="MM_swapImage(\'b_reenviar_dte\',\'\',\'../../images_appl/b_reenviar_over.jpg\',1)"
							 onClick="return true;"/>';
			}else{
				$control = '<img src="../../images_appl/b_reenviar_d.jpg">';
			}
			
			$temp->setVar("WSWAP_REENVIAR_DTE", $control);
		}
		if($boton == 'xml_dte'){
			if($habilita){
				$control = '<input name="b_xml_dte" id="b_xml_dte" src="../../images_appl/b_xml_dte.jpg" type="image" '.
							 'onMouseDown="MM_swapImage(\'b_xml_dte\',\'\',\'../../images_appl/b_xml_dte_click.jpg\',1)" '.
							 'onMouseUp="MM_swapImgRestore()" onMouseOut="MM_swapImgRestore()" '.
							 'onMouseOver="MM_swapImage(\'b_xml_dte\',\'\',\'../../images_appl/b_xml_dte_over.jpg\',1)"
							 onClick="return true;"/>';
			}else{
				$control = '<img src="../../images_appl/b_xml_dte_d.jpg">';
			}
			
			$temp->setVar("WSWAP_XML_DTE", $control);
		}
		////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		
	}
	
	function navegacion(&$temp){
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		parent::navegacion($temp);

		$cod_factura = $this->get_key();
		if($cod_factura <> ''){
			$Sql= "SELECT F.COD_ESTADO_DOC_SII
							,F.TRACK_ID_DTE
							,F.RESP_EMITIR_DTE
				    FROM FACTURA F
					WHERE F.COD_FACTURA = $cod_factura";
			$result = $db->build_results($Sql);
			$COD_ESTADO_DOC_SII = $result[0]['COD_ESTADO_DOC_SII'];
			$TRACK_ID_DTE		= $result[0]['TRACK_ID_DTE'];
			$RESP_EMITIR_DTE	= $result[0]['RESP_EMITIR_DTE'];
		}
		if($COD_ESTADO_DOC_SII == self::K_ESTADO_SII_EMITIDA){
			if($RESP_EMITIR_DTE == '' && $TRACK_ID_DTE == ''){ //ingresa por primera vez
				if($this->tiene_privilegio_opcion(self::K_AUTORIZA_ENVIAR_DTE)== 'S')
					$this->habilita_boton($temp, 'enviar_dte', true);
				else
					$this->habilita_boton($temp, 'enviar_dte', false);
			
			}else if($RESP_EMITIR_DTE <> '' && $TRACK_ID_DTE == ''){ //Reimprime
				$this->habilita_boton($temp, 'enviar_dte', false);
			}
			
			$this->habilita_boton($temp, 'imprimir_dte', false);
			$this->habilita_boton($temp, 'consultar_dte', false);
			$this->habilita_boton($temp, 'xml_dte', false);
			$this->habilita_boton($temp, 'reenviar_dte', false);
		}else if ($COD_ESTADO_DOC_SII == self::K_ESTADO_SII_ENVIADA){
			if($TRACK_ID_DTE <> ''){
				$this->habilita_boton($temp, 'enviar_dte', false);
			
				if($this->tiene_privilegio_opcion(self::K_AUTORIZA_CONSULTAR_DTE)== 'S')
					$this->habilita_boton($temp, 'consultar_dte', true);
				else
					$this->habilita_boton($temp, 'consultar_dte', false);

				if($this->tiene_privilegio_opcion(self::K_AUTORIZA_XML_DTE)== 'S')
					$this->habilita_boton($temp, 'xml_dte', true);
				else
					$this->habilita_boton($temp, 'xml_dte', false);
					
				if($this->tiene_privilegio_opcion(self::K_AUTORIZA_REENVIAR_DTE)== 'S')
					$this->habilita_boton($temp, 'reenviar_dte', true);
				else
					$this->habilita_boton($temp, 'reenviar_dte', false);	
			}
			
			if($this->tiene_privilegio_opcion(self::K_AUTORIZA_IMPRIMIR_DTE)== 'S')
				$this->habilita_boton($temp, 'imprimir_dte', true);
			else
				$this->habilita_boton($temp, 'imprimir_dte', false);
		}else{
			$this->habilita_boton($temp, 'enviar_dte', false);
			$this->habilita_boton($temp, 'imprimir_dte', false);
			$this->habilita_boton($temp, 'consultar_dte', false);
			$this->habilita_boton($temp, 'xml_dte', false);
			$this->habilita_boton($temp, 'reenviar_dte', false);
		}
	}
	
	function enviar_dte($reenviar = false){
		if (!$this->lock_record())
			return false;

		$cod_factura = $this->get_key();	
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		
		$sql = "SELECT NRO_FACTURA
				FROM FACTURA
				WHERE COD_FACTURA = $cod_factura";
		$result = $db->build_results($sql);
		
		if($result[0]['NRO_FACTURA'] <> '' && $reenviar == false)
			return false;
		
		if($reenviar){
			$dte = new dte();
				
			//Se le pasa como variable hash de la clase obtenida en parametros en la BD
			$SqlHash = "SELECT dbo.f_get_parametro(".self::K_PARAM_HASH.") K_HASH";  
			$Datos_Hash = $db->build_results($SqlHash);
			$dte->hash = $Datos_Hash[0]['K_HASH'];
			$PORC_IVA = $this->dws['dw_factura']->get_item(0, 'PORC_IVA');
			
			if ($PORC_IVA==0){
				$cod_tipo_dte = 34;
			}else{
				$cod_tipo_dte = 33;
			}
				
			$sql_folio = "select  NRO_FACTURA 
								,REPLACE(REPLACE(dbo.f_get_parametro(20),'.',''),'-5','') as RUTEMISOR
						 from FACTURA 
						 where COD_FACTURA=$cod_factura";
			
			$result_folio = $db->build_results($sql_folio);
			$nro_factura = $result_folio[0]['NRO_FACTURA'];
			$tipo_doc = $cod_tipo_dte;
			$rutemisor = $result_folio[0]['RUTEMISOR'];
			//resuelve la cadena entrega
			$objEnJson_genera = $dte->eliminar_dte($nro_factura,$tipo_doc,$rutemisor);
			if(trim($objEnJson_genera) <> 'true'){
				//Llamamos al envio consultar estado documento.
				$response = $dte->actualizar_estado($tipo_doc,$nro_factura,$rutemisor);
				$actualizar_estado = $dte->respuesta_actualizar_estado($response);
				$revision_estado	= substr($actualizar_estado[6], 0, 3);
				
				if($revision_estado == 'EPR')
					$estado_libre_dte = 'Aceptada';
				else if($revision_estado == 'RPR')
					$estado_libre_dte = 'Aceptado con Reparos';
				else if($revision_estado == 'RLV')
					$estado_libre_dte = 'Aceptada con Reparos Leves';
			
				$this->_load_record();
				print "<script>alert('No se puede reenviar el DTE al SII pues su estado actual es: ".$estado_libre_dte."');</script>";
				return;
			}
		}
		
		$sql = "SELECT (CAST(F.RUT AS NVARCHAR(8)))+'-'+(CAST (F.DIG_VERIF AS NVARCHAR(1))) as RUT_COMPLETO
						,F.NOM_EMPRESA
              			,F.GIRO
              			,F.DIRECCION
              			,F.NOM_COMUNA
              			,F.PORC_DSCTO1
              			,F.MONTO_DSCTO1
              			,F.MONTO_DSCTO2
              			,F.REFERENCIA TermPagoGlosa
              			,801 TpoDocRef
              			,NRO_ORDEN_COMPRA FolioRef
              			,replace (CONVERT(varchar,FECHA_ORDEN_COMPRA_CLIENTE,102),'.','-')FchRef
				FROM FACTURA F
				WHERE F.COD_FACTURA =$cod_factura";
		$contenido = $db->build_results($sql);
		
		$SqlDetalles ="SELECT ROW_NUMBER()OVER(ORDER BY ITF.ORDEN) AS NroLinDet
							,('INT1')AS TpoCodigo
							,ITF.COD_PRODUCTO AS VlrCodigo
							,ITF.NOM_PRODUCTO AS NmbItem 
							,ITF.CANTIDAD
							,ITF.PRECIO
							,(ITF.CANTIDAD * ITF.PRECIO) AS MONTO_TOTAL
						FROM ITEM_FACTURA ITF WHERE ITF.COD_FACTURA = $cod_factura
						ORDER BY ITF.ORDEN";
		$Detalles = $db->build_results($SqlDetalles);

		for($i = 0; $i < count($Detalles); $i++) {
			$NmbItem	= substr($Detalles[$i]['NmbItem'], 0, 80);
			$VlrCodigo	= substr($Detalles[$i]['VlrCodigo'], 0, 35);
			$CANTIDAD	= substr($Detalles[$i]['CANTIDAD'], 0, 18);
			$PRECIO		= substr($Detalles[$i]['PRECIO'], 0, 18);

			$ad['Detalle'][$i]["NmbItem"]= utf8_encode(trim($NmbItem));
			$ad['Detalle'][$i]["CdgItem"]= $VlrCodigo;
			$ad['Detalle'][$i]["QtyItem"]= $CANTIDAD;
			$ad['Detalle'][$i]["PrcItem"]= $PRECIO;
		}
		
		$RutRecep		= substr($contenido[0]['RUT_COMPLETO'], 0, 10); 
		$RznSocRecep	= substr($contenido[0]['NOM_EMPRESA'], 0, 100);
		$GiroRecep		= substr($contenido[0]['GIRO'], 0, 40);
		$DirRecep		= substr($contenido[0]['DIRECCION'], 0, 70);
		$ComRecep		= substr($contenido[0]['NOM_COMUNA'], 0, 20);
		$DireccionC		= substr(str_replace("#","N",$DirRecep), 0, 70);
		$GiroRecep40	= substr($GiroRecep, 0, 40);
		$DescuentoMonto1= substr($contenido[0]['MONTO_DSCTO1'], 0, 18);
		$DescuentoMonto2= substr($contenido[0]['MONTO_DSCTO2'], 0, 18);
		$TpoDocRef		= substr($contenido[0]['TpoDocRef'], 0, 3);
		$FolioRef		= substr(trim($contenido[0]['FolioRef']), 0, 18);
		$FchRef			= substr($contenido[0]['FchRef'], 0, 10);
		  
		
		if($ComRecep == ''){
			$this->_load_record();
			print " <script>alert('Error al Emitir Dte, la empresa de la factura no tiene asignada Comuna.');</script>";
			return;
		}
		
		$GiroRecep=ltrim(rtrim($GiroRecep));
		if($GiroRecep == ''){
		    $this->_load_record();
		    print " <script>alert('Error al Emitir Dte, la empresa de la factura no tiene Giro.');</script>";
		    return;
		}
		
		$SqlEmisor ="SELECT	REPLACE(dbo.f_get_parametro(".self::K_PARAM_RUTEMISOR."),'.','') RUTEMISOR
							,dbo.f_get_parametro(".self::K_PARAM_RZNSOC.") RZNSOC
							,dbo.f_get_parametro(".self::K_PARAM_GIROEMIS.") GIROEMIS
							,dbo.f_get_parametro(".self::K_PARAM_DIRORIGEN.") DIRORIGEN
							,dbo.f_get_parametro(".self::K_PARAM_CMNAORIGEN.") CMNAORIGEN";  
		$Datos_Emisor = $db->build_results($SqlEmisor);
		
		$rutemisor	= $Datos_Emisor[0]['RUTEMISOR']; 
		$rznsoc		= $Datos_Emisor[0]['RZNSOC']; 
		$giroemis	= $Datos_Emisor[0]['GIROEMIS']; 
		$dirorigen	= $Datos_Emisor[0]['DIRORIGEN']; 
		$cmnaorigen	= $Datos_Emisor[0]['CMNAORIGEN']; 
		
		$a['Encabezado']['IdDoc']['TipoDTE']		= self::K_TIPO_DOC; //Factura
		$a['Encabezado']['IdDoc']['Folio']			= 0; //el folio lo da el sistema de facturacion.
		
		if($RutRecep == '89257000-0' || $RutRecep == '80112900-5' || $RutRecep == '77773650-7' || $RutRecep == '91462001-5'){
			$a['Encabezado']['IdDoc']['FmaPago']	= 1;
		}else{
			$COD_FORMA_PAGO = $this->dws['dw_factura']->get_item(0, 'COD_FORMA_PAGO');
			
			$Sql_forma_pago_sii = "SELECT FORMA_PAGO_SII
								   FROM FORMA_PAGO
								   WHERE COD_FORMA_PAGO = $COD_FORMA_PAGO";
			$result_pago_sii = $db->build_results($Sql_forma_pago_sii);
			
			$a['Encabezado']['IdDoc']['FmaPago'] = $result_pago_sii[0]['FORMA_PAGO_SII'];
			
		}
			
		$a['Encabezado']['Emisor']['RUTEmisor']		= substr($rutemisor, 0, 10);
		$a['Encabezado']['Emisor']['RznSoc']		= utf8_encode(substr($rznsoc, 0, 100));
		$a['Encabezado']['Emisor']['GiroEmis']		= utf8_encode(substr($giroemis, 0, 80));
		$a['Encabezado']['Emisor']['Acteco']		= self::K_ACTV_ECON;//codigo de actividad economica del emisor registrada en el sii.
		$a['Encabezado']['Emisor']['DirOrigen']		= utf8_encode(substr($dirorigen, 0, 60));
		$a['Encabezado']['Emisor']['CmnaOrigen']	= utf8_encode(substr($cmnaorigen, 0, 20));
		$a['Encabezado']['Receptor']['RUTRecep']	= $RutRecep;
		$a['Encabezado']['Receptor']['RznSocRecep']	= utf8_encode($RznSocRecep);
		$a['Encabezado']['Receptor']['GiroRecep']	= utf8_encode($GiroRecep40);
		$a['Encabezado']['Receptor']['DirRecep']	= utf8_encode($DireccionC);
		$a['Encabezado']['Receptor']['CmnaRecep']	= utf8_encode($ComRecep);
		
		$tiene_Folio = 'N';
		$tiene_descuento = 'N';
		if ($FolioRef <> ''){
			$c['Referencia']['NroLinRef']	= 1;
			$c['Referencia']['TpoDocRef']	= $TpoDocRef;
			$c['Referencia']['FolioRef']	= $FolioRef;
			$c['Referencia']['FchRef']		= $FchRef;
			$tiene_Folio = 'S';
		}

		if($DescuentoMonto1 <> 0){
			$b['DscRcgGlobal'][0]['NroLinDR']	= 1;
			$b['DscRcgGlobal'][0]['TpoMov']	= 'D'; //D(descuento) o R(recargo)
			$b['DscRcgGlobal'][0]['TpoValor']= '$';//Indica si es Porcentaje o Monto �%� o �$�
			$b['DscRcgGlobal'][0]['ValorDR']	= $DescuentoMonto1;
			
			$tiene_descuento = 'S';
			//junta los arreglos en uno.
		}
		
		if($DescuentoMonto2 <> 0){
			$b['DscRcgGlobal'][1]['NroLinDR']	= 2; 
			$b['DscRcgGlobal'][1]['TpoMov']	= 'D'; //D(descuento) o R(recargo)
			$b['DscRcgGlobal'][1]['TpoValor']= '$';//Indica si es Porcentaje o Monto �%� o �$�
			$b['DscRcgGlobal'][1]['ValorDR']	= $DescuentoMonto2;
			
			$tiene_descuento = 'S';
			//junta los arreglos en uno.
		}
		
		if($tiene_Folio == 'S' && $tiene_descuento == 'N'){
			 $resultado = array_merge($a,$ad,$c);
		}else if ($tiene_Folio == 'N' && $tiene_descuento == 'S'){
			//junta los arreglos en uno.
			$resultado = array_merge($a,$ad,$b);
		}else if ($tiene_Folio == 'S' && $tiene_descuento == 'S'){
			$resultado = array_merge($a,$ad,$b,$c);
		}else{
			$resultado = array_merge($a,$ad);
		}
		
		//se agrega el json_para codificacion requerida por libre_dte.
		$objEnJson = json_encode($resultado);
		
		//LLamo a la nueva clase dte.
		$dte = new dte();
		
		//Se le pasa como variable hash de la clase obtenida en parametros en la BD
		$SqlHash = "SELECT dbo.f_get_parametro(".self::K_PARAM_HASH.") K_HASH";  
		$Datos_Hash = $db->build_results($SqlHash);
		$dte->hash = $Datos_Hash[0]['K_HASH'];
		
		$Sql= "SELECT RESP_EMITIR_DTE
			    FROM FACTURA
				WHERE COD_FACTURA = $cod_factura";
		$result_emitir = $db->build_results($Sql);
		$RESP_EMITIR_DTE	= $result_emitir[0]['RESP_EMITIR_DTE'];

		if(trim($RESP_EMITIR_DTE) <> ''){
			$this->_load_record();
		    print " <script>alert('Error al Emitir Dte, el documento ya tiene un intento de env�o al SII.');</script>";
		    return;
		}
		
		$Sql= "select COD_LOG_CAMBIO
                from LOG_CAMBIO 
                where KEY_TABLA='$cod_factura'
                AND TIPO_CAMBIO='E'
                and NOM_TABLA='FACTURA'";
		$result_log = $db->build_results($Sql);
				
		if(count($result_log)>0){
		    $this->_load_record();
		    print " <script>alert('Error al Emitir Dte, el documento ya tiene un intento de env�o al SII.');</script>";
		    return;
		}else{
		    
		    $sp = 'sp_log_cambio';
		    $param="'FACTURA'
                ,'$cod_factura'
		        ,$this->cod_usuario
		        ,'E'";
		    
		    if (!$db->EXECUTE_SP($sp, $param)) {
				$this->_load_record();
			    print " <script>alert('Error al guardar Log Cambio.');</script>";
			    return;
		    }    
		}
		
		//envio json al la funcion de la clase dte.
		$response = $dte->post_emitir_dte($objEnJson);
		$response2 = str_replace("'", "''", $response);	// reemplaza ' por ''
		
		
		//Guarda el response de la funci�n emitir_dte.
		$sp = 'spu_factura';
		$param = "'SAVE_EMITIR_DTE' 
								,$cod_factura 	--@ve_cod_factura
                                ,NULL 			--@ve_cod_usuario_impresion
                                ,NULL 			--@ve_cod_usuario
                                ,NULL 			--@ve_nro_factura
                                ,NULL 			--@ve_fecha_factura
                                ,NULL 			--@ve_cod_estado_doc_sii
                                ,NULL 			--@ve_cod_empresa
                                ,NULL 			--@ve_cod_sucursal_factura
                                ,NULL 			--@ve_cod_persona
                                ,NULL 			--@ve_referencia
                                ,NULL 			--@ve_nro_orden_compra
                                ,NULL 			--@ve_fecha_orden_compra_cliente
                                ,NULL 			--@ve_obs
                                ,NULL 			--@ve_retirado_por
                                ,NULL 			--@ve_rut_retirado_por
                                ,NULL 			--@ve_dig_verif_retirado_por
                                ,NULL 			--@ve_guia_transporte
                                ,NULL 			--@ve_patente
                                ,NULL 			--@ve_cod_bodega
                                ,NULL 			--@ve_cod_tipo_factura
                                ,NULL 			--@ve_cod_doc
                                ,NULL 			--@ve_motivo_anula
                                ,NULL 			--@ve_cod_usuario_anula
                                ,NULL 			--@ve_cod_usuario_vendedor1
                                ,NULL 			--@ve_porc_vendedor1
                                ,NULL 			--@ve_cod_usuario_vendedor2
                                ,NULL 			--@ve_porc_vendedor2
                                ,NULL 			--@ve_cod_forma_pago
                                ,NULL 			--@ve_cod_origen_venta
                                ,NULL 			--@ve_subtotal
                                ,NULL 			--@ve_porc_dscto1
                                ,NULL 			--@ve_ingreso_usuario_dscto1
                                ,NULL 			--@ve_monto_dscto1
                                ,NULL 			--@ve_porc_dscto2
                                ,NULL 			--@ve_ingreso_usuario_dscto2
                                ,NULL 			--@ve_monto_dscto2
                                ,NULL 			--@ve_total_neto
                                ,NULL 			--@ve_porc_iva
                                ,NULL 			--@ve_monto_iva
                                ,NULL 			--@ve_total_con_iva
                                ,NULL 			--@ve_porc_factura_parcial
                                ,NULL 			--@ve_nom_forma_pago_otro
                                ,NULL 			--@ve_genera_salida
                                ,NULL 			--@ve_tipo_doc
                                ,NULL 			--@ve_cancelada
                                ,NULL 			--@ve_cod_centro_costo
                                ,NULL 			--@ve_cod_vendedor_sofland
                                ,NULL 			--@ve_ws_origen
                                ,NULL 			--@ve_xml_dte
                                ,NULL 			--@ve_track_id_dte
                                ,'$response2' 	--@ve_resp_emitir_dte respuesta del envio";
                       
		if (!$db->EXECUTE_SP($sp, $param)) {
			$this->_load_record();
			print " <script>alert('Error al emitir DTE.');</script>";
			return;
		}
		
		//Verificamos que realice bien el documento emitido.
		$rep_response = explode("200 OK", $response);
		
		if($rep_response[1] <> ''){
			
			//resuelve la cadena entrega
			$objEnJson_genera = $dte->respuesta_emitir_dte($response);
			
			//se envia al genera.
			$response_genera = $dte->post_genera_dte($objEnJson_genera);
			
			//Se consulta por el registro que hizo en log cambio
			$sql_detalle_dte = "select COD_LOG_CAMBIO
	                from LOG_CAMBIO 
	                where KEY_TABLA='$cod_factura'
	                AND TIPO_CAMBIO='E'
	                and NOM_TABLA='FACTURA'";
	        $result_detalle_dte = $db->build_results($sql_detalle_dte);
			$cod_log_cambio	= $result_detalle_dte[0]['COD_LOG_CAMBIO'];
			
			//se registra en BD la respuesta de libredTE, venga bien o mal
			if($cod_log_cambio <> ''){
				$sp = "sp_log_detalle_dte";
				$response_genera2 = str_replace("'", "''", $response_genera);	// reemplaza ' por ''
				$param = "$cod_log_cambio,'$response_genera2'";
				if (!$db->EXECUTE_SP($sp, $param)) {
					$this->_load_record();
					print " <script>alert('Error con sp_log_detalle_dte');</script>";
					return;
				}
			}
			else{
				$this->_load_record();
				print " <script>alert('No se encuentra Log Cambio del Envio Dte');</script>";
				return;
			}
			
			//se valida si vienen OK la respuesta de libreDte
			$pos = strpos($response_genera, "200 OK");
			if ($pos === false) {
				$this->_load_record();
				print " <script>alert('Error con generar al SII, revisar documento en IntegraDte.');</script>";
				return;
			}
			
			//resuelve cadena enviada desde el genera
			$respuesta_genera_dte = $dte->respuesta_genera_dte($response_genera);
			
			$nro_fa_dte		= $respuesta_genera_dte [6];
			$EnvioDTExml	= $respuesta_genera_dte [28];
			$track_id		= $respuesta_genera_dte [30];
				
			if (($nro_fa_dte <> '') && ($EnvioDTExml <> '')&& ($track_id <> '')){
				$cod_factura = $this->get_key();
				
				if($reenviar)
					$operacion = 'REENVIA_SAVE_DTE';
				else
					$operacion = 'SAVE_DTE';
				
				$sp = 'spu_factura';
				$param = "'$operacion'
								,$cod_factura
                                ,$this->cod_usuario
                                ,NULL 			--@ve_cod_usuario
                                ,$nro_fa_dte
                                ,NULL 			--@ve_fecha_factura
                                ,".self::K_ESTADO_SII_ENVIADA."
                                ,NULL 			--@ve_cod_empresa
                                ,NULL 			--@ve_cod_sucursal_factura
                                ,NULL 			--@ve_cod_persona
                                ,NULL 			--@ve_referencia
                                ,NULL 			--@ve_nro_orden_compra
                                ,NULL 			--@ve_fecha_orden_compra_cliente
                                ,NULL 			--@ve_obs
                                ,NULL 			--@ve_retirado_por
                                ,NULL 			--@ve_rut_retirado_por
                                ,NULL 			--@ve_dig_verif_retirado_por
                                ,NULL 			--@ve_guia_transporte
                                ,NULL 			--@ve_patente
                                ,NULL 			--@ve_cod_bodega
                                ,NULL 			--@ve_cod_tipo_factura
                                ,NULL 			--@ve_cod_doc
                                ,NULL 			--@ve_motivo_anula
                                ,NULL 			--@ve_cod_usuario_anula
                                ,NULL 			--@ve_cod_usuario_vendedor1
                                ,NULL 			--@ve_porc_vendedor1
                                ,NULL 			--@ve_cod_usuario_vendedor2
                                ,NULL 			--@ve_porc_vendedor2
                                ,NULL 			--@ve_cod_forma_pago
                                ,NULL 			--@ve_cod_origen_venta
                                ,NULL 			--@ve_subtotal
                                ,NULL 			--@ve_porc_dscto1
                                ,NULL 			--@ve_ingreso_usuario_dscto1
                                ,NULL 			--@ve_monto_dscto1
                                ,NULL 			--@ve_porc_dscto2
                                ,NULL 			--@ve_ingreso_usuario_dscto2
                                ,NULL 			--@ve_monto_dscto2
                                ,NULL 			--@ve_total_neto
                                ,NULL 			--@ve_porc_iva
                                ,NULL 			--@ve_monto_iva
                                ,NULL 			--@ve_total_con_iva
                                ,NULL 			--@ve_porc_factura_parcial
                                ,NULL 			--@ve_nom_forma_pago_otro
                                ,NULL 			--@ve_genera_salida
                                ,NULL 			--@ve_tipo_doc
                                ,NULL 			--@ve_cancelada
                                ,NULL 			--@ve_cod_centro_costo
                                ,NULL 			--@ve_cod_vendedor_sofland
                                ,NULL 			--@ve_ws_origen
                                ,'$EnvioDTExml'		--@ve_xml_dte
                                ,$track_id 			--@ve_track_id_dte";
					if (!$db->EXECUTE_SP($sp, $param)) {
						$this->_load_record();
					    print " <script>alert('Error al generar DTE.');</script>";
					    return;
				    }
					
					if(!$reenviar){
						$WS_ORIGEN			= $this->dws['dw_factura']->get_item(0, 'WS_ORIGEN');
						$NRO_ORDEN_COMPRA	= $this->dws['dw_factura']->get_item(0, 'NRO_ORDEN_COMPRA');
						
						$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
						
						$sql = "SELECT NRO_FACTURA
									  ,NRO_ORDEN_COMPRA
									  ,dbo.f_format_date(FECHA_FACTURA,1) FECHA_FACTURA
									  ,TOTAL_NETO
									  ,MONTO_IVA
									  ,TOTAL_CON_IVA
								FROM FACTURA
								WHERE COD_FACTURA = $cod_factura";
						$result_dte = $db->build_results($sql);		
						
						if($WS_ORIGEN == 'TODOINOX'){
							//Se valida las OC de las cuales se va a realizar esta nueva implementacion
							if($NRO_ORDEN_COMPRA > 23150)
								$sistema = 'TODOINOX';
							
						}else if($WS_ORIGEN == 'COMERCIAL'){
							//Se valida las OC de las cuales se va a realizar esta nueva implementacion
							if($NRO_ORDEN_COMPRA > 184160)
								$sistema = 'COMERCIAL';
							
						}if($WS_ORIGEN == 'RENTAL'){
							//Se valida las OC de las cuales se va a realizar esta nueva implementacion
							if($NRO_ORDEN_COMPRA > 66110)
								$sistema = 'RENTAL';	
						}
						
						if($sistema <> ''){
							$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
							$sql = "SELECT SISTEMA, URL_WS, USER_WS,PASSWROD_WS  FROM PARAMETRO_WS
									WHERE SISTEMA = '".$WS_ORIGEN."'";
							$result = $db->build_results($sql);
							
							$user_ws		= $result[0]['USER_WS'];
							$passwrod_ws	= $result[0]['PASSWROD_WS'];
							$url_ws			= $result[0]['URL_WS'];
							
							$biggi = new client_biggi("$user_ws", "$passwrod_ws", "$url_ws");
							$result_ws = $biggi->cli_add_faprov_bodega($result_dte, $sistema);
							
							if($result_ws == 'MSJ_REGISTRO')
								$this->alert('Ya hay un Registro en faprov bodega biggi');
							else if($result_ws == 'NO_REGISTRO_OC')
								$this->alert('No hay OC asociado a esta factura o factura no es para bodega biggi');
							else if($result_ws == 'NO_IGUAL')
								$this->alert('Tiene diferentes item, cantidades o productos');
							//else if($result_ws == 'HECHO')
								//$this->alert('registro guardado');
						}
					}	
					
					if($reenviar)
						$this->alert('Se ha reenviado exitosamente el DTE al SII');
					
					print " <script>window.open('../common_appl/print_dte.php?cod_documento=$cod_factura&DTE_ORIGEN=33&ES_CEDIBLE=N')</script>";
					$this->_load_record();
				
			}else{
				$this->_load_record();
				print " <script>alert('Error al Generar Dte contactarse con Integrasystem. $respuesta_genera_dte[0]');</script>";
			}	
		}else{
			//responde al dte consultado.
			$this->_load_record();
			print " <script>alert('Error al Emitir Dte contactarse con Integrasystem.');</script>";
		}
		$this->unlock_record();
	}
	
	function validate_delete($db) {
		$cod_factura = $this->get_key();
		
		$Sql= "select COD_LOG_CAMBIO
                from LOG_CAMBIO 
                where KEY_TABLA='$cod_factura'
                AND TIPO_CAMBIO='E'
                and NOM_TABLA='FACTURA'";
		$result_log = $db->build_results($Sql);
				
		if(count($result_log)>0){
		    return 'Error al Eliminar Dte, el documento ya tiene un intento de env�o al SII.';
		}
		else 
			return '';
	}
	
	function actualizar_estado_dte(){
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$cod_factura = $this->get_key();
		
		$sql = "SELECT F.NRO_FACTURA
              			,REPLACE(REPLACE(dbo.f_get_parametro(".self::K_PARAM_RUTEMISOR."),'.',''),'-7','') as RUTEMISOR
				FROM FACTURA F
				WHERE F.COD_FACTURA =$cod_factura";
		$consultar = $db->build_results($sql);
		
		$PORC_IVA = $this->dws['dw_factura']->get_item(0, 'PORC_IVA');
		if ($PORC_IVA==0)
			$tipodte = 34;
		else
			$tipodte = 33;
			
		$nro_factura	= $consultar[0]['NRO_FACTURA']; 
		$rutemisor		= $consultar[0]['RUTEMISOR'];
		
		//Llamamos a dte.
		$dte = new dte();
		
		//Se le pasa como variable hash de la clase obtenida en parametros en la BD
		$SqlHash = "SELECT dbo.f_get_parametro(".self::K_PARAM_HASH.") K_HASH";  
		$Datos_Hash = $db->build_results($SqlHash);
		$dte->hash = $Datos_Hash[0]['K_HASH'];
		
		//Llamamos al envio consultar estado de socumento.
		$response = $dte->actualizar_estado($tipodte,$nro_factura,$rutemisor);
		
		$actualizar_estado = $dte->respuesta_actualizar_estado($response);
		
		$revision_estado	= $actualizar_estado [9]; //respuesta de aceptado.
		if ($revision_estado == ''){
			$revision_estado	= $actualizar_estado [6]; //respuesta de rechazado.
		}
		//responde al dte consultado.
		$this->_load_record();
		print "<script>alert('Su documento electronico se encuentra en estado: $revision_estado');</script>";
	}
	
	function imprimir_dte($es_cedible, $desde_output=false){
		$cod_factura = $this->get_key();
		
		if($cod_factura > 12833)
			print " <script>window.open('../common_appl/print_dte.php?cod_documento=$cod_factura&DTE_ORIGEN=33&ES_CEDIBLE=$es_cedible')</script>";
		else{
			$nro_factura = $this->get_key_para_ruta_menu();
			$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
			$sql= "SELECT YEAR(FECHA_FACTURA) YEAR
				   FROM FACTURA
				   WHERE COD_FACTURA = $cod_factura";
			$result = $db->build_results($sql);
			$year = $result[0]['YEAR'];
			
			if(file_exists("../../../../PDF/PDFBIGGICHILE/$year/33_$nro_factura.pdf"))
				print " <script>window.open('../../../../PDF/PDFBIGGICHILE/$year/33_$nro_factura.pdf')</script>";
			else
				$this->alert('No se registra PDF del documento solicitado en respaldos Signature.');
		}	
			
		if(!$desde_output)
			$this->_load_record();
	}
	
	function reenviar_dte(){
		$this->enviar_dte(true);
	}
	
	function xml_dte(){
		$cod_factura = $this->get_key();
		$name_archivo = "XML_DTE_33_".$this->get_key_para_ruta_menu().".xml";
		
		$fname = tempnam("/tmp", $name_archivo);
		$handle = fopen($fname,"w");
		
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

		$sql= "SELECT XML_DTE
			   FROM FACTURA
			   WHERE COD_FACTURA = $cod_factura";
		$result = $db->build_results($sql);
		
		$XML_DTE = base64_decode($result[0]['XML_DTE']);
		
		fwrite($handle, $XML_DTE);				
		fwrite($handle, "\r\n");
		
		fclose($handle);
		
		header("Content-Type: application/force-download; name=\"$name_archivo\"");
		header("Content-Disposition: inline; filename=\"$name_archivo\"");
		$fh=fopen($fname, "rb");
		fpassthru($fh);
	}
	

	
	/*function envia_FA_electronica(){
		if (!$this->lock_record())
			return false;

		$COD_ESTADO_DOC_SII = $this->dws['dw_factura']->get_item(0, 'COD_ESTADO_DOC_SII');
		
		if($COD_ESTADO_DOC_SII == 1){//Emitida
			/////////// reclacula la FA porsiaca
			$parametros_sp = "'RECALCULA',$cod_factura";   
			$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
			$db->EXECUTE_SP('spu_factura', $parametros_sp);
            /////////
		}	
			
		$cod_factura = $this->get_key();	
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$count1= 0;
		
		$sql_valida="SELECT CANTIDAD 
			  		 FROM ITEM_FACTURA
			  		 WHERE COD_FACTURA = $cod_factura";
			  
		$result_valida = $db->build_results($sql_valida);

		for($i = 0 ; $i < count($result_valida) ; $i++){
			if($result_valida[$i] <> 0)
				$count1 = $count1 + 1;
		}
		if($count1 > 18){
			$this->_load_record();
			$this->alert('Se est� ingresando m�s item que la cantidad permitida, favor contacte a IntegraSystem.');
			return false;
		}	
			
		$this->sepa_decimales	= ',';	//Usar , como separador de decimales
		$this->vacio 			= ' ';	//Usar rellenos de blanco, CAMPO ALFANUMERICO
		$this->llena_cero		= 0;	//Usar rellenos con '0', CAMPO NUMERICO
		$this->separador		= ';';	//Usar ; como separador de campos
		$cod_usuario_impresion = $this->cod_usuario;
		$CMR = 9;
		$cod_impresora_dte = $_POST['wi_impresora_dte'];
		if($cod_impresora_dte == 100){
			$emisor_factura = 'SALA VENTA';
		}else{
			
		if ($cod_impresora_dte == '')
			$sql = "SELECT U.NOM_USUARIO EMISOR_FACTURA
					FROM USUARIO U, FACTURA F
					WHERE F.COD_FACTURA = $cod_factura
					  and U.COD_USUARIO = $cod_usuario_impresion";
		else
			$sql = "SELECT NOM_REGLA EMISOR_FACTURA
					FROM IMPRESORA_DTE
					WHERE COD_IMPRESORA_DTE = $cod_impresora_dte";
					
		$result = $db->build_results($sql);
		$emisor_factura = $result[0]['EMISOR_FACTURA'] ;
		}
		
		$db->BEGIN_TRANSACTION();
		$sp = 'spu_factura';
		$param = "'ENVIA_DTE', $cod_factura, $cod_usuario_impresion";

		if ($db->EXECUTE_SP($sp, $param)) {
			$db->COMMIT_TRANSACTION();
			
			$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
			//declrar constante para que el monto con iva del reporte lo transpforme a palabras
			$sql_total = "select TOTAL_CON_IVA from FACTURA where COD_FACTURA = $cod_factura";
			$resul_total = $db->build_results($sql_total);
			$total_con_iva = $resul_total[0]['TOTAL_CON_IVA'] ;
			$total_en_palabras =  Numbers_Words::toWords($total_con_iva,"es"); 
			$total_en_palabras = strtr($total_en_palabras, "�����", "aeiou");
			$total_en_palabras = strtoupper($total_en_palabras);
			
			$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
			$sql_dte = "SELECT	F.COD_FACTURA,
								F.NRO_FACTURA,
								F.TIPO_DOC,
								dbo.f_format_date(FECHA_FACTURA,1)FECHA_FACTURA,
								F.COD_USUARIO_IMPRESION,
								'$emisor_factura' EMISOR_FACTURA,
								F.NRO_ORDEN_COMPRA,
								dbo.f_fa_nros_guia_despacho(".$cod_factura.") NRO_GUIAS_DESPACHO,	
								F.REFERENCIA,
								F.NOM_EMPRESA,
								F.GIRO,
								F.RUT,
								F.DIG_VERIF,
								F.DIRECCION,
								dbo.f_emp_get_mail_cargo_persona(F.COD_PERSONA, '[EMAIL]') MAIL_CARGO_PERSONA,
								F.TELEFONO,
								F.FAX,
								F.COD_DOC,
								F.SUBTOTAL,
								F.PORC_DSCTO1,
								F.MONTO_DSCTO1,
								F.PORC_DSCTO2,
								F.MONTO_DSCTO2,
								F.MONTO_DSCTO1 + F.MONTO_DSCTO2 TOTAL_DSCTO,
								F.TOTAL_NETO,
								F.PORC_IVA,
								F.MONTO_IVA,
								F.TOTAL_CON_IVA,
								F.RETIRADO_POR,
								F.RUT_RETIRADO_POR,
								F.DIG_VERIF_RETIRADO_POR,
								COM.NOM_COMUNA,
								CIU.NOM_CIUDAD,
								FP.NOM_FORMA_PAGO,
								FP.COD_PAGO_DTE,
								F.NOM_FORMA_PAGO_OTRO,
								ITF.COD_ITEM_FACTURA,
								ITF.ORDEN,								
								ITF.ITEM,
								ITF.CANTIDAD,
								ITF.COD_PRODUCTO,
								ITF.NOM_PRODUCTO,
								ITF.PRECIO,
								ITF.PRECIO * ITF.CANTIDAD  TOTAL_FA,
								'".$total_en_palabras."' TOTAL_EN_PALABRAS,
								convert(varchar(5), GETDATE(), 8) HORA,
								F.GENERA_SALIDA,
								F.OBS,
								F.CANCELADA,
								F.WS_ORIGEN
						FROM 	FACTURA F left outer join COMUNA COM on F.COD_COMUNA = COM.COD_COMUNA,
								ITEM_FACTURA ITF, CIUDAD CIU, FORMA_PAGO FP 
						WHERE 	F.COD_FACTURA = ".$cod_factura." 
						AND	ITF.COD_FACTURA = F.COD_FACTURA
						AND	CIU.COD_CIUDAD = F.COD_CIUDAD
						AND	FP.COD_FORMA_PAGO = F.COD_FORMA_PAGO";
			$result_dte = $db->build_results($sql_dte);
			//CANTIDAD DE ITEM_FACTURA 
			$count = count($result_dte);
			
			// datos de factura
			$NRO_FACTURA		= $result_dte[0]['NRO_FACTURA'] ;		// 1 Numero Factura
			$FECHA_FACTURA		= $result_dte[0]['FECHA_FACTURA'] ;		// 2 Fecha Factura
			//Email - VE: =>En el caso de las Factura y otros documentos, no aplica por lo que se dejan 0;0 
			$TD					= $this->llena_cero;					// 3 Tipo Despacho
			$TT					= $this->llena_cero;					// 4 Tipo Traslado
			//Email - VE: => 
			$PAGO_DTE			= $result_dte[0]['COD_PAGO_DTE'];		// 5 Forma de Pago
			$FV					= $this->vacio;							// 6 Fecha Vencimiento
			$RUT				= $result_dte[0]['RUT'];				
			$DIG_VERIF			= $result_dte[0]['DIG_VERIF'];
			$RUT_EMPRESA		= $RUT.'-'.$DIG_VERIF;					// 7 Rut Empresa
			$NOM_EMPRESA		= $result_dte[0]['NOM_EMPRESA'] ;		// 8 Razol Social_Nombre Empresa
			$GIRO				= $result_dte[0]['GIRO'];				// 9 Giro Empresa
			$DIRECCION			= $result_dte[0]['DIRECCION'];			//10 Direccion empresa
			$MAIL_CARGO_PERSONA	= $result_dte[0]['MAIL_CARGO_PERSONA'];	//11 E-Mail Contacto
			$TELEFONO			= $result_dte[0]['TELEFONO'];			//12 Telefono Empresa
			$REFERENCIA			= $result_dte[0]['REFERENCIA'];			//12 Referencia de la Factura  //datos olvidado por VE.
			$NRO_GUIA_DESPACHO	= $result_dte[0]['NRO_GUIAS_DESPACHO'];	//Solicitado a VE por SP
			$GENERA_SALIDA		= $result_dte[0]['GENERA_SALIDA'];		//Solicitado a VE por SP "DESPACHADO"
			if ($GENERA_SALIDA == 'S'){
				$GENERA_SALIDA = 'DESPACHADO';
			}else{
				$GENERA_SALIDA = '';
			}
			$CANCELADA			= $result_dte[0]['CANCELADA'];			//Solicitado a VE por SP "CANCELADO"
			if ($CANCELADA == 'S'){
				$CANCELADA = 'CANCELADA';
			}else{
				$CANCELADA = '';
			}
			$SUBTOTAL			= number_format($result_dte[0]['SUBTOTAL'], 1, ',', '');	//Solicitado a VE por SP "SUBTOTAL"
			$PORC_DSCTO1		= number_format($result_dte[0]['PORC_DSCTO1'], 1, ',', '');	//Solicitado a VE por SP "PORC_DSCTO1"
			$PORC_DSCTO2		= number_format($result_dte[0]['PORC_DSCTO2'], 1, ',', '');	//Solicitado a VE por SP "PORC_DSCTO2"
			$EMISOR_FACTURA		= $result_dte[0]['EMISOR_FACTURA'];		//Solicitado a VE por SP "EMISOR_FACTURA"
			$NOM_COMUNA			= $result_dte[0]['NOM_COMUNA'];			//13 Comuna Recepcion
			$NOM_CIUDAD			= $result_dte[0]['NOM_CIUDAD'];			//14 Ciudad Recepcion
			$DP					= $result_dte[0]['DIRECCION'];			//15 Direcci�n Postal
			$COP				= $result_dte[0]['NOM_COMUNA'];			//16 Comuna Postal
			$CIP				= $result_dte[0]['NOM_CIUDAD'];			//17 Ciudad Postal
			
			//DATOS DE TOTALES number_format($result_dte[$i]['TOTAL_FA'], 0, ',', '.');
			$TOTAL_NETO			= number_format($result_dte[0]['TOTAL_NETO'], 1, ',', '');		//18 Monto Neto
			$PORC_IVA			= number_format($result_dte[0]['PORC_IVA'], 1, ',', '');		//19 Tasa IVA
			$MONTO_IVA			= number_format($result_dte[0]['MONTO_IVA'], 1, ',', '');		//20 Monto IVA
			$TOTAL_CON_IVA		= number_format($result_dte[0]['TOTAL_CON_IVA'], 1, ',', '');	//21 Monto Total
			$D1					= 'D1';															//22 Tipo de Mov 1 (Desc/Rec)
			$P1					= '$';															//23 Tipo de valor de Desc/Rec 1
			$MONTO_DSCTO1		= number_format($result_dte[0]['MONTO_DSCTO1'], 1, ',', '');	//24 Valor del Desc/Rec 1
			$D2					= 'D2';															//25 Tipo de Mov 2 (Desc/Rec)
			$P2					= '$';															//26 Tipo de valor de Desc/Rec 2
			$MONTO_DSCTO2		= number_format($result_dte[0]['MONTO_DSCTO2'], 1, ',', '');	//27 Valor del Desc/Rec 2
			$D3					= 'D3';															//28 Tipo de Mov 3 (Desc/Rec)
			$P3					= '$';															//29 Tipo de valor de Desc/Rec 3
			$MONTO_DSCTO3		= '';															//30 Valor del Desc/Rec 3
			$NOM_FORMA_PAGO		= $result_dte[0]['NOM_FORMA_PAGO'];								//Dato Especial forma de pago adicional
			$NRO_ORDEN_COMPRA	= $result_dte[0]['NRO_ORDEN_COMPRA'];							//Numero de Orden Pago
			$NRO_NOTA_VENTA		= $result_dte[0]['COD_DOC'];									//Numero de Nota Venta
			$OBSERVACIONES		= $result_dte[0]['OBS'];										//si la factura tiene notas u observaciones
			$OBSERVACIONES		=  eregi_replace("[\n|\r|\n\r]", ' ', $OBSERVACIONES); //elimina los saltos de linea. entre otros caracteres
			$TOTAL_EN_PALABRAS	= $result_dte[0]['TOTAL_EN_PALABRAS'];							//Total en palabras: Posterior al campo Notas

			//GENERA EL NOMBRE DEL ARCHIVO
			if($PORC_IVA != 0){
				$TIPO_FACT = 33;	//FACTURA AFECTA
			}else{
				$TIPO_FACT = 34;	//FACTURA EXENTA
			}

			//GENERA EL ALFANUMERICO ALETORIO Y LLENA LA VARIABLE $RES = ALETORIO
			$length = 36;
			$source = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
			$source .= '1234567890';
			
			if($length>0){
		        $RES = "";
		        $source = str_split($source,1);
		        for($i=1; $i<=$length; $i++){
		            mt_srand((double)microtime() * 1000000);
		            $num	= mt_rand(1,count($source));
		            $RES	.= $source[$num-1];
		        }
			 
		    }			
			
			//GENERA ESPACIOS EN BLANCO
			$space = ' ';
			$i = 0; 
			while($i<=100){
				$space .= ' ';
			$i++;
			}
			
			//GENERA ESPACIOS CON CEROS
			$llena_cero = 0;
			$i = 0; 
			while($i<=100){
				$llena_cero .= 0;
			$i++;
			}
			
			//Asignando espacios en blanco Factura
			//LINEA 3
			$NRO_FACTURA	= substr($NRO_FACTURA.$space, 0, 10);		// 1 Numero Factura
			$FECHA_FACTURA	= substr($FECHA_FACTURA.$space, 0, 10);		// 2 Fecha Factura
			$TD				= substr($TD.$space, 0, 1);					// 3 Tipo Despacho
			$TT				= substr($TT.$space, 0, 1);					// 4 Tipo Traslado
			$PAGO_DTE		= substr($PAGO_DTE.$space, 0, 1);			// 5 Forma de Pago
			$FV				= substr($FV.$space, 0, 10);				// 6 Fecha Vencimiento
			$RUT_EMPRESA	= substr($RUT_EMPRESA.$space, 0, 10);		// 7 Rut Empresa
			$NOM_EMPRESA	= substr($NOM_EMPRESA.$space, 0, 100);		// 8 Razol Social_Nombre Empresa
			$GIRO			= substr($GIRO.$space, 0, 40);				// 9 Giro Empresa
			$DIRECCION		= substr($DIRECCION.$space, 0, 60);			//10 Direccion empresa
			$MAIL_CARGO_PERSONA = substr($MAIL_CARGO_PERSONA.$space, 0, 60);//11 E-Mail Contacto
			$TELEFONO		= substr($TELEFONO.$space, 0, 15);			//12 Telefono Empresa
			$REFERENCIA		= substr($REFERENCIA.$space, 0, 80);
			$NRO_GUIA_DESPACHO	= substr($NRO_GUIA_DESPACHO.$space, 0, 20);//Solicitado a VE por SP
			$GENERA_SALIDA	= substr($GENERA_SALIDA.$space, 0, 30);		//DESPACHADO
			$CANCELADA		= substr($CANCELADA.$space, 0, 30);			//CANCELADO
			$SUBTOTAL		= substr($SUBTOTAL.$space, 0, 18);			//Solicitado a VE por SP "SUBTOTAL"
			$PORC_DSCTO1	= substr($PORC_DSCTO1.$space, 0, 5);		//Solicitado a VE por SP "PORC_DSCTO1"
			$PORC_DSCTO2	= substr($PORC_DSCTO2.$space, 0, 5);		//Solicitado a VE por SP "PORC_DSCTO2"
			$EMISOR_FACTURA	= substr($EMISOR_FACTURA.$space, 0, 50);	//Solicitado a VE por SP "EMISOR_FACTURA"
			//LINEA4
			$NOM_COMUNA		= substr($NOM_COMUNA.$space, 0, 20);		//13 Comuna Recepcion
			$NOM_CIUDAD		= substr($NOM_CIUDAD.$space, 0, 20);		//14 Ciudad Recepcion
			$DP				= substr($DP.$space, 0, 60);				//15 Direcci�n Postal
			$COP			= substr($COP.$space, 0, 20);				//16 Comuna Postal
			$CIP			= substr($CIP.$space, 0, 20);				//17 Ciudad Postal

			//Asignando espacios en blanco Totales de Factura
			$TOTAL_NETO		= substr($TOTAL_NETO.$space, 0, 18);		//18 Monto Neto
			$PORC_IVA		= substr($PORC_IVA.$space, 0, 5);			//19 Tasa IVA
			$MONTO_IVA		= substr($MONTO_IVA.$space, 0, 18);			//20 Monto IVA
			$TOTAL_CON_IVA	= substr($TOTAL_CON_IVA.$space, 0, 18);		//21 Monto Total
			$D1				= substr($D1.$space, 0, 1);					//22 Tipo de Mov 1 (Desc/Rec)
			$P1				= substr($P1.$space, 0, 1);					//23 Tipo de valor de Desc/Rec 1
			$MONTO_DSCTO1	= substr($MONTO_DSCTO1.$space, 0, 18);		//24 Valor del Desc/Rec 1
			$D2				= substr($D2.$space, 0, 1);					//25 Tipo de Mov 2 (Desc/Rec)
			$P2				= substr($P2.$space, 0, 1);					//26 Tipo de valor de Desc/Rec 2
			$MONTO_DSCTO2	= substr($MONTO_DSCTO2.$space, 0, 18);		//27 Valor del Desc/Rec 2
			$D3				= substr($D3.$space, 0, 1);					//28 Tipo de Mov 3 (Desc/Rec)
			$P3				= substr($P3.$space, 0, 1);					//29 Tipo de valor de Desc/Rec 3
			$MONTO_DSCTO3	= substr($MONTO_DSCTO3.$space, 0, 18);		//30 Valor del Desc/Rec 3
			$NOM_FORMA_PAGO = substr($NOM_FORMA_PAGO.$space, 0, 80);	//Dato Especial forma de pago adicional
			$NRO_ORDEN_COMPRA= substr($NRO_ORDEN_COMPRA.$space, 0, 20);	//Numero de Orden Pago
			$NRO_NOTA_VENTA = substr($NRO_NOTA_VENTA.$space, 0, 20);	//Numero de Nota Venta
			$OBSERVACIONES = substr($OBSERVACIONES.$space.$space.$space, 0, 250); //si la factura tiene notas u observaciones
			$TOTAL_EN_PALABRAS = substr($TOTAL_EN_PALABRAS.' PESOS.'.$space.$space, 0, 200);	//Total en palabras: Posterior al campo Notas
			
			$name_archivo = $TIPO_FACT."_NPG_".$RES.".SPF";
			$fname = tempnam("/tmp", $name_archivo);
			$handle = fopen($fname,"w");
			//DATOS DE FACTURA A EXPORTAR 
			//linea 1 y 2
			fwrite($handle, "\r\n"); //salto de linea
			fwrite($handle, "\r\n"); //salto de linea
			//linea 3		
			fwrite($handle, ' ');									// 0 space 2
			fwrite($handle, $NRO_FACTURA.$this->separador);			// 1 Numero Factura
			fwrite($handle, $FECHA_FACTURA.$this->separador);		// 2 Fecha Factura
			fwrite($handle, $TD.$this->separador);					// 3 Tipo Despacho
			fwrite($handle, $TT.$this->separador);					// 4 Tipo Traslado
			fwrite($handle, $PAGO_DTE.$this->separador);			// 5 Forma de Pago
			fwrite($handle, $FV.$this->separador);					// 6 Fecha Vencimiento
			fwrite($handle, $RUT_EMPRESA.$this->separador);			// 7 Rut Empresa
			fwrite($handle, $NOM_EMPRESA.$this->separador);			// 8 Razol Social_Nombre Empresa
			fwrite($handle, $GIRO.$this->separador);				// 9 Giro Empresa
			fwrite($handle, $DIRECCION.$this->separador);			//10 Direccion empresa
			//Personalizados Linea 3
			fwrite($handle, $MAIL_CARGO_PERSONA.$this->separador);	//11 E-Mail Contacto 
			fwrite($handle, $TELEFONO.$this->separador);			//12 Telefono Empresa
			fwrite($handle, $REFERENCIA.$this->separador);			//Referencia de la Factura
			fwrite($handle, $NRO_GUIA_DESPACHO.$this->separador);	//Solicitado a VE por SP
			fwrite($handle, $GENERA_SALIDA.$this->separador);		//DESPACHADO Solicitado a VE por SP
			fwrite($handle, $CANCELADA.$this->separador);			//CANCELADO Solicitado a VE por SP
			fwrite($handle, $SUBTOTAL.$this->separador);			//Solicitado a VE por SP "SUBTOTAL"
			fwrite($handle, $PORC_DSCTO1.$this->separador);			//Solicitado a VE por SP "PORC_DSCTO1"
			fwrite($handle, $PORC_DSCTO2.$this->separador);			//Solicitado a VE por SP "PORC_DSCTO2"
			fwrite($handle, $EMISOR_FACTURA.$this->separador);		//Solicitado a VE por SP "EMISOR_FACTURA"
			fwrite($handle, "\r\n"); //salto de linea
			
			//linea 4
			fwrite($handle, ' ');									// 0 space 2
			fwrite($handle, $NOM_COMUNA.$this->separador);			//13 Comuna Recepcion
			fwrite($handle, $NOM_CIUDAD.$this->separador);			//14 Ciudad Recepcion
			fwrite($handle, $DP.$this->separador);					//15 Direcci�n Postal
			fwrite($handle, $COP.$this->separador);					//16 Comuna Postal
			fwrite($handle, $CIP.$this->separador);					//17 Ciudad Postal
			fwrite($handle, $TOTAL_NETO.$this->separador);			//18 Monto Neto
			fwrite($handle, $PORC_IVA.$this->separador);			//19 Tasa IVA
			fwrite($handle, $MONTO_IVA.$this->separador);			//20 Monto IVA
			fwrite($handle, $TOTAL_CON_IVA.$this->separador);		//21 Monto Total
			fwrite($handle, $D1.$this->separador);					//22 Tipo de Mov 1 (Desc/Rec)
			fwrite($handle, $P1.$this->separador);					//23 Tipo de valor de Desc/Rec 1
			fwrite($handle, $MONTO_DSCTO1.$this->separador);		//24 Valor del Desc/Rec 1
			fwrite($handle, $D2.$this->separador);					//25 Tipo de Mov 2 (Desc/Rec)
			fwrite($handle, $P2.$this->separador);					//26 Tipo de valor de Desc/Rec 2
			fwrite($handle, $MONTO_DSCTO2.$this->separador);		//27 Valor del Desc/Rec 2
			fwrite($handle, $D3.$this->separador);					//28 Tipo de Mov 3 (Desc/Rec)
			fwrite($handle, $P3.$this->separador);					//29 Tipo de valor de Desc/Rec 3			
			fwrite($handle, $MONTO_DSCTO3.$this->separador);		//30 Valor del Desc/Rec 2
			fwrite($handle, $NOM_FORMA_PAGO.$this->separador);		//Dato Especial forma de pago adicional
			fwrite($handle, $NRO_ORDEN_COMPRA.$this->separador);	//Numero de Orden Pago
			fwrite($handle, $NRO_NOTA_VENTA.$this->separador);		//Numero de Nota Venta
			fwrite($handle, $OBSERVACIONES.$this->separador);		//si la factura tiene notas u observaciones
			fwrite($handle, $TOTAL_EN_PALABRAS.$this->separador);	//Total en palabras: Posterior al campo Notas
			fwrite($handle, "\r\n"); //salto de linea
			
			//datos de dw_item_factura linea 5 a 34
			for ($i = 0; $i < 30; $i++){
				if($i < $count){
					fwrite($handle, ' '); //0 space 2
					$ORDEN		= $result_dte[$i]['ORDEN'];	
					$MODELO		= $result_dte[$i]['COD_PRODUCTO'];
					$NOM_PRODUCTO = substr($result_dte[$i]['NOM_PRODUCTO'], 0, 60);
					$CANTIDAD	= number_format($result_dte[$i]['CANTIDAD'], 1, ',', '');
					$P_UNITARIO	= number_format($result_dte[$i]['PRECIO'], 1, ',', '');
					$TOTAL		= number_format($result_dte[$i]['TOTAL_FA'], 1, ',', '');
					$DESCRIPCION= $MODELO; // se repite el modelo
					$CANTIDAD_DETALLE = $CANTIDAD; // se repite el $CANTIDAD
					
					//Asignando espacios en blanco dw_item_factura
					$ORDEN = $ORDEN / 10; //ELIMINA EL CERO
					$ORDEN		= substr($ORDEN.$space, 0, 2);
					$MODELO		= substr($MODELO.$space, 0, 35);
					$NOM_PRODUCTO= substr($NOM_PRODUCTO.$space, 0, 80);
					$CANTIDAD	= substr($CANTIDAD.$space, 0, 18);
					$P_UNITARIO	= substr($P_UNITARIO.$space, 0, 18);
					$TOTAL		= substr($TOTAL.$space, 0, 18);
					$DESCRIPCION= substr($DESCRIPCION.$space, 0, 59);
					$CANTIDAD_DETALLE = substr($CANTIDAD_DETALLE.$space, 0, 18);

					//DATOS DE ITEM_FACTURA A EXPORTAR
					fwrite($handle, $ORDEN.$this->separador);		//31 N�mero de L�nea
					fwrite($handle, $MODELO.$this->separador);		//32 C�digo item
					fwrite($handle, $NOM_PRODUCTO.$this->separador);//33 Nombre del Item
					fwrite($handle, $CANTIDAD.$this->separador);	//34 Cantidad
					fwrite($handle, $P_UNITARIO.$this->separador);	//35 Precio Unitario
					fwrite($handle, $TOTAL.$this->separador);		//36 Valor por linea de detalle
					fwrite($handle, $DESCRIPCION.$this->separador);	//37 personalizados Zona Detalles(Modelo �tem)
					fwrite($handle, $CANTIDAD_DETALLE.$this->separador);	//personalizados Zona Detalles SE REPITE $CANTIDAD
				}
				fwrite($handle, "\r\n");
			}
			
			//LINEA 35 SOLICITU DE V ESPINOIZA FA MINERAS
			$sql_ref = "SELECT	 NRO_ORDEN_COMPRA
								,CONVERT(VARCHAR(10), FECHA_ORDEN_COMPRA_CLIENTE ,103) FECHA_OC
						FROM 	FACTURA 
						WHERE 	COD_FACTURA = $cod_factura";
			
			$result_ref = $db->build_results($sql_ref);
			$NRO_OC_FACTURA	= $result_ref[0]['NRO_ORDEN_COMPRA'];
			$FECHA_REF_OC	= $result_ref[0]['FECHA_OC'];
			
			//($a == $b) && ($c > $b)
			if(($NRO_OC_FACTURA == '') or ($FECHA_REF_OC == '')){
				//no existe OC en factura
				//Linea 36 a 44	Referencia
				$TDR	= $this->llena_cero;
				$FR		= $this->llena_cero;
				$FECHA_R= $this->vacio;
				$CR		= $this->llena_cero;
				$RER	= $this->vacio;
				
				//Asignando espacios en blanco Referencia
				$TDR	= substr($TDR.$space, 0, 3);
				$FR		= substr($FR.$space, 0, 18);
				$FECHA_R= substr($FECHA_R.$space, 0, 10);
				$CR		= substr($CR.$space, 0, 1);
				$RER	= substr($RER.$space, 0, 100);					
				
				fwrite($handle, ' '); //0 space 2
				fwrite($handle, $TDR.$this->separador);			//38 Tipo documento referencia
				fwrite($handle, $FR.$this->separador);			//39 Folio Referencia
				fwrite($handle, $FECHA_R.$this->separador);		//40 Fecha de Referencia
				fwrite($handle, $CR.$this->separador);			//41 C�digo de Referencia
				fwrite($handle, $RER.$this->separador);			//42 Raz�n expl�cita de la referencia
			}else{
				$TIPO_COD_REF		= '801';
				$NRO_OC_FACTURA		= $result_ref[0]['NRO_ORDEN_COMPRA'];	
				$FECHA_REF_OC		= $result_ref[0]['FECHA_OC'];
				$CR					= '1';
				$RAZON_REF_OC		= 'ORDEN DE COMPRA';
				
				$TIPO_COD_REF	= substr($TIPO_COD_REF.$space, 0, 3);
				$NRO_OC_FACTURA	= substr($NRO_OC_FACTURA.$space, 0, 18);
				$FECHA_REF_OC	= substr($FECHA_REF_OC.$space, 0, 10);
				$CR				= substr($CR.$space, 0, 1);
				$RAZON_REF_OC	= substr($RAZON_REF_OC.$space, 0, 100);
				
				fwrite($handle, ' '); //0 space 2
				fwrite($handle, $TIPO_COD_REF.$this->separador);			//TIPOCODREF. SOLI 
				fwrite($handle, $NRO_OC_FACTURA.$this->separador);			//FOLIOREF......Folio Referencia
				fwrite($handle, $FECHA_REF_OC.$this->separador);			//FECHA OC C�digo de Referencia
				fwrite($handle, $CR.$this->separador);						//41 C�digo de Referencia
				fwrite($handle, $RAZON_REF_OC.$this->separador);			//RAZON  KJNSK... Raz�n expl�cita de la referencia
			}
			fclose($handle);
			/*
			header("Content-Type: application/x-msexcel; name=\"$name_archivo\"");
			header("Content-Disposition: inline; filename=\"$name_archivo\"");
			$fh=fopen($fname, "rb");
			fpassthru($fh);*/
			/*
			$upload = $this->Envia_DTE($name_archivo, $fname);
			$NRO_FACTURA	= trim($NRO_FACTURA);
			if (!$upload) {
				$this->_load_record();
				$this->alert('No se pudo enviar Fatura Electronica N� '.$NRO_FACTURA.', Por favor contacte a IntegraSystem.');								
			}else{
				
				if ($PORC_IVA == 0){
					$this->_load_record();
					$this->alert('Gesti�n Realizada con ex�to. Factura Exenta Electronica N� '.$NRO_FACTURA.'.');
				}else{
					
					$this->_load_record();
					
					if($result_dte[0]['WS_ORIGEN'] == 'TODOINOX'){
						//Se valida las OC de las cuales se va a realizar esta nueva implementacion
						if($result_dte[0]['NRO_ORDEN_COMPRA'] > 23150)
							$sistema = 'TODOINOX';
						
					}else if($result_dte[0]['WS_ORIGEN'] == 'COMERCIAL'){
						//Se valida las OC de las cuales se va a realizar esta nueva implementacion
						if($result_dte[0]['NRO_ORDEN_COMPRA'] > 184160)
							$sistema = 'COMERCIAL';
						
					}if($result_dte[0]['WS_ORIGEN'] == 'RENTAL'){
						//Se valida las OC de las cuales se va a realizar esta nueva implementacion
						if($result_dte[0]['NRO_ORDEN_COMPRA'] > 66110)
							$sistema = 'RENTAL';
						
					}
					
					if($sistema <> ''){
						$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
						$sql = "SELECT SISTEMA, URL_WS, USER_WS,PASSWROD_WS  FROM PARAMETRO_WS
								WHERE SISTEMA = '".$result_dte[0]['WS_ORIGEN']."'";
						$result = $db->build_results($sql);
						
						$user_ws		= $result[0]['USER_WS'];
						$passwrod_ws	= $result[0]['PASSWROD_WS'];
						$url_ws			= $result[0]['URL_WS'];
						
						$biggi = new client_biggi("$user_ws", "$passwrod_ws", "$url_ws");
						$result_ws = $biggi->cli_add_faprov_bodega($result_dte, $sistema);
						/*
						if($result_ws == 'MSJ_REGISTRO')
							$this->alert('Ya hay un Registro en faprov bodega biggi');
						else if($result_ws == 'NO_REGISTRO_OC')
							$this->alert('No hay OC asociado a esta factura o factura no es para bodega biggi');
						else if($result_ws == 'NO_IGUAL')
							$this->alert('Tiene diferentes item, cantidades o productos');
						else if($result_ws == 'HECHO')
							$this->alert('registro guardado');	
							*//*
					}
					$this->alert('Gesti�n Realizada con ex�to. Factura Electronica N� '.$NRO_FACTURA.'.');
				}								
			}
			unlink($fname);
		}else{
			$db->ROLLBACK_TRANSACTION();
			return false;
		}
		$this->unlock_record();
	}*/

}
class print_factura extends print_factura_base {	
	function print_factura($sql, $xml, $labels=array(), $titulo, $con_logo, $vuelve_a_presentacion=false) {
		parent::print_factura_base($sql, $xml, $labels, $titulo, $con_logo, $vuelve_a_presentacion);
	}			
	///////////FACTURA CON IVA BODEGA BIGGI/////////////////////////////////////////
	function print_con_iva_fa_Bodega_Biggi(&$pdf, $x, $y) {
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$result = $db->build_results($this->sql);
		$count = count($result);
		
		$fecha = $result[0]['FECHA_FACTURA'];		
		// CABECERA		
		$cod_factura = $result[0]['COD_FACTURA'];		
		$nro_factura = $result[0]['NRO_FACTURA'];
		$nom_empresa = $result[0]['NOM_EMPRESA'];
		$rut = number_format($result[0]['RUT'], 0, ',', '.')."-".$result[0]['DIG_VERIF'];
		$oc = $result[0]['NRO_ORDEN_COMPRA'];
		$direccion = $result[0]['DIRECCION'];
		$comuna = $result[0]['NOM_COMUNA'];		
		$ciudad = $result[0]['NOM_CIUDAD'];		
		$giro = $result[0]['GIRO'];
		
		$fono = $result[0]['TELEFONO'];
		$total_en_palabras = $result[0]['TOTAL_EN_PALABRAS'];
		
		$subtotal = number_format($result[0]['SUBTOTAL'], 0, ',', '.');
		$porc_dscto1 = number_format($result[0]['PORC_DSCTO1'], 1, ',', '.');
		$monto_dscto1 = number_format($result[0]['MONTO_DSCTO1'], 0, ',', '.');
		$porc_dscto2 = number_format($result[0]['PORC_DSCTO2'], 1, ',', '.');
		$monto_dscto2 = number_format($result[0]['MONTO_DSCTO2'], 0, ',', '.');
		$total_dscto = number_format($result[0]['TOTAL_DSCTO'], 0, ',', '.');
		$neto = number_format($result[0]['TOTAL_NETO'], 0, ',', '.');
		$porc_iva = number_format($result[0]['PORC_IVA'], 1, ',', '.');
		$monto_iva = number_format($result[0]['MONTO_IVA'], 0, ',', '.');
		$total_con_iva = number_format($result[0]['TOTAL_CON_IVA'], 0, ',', '.');
		$guia_despacho = $result[0]['NRO_GUIAS_DESPACHO'];
		$cond_venta = $result[0]['NOM_FORMA_PAGO'];
		$cond_venta_otro = $result[0]['NOM_FORMA_PAGO_OTRO'];
		$retirado_por = $result[0]['RETIRADO_POR'];
		$GENERA_SALIDA	= $result[0]['GENERA_SALIDA'];
		if ($result[0]['REFERENCIA']=='')
			$REFERENCIA	= '';
		else
			$REFERENCIA	= 'REF: '.substr ($result[0]['REFERENCIA'], 0, 53);

		$sql = "select dbo.f_fa_NV_COMERCIAL(COD_FACTURA) COD_NOTA_VENTA
				from FACTURA
				where COD_FACTURA = $cod_factura";
		$result_NV = $db->build_results($sql);
		$COD_NV		= $result_NV[0]['COD_NOTA_VENTA'];	
		
		$OBS		= $result[0]['OBS'];
		$linea	= '______________________________';
		$CANCELADA	=	$result[0]['CANCELADA']; 

		$retirado_por_rut = number_format($result[0]['RUT_RETIRADO_POR'], 0, ',', '.');
		if ($retirado_por_rut == 0) {
			$retirado_por_rut = '';
		}else {
			$retirado_por_rut = number_format($result[0]['RUT_RETIRADO_POR'], 0, ',', '.')."-".$result[0]['DIG_VERIF_RETIRADO_POR'];
		}
				
		$retira_fecha = $result[0]['HORA'];
		if($cond_venta == 'OTRO')
			 $cond_venta = $cond_venta_otro;		
		
		if(strlen($cond_venta) > 30)
			$cond_venta = substr($cond_venta, 0, 30);

		// DIBUJANDO LA CABECERA	
		$pdf->SetFont('Arial','',11);		
		$pdf->Text($x-11, $y-4, $fecha);
		
		$pdf->SetFont('Arial','',8);		
		$pdf->Text($x+339, $y-40, $nro_factura);
		
		$pdf->SetFont('Arial','',11);
		$pdf->SetXY($x-16, $y+8);
		$pdf->SetFont('Arial','B',11);
		$pdf->MultiCell(250, 15,"$nom_empresa");
		
		$pdf->Text($x+350, $y+16, $rut);
		
		$pdf->SetFont('Arial','',11);
		$pdf->Text($x+350, $y+45, $oc);
		
		$pdf->SetXY($x-16, $y+65);
		$pdf->MultiCell(250,10,"$direccion");
		
		$pdf->SetFont('Arial','',10);
		$pdf->Text($x+350, $y+70, $comuna);
		
		$pdf->Text($x-29, $y+98, $ciudad);
		
		$pdf->SetXY($x+126, $y+81);
		$pdf->MultiCell(120, 8,"$giro", 0, 'L');
		
		$pdf->Text($x+350, $y+98, $fono);
		
		$pdf->Text($x+25, $y+115, $guia_despacho);
		
		$pdf->Text($x+375, $y+125, $cond_venta);	
					
		$pdf->SetFont('Arial','B',10);
		$pdf->Text($x, $y+170, "$REFERENCIA");
		
		$pdf->SetFont('Arial','',9);	
		//DIBUJANDO LOS ITEMS DE LA FACTURA	
		for($i=0; $i<$count; $i++){
			$item = $result[$i]['ITEM'];
			$cantidad = $result[$i]['CANTIDAD'];
			$modelo = $result[$i]['COD_PRODUCTO'];
			$detalle = substr ($result[$i]['NOM_PRODUCTO'], 0, 45);	
			$p_unitario = number_format($result[$i]['PRECIO'], 0, ',', '.');
			$total = number_format($result[$i]['TOTAL_FA'], 0, ',', '.');
			// por cada pasada le asigna una nueva posicion	
			$pdf->Text($x-61, $y+188+(15*$i), $item);			
			$pdf->Text($x-31, $y+188+(15*$i), $cantidad);
			$pdf->Text($x+3, $y+188+(15*$i), $modelo);			
			$pdf->SetXY($x+54, $y+185+(15*$i));
			$pdf->Cell(300, 0, "$detalle");
			$pdf->SetXY($x+310, $y+181+(15*$i));
			$pdf->MultiCell(80,7, $p_unitario,0, 'R');		
			$pdf->SetXY($x+390, $y+181+(15*$i));
			$pdf->MultiCell(80,7, $total,0, 'R');							
		}					
									
		// DIBUJANDO TOTALES
		$pdf->SetFont('Arial','',12);
		$pdf->SetXY($x+48,$y+455);
		$pdf->MultiCell(270,10,'Son: '.$total_en_palabras.' pesos.');
		
		if($total_dscto <> 0){//tiene dscto
			if(($monto_dscto1 <> 0 && $monto_dscto2 == 0) || ($monto_dscto2 <> 0 && $monto_dscto1 == 0)){//solo tiene un DSCTO 1
				$pdf->SetXY($x+346, $y+490);
				$pdf->SetFont('Arial','',9);
				$pdf->MultiCell(80,4, 'SUBTOTAL  $ ',0, 'R');
			
				$pdf->SetXY($x+378, $y+490);
				$pdf->SetFont('Arial','B',11);
				$pdf->MultiCell(105,4,$subtotal,0, 'R');

				if($monto_dscto1 <> 0){
					$pdf->SetXY($x+343, $y+505);
					$pdf->SetFont('Arial','',9);
					$pdf->MultiCell(80,4,$porc_dscto1.' % DSCTO  $ ',0, 'R');

					$pdf->SetXY($x+378, $y+505);
					$pdf->SetFont('Arial','B',11);
					$pdf->MultiCell(105,4,$monto_dscto1, 0, 'R');
				}
				else{
					$pdf->SetXY($x+333, $y+505);
					$pdf->SetFont('A4ial','',9);
					$pdf->MultiCell(80,4,$porc_dscto2.' % DSCTO: $ ',0, 'R');

					$pdf->SetXY($x+378, $y+505);
					$pdf->SetFont('Arial','B',11);
					$pdf->MultiCell(105,4,$monto_dscto2, 0, 'R');				
				}				
			}else if($monto_dscto1 <> 0 && $monto_dscto2 <> 0){//tiene ambos DSCTO

				$pdf->SetXY($x+346, $y+475);
				$pdf->SetFont('Arial','',9);
				$pdf->MultiCell(80,4, 'SUBTOTAL  $ ',0, 'R');
			
				$pdf->SetXY($x+378, $y+475);
				$pdf->SetFont('Arial','B',11);
				$pdf->MultiCell(105,4,$subtotal,0, 'R');

				$pdf->SetXY($x+340, $y+490);
				$pdf->SetFont('Arial','',9);
				$pdf->MultiCell(85,4,$porc_dscto1.' % DSCTO1 $ ',0, 'R');

				$pdf->SetXY($x+378, $y+490);
				$pdf->SetFont('Arial','B',11);
				$pdf->MultiCell(105,4,$monto_dscto1, 0, 'R');	
				
				$pdf->SetXY($x+346, $y+505);
				$pdf->SetFont('Arial','',9);
				$pdf->MultiCell(80,4,$porc_dscto2.' % DSCTO2 $ ',0, 'R');

				$pdf->SetXY($x+378, $y+505);
				$pdf->SetFont('Arial','B',11);
				$pdf->MultiCell(105,4,$monto_dscto2, 0, 'R');
			}
		}

		
		
		$pdf->SetXY($x+346, $y+520);
		$pdf->SetFont('Arial','',10);
		$pdf->MultiCell(80,4, 'TOTAL NETO $ ',0, 'R');
		$pdf->SetXY($x+378, $y+520);
		$pdf->SetFont('Arial','B',11);
		$pdf->MultiCell(105,4,$neto,0, 'R');
		$pdf->SetXY($x+346, $y+535);
		$pdf->SetFont('Arial','',9);
		$pdf->MultiCell(80,4, $porc_iva.' % IVA  $ ',0, 'R');
		$pdf->SetXY($x+378, $y+535);
		$pdf->SetFont('Arial','B',11);
		$pdf->MultiCell(105,4,$monto_iva,0, 'R');
		$pdf->Rect($x+360, $y+544, 120, 2, 'f');
		$pdf->SetXY($x+346, $y+555);
		$pdf->SetFont('Arial','',10);
		$pdf->MultiCell(80,4,'TOTAL  $ ',0, 'R');
		$pdf->SetXY($x+378, $y+555);
		$pdf->SetFont('Arial','B',11);
		$pdf->MultiCell(105,4,$total_con_iva,0, 'R');	


		//DIBUJANDO PERSONA QUE RETIRA PRODUCTOS 
		$pdf->SetFont('Arial','B',11);
		if ($GENERA_SALIDA == 'S'){
			$pdf->Rect($x-53, $y+510, 90, 15, 'f');
			$pdf->Text($x-47, $y+522, 'DESPACHADO');
		}	
		
		if ($CANCELADA == 'S'){
			$pdf->Rect($x-53, $y+550, 90, 14, 'f');
			$pdf->Text($x-47, $y+562, 'CANCELADA');
		}
		
		$pdf->SetFont('Arial','',13);
		$pdf->Text($x-52, $y+543, $COD_NV);
		
		$pdf->SetFont('Arial','',9);
		$pdf->SetXY($x-70, $y+481);
		$pdf->MultiCell(380, 8, "$OBS");
		
		$pdf->SetFont('Arial','',9);
		$pdf->Text($x+83, $y+488, $retirado_por);
		$pdf->Text($x+83, $y+508, $retirado_por_rut);
		$pdf->Text($x+249, $y+530, $retira_fecha);
	}
	
///////////FIN FACTURA CON IVA BODEGA BIGGI/////////////////////////////////////////
	function modifica_pdf(&$pdf){
		$pdf->AutoPageBreak=false;		
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$result = $db->build_results($this->sql);
		$porc_iva = $result[0]['PORC_IVA'];
		
		//USUARIOS
		$USUARIO_IMPRESION = $result[0]['USUARIO_IMPRESION'];
		$ADM = 1;

		//BODEGA BIGGI NO IMPRIME FA SIN IVA
		if($porc_iva != 0){
			if($USUARIO_IMPRESION == $ADM){ //Admin en Bodega Biggi
				$this->print_con_iva_fa_Bodega_Biggi($pdf, 85, 145);
			}else{//otros usuarios
				$this->print_con_iva_fa($pdf, 100, 145);
			}
		} else {
			if($USUARIO_IMPRESION == $ADM){ //Admin en Bodega Biggi
				$this->print_sin_iva_fa_Bodega_Biggi($pdf, 100, 145);
			}else{//otros usuarios
				$this->print_sin_iva_fa($pdf, 79, 155);
			}
		}
	}
}
?>