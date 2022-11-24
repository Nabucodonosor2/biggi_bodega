<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

class dw_pago_faprov_faprov_com extends datawindow {
	function dw_pago_faprov_faprov_com() {		
		$sql = "SELECT COD_PAGO_FAPROV
						,COD_PAGO_FAPROV
						,COD_FAPROV
						,NRO_FAPROV
						,CONVERT(VARCHAR, FECHA_FAPROV, 103) FECHA_FAPROV
						,TOTAL_CON_IVA_FA
						,MONTO_NC_PROV
						,SALDO_SIN_PAGO_FAPROV
						,MONTO_ASIGNADO
						,PAGO_ANTERIOR
						,NOM_CUENTA_CORRIENTE
				FROM 	PAGO_FAPROV_FAPROV_COM PFF
				WHERE 	COD_PAGO_FAPROV = {KEY1} AND
						COD_USUARIO_WS = {KEY2}
				ORDER BY NRO_FAPROV";
					
		parent::datawindow($sql, 'PAGO_FAPROV_FAPROV', true, true);	
		
		$this->add_control(new static_num('TOTAL_CON_IVA_FA'));
		$this->add_control(new static_num('MONTO_NC_PROV'));
		$this->add_control(new static_num('PAGO_ANTERIOR'));
		
		$this->add_control(new static_num('SALDO_SIN_PAGO_FAPROV'));	
		$this->add_control(new edit_precio('MONTO_ASIGNADO',10, 10));
	}
	
	function fill_template(&$temp){
		parent::fill_template($temp);
		$sum_nc = 0;
		$valor_nc = 0;
		for($i=0 ; $i < $this->row_count(); $i++){
			$valor_nc = $this->get_item($i, 'MONTO_NC_PROV');
			$sum_nc = $sum_nc + $valor_nc;
		}
		$sum_nc = number_format($sum_nc, 0, ',', '.');
		$temp->setVar("TOTAL_MONTO_NC", "<label id=\"TOTAL_MONTO_NC_0\">$sum_nc</label>");
	}
}


class dw_pago_faprov_comercial extends datawindow{
	function dw_pago_faprov_comercial(){
		$sistema = K_CLIENTE;
		
		$sql = "SELECT COD_PAGO_FAPROV_COMERCIAL
					  ,CONVERT(VARCHAR, FECHA_PAGO_FAPROV, 103) FECHA_PAGO_FAPROV
					  ,FECHA_PAGO_FAPROV DATE_PAGO_FAPROV
					  ,RUT
					  ,ALIAS
					  ,COD_EMPRESA
					  ,COD_EMPRESA COD_EMPRESA_H
					  ,NOM_EMPRESA
					  ,NOM_USUARIO
					  ,NRO_DOCUMENTO
					  ,CONVERT(VARCHAR, FECHA_DOCUMENTO, 103) FECHA_DOCUMENTO
					  ,FECHA_DOCUMENTO DATE_FECHA_DOCUMENTO
					  ,MONTO_DOCUMENTO
					  ,MONTO_DOCUMENTO MONTO_DOCUMENTO_S
					  ,NOM_TIPO_PAGO_FAPROV
                      ,NOM_TIPO_PAGO_FAPROV NOM_TIPO_PAGO_FAPROV_H
					  ,'$sistema' SISTEMA
				FROM PAGO_FAPROV_COMERCIAL
				WHERE COD_PAGO_FAPROV_COMERCIAL = {KEY1}
				AND COD_USUARIO_WS = {KEY2}
				ORDER BY COD_PAGO_FAPROV_COMERCIAL DESC";

		// DATAWINDOWS HELP_EMPRESA
		parent::datawindow($sql, '', false, false, 'P');	// El último parametro indica que solo acepta proveedores
		
		$this->add_control(new edit_nro_doc('COD_PAGO_FAPROV_COMERCIAL','PAGO_FAPROV'));
		$this->add_control(new edit_num('NRO_DOCUMENTO',10, 10, 0, true, false, false));
		$this->add_control(new edit_date('FECHA_DOCUMENTO'));
		$this->add_control(new static_text('RUT'));
		$this->add_control(new edit_text_hidden('COD_EMPRESA_H'));
		$this->add_control(new edit_text_hidden('NOM_TIPO_PAGO_FAPROV_H'));
		$this->add_control(new static_num('MONTO_DOCUMENTO'));
		$this->add_control(new static_num('MONTO_DOCUMENTO_S'));
		$this->add_control(new static_num('TOTAL_MONTO_NC'));
		$this->add_control(new edit_text_hidden('SISTEMA'));
	}
}

class wi_pago_faprov_comercial extends w_input{
	function wi_pago_faprov_comercial($cod_item_menu) {
		parent::w_input('pago_faprov_comercial', $cod_item_menu);
		
		$this->dws['dw_pago_faprov_comercial'] = new dw_pago_faprov_comercial();
		$this->dws['dw_pago_faprov_faprov_com'] = new dw_pago_faprov_faprov_com();
	}
	
	function load_record() {
		$cod_pago_faprov = $this->get_item_wo($this->current_record, 'COD_PAGO_FAPROV_COMERCIAL');
		$cod_usuario = session::get('COD_USUARIO');
		$this->dws['dw_pago_faprov_comercial']->retrieve($cod_pago_faprov, $cod_usuario);

		//Llenado de item correspondiente al pago faprov al cual esta situado
		$cod_usuario = $this->cod_usuario;
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$sql = "SELECT COUNT(*) COUNT
				FROM PAGO_FAPROV_FAPROV_COM
				WHERE COD_USUARIO_WS = $cod_usuario
				AND COD_PAGO_FAPROV = $cod_pago_faprov";
		$result = $db->build_results($sql);		
	
		if($result[0]['COUNT'] == 0){
			$cod_empresa = $this->dws['dw_pago_faprov_comercial']->get_item(0, 'COD_EMPRESA');
			
			if($cod_empresa == 1337)
				$sistema_proveedor = 'COMERCIAL';
			else if($cod_empresa == 9)
				$sistema_proveedor = 'BODEGA';
			else if($cod_empresa == 29)
				$sistema_proveedor = 'RENTAL';
			else if($cod_empresa == 7)
				$sistema_proveedor = 'TODOINOX';
			
			$sql = "SELECT SISTEMA
	   					  ,URL_WS
	   					  ,USER_WS
	   					  ,PASSWROD_WS
	   				FROM PARAMETRO_WS
					WHERE SISTEMA = '$sistema_proveedor'";
			$result = $db->build_results($sql);
			
			$user_ws		= $result[0]['USER_WS'];
			$passwrod_ws	= $result[0]['PASSWROD_WS'];
			$url_ws			= $result[0]['URL_WS'];
	   		 
	   		$biggi = new client_biggi("$user_ws", "$passwrod_ws", "$url_ws");
	   		$biggi->cli_wi_pago_faprov($cod_usuario, $cod_pago_faprov);
		}
		/////////////////////////////////////////////////////////////////////
		
		$this->dws['dw_pago_faprov_faprov_com']->retrieve($cod_pago_faprov, $cod_usuario);
	}
	
	function get_key() {
		return $this->dws['dw_pago_faprov_comercial']->get_item(0, 'COD_PAGO_FAPROV_COMERCIAL');
	}
	
	function habilita_boton(&$temp, $boton, $habilita) {
		parent::habilita_boton($temp, $boton, $habilita);
		if ($boton=='traspaso') {
			if ($habilita)
				$temp->setVar("WI_".strtoupper($boton), '<input name="b_'.$boton.'" id="b_'.$boton.'" src="../../images_appl/b_'.$boton.'.jpg" type="image" '.
															'onMouseDown="MM_swapImage(\'b_'.$boton.'\',\'\',\'../../images_appl/b_'.$boton.'_click.jpg\',1)" '.
															'onMouseUp="MM_swapImgRestore()" onMouseOut="MM_swapImgRestore()" '.
															'onMouseOver="MM_swapImage(\'b_'.$boton.'\',\'\',\'../../images_appl/b_'.$boton.'_over.jpg\',1)" '.
															'onClick="return confirm_cambio();"'.'style="display:"'.
														'/>');
			else
				$temp->setVar("WI_".strtoupper($boton), '<img src="../../images_appl/b_'.$boton.'_d.jpg"/>');
		}
		if ($boton=='anula') {
			if ($habilita)
				$temp->setVar("WI_".strtoupper($boton), '<input name="b_'.$boton.'" id="b_'.$boton.'" src="../../images_appl/b_'.$boton.'.jpg" type="image" '.
															'onClick="return confirm_anula();"'.'style="display:"'.
														'/>');
			else
				$temp->setVar("WI_".strtoupper($boton), '<img src="../../images_appl/b_'.$boton.'_d.jpg"/>');
		}
	}
	
	function habilitar(&$temp, $habilita) { 
		parent::habilitar(&$temp, $habilita);
		
		$this->habilita_boton($temp, 'traspaso', true);	
		$this->habilita_boton($temp, 'anula', true);
	}
	
	function crea_ingreso_pago(){
		////////variables de datos por sistema/////////
		if(K_CLIENTE == 'TODOINOX'){
			$nom_empresa_proveedor = $this->dws['dw_pago_faprov_comercial']->get_item(0, 'NOM_EMPRESA');
			$nom_empresa_cliente = "Todoinox";
			$v_cod_empresa = $this->dws['dw_pago_faprov_comercial']->get_item(0, 'COD_EMPRESA');
			if ($v_cod_empresa==1337) {	//comercial
				$v_cod_empresa = 1; //comercial en BD TODOINOX
				$cod_proyecto_ingreso = 2;//depositos com.biggi
				$sistema_proveedor = "COMERCIAL";
			}
			else if ($v_cod_empresa==9) {	//bodega
				$v_cod_empresa = 37; //bodega en BD TODOINOX
				$cod_proyecto_ingreso = 1;//depositos bodega
				$sistema_proveedor = "BODEGA";
			}else if ($v_cod_empresa==29) {	//rental
				$v_cod_empresa = 1; //rental en BD TODOINOX
				$cod_proyecto_ingreso = 5;//depositos com.biggi - rental
				$sistema_proveedor = "RENTAL";
			}
		}else if(K_CLIENTE == 'BODEGA'){
			$nom_empresa_proveedor = $this->dws['dw_pago_faprov_comercial']->get_item(0, 'NOM_EMPRESA');
			$nom_empresa_cliente = "Bodega";
			$cod_proyecto_ingreso = 1;//depositos bodega biggi
			
			$v_cod_empresa = $this->dws['dw_pago_faprov_comercial']->get_item(0, 'COD_EMPRESA');
			if ($v_cod_empresa==1337) {	//comercial
				$v_cod_empresa = 1; //comercial en BD BODEGA_BIGGI
				$sistema_proveedor = "COMERCIAL";
			}else if ($v_cod_empresa==29) {	//rental
				$v_cod_empresa = 1; //rental en BD BODEGA_BIGGI
				$sistema_proveedor = "RENTAL";
			}else if ($v_cod_empresa==7) {	//todoinox
				$v_cod_empresa = 4; //todoinox en BD BODEGA_BIGGI
				$sistema_proveedor = "TODOINOX";
			}
		}
		///////////////////////////////////////////////
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$count = $this->dws['dw_pago_faprov_faprov_com']->row_count();
		$sum_nc = 0;
		
		for($j=0 ; $j < $count ; $j++){
			$nro_faprov			= $this->dws['dw_pago_faprov_faprov_com']->get_item($j, 'NRO_FAPROV');
			$total_con_iva_fa	= $this->dws['dw_pago_faprov_faprov_com']->get_item($j, 'TOTAL_CON_IVA_FA');
			$monto_asignado		= $this->dws['dw_pago_faprov_faprov_com']->get_item($j, 'MONTO_ASIGNADO');
			$monto_nc_prov		= $this->dws['dw_pago_faprov_faprov_com']->get_item($j, 'MONTO_NC_PROV');
			
			$sql = "SELECT TOTAL_CON_IVA
						  ,COD_EMPRESA
						  ,dbo.f_fa_saldo(COD_FACTURA) MONTO_POR_COBRAR
						  ,TOTAL_CON_IVA
					FROM FACTURA
					WHERE NRO_FACTURA = $nro_faprov";
					
			$result = $db->build_results($sql);
			if(count($result) == 0){
				$this->alert('Señor usuario, en PAGO FAPROV Nº '.$this->get_key().' desde '.$nom_empresa_proveedor.', se esta cancelando una factura\ninexistente en '.$nom_empresa_cliente.' ('.$nro_faprov.')\nNo se puede realizar el traspaso.');
				return true;
			}
			if($monto_asignado > $result[0]['MONTO_POR_COBRAR']){
				$total_con_iva_fa = number_format($total_con_iva_fa, 0, ',','.');
				$this->alert('Señor usuario, en PAGO FAPROV Nº '.$this->get_key().' desde '.$nom_empresa_proveedor.', se esta cancelando la factura N° '.$nro_faprov.' por\nun monto de $'.number_format($monto_asignado, 0, ',', '.').' mayor al saldo por cobrar en '.$nom_empresa_cliente.' de $'.$result[0]['MONTO_POR_COBRAR'].'\nNo se puede realizar el traspaso.');
				return true;
			}
			if($result[0]['COD_EMPRESA'] <> $v_cod_empresa){
				$this->alert('Señor usuario, en PAGO FAPROV Nº '.$this->get_key().' desde '.$nom_empresa_proveedor.', se esta cancelando una factura que\nexiste en '.$nom_empresa_cliente.' pero no es para el cliente '.$nom_empresa_proveedor.' ('.$nro_faprov.')\nNo se puede realizar el traspaso.');
				return true;
			}
			//nuevo
			if($result[0]['TOTAL_CON_IVA'] <> $total_con_iva_fa){
				$this->alert('Señor usuario, en PAGO FAPROV Nº '.$this->get_key().', desde '.$nom_empresa_proveedor.', se ha \nrecepcionado la Factura Nº '.$nro_faprov.' por $'.number_format($total_con_iva_fa, 0, ',', '.').', sin embargo el monto correcto de la factura es \n$'.number_format($result[0]['TOTAL_CON_IVA'], 0, ',', '.').'.\nNo es posible realizar el traspaso.');
				return true;
			}
			
			$sum_nc = $sum_nc + $monto_nc_prov;
		}
		
		$sp = "spu_ingreso_pago";
		
		$db->BEGIN_TRANSACTION();
		$param	= "'INSERT'
				  ,null
				  ,$this->cod_usuario
				  ,$v_cod_empresa
				  ,0
				  ,0
				  ,1			--emitida
				  ,null
				  ,null
				  ,null
				  ,0
				  ,$cod_proyecto_ingreso			
				  ,'$sistema_proveedor'
				  ,".$this->get_key();
		
		if ($db->EXECUTE_SP($sp, $param)){
			$cod_ingreso_pago	= $db->GET_IDENTITY();
			$nro_doc			= $this->dws['dw_pago_faprov_comercial']->get_item(0, 'NRO_DOCUMENTO');
			$fecha_doc			= $this->dws['dw_pago_faprov_comercial']->get_item(0, 'FECHA_DOCUMENTO');
			$monto_documento	= $this->dws['dw_pago_faprov_comercial']->get_item(0, 'MONTO_DOCUMENTO');
			$nom_documento	    = $this->dws['dw_pago_faprov_comercial']->get_item(0, 'NOM_TIPO_PAGO_FAPROV_H');

			$tipo_doc_pago = 2; //cheque al día 
			if(strtoupper($nom_documento) == 'TRANSFERENCIA')
			    $tipo_doc_pago = 10;
			
			$sp = "spu_doc_ingreso_pago";
			$param	= "'INSERT'
					  ,null
					  ,$cod_ingreso_pago
					  ,$tipo_doc_pago		--cheque al dia
					  ,27		--corpbanca
					  ,$nro_doc
					  ,'$fecha_doc'
					  ,$monto_documento";
			
			if ($db->EXECUTE_SP($sp, $param)){
				
				$sql = "SELECT SISTEMA
		   					  ,URL_WS
		   					  ,USER_WS
		   					  ,PASSWROD_WS
		   				FROM PARAMETRO_WS
						WHERE SISTEMA = '$sistema_proveedor'";
				$result = $db->build_results($sql);
				
				$user_ws		= $result[0]['USER_WS'];
				$passwrod_ws	= $result[0]['PASSWROD_WS'];
				$url_ws			= $result[0]['URL_WS'];
		   		 
		   		$biggi = new client_biggi("$user_ws", "$passwrod_ws", "$url_ws");
		   		$array_nc = $biggi->cli_add_nota_credito($this->get_key());

				if(count($array_nc['NCPROV']) <> 0){
					for($k = 0 ; $k < count($array_nc['NCPROV']) ; $k++){
						$nro_doc_nc = $array_nc['NCPROV'][$k]['NRO_NCPROV'];
						$fecha		= $array_nc['NCPROV'][$k]['FECHA_NCPROV'];
						$monto_doc	= $array_nc['NCPROV'][$k]['MONTO_ASIGNADO'];
						$param	= "'INSERT'
								  ,null
								  ,$cod_ingreso_pago
								  ,7		--nota credito
								  ,null		
								  ,$nro_doc_nc
								  ,'$fecha'
								  ,$monto_doc";
						
						$sql = "SELECT COD_EMPRESA
								FROM NOTA_CREDITO
								WHERE NRO_NOTA_CREDITO = $nro_doc_nc";
								
						$result = $db->build_results($sql);
						if(count($result) == 0){
							$this->alert('Señor usuario, en PAGO FAPROV Nº '.$this->get_key().' desde '.$nom_empresa_proveedor.', se esta indicando como\ndocumento de pago una NC que no existe en '.$nom_empresa_cliente.' ('.$nro_doc_nc.')\nNo se puede realizar el traspaso.');
							$db->ROLLBACK_TRANSACTION();
							return false;
						}

						if($result[0]['COD_EMPRESA'] <> $v_cod_empresa){
							$this->alert('Señor usuario, en PAGO FAPROV Nº '.$this->get_key().' desde '.$nom_empresa_proveedor.', se esta indicando como\ndocumento de pago una NC que existe en '.$nom_empresa_cliente.' pero no es para el cliente '.$nom_empresa_proveedor.' ('.$nro_doc_nc.')\nNo se puede realizar el traspaso');
							$db->ROLLBACK_TRANSACTION();
							return false;
						}
								  
						if(!$db->EXECUTE_SP($sp, $param)){
							$db->ROLLBACK_TRANSACTION();
							return false;
						}	
					}
				}
				
				$sp = "spu_ingreso_pago_factura";
				for($i=0 ; $i < $count ; $i++){
					$cod_doc = $this->dws['dw_pago_faprov_faprov_com']->get_item($i, 'NRO_FAPROV');
					$monto_asignado = $this->dws['dw_pago_faprov_faprov_com']->get_item($i, 'MONTO_ASIGNADO');
					
					$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
					$sql_fac = "SELECT COD_FACTURA
								FROM FACTURA
								WHERE NRO_FACTURA =".$cod_doc;
					$result_fac = $db->build_results($sql_fac);
					
					$param = "'INSERT'
							 ,null
							 ,$cod_ingreso_pago
							 ,".$result_fac[0]['COD_FACTURA']."
							 ,'FACTURA'
							 ,".$monto_asignado;
					
					if (!$db->EXECUTE_SP($sp, $param)){
						$db->ROLLBACK_TRANSACTION();
						return false;
					}
				}
			}
			$db->COMMIT_TRANSACTION();
			///////llama a web_service para cambiar el campo TRASPASADO_WS//////
			$sql = "SELECT SISTEMA
	   					  ,URL_WS
	   					  ,USER_WS
	   					  ,PASSWROD_WS
	   				FROM PARAMETRO_WS
					WHERE SISTEMA = '$sistema_proveedor'";
			$result = $db->build_results($sql);
			
			$user_ws		= $result[0]['USER_WS'];
			$passwrod_ws	= $result[0]['PASSWROD_WS'];
			$url_ws			= $result[0]['URL_WS'];
	   		 
	   		$biggi = new client_biggi("$user_ws", "$passwrod_ws", "$url_ws");
	   		$value = $biggi->cli_cambio_estado_traspaso($this->get_key(), 'TRASPASADO');
	   		
	   		session::set('CREADO_TRASPASO', $cod_ingreso_pago);
	   		header ('Location:'.K_ROOT_URL."appl/login/presentacion_esp.php");
			//////////////////////////////////////////////////////////////////////
			return true;
		}
		$db->ROLLBACK_TRANSACTION();
		return false;
			
	}
	
	function anula_ingreso_pago(){
	    $db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
	    
	    if(K_CLIENTE == 'TODOINOX'){
			$v_cod_empresa = $this->dws['dw_pago_faprov_comercial']->get_item(0, 'COD_EMPRESA');
			if ($v_cod_empresa==1337) {	//comercial
				$v_cod_empresa = 1; //comercial en BD TODOINOX
				$sistema_proveedor = "COMERCIAL";
			}
			else if ($v_cod_empresa==9) {	//bodega
				$v_cod_empresa = 37; //bodega en BD TODOINOX
				$sistema_proveedor = "BODEGA";
			}else if ($v_cod_empresa==29) {	//rental
				$v_cod_empresa = 1; //rental en BD TODOINOX
				$sistema_proveedor = "RENTAL";
			}
		}else if(K_CLIENTE == 'BODEGA'){
			$v_cod_empresa = $this->dws['dw_pago_faprov_comercial']->get_item(0, 'COD_EMPRESA');
			if ($v_cod_empresa==1337) {	//comercial
				$v_cod_empresa = 1; //comercial en BD BODEGA_BIGGI
				$sistema_proveedor = "COMERCIAL";
			}else if ($v_cod_empresa==29) {	//rental
				$v_cod_empresa = 1; //rental en BD BODEGA_BIGGI
				$sistema_proveedor = "RENTAL";
			}else if ($v_cod_empresa==7) {	//todoinox
				$v_cod_empresa = 4; //todoinox en BD BODEGA_BIGGI
				$sistema_proveedor = "TODOINOX";
			}
		}
		
	    $sql = "SELECT SISTEMA
	   					  ,URL_WS
	   					  ,USER_WS
	   					  ,PASSWROD_WS
	   				FROM PARAMETRO_WS
					WHERE SISTEMA = '$sistema_proveedor'";
			$result = $db->build_results($sql);
			
			$user_ws		= $result[0]['USER_WS'];
			$passwrod_ws	= $result[0]['PASSWROD_WS'];
			$url_ws			= $result[0]['URL_WS'];
	   		 
	   		$biggi = new client_biggi("$user_ws", "$passwrod_ws", "$url_ws");
	   		$value = $biggi->cli_cambio_estado_traspaso($this->get_key(), 'ANULADO');
	   		
	   		
	   		header ('Location:'.K_ROOT_URL."appl/login/presentacion_esp.php");
			//////////////////////////////////////////////////////////////////////
			return true;
	}
	
	function procesa_event() {		
		if(isset($_POST['b_traspaso_x'])){
			if(!$this->crea_ingreso_pago()){
				$this->unlock_record();
				$this->goto_record($this->current_record);
				return;
			}
			$this->_load_record();
		}else if(isset($_POST['b_anula_x'])){
			if(!$this->anula_ingreso_pago()){
				$this->unlock_record();
				$this->goto_record($this->current_record);
				return;
			}
			$this->_load_record();
		}
		else
			parent::procesa_event();
	}
}
?>