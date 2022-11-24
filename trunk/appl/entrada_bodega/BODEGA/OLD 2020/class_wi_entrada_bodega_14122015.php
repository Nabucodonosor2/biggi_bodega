<?php
require_once(dirname(__FILE__) . "/../../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../../common_appl/class_reporte_biggi.php");
require_once(dirname(__FILE__)."/../../ws_client_biggi/class_client_biggi.php");

class dw_item_entrada_bodega extends dw_item_entrada_bodega_base{
	function dw_item_entrada_bodega() {
		$sql = "SELECT	IEB.COD_ITEM_ENTRADA_BODEGA,
						IEB.COD_ITEM_ENTRADA_BODEGA COD_ITEM_ENTRADA_BODEGA_H,
						IEB.COD_ENTRADA_BODEGA,
						IEB.ORDEN,
						IEB.ITEM,
						IEB.COD_PRODUCTO,
						IEB.NOM_PRODUCTO,
						IEB.CANTIDAD,
						dbo.f_bodega_precio(IEB.COD_PRODUCTO, 2, getdate()) PRECIO,
						NULL CANTIDAD_MAX,
						NULL COD_ITEM_DOC
				FROM	ITEM_ENTRADA_BODEGA IEB
				WHERE 	IEB.COD_ENTRADA_BODEGA =  {KEY1}";
	
		parent::datawindow($sql, 'ITEM_ENTRADA_BODEGA', true, true);	
	
		$this->add_control(new edit_text('COD_ITEM_ENTRADA_BODEGA_H',10, 10, 'hidden'));
		$this->add_control(new edit_num('ORDEN',4, 10));
		$this->add_control(new edit_text('ITEM',4 , 5));
		$this->add_control($control = new edit_cantidad('CANTIDAD',12,10));
		$control->set_onChange("valida_cantidad_max(this);");
		
		$this->add_control(new edit_text('CANTIDAD_MAX',10, 10, 'hidden'));
		$this->add_control(new edit_text('COD_ITEM_DOC',10, 10, 'hidden'));
		
		$this->add_control(new computed('PRECIO', 0));
		$this->set_computed('TOTAL', '[CANTIDAD] * [PRECIO]');
		$this->accumulate('TOTAL');
		$this->add_controls_producto_help();		// Debe ir despues de los computed donde esta involucrado precio, porque add_controls_producto_help() afecta el campos PRECIO
		
		$this->set_first_focus('COD_PRODUCTO');
	
		// asigna los mandatorys
		$this->set_mandatory('ORDEN', 'Orden');
		$this->set_mandatory('COD_PRODUCTO', 'Código del producto');
		$this->set_mandatory('CANTIDAD', 'Cantidad');	
	}
	function insert_row($row=-1) {
		$row = parent::insert_row($row);
		$this->set_item($row, 'ORDEN', $this->row_count() * 10);
		$this->set_item($row, 'ITEM', $this->row_count());
		return $row;
	}
	function update($db, $cod_entrada_bodega)	{
		$sp = 'spu_item_entrada_bodega';

		for ($i = 0; $i < $this->row_count(); $i++){
			$statuts = $this->get_status_row($i);
			if ($statuts == K_ROW_NOT_MODIFIED || $statuts == K_ROW_NEW)
				continue;

			$cod_item_entrada_bodega= $this->get_item($i, 'COD_ITEM_ENTRADA_BODEGA_H');
			$orden 					= $this->get_item($i, 'ORDEN');
			$item 					= $this->get_item($i, 'ITEM');
			$cod_producto 			= $this->get_item($i, 'COD_PRODUCTO');
			$nom_producto 			= $this->get_item($i, 'NOM_PRODUCTO');
			$cantidad 				= $this->get_item($i, 'CANTIDAD');
			$precio 				= $this->get_item($i, 'PRECIO');
			$cod_item_doc			= $this->get_item($i, 'COD_ITEM_DOC');

			$cod_item_entrada_bodega = ($cod_item_entrada_bodega=='') ? "null" : $cod_item_entrada_bodega;
			$cod_item_doc			= ($cod_item_doc == '') ? "null" : $cod_item_doc;
			if ($statuts == K_ROW_NEW_MODIFIED)
				$operacion = 'INSERT';
			elseif ($statuts == K_ROW_MODIFIED)
				$operacion = 'UPDATE';

			$param = "'$operacion'
						,$cod_item_entrada_bodega
						,$cod_entrada_bodega
						,$orden
						,'$item'
						,'$cod_producto'
						,'$nom_producto'
						,$cantidad
						,$precio
						,$cod_item_doc";
						
					
			if (!$db->EXECUTE_SP($sp, $param))
				return false;
		}
		for ($i = 0; $i < $this->row_count('delete'); $i++) {
			$statuts = $this->get_status_row($i, 'delete');
			if ($statuts == K_ROW_NEW || $statuts == K_ROW_NEW_MODIFIED)
				continue;
				
			$cod_item_entrada_bodega = $this->get_item($i, 'COD_ITEM_ENTRADA_BODEGA', 'delete');
			if (!$db->EXECUTE_SP($sp, "'DELETE', $cod_item_entrada_bodega")){
				return false;
			}
		}
		//Ordernar
		if ($this->row_count() > 0) {
			$parametros_sp = "'ITEM_ENTRADA_BODEGA','ENTRADA_BODEGA', $cod_entrada_bodega";
			if (!$db->EXECUTE_SP('sp_orden_no_parametricas', $parametros_sp))
				return false;
		}
		return true;
	}
}

class wi_entrada_bodega extends wi_entrada_bodega_base{
	const K_HABILITA_CREAR_DESDE = '993015';
	function wi_entrada_bodega($cod_item_menu) {
		parent::wi_entrada_bodega_base($cod_item_menu);
	}
	
	function new_record() {
		parent::new_record();
		$this->dws['dw_entrada_bodega']->set_item(0, 'TIPO_DOC', 'AJUSTE');
		
		unset($this->dws['dw_entrada_bodega']->controls['COD_BODEGA']);
		
		$sql = "select COD_BODEGA
						,NOM_BODEGA
				from BODEGA
				where COD_BODEGA = 2";
		$this->dws['dw_entrada_bodega']->add_control(new drop_down_dw('COD_BODEGA', $sql));
		
		if (session::is_set("ENTRADA_CREADA_DESDE")) {
			$valor_devuelto = session::get("ENTRADA_CREADA_DESDE");
			session::un_set("ENTRADA_CREADA_DESDE");
			
			$this->crear_desde_oc($valor_devuelto);
		}
		
	}
	function load_record() {
		parent::load_record();
		$COD_ENTRADA_BODEGA = $this->get_item_wo($this->current_record, 'COD_ENTRADA_BODEGA');
		$this->dws['dw_entrada_bodega']->retrieve($COD_ENTRADA_BODEGA);	
		$this->dws['dw_item_entrada_bodega']->retrieve($COD_ENTRADA_BODEGA);
		
		
		$priv = $this->get_privilegio_opcion_usuario(self::K_HABILITA_CREAR_DESDE, $this->cod_usuario);
		if ($priv=='E'){
			$this->b_create_visible = true;
		}else{
			$this->b_create_visible = false;
		}
		
		$this->b_delete_visible  = false;
		$this->b_save_visible 	 = false;
		//$this->b_no_save_visible = false;
		$this->b_modify_visible	 = false;
		$this->b_print_visible	 = true;
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$cod_orden_compra = $this->dws['dw_entrada_bodega']->get_item(0, 'COD_DOC');
		$sql_emp = "SELECT COD_EMPRESA
						  ,COD_ORDEN_COMPRA
					FROM ORDEN_COMPRA
					WHERE COD_ORDEN_COMPRA = $cod_orden_compra";
		$result_emp = $db->build_results($sql_emp);
				
		if($result_emp[0]['COD_EMPRESA'] == 4 && $result_emp[0]['COD_ORDEN_COMPRA'] > 57130){
			$this->dws['dw_entrada_bodega']->set_entrable('CANTIDAD', false);
		}else if($result_emp[0]['COD_EMPRESA'] == 5 && $result_emp[0]['COD_ORDEN_COMPRA'] > 0){
			$this->dws['dw_entrada_bodega']->set_entrable('CANTIDAD', false);
		}else{
			$this->dws['dw_entrada_bodega']->set_entrable('CANTIDAD', true);
		}
	}
	
	function crear_desde_oc($valor_devuelto) {
		list($cod_orden_compra, $nro_fa_proveedor, $fecha_fa_proveedor, $tipo_fa_proveedor)=split('[|]', $valor_devuelto);
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$this->dws['dw_entrada_bodega']->set_item(0, 'COD_DOC', $cod_orden_compra);
		
		$sql = "SELECT SC.COD_PRODUCTO
					,SC.TERMINADO_COMPUESTO
					,OC.REFERENCIA
					,P.NOM_PRODUCTO
				FROM ORDEN_COMPRA OC, SOLICITUD_COMPRA SC, PRODUCTO P
				WHERE COD_ORDEN_COMPRA = $cod_orden_compra
					AND SC.COD_SOLICITUD_COMPRA = OC.COD_DOC
					AND OC.TIPO_ORDEN_COMPRA = 'SOLICITUD_COMPRA'
					AND P.COD_PRODUCTO = SC.COD_PRODUCTO";

		$result = $db->build_results($sql);
		$cod_producto_solicitado = $result[0]['COD_PRODUCTO'];
		$nom_producto_solicitado = $result[0]['NOM_PRODUCTO'];		
		$terminado_compuesto  = $result[0]['TERMINADO_COMPUESTO'];
		$referencia  = $result[0]['REFERENCIA'];
		
		$arma_compuesto = 'N';
		if ($terminado_compuesto == 'C'){
			$sql ="SELECT	 ITS.ARMA_COMPUESTO
				FROM ITEM_ORDEN_COMPRA IT, ITEM_SOLICITUD_COMPRA ITS
				WHERE IT.COD_ORDEN_COMPRA = $cod_orden_compra
					AND ITS.COD_ITEM_SOLICITUD_COMPRA = IT.COD_ITEM_DOC";
			$result = $db->build_results($sql);
			
			$arma_compuesto = $result[0]['ARMA_COMPUESTO'];
			if($arma_compuesto == 'S')
				$cod_bodega = 2; //terminado
			else {
				// debe buscar la bodega del proveedor
				$sql = "SELECT ITS.COD_EMPRESA
						FROM ORDEN_COMPRA O, SOLICITUD_COMPRA S, ITEM_SOLICITUD_COMPRA ITS
						WHERE O.COD_ORDEN_COMPRA = $cod_orden_compra
							AND S.COD_SOLICITUD_COMPRA = O.COD_DOC
							AND ITS.COD_SOLICITUD_COMPRA = S.COD_SOLICITUD_COMPRA 
							AND ITS.ARMA_COMPUESTO = 'S'";
				$result = $db->build_results($sql);
				$cod_empresa = $result[0]['COD_EMPRESA'];			
				
				$sql = "SELECT	 ISNULL(COD_BODEGA, 0) COD_BODEGA
								,ALIAS
						FROM EMPRESA
						WHERE COD_EMPRESA = $cod_empresa";
				$result = $db->build_results($sql);
				$cod_bodega = $result[0]['COD_BODEGA'];
				$alias_empresa = $result[0]['ALIAS'];
				if($cod_bodega == 0){
					$sp = 'spu_bodega';
					$operacion = 'INSERT';
			    	$param	= "'$operacion' ,NULL , 'BODEGA_$alias_empresa', 1";
			    	
			    	if (!$db->EXECUTE_SP($sp, $param))
						return false;
					
					$cod_bodega = $db->GET_IDENTITY("bodega");
					$sp = 'spu_bodega';
					$operacion = 'UPDATE_EMPRESA';
			    	$param	= "'$operacion' ,$cod_bodega , NULL, $cod_empresa";
					if (!$db->EXECUTE_SP($sp, $param))
						return false;
				}
			}
		}	
		else
			$cod_bodega = 2; //terminado
		$this->dws['dw_entrada_bodega']->set_item(0, 'TIPO_DOC', 'ORDEN_COMPRA');
		$this->dws['dw_entrada_bodega']->set_item(0, 'COD_BODEGA', $cod_bodega);
		$this->dws['dw_entrada_bodega']->set_entrable('COD_BODEGA', false);
		$this->dws['dw_entrada_bodega']->set_item(0, 'REFERENCIA', $referencia);
		unset($this->dws['dw_entrada_bodega']->controls['NRO_FA_PROVEEDOR']);
		$this->dws['dw_entrada_bodega']->add_control(new static_text('NRO_FA_PROVEEDOR'));
		unset($this->dws['dw_entrada_bodega']->controls['FECHA_FA_PROVEEDOR']);
		$this->dws['dw_entrada_bodega']->add_control(new static_text('FECHA_FA_PROVEEDOR'));
		$this->dws['dw_entrada_bodega']->set_item(0, 'NRO_FA_PROVEEDOR', $nro_fa_proveedor);
		$this->dws['dw_entrada_bodega']->set_item(0, 'FECHA_FA_PROVEEDOR', $fecha_fa_proveedor);
		
		session::set('TIPO_FA_PROVEEDOR', $tipo_fa_proveedor);
		
		unset($this->dws['dw_item_entrada_bodega']->controls['ORDEN']);
		$this->dws['dw_item_entrada_bodega']->add_control(new static_num('ORDEN'));
		
		unset($this->dws['dw_item_entrada_bodega']->controls['ITEM']);
		$this->dws['dw_item_entrada_bodega']->add_control(new static_text('ITEM'));
		
		unset($this->dws['dw_item_entrada_bodega']->controls['COD_PRODUCTO']);
		$this->dws['dw_item_entrada_bodega']->add_control(new static_text('COD_PRODUCTO'));
		
		unset($this->dws['dw_item_entrada_bodega']->controls['NOM_PRODUCTO']);
		$this->dws['dw_item_entrada_bodega']->add_control(new static_text('NOM_PRODUCTO'));

		unset($this->dws['dw_item_entrada_bodega']->controls['PRECIO']);
		$this->dws['dw_item_entrada_bodega']->add_control(new static_num('PRECIO'));
				
		$sql = "SELECT SUM(CANTIDAD_UNITARIA * PRECIO_COMPRA) PRECIO_SOLICITADO
		FROM ITEM_SOLICITUD_COMPRA ITS, ORDEN_COMPRA OC 
		WHERE OC.COD_ORDEN_COMPRA = $cod_orden_compra
		AND OC.COD_DOC = ITS.COD_SOLICITUD_COMPRA";

		$result = $db->build_results($sql);
		$precio_producto_solicitado = $result[0]['PRECIO_SOLICITADO'];
		
		$sql="SELECT ITEM, 
				COD_PRODUCTO, 
				NOM_PRODUCTO, 
				dbo.f_oc_por_llegar(COD_ITEM_ORDEN_COMPRA) CANTIDAD,
				PRECIO,
				ROUND(dbo.f_oc_por_llegar(COD_ITEM_ORDEN_COMPRA) * PRECIO, 0) TOTAL,
				COD_ITEM_ORDEN_COMPRA
			FROM ITEM_ORDEN_COMPRA
			WHERE COD_ORDEN_COMPRA = $cod_orden_compra";
		
		$result = $db->build_results($sql);
		$sum_total = 0;
		for ($i=0; $i<count($result); $i++) {
			$orden = (10 * $i) + 10;
			$row = $this->dws['dw_item_entrada_bodega']->insert_row();
			$this->dws['dw_item_entrada_bodega']->set_item($row, 'ORDEN', $orden);
			$this->dws['dw_item_entrada_bodega']->set_item($row, 'COD_ITEM_DOC', $result[$i]['COD_ITEM_ORDEN_COMPRA']);
			$this->dws['dw_item_entrada_bodega']->set_item($row, 'ITEM', $result[$i]['ITEM']);
			$this->dws['dw_item_entrada_bodega']->set_item($row, 'COD_PRODUCTO', $result[$i]['COD_PRODUCTO']);
			$this->dws['dw_item_entrada_bodega']->set_item($row, 'NOM_PRODUCTO', $result[$i]['NOM_PRODUCTO']);
			$this->dws['dw_item_entrada_bodega']->set_item($row, 'CANTIDAD', $result[$i]['CANTIDAD']);
			$this->dws['dw_item_entrada_bodega']->set_item($row, 'CANTIDAD_MAX', $result[$i]['CANTIDAD']);
			$this->dws['dw_item_entrada_bodega']->set_item($row, 'PRECIO', $result[$i]['PRECIO']);
			$this->dws['dw_item_entrada_bodega']->set_item($row, 'TOTAL', $result[$i]['TOTAL']);
			$sum_total = $sum_total + $result[$i]['TOTAL'];
			
			if ($terminado_compuesto == 'C' && $arma_compuesto == 'S') {
				// se debe ingresar el equipo terminado
				
				$this->dws['dw_item_entrada_bodega']->set_item($row, 'COD_PRODUCTO', $cod_producto_solicitado);
				$this->dws['dw_item_entrada_bodega']->set_item($row, 'NOM_PRODUCTO', $nom_producto_solicitado);
				$this->dws['dw_item_entrada_bodega']->set_item($row, 'PRECIO', $precio_producto_solicitado);
				$this->dws['dw_item_entrada_bodega']->set_item($row, 'TOTAL', ($result[$i]['CANTIDAD'] * $precio_producto_solicitado));
				$sum_total = $sum_total - $result[$i]['TOTAL'] + ($result[$i]['CANTIDAD'] * $precio_producto_solicitado);
				break;
			}
		}
		
		$this->dws['dw_item_entrada_bodega']->set_item(0, 'SUM_TOTAL', $sum_total);
		
		$sql_emp = "SELECT COD_EMPRESA
						  ,COD_ORDEN_COMPRA
					FROM ORDEN_COMPRA
					WHERE COD_ORDEN_COMPRA = $cod_orden_compra";
		$result_emp = $db->build_results($sql_emp);
		
		if($result_emp[0]['COD_EMPRESA'] == 4 && $result_emp[0]['COD_ORDEN_COMPRA'] > 57130){
			$sql = "select SISTEMA, URL_WS, USER_WS,PASSWROD_WS  from PARAMETRO_WS
					where SISTEMA = 'TODOINOX' ";
			$result = $db->build_results($sql);
			
			$user_ws		= $result[0]['USER_WS'];
			$passwrod_ws	= $result[0]['PASSWROD_WS'];
			$url_ws			= $result[0]['URL_WS'];
			
			$biggi = new client_biggi("$user_ws", "$passwrod_ws", "$url_ws");
			$result = $biggi->cli_entrada_bodega($cod_orden_compra, $nro_fa_proveedor);
			
			$new_fecha	= $result['FACTURA'][0]['FECHA_FACTURA'];
			$cantidad	= $result['FACTURA'][0]['CANTIDAD'];
			
			if($result <> 'NO_COINCIDE'){
				$this->dws['dw_entrada_bodega']->set_item(0, 'FECHA_FA_PROVEEDOR', $new_fecha);
				$this->dws['dw_item_entrada_bodega']->set_item(0, 'CANTIDAD', $cantidad);
				$total = $cantidad * $precio_producto_solicitado;
				$this->dws['dw_item_entrada_bodega']->set_item($row, 'TOTAL', $total);
				$this->dws['dw_item_entrada_bodega']->set_item(0, 'SUM_TOTAL', $total);
			}

			$this->dws['dw_item_entrada_bodega']->set_entrable('CANTIDAD', false);
		
		}else if($result_emp[0]['COD_EMPRESA'] == 5 && $result_emp[0]['COD_ORDEN_COMPRA'] > 57733){
			$sql = "select SISTEMA, URL_WS, USER_WS,PASSWROD_WS  from PARAMETRO_WS
					where SISTEMA = 'SERVINDUS' ";
			$result = $db->build_results($sql);

			$user_ws		= $result[0]['USER_WS'];
			$passwrod_ws	= $result[0]['PASSWROD_WS'];
			$url_ws			= $result[0]['URL_WS'];
			
			$biggi = new client_biggi("$user_ws", "$passwrod_ws", "$url_ws");
			$result = $biggi->cli_entrada_bodega_serv($cod_orden_compra, $nro_fa_proveedor);
			
			$new_fecha	= $result['FACTURA'][0]['FECHA_FACTURA'];
			$cantidad	= $result['FACTURA'][0]['CANTIDAD'];
			
			if($result <> 'NO_COINCIDE'){
				$this->dws['dw_entrada_bodega']->set_item(0, 'FECHA_FA_PROVEEDOR', $new_fecha);
				$this->dws['dw_item_entrada_bodega']->set_item(0, 'CANTIDAD', $cantidad);
				$total = $cantidad * $precio_producto_solicitado;
				$this->dws['dw_item_entrada_bodega']->set_item($row, 'TOTAL', $total);
				$this->dws['dw_item_entrada_bodega']->set_item(0, 'SUM_TOTAL', $total);
			}

			$this->dws['dw_item_entrada_bodega']->set_entrable('CANTIDAD', false);
		}else
			$this->dws['dw_item_entrada_bodega']->set_entrable('CANTIDAD', true);
		
	}
	function save_record($db) {
		$cod_entrada_bodega = $this->get_key();
		$cod_bodega = $this->dws['dw_entrada_bodega']->get_item(0, 'COD_BODEGA');
		$tipo_doc = $this->dws['dw_entrada_bodega']->get_item(0, 'TIPO_DOC');
		$cod_doc= $this->dws['dw_entrada_bodega']->get_item(0, 'COD_DOC');
		$referencia = $this->dws['dw_entrada_bodega']->get_item(0, 'REFERENCIA');
		$obs = $this->dws['dw_entrada_bodega']->get_item(0, 'OBS');
		$nro_fa_proveedor = $this->dws['dw_entrada_bodega']->get_item(0, 'NRO_FA_PROVEEDOR');
		$fecha_fa_proveedor = $this->dws['dw_entrada_bodega']->get_item(0, 'FECHA_FA_PROVEEDOR');
		$tipo_fa_proveedor = 'NULL';
		
		$cod_entrada_bodega = ($cod_entrada_bodega=='') ? 'NULL' : $cod_entrada_bodega;
		$tipo_doc			= ($tipo_doc	== '') ? 'NULL' : $tipo_doc;
		$cod_doc			= ($cod_doc	==	'')?'NULL' : $cod_doc;
		$nro_fa_proveedor	= ($nro_fa_proveedor == '') ? 'NULL' : $nro_fa_proveedor;
		//$fecha_fa_proveedor	= ($fecha_fa_proveedor == '') ? 'NULL' : $fecha_fa_proveedor;
		
		
		$sp = 'spu_entrada_bodega';
	    if ($this->is_new_record())
	    	$operacion = 'INSERT';
	    else
	    	$operacion = 'UPDATE';
	    		    
	    $param	= "'$operacion'
	    			,$cod_entrada_bodega
	    			,$this->cod_usuario
	    			,$cod_bodega
	    			,'$tipo_doc'
	    			,$cod_doc
	    			,'$referencia'
	    			,'$obs'
	    			,$nro_fa_proveedor
	    			,'$fecha_fa_proveedor'
	    			,$tipo_fa_proveedor";
	    			
		
		if ($db->EXECUTE_SP($sp, $param)){
			if ($this->is_new_record()) {
				$cod_entrada_bodega = $db->GET_IDENTITY();
				$this->dws['dw_entrada_bodega']->set_item(0, 'COD_ENTRADA_BODEGA', $cod_entrada_bodega);
			}
			
			if (!$this->dws['dw_item_entrada_bodega']->update($db, $cod_entrada_bodega))
				return false;
			if ($tipo_doc != 'AJUSTE'){
				$sql_emp = "SELECT COD_EMPRESA
								  ,COD_ORDEN_COMPRA
							FROM ORDEN_COMPRA
							WHERE COD_ORDEN_COMPRA = $cod_doc";
				$result_emp = $db->build_results($sql_emp);
				
				if($result_emp[0]['COD_EMPRESA'] <> 4 && $result_emp[0]['COD_EMPRESA'] <> 5){//4: Todoinox	5:Servindus
					//se crea Factura proveedor 
					$tipo_fa_proveedor = session::get("TIPO_FA_PROVEEDOR");
					session::un_set("TIPO_FA_PROVEEDOR");
					$tipo_fa_proveedor = ($tipo_fa_proveedor=='') ? 'NULL' : $tipo_fa_proveedor;
		
					$sp = 'spu_entrada_bodega';
					$operacion = 'FAPROV';
			    	$param	= "'$operacion' ,$cod_entrada_bodega ,NULL ,NULL,NULL,NULL
			    				,NULL ,NULL ,NULL ,NULL,$tipo_fa_proveedor";
		    	
			    	if (!$db->EXECUTE_SP($sp, $param))
						return false;
				}else{
					if($result_emp[0]['COD_EMPRESA'] == 4)
						$lim_oc = 57130;
					else	
						$lim_oc = 57733;
					
					if($result_emp[0]['COD_ORDEN_COMPRA'] < $lim_oc){
						//se crea Factura proveedor 
						$tipo_fa_proveedor = session::get("TIPO_FA_PROVEEDOR");
						session::un_set("TIPO_FA_PROVEEDOR");
						$tipo_fa_proveedor = ($tipo_fa_proveedor=='') ? 'NULL' : $tipo_fa_proveedor;
			
						$sp = 'spu_entrada_bodega';
						$operacion = 'FAPROV';
				    	$param	= "'$operacion' ,$cod_entrada_bodega ,NULL ,NULL,NULL,NULL
				    				,NULL ,NULL ,NULL ,NULL,$tipo_fa_proveedor";
			    	
				    	if (!$db->EXECUTE_SP($sp, $param))
							return false;
						}
				}
			}
			return true;
		}
		return false;		
				
	}
	
	function print_record(){
		$cod_entrada = $this->get_key();
		$sql = "exec spi_entrada_bodega $cod_entrada";
		// reporte
		$labels = array();
		$labels['strCOD_ENTRADA'] = $cod_entrada;					
		$file_name = $this->find_file('entrada_bodega/BODEGA', 'entrada_bodega.xml');					
		$rpt = new print_entrada_bodega($sql, $file_name, $labels, "Entrada Bodega".$cod_entrada, 1);
		$this->_load_record();
		return true;
	}	
}

class print_entrada_bodega extends reporte_biggi {	
	function print_entrada_bodega($sql, $xml, $labels=array(), $titulo, $con_logo, $vuelve_a_presentacion=false) {
		parent::reporte($sql, $xml, $labels, $titulo, $con_logo, $vuelve_a_presentacion);			
	}
}
?>