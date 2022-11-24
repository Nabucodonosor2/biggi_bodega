<?php
require_once(dirname(__FILE__)."/../../ws_client_biggi/class_client_biggi.php");
////////////////////////////////////////
/////////// BODEGA_BIGGI ///////////////
////////////////////////////////////////
class wo_factura extends wo_factura_base {
	const K_EMPRESA_BODEGA_BIGGI = 1138;
	var $autoriza_print;
	var $autoriza_xml;
	
	function wo_factura() {
		parent::wo_factura_base();
		
		// se elimina F.COD_TIPO_FACTURA = ".self::K_TIPO_VENTA."
		// parab que traiga todas las FA
		$sql = "select F.COD_FACTURA
						,convert(varchar(20), F.FECHA_FACTURA, 103) FECHA_FACTURA
						,F.NRO_FACTURA
						,F.RUT
						,F.DIG_VERIF
						,F.NOM_EMPRESA
						,dbo.f_fa_NV_COMERCIAL(F.COD_FACTURA) COD_DOC
						,EDS.NOM_ESTADO_DOC_SII
						,F.TOTAL_CON_IVA
						,U.INI_USUARIO
						,dbo.f_tipo_fa(EDS.NOM_ESTADO_DOC_SII) TIPO_FA
						,F.NRO_ORDEN_COMPRA
						,EDS.COD_ESTADO_DOC_SII
				 from	FACTURA F, ESTADO_DOC_SII EDS, USUARIO U
				where	F.COD_ESTADO_DOC_SII = EDS.COD_ESTADO_DOC_SII AND
						F.COD_USUARIO_VENDEDOR1 = U.COD_USUARIO
				order by	isnull(NRO_FACTURA, 9999999999) desc, COD_FACTURA desc";
		$this->dw->set_sql($sql);		
		$this->sql_original = $sql;
		//$this->add_header(new header_text('COD_DOC', 'dbo.f_fa_NV_COMERCIAL(F.COD_FACTURA)', 'N° NV'));
		$this->add_header(new header_text('NRO_ORDEN_COMPRA', 'F.NRO_ORDEN_COMPRA', 'N° OC Comercial'));
		
		$priv = $this->get_privilegio_opcion_usuario('992075', $this->cod_usuario); //print
		if($priv=='E')
			$this->autoriza_print = true;
      	else
			$this->autoriza_print = false;
			
		$priv = $this->get_privilegio_opcion_usuario('992085', $this->cod_usuario); //xml
		if($priv=='E')
			$this->autoriza_xml = true;
      	else
			$this->autoriza_xml = false;
	}
	
	function redraw_item(&$temp, $ind, $record){
		$COD_FACTURA		= $this->dw->get_item($record, 'COD_FACTURA');
		$COD_ESTADO_DOC_SII = $this->dw->get_item($record, 'COD_ESTADO_DOC_SII');
	
		$temp->gotoNext("wo_registro");
		if ($ind % 2 == 0) {
			$temp->setVar("wo_registro.WO_TR_CSS", $this->css_claro);
			$temp->setVar("wo_registro.WO_DETALLE", '<input name="b_detalle_'.$ind.'" id="b_detalle_'.$ind.'" value="'.$ind.'" src="../../../../com	monlib/trunk/images/lupa1.jpg" type="image">');
		}
		else {
			$temp->setVar("wo_registro.WO_TR_CSS", $this->css_oscuro);
			$temp->setVar("wo_registro.WO_DETALLE", '<input name="b_detalle_'.$ind.'" id="b_detalle_'.$ind.'" value="'.$ind.'" src="../../../../commonlib/trunk/images/lupa2.jpg" type="image">');
		}
		
		if($COD_ESTADO_DOC_SII == 2 && $this->autoriza_print == true){
			$control =  '<input name="b_printDTE_'.$ind.'" id="b_printDTE_'.$ind.'" type="button" title="Imprimir"'.
			   			'style="cursor:pointer;height:26px;width:17px;border: 0;background-image:url(../../images_appl/b_dte_print.png);background-repeat:no-repeat;background-position:center;"'.
			   			'onClick="return dlg_print_dte_wo(this, '.$COD_FACTURA.');"/>';
			
			$temp->setVar("wo_registro.WO_PRINT_DTE", $control);
		}else if($COD_ESTADO_DOC_SII == 3 && $this->autoriza_print == true){
			$control =  '<input name="b_printDTE_'.$ind.'" id="b_printDTE_'.$ind.'" type="button" title="Imprimir"'.
			   			'style="cursor:pointer;height:26px;width:17px;border: 0;background-image:url(../../images_appl/b_dte_print.png);background-repeat:no-repeat;background-position:center;"'.
			   			'onClick="return dlg_print_dte_wo(this, '.$COD_FACTURA.');"/>';
			
			$temp->setVar("wo_registro.WO_PRINT_DTE", $control);
		}else
			$temp->setVar("wo_registro.WO_PRINT_DTE", '<img src="../../images_appl/b_dte_print_d.png">');
		
		
		if($COD_ESTADO_DOC_SII == 3 && $this->autoriza_xml == true)
			$temp->setVar("wo_registro.WO_XML_DTE", '<input name="b_xmlDTE_'.$ind.'" id="b_xmlDTE_'.$ind.'" value="'.$ind.'" title="Descargar XML" src="../../images_appl/b_dte_xml.png" type="image">');
		else
			$temp->setVar("wo_registro.WO_XML_DTE", '<img src="../../images_appl/b_dte_xml_d.png">');
		
		
		$this->dw->fill_record($temp, $record);
		
		//////////////////
		// llama al js para grabar scrol
		$temp->setVar("wo_registro.WO_DETALLE", '<input name="b_detalle_'.$ind.'" id="b_detalle_'.$ind.'" value="'.$ind.'" src="../../../../commonlib/trunk/images/lupa2.jpg" type="image" onClick="graba_scroll(\''.$this->nom_tabla.'\');">');
		
		if (session::is_set('W_OUTPUT_RECNO_'.$this->nom_tabla)) {	
			$rec_no = session::get('W_OUTPUT_RECNO_'.$this->nom_tabla);	
			if ($rec_no==$ind) {
				session::un_set('W_OUTPUT_RECNO_'.$this->nom_tabla);	
				$temp->setVar("wo_registro.WO_TR_CSS", 'linea_selected');
			}
		}
		//////////////////
	}
	
	function redraw(&$temp) {
  		if ($this->b_add_visible){
			$this->habilita_boton($temp, 'create', $this->get_privilegio_opcion_usuario($this->cod_item_menu, $this->cod_usuario)=='E');
  		}
  		$this->habilita_boton($temp, 'crear_desde', $this->get_privilegio_opcion_usuario('992055', $this->cod_usuario)=='E');
  		
  		$this->dw_check_box->habilitar($temp, true);
	}
	function habilita_boton(&$temp, $boton, $habilita) {
		if ($boton=='create') {
			if ($habilita)
				$temp->setVar("WO_CREATE", '<input name="b_create" id="b_create" src="../../../../commonlib/trunk/images/b_create.jpg" type="image" '.
											'onMouseDown="MM_swapImage(\'b_create\',\'\',\'../../../../commonlib/trunk/images/b_create_click.jpg\',1)" '.
											'onMouseUp="MM_swapImgRestore()" onMouseOut="MM_swapImgRestore()" '.
											'onMouseOver="MM_swapImage(\'b_create\',\'\',\'../../../../commonlib/trunk/images/b_create_over.jpg\',1)" '.
											'onClick="return request_factura(\'Ingrese Nº de la Nota de Venta\',\'\');"'.
											'/>');
			else
				$temp->setVar("WO_".strtoupper($boton), '<img src="../../../../commonlib/trunk/images/b_create_d.jpg"/>');
		}else if($boton=='crear_desde'){
			if ($habilita){
		/*		$temp->setVar("WO_CREATE_FROM", '<input name="b_crear_desde" id="b_crear_desde" src="../../images_appl/b_crear_desde.jpg" type="image" '.
												'onMouseDown="MM_swapImage(\'b_crear_desde\',\'\',\'../../images_appl/b_crear_desde_click.jpg\',1)" '.
												'onMouseUp="MM_swapImgRestore()" onMouseOut="MM_swapImgRestore()" '.
												'onMouseOver="MM_swapImage(\'b_crear_desde\',\'\',\'../../images_appl/b_crear_desde_over.jpg\',1)" '.
												'onClick="return dlg_crear_desde(\'Ingrese Nº de la OC\',\'\');"'.
												'/>');*/
				$ruta_over = "'../../images_appl/b_crear_desde_over.jpg'";
				$ruta_out = "'../../images_appl/b_crear_desde.jpg'";
				$ruta_click = "'../../images_appl/b_crear_desde_click.jpg'";
				$temp->setVar("WO_CREATE_FROM", '<input name="b_'.$boton.'" id="b_'.$boton.'" type="button" onmouseover="entrada(this, '.$ruta_over.')" onmouseout="salida(this, '.$ruta_out.')" onmousedown="down(this, '.$ruta_click.')"'.
													 'style="cursor:pointer;height:68px;width:66px;border: 0;background-image:url(../../images_appl/b_crear_desde.jpg);background-repeat:no-repeat;background-position:center;border-radius: 15px;"'.
													 'onClick="dlg_crear_desde(\'Ingrese Nº de la OC\',\'\');" />');
			}else
				$temp->setVar("WO_".strtoupper($boton), '<img src="../../images_appl/b_crear_desde_d.jpg"/>');	
		}else
			parent::habilita_boton($temp, $boton, $habilita);
	}
  	function crear_fa_from_oc_comercial($cod_orden_compra_comercial) {
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$sql = "SELECT COD_ORDEN_COMPRA FROM BIGGI_dbo_ORDEN_COMPRA WHERE COD_ORDEN_COMPRA = $cod_orden_compra_comercial";
		$result = $db->build_results($sql);
		if (count($result) == 0){
			$this->_redraw();
			$this->alert('La OC Nº'.$cod_orden_compra_comercial.' no existe en Comercial.');								
			return;
		}else{
			//si existe la Oc determina el tipo
			$sql = "SELECT COD_ORDEN_COMPRA
							,COD_EMPRESA 
					FROM BIGGI_dbo_ORDEN_COMPRA 
					WHERE COD_ORDEN_COMPRA = $cod_orden_compra_comercial
						and TIPO_ORDEN_COMPRA IN ('NOTA_VENTA', 'BACKCHARGE')";

			$result = $db->build_results($sql);
			if (count($result) == 0){
				$this->_redraw();
				$this->alert('La OC Nº'.$cod_orden_compra_comercial.' no es de tipo VENTA ó BACKCHARGE');								
				return;
			}
			else if ($result[0]['COD_EMPRESA'] != self::K_EMPRESA_BODEGA_BIGGI){
				$this->_redraw();
				$this->alert('La OC Nº'.$cod_orden_compra_comercial.' no es para Bodega Biggi');								
				return;
			}

			//Verica si tiene items pendientes de facturar
			$sql = "SELECT isnull(sum(dbo.f_fa_OC_Comercial_por_facturar(COD_ITEM_ORDEN_COMPRA)), 0) CANT 
					FROM BIGGI_dbo_ITEM_ORDEN_COMPRA 
					WHERE COD_ORDEN_COMPRA = $cod_orden_compra_comercial";
			$result = $db->build_results($sql);
			if ($result[0]['CANT']==0) {
				$this->_redraw();
				$this->alert('La OC Nº'.$cod_orden_compra_comercial.' ya esta facturada');								
				return;
			}
			else {	
				session::set('FACTURA_DESDE_OC_COMERCIAL', $cod_orden_compra_comercial);
				$this->add();
	   		}				
		}
  		
	}
	function procesa_event() {	
		if(session::get("SIN_PRODUCTOS") != ''){
			$str_alert_no_existe = session::get("SIN_PRODUCTOS");
			
			$mensaje = 'Los productos incluidos en la OC no existen en Bodega Biggi, estos son: \n\n';
			$mensaje .= $str_alert_no_existe;
			$mensaje .= 'La factura no podrá ser creada.';
			$this->alert($mensaje);
			session::un_set("SIN_PRODUCTOS");
		}
		if(isset($_POST['b_create_x'])) {
			$this->crear_fa_from_oc_comercial($_POST['wo_hidden']);
		}else if(isset($_POST['b_crear_desde_x'])){
			$values = explode("|", $_POST['wo_hidden']);
			if($values[2] == 'etiqueta'){
				if($values[1] == '89257000X') //todoinox
					$this->crear_desde_oc($values[0], 'TODOINOX');
				else if($values[1] == '91462001X') //comercial
					$this->crear_desde_oc($values[0], 'COMERCIAL');
				else if($values[1] == '91462001R') //rental
					$this->crear_desde_oc($values[0], 'RENTAL');				
			}else{
				if($values[1] == 'todoinox')
					$this->crear_desde_oc($values[0], 'TODOINOX');
				if($values[1] == 'comercial')
					$this->crear_desde_oc($values[0], 'COMERCIAL');
				if($values[1] == 'rental')
					$this->crear_desde_oc($values[0], 'RENTAL');	
			}
		}else if($_POST['HIZO_CLICK_0'] == 'S'){
			$this->checkbox_sumar = isset($_POST['CHECK_SUMAR_0']);
			
			// obtiene los datos del filtro aplicado
			$valor_filtro = $this->headers['TOTAL_CON_IVA']->valor_filtro;
			$valor_filtro2 = $this->headers['TOTAL_CON_IVA']->valor_filtro2;
			
			if($this->checkbox_sumar){
				$this->dw_check_box->set_item(0, 'CHECK_SUMAR', 'S');
				$this->add_header(new header_num('TOTAL_CON_IVA', 'TOTAL_CON_IVA', 'Total c/IVA', 0, true, 'SUM'));
			}
			else{
				$this->dw_check_box->set_item(0, 'CHECK_SUMAR', 'N');
				$this->add_header(new header_num('TOTAL_CON_IVA', 'TOTAL_CON_IVA', 'Total c/IVA'));  
			}

			// vuelve a setear el filtro aplicado
			$this->headers['TOTAL_CON_IVA']->valor_filtro = $valor_filtro;
			$this->headers['TOTAL_CON_IVA']->valor_filtro2 = $valor_filtro2;
			
			$this->save_SESSION();	
			$this->make_filtros();
			$this->retrieve();	
		}else if ($this->clicked_boton('b_printDTE', $value_boton))
			$this->printdte($value_boton);
		else if ($this->clicked_boton('b_xmlDTE', $value_boton))
			$this->xmldte($value_boton);
		else{
			$this->checkbox_sumar = 0;
			parent::procesa_event();
		}	
	}
	function crear_desde_oc($cod_orden_compra, $sistema){
		if($sistema == 'TODOINOX'){
			if($cod_orden_compra <= 22231){
				$this->_redraw();
				$this->alert('La Orden Compra N° '.$cod_orden_compra.', debe ser facturada por el metodo tradicional.');								
				return;
			}
			session::set('WS_ORIGEN', $sistema);
		
			$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
			$sql = "select SISTEMA, URL_WS, USER_WS,PASSWROD_WS  from PARAMETRO_WS
					where SISTEMA = 'TODOINOX' ";
			$result = $db->build_results($sql);
			
			$user_ws		= $result[0]['USER_WS'];
			$passwrod_ws	= $result[0]['PASSWROD_WS'];
			$url_ws			= $result[0]['URL_WS'];
			
			$biggi = new client_biggi("$user_ws", "$passwrod_ws", "$url_ws");
			
		}else if($sistema == 'COMERCIAL'){
			if($cod_orden_compra <= 177994){
				$this->_redraw();
				$this->alert('La Orden Compra N° '.$cod_orden_compra.', debe ser facturada por el metodo tradicional.');								
				return;
			}
			session::set('WS_ORIGEN', $sistema);
			$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
			$sql = "select SISTEMA, URL_WS, USER_WS,PASSWROD_WS  from PARAMETRO_WS
					where SISTEMA = 'COMERCIAL' ";
			$result = $db->build_results($sql);
			
			$user_ws		= $result[0]['USER_WS'];
			$passwrod_ws	= $result[0]['PASSWROD_WS'];
			$url_ws			= $result[0]['URL_WS'];
			
			$biggi = new client_biggi("$user_ws", "$passwrod_ws", "$url_ws");
		}else{ // RENTAL
			if($cod_orden_compra <= 65671){
				$this->_redraw();
				$this->alert('La Orden Compra N° '.$cod_orden_compra.', debe ser facturada por el metodo tradicional.');								
				return;
			}
			session::set('WS_ORIGEN', $sistema);
			$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
			$sql = "select SISTEMA, URL_WS, USER_WS,PASSWROD_WS  from PARAMETRO_WS
					where SISTEMA = 'RENTAL' ";
			$result = $db->build_results($sql);
			
			$user_ws		= $result[0]['USER_WS'];
			$passwrod_ws	= $result[0]['PASSWROD_WS'];
			$url_ws			= $result[0]['URL_WS'];
			
			$biggi = new client_biggi("$user_ws", "$passwrod_ws", "$url_ws");	
		}
		$result = $biggi->cli_orden_compra($cod_orden_compra);
		$index = '';
		//Valida que exista un registro en la OC ingresada
		if(count($result['ORDEN_COMPRA']) != 0){
			//Valida que la OC no este en estado anulada
			if($result['ORDEN_COMPRA'][0]['COD_ESTADO_ORDEN_COMPRA'] == 2){
				$this->_redraw();
				$this->alert('La Orden de Compra N° '.$cod_orden_compra.', del Sistema '.$sistema.' está anulada.');								
				return;
			}
			//Valida que la OC sea para BODEGA
			if($result['ORDEN_COMPRA'][0]['RUT'] == 80112900){ //BIGGI CHILE  SOC LTDA.
				if($sistema == 'RENTAL' && $result['ORDEN_COMPRA'][0]['COD_ESTADO_ORDEN_COMPRA'] != 4){ //Confirmada
					$this->_redraw();
					$this->alert('La Orden de Compra N° '.$cod_orden_compra.', del Sistema Web Rental, NO ESTA en estado autorizada.\nEl responsable de la OC en Sistema Web Rental debe solicitar autorizacion de la OC a Administracion BIGGI.\n\nNo se puede facturar la OC N° '.$cod_orden_compra.'.');								
					return;
				}
				/*
				Cuando se crean facturas desde OC de Rental, inexplicablemente habían OC
				en la bd oficial de rental con tipo_orden_compra = NOTA_VENTA pero el COD_DOC era NULL.
				No se sabe aun por que ocurre esto, pero por mientras se valida con un mensaje
				y se deriva al usuario que llama a integrasystem.
 				*/
				if($sistema == 'RENTAL'){
					if($result['ORDEN_COMPRA'][0]['TIPO_ORDEN_COMPRA'] != 'ARRIENDO'){
						if($result['ORDEN_COMPRA'][0]['COD_NOTA_VENTA'] == ''){
							$this->_redraw();
							$this->alert('Error al crear la factura, la OC '.$cod_orden_compra.' no proviene desde una Nota de Venta o Arriendo.\nContactese con Integrasystem indicando este mensaje.');								
							return;
						}
					}
				}
				//////////////////////////////////		
				for ($i=0; $i < count($result['ITEM_ORDEN_COMPRA']); $i++) {
					
					$cod_item_oc = $result['ITEM_ORDEN_COMPRA'][$i]['COD_ITEM_ORDEN_COMPRA'];
					$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);			
					
					$sql = "SELECT SUM(CANTIDAD) CANTIDAD
							FROM ITEM_FACTURA
							WHERE COD_ITEM_DOC = $cod_item_oc";
					
					if($sistema == 'TODOINOX')
						$sql .= " AND TIPO_DOC = 'ITEM_ORDEN_COMPRA_TODOINOX'";
					else if($sistema == 'COMERCIAL')
						$sql .= " AND TIPO_DOC = 'ITEM_ORDEN_COMPRA_COMERCIAL'";
					else //RENTAL
						$sql .= " AND TIPO_DOC = 'ITEM_ORDEN_COMPRA_RENTAL'";				
					
					$result_cant = $db->build_results($sql);
					$cantidad = $result['ITEM_ORDEN_COMPRA'][$i]['CANTIDAD'] - $result_cant[0]['CANTIDAD'];
					
					//Se concatena los indices del arreglo con cantidades = 0
					if($cantidad == 0)
						$index = $index.$i.'|';
							
				}
				
				$index = explode('|', trim($index,'|'));
				for($j= 0 ; $j < count($index) ; $j++)
					unset($result['ITEM_ORDEN_COMPRA'][$index[$j]]);
				
				$result['ITEM_ORDEN_COMPRA'] = array_values($result['ITEM_ORDEN_COMPRA']);
				
				$count_item = count($result['ITEM_ORDEN_COMPRA']);
				//Valida que los item de la OC esten totalmente facturadas
				if($count_item == 0){
					$this->_redraw();
					$message = 'La Orden de compra N '.$cod_orden_compra.' del Sistema '.$sistema.' está 100% facturada \n\n Facturas asociadas: ';
					
					$sql = "SELECT DISTINCT COD_FACTURA
							FROM ITEM_FACTURA
							WHERE COD_ITEM_DOC = $cod_item_oc";
					
					if($sistema == 'TODOINOX')
						$sql .= " AND TIPO_DOC = 'ITEM_ORDEN_COMPRA_BODEGA'";
					else if($sistema == 'COMERCIAL')
						$sql .= " AND TIPO_DOC = 'ITEM_ORDEN_COMPRA_COMERCIAL'";
					else //RENTAL
						$sql .= " AND TIPO_DOC = 'ITEM_ORDEN_COMPRA_RENTAL'";
					
					$result_m = $db->build_results($sql);
					
					for($i=0 ; $i < count($result_m) ; $i++)
						$message .= 'FA N° '.$result_m[$i]['COD_FACTURA'].', ';

					$message = 	trim($message,', ');
						
					$this->alert($message);								
					return;
				}
				
				if($sistema == 'TODOINOX')
					$result['ORDEN_COMPRA'][0]['RUT'] = 89257000; // RUT DE TODOINOX
				else
					$result['ORDEN_COMPRA'][0]['RUT'] = 91462001; // RUT DE COMERCIAL	
				
				session::set('FACTURA_DESDE_OC', $result);
				$this->add();
			}else{
				$this->_redraw();
				$this->alert('La Orden de compra N° '.$cod_orden_compra.' del Sistema '.$sistema.' no es para BIGGI CHILE  SOC LTDA.');								
				return;
			}	
		}else{
			$this->_redraw();
			$this->alert('La Orden de compra N° '.$cod_orden_compra.' no existe en el Sistema '.$sistema);								
			return;
		}	
	}
	
	function printdte($rec_no){
		$es_cedible = $_POST['wo_hidden2'];
  		$wi = new wi_factura('cod_item_menu');
		$wi->current_record = $rec_no;
		$wi->load_record();
		$wi->imprimir_dte($es_cedible, true);
		$this->goto_page($this->current_page);
  	}
  	
	function xmldte($rec_no){
  		$wi = new wi_factura('cod_item_menu');
		$wi->current_record = $rec_no;
		$wi->load_record();
		$wi->xml_dte();
  	}
}
?>