<?php
class wo_inf_oc_por_facturar_bodega extends wo_inf_oc_por_facturar_bodega_base {
	function wo_inf_oc_por_facturar_bodega() 
	{
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$cod_usuario = session::get("COD_USUARIO");
	
		$origen = session::get("inf_oc_por_facturar_bodega.ORIGEN");
		session::un_set("inf_oc_por_facturar_bodega.ORIGEN");
		
		//////////////// SE LIMPIA LA TABLA ANTES DE LLENARLA ////////////////////////////////
		$db->EXECUTE_SP("spu_inf_oc_por_facturar_bodega", "$cod_usuario");
   				
		///////////////////////////// COMUNICACION CON WEB SERVICE ///////////////////////////
   			$sql = "select SISTEMA, URL_WS, USER_WS,PASSWROD_WS  from PARAMETRO_WS
					where SISTEMA = '".$origen."' ";
			$result = $db->build_results($sql);

			$user_ws		= $result[0]['USER_WS'];
			$passwrod_ws	= $result[0]['PASSWROD_WS'];
			$url_ws			= $result[0]['URL_WS'];
   		 
   		$biggi = new client_biggi("$user_ws", "$passwrod_ws", "$url_ws");
   		$lista_item_oc 	= '';
   		
   		////////////////-----------------------------------------------------\\\\\\\\\\\\\\\\\
		
   		$biggi->cli_oc_por_facturar($cod_usuario,"",$origen."|BODEGA");
   		
   		////////////////////////////////////////////////////////
		
		$sql = "SELECT COD_ORDEN_COMPRA
						,convert(varchar, FECHA_ORDEN_COMPRA, 103) FECHA_ORDEN_COMPRA
						,COD_NOTA_VENTA
						,COD_USUARIO_VENDEDOR
						,COD_PRODUCTO
						,NOM_PRODUCTO
						,CANTIDAD_OC
						,CANT_FA
						,CANT_POR_FACT
						,NOM_USUARIO
				FROM inf_oc_por_facturar_bodega
				WHERE COD_USUARIO = $cod_usuario
				AND CANT_POR_FACT > 0
				order by COD_ORDEN_COMPRA";

		parent::w_informe_pantalla('inf_oc_por_facturar_bodega', $sql, $_REQUEST['cod_item_menu']);

		// headers
		$this->add_header(new header_num('COD_ORDEN_COMPRA', 'COD_ORDEN_COMPRA', 'NUM OC'));
		$this->add_header(new header_date('FECHA_ORDEN_COMPRA', 'FECHA_ORDEN_COMPRA', 'Fecha OC'));

		// COD_SOLICITUD DE COMPRA SE ALMACENARA EN COD_NOTA_VENTA PARA NO ALTERAR LA TABLA inf_oc_por_facturar_bodega
		$this->add_header(new header_num('COD_NOTA_VENTA', 'COD_NOTA_VENTA', 'Nº NV'));

		$sql = "SELECT DISTINCT COD_USUARIO_VENDEDOR,NOM_USUARIO FROM inf_oc_por_facturar_bodega WHERE COD_USUARIO_VENDEDOR NOT IN ('AH', 'PP', 'RE', 'VP') order by COD_USUARIO_VENDEDOR";
		$this->add_header($control = new header_drop_down_string('COD_USUARIO_VENDEDOR', "COD_USUARIO_VENDEDOR", 'V1',$sql));
		$control->field_bd_order = 'NOM_USUARIO';
		$this->add_header(new header_text('COD_PRODUCTO', 'COD_PRODUCTO', 'Num producto'));
		$this->add_header(new header_text('NOM_PRODUCTO', "NOM_PRODUCTO", 'Nombre Product'));
		$this->add_header(new header_num('CANTIDAD_OC', 'CANTIDAD_OC', 'Cant OC'));
		$this->add_header(new header_num('CANT_FA', 'CANT_FA', 'Cant Facturada'));
		$this->add_header(new header_num('CANT_POR_FACT', 'CANT_POR_FACT', 'Cant Por Facturar'));
	}
	
	function procesa_event() {
		if(isset($_POST['b_back_x']))
			header('Location:' . $this->root_url . 'appl/inf_oc_por_facturar_bodega/BODEGA/inf_oc_por_facturar_bodega.php?cod_item_menu='.$this->cod_item_menu_parametro);
		else
			parent::procesa_event();	
	}
}
?>