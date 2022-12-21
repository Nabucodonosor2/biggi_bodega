<?php
class wo_inf_oc_por_facturar_bodega extends wo_inf_oc_por_facturar_bodega_base {
	var $origen;
	var $permiso;
	const K_PERMITE_FACTURAR_INF_OC = '992095';

	function wo_inf_oc_por_facturar_bodega(){
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$cod_usuario = session::get("COD_USUARIO");
	
		$origen = session::get("inf_oc_por_facturar_bodega.ORIGEN");
		session::un_set("inf_oc_por_facturar_bodega.ORIGEN");

		$this->origen = $origen;
		$this->permiso = $this->get_privilegio_opcion_usuario(self::K_PERMITE_FACTURAR_INF_OC, $cod_usuario);

		//////////////// SE LIMPIA LA TABLA ANTES DE LLENARLA ////////////////////////////////
		$db->EXECUTE_SP("spu_inf_oc_por_facturar_bodega", "$cod_usuario");
   				
		///////////////////////////// AQUI SE EJECUTA LO QUE HACIA CON WEBSERVICE ///////////////////////////
		$sp = "exec spi_inf_oc_por_facturar_bodega_universal '$cod_usuario', '$origen'";
		$result = $db->build_results($sp);

		$sp = "spi_inf_oc_por_facturar_bodega";

		$error = false;
		$db->BEGIN_TRANSACTION();

		for($i=0; $i<count($result); $i++){
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
			
			$cod_usuario_vendedor			= ($cod_usuario_vendedor == "") ? "null":"'".$cod_usuario_vendedor."'";
			$nom_usuario 		  			= ($nom_usuario == "") ? "null":"'".$nom_usuario."'";
			$cod_nota_venta	  				= ($cod_nota_venta == "") ? "null": $cod_nota_venta;
			$fecha_inf_oc_por_facturar_tdnx = $this->str2date($fecha_inf_oc_por_facturar_tdnx);
			$fecha_orden_compra				= $this->str2date($fecha_orden_compra);
			
			$param =   "'$origen'
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

			if (!$db->EXECUTE_SP($sp, $param))
				$error = true;
		}
		
		if($error)
			$db->ROLLBACK_TRANSACTION();						
		else
			$db->COMMIT_TRANSACTION();

		/////////////////////////////////////////////////////////////////////////////////////////////////////
		
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

	function procesa_event(){
		if(isset($_POST['b_back_x'])){
			header('Location:' . $this->root_url . 'appl/inf_oc_por_facturar_bodega/BODEGA/inf_oc_por_facturar_bodega.php?cod_item_menu='.$this->cod_item_menu_parametro);
		}else if(isset($_POST['b_factura_oc_x'])){
			$nro_orden_compra = $_POST['NRO_OC_INF_FACTURA'];
			$this->dws['dw_wo_factura'] = new wo_factura();
			$this->dws['dw_wo_factura']->cod_item_menu = '1535';
			$this->dws['dw_wo_factura']->retrieve();
			$this->dws['dw_wo_factura']->crear_desde_oc($nro_orden_compra, 'COMERCIAL');

			session::set('FACTURA_DESDE_INF_X_FAC', 'true');
			session::set('inf_oc_por_facturar_bodega.ORIGEN', $this->origen);
			//session::set('inf_oc_por_facturar_tdnx.TIPO', $this->tipo); "Se implementará despues"
		
		}else
			parent::procesa_event();	
	}



	function redraw_item(&$temp, $ind, $record){
		parent::redraw_item($temp, $ind, $record);
		$cod_orden_compra = $this->dw->get_item($record, 'COD_ORDEN_COMPRA');
		if($this->origen == 'COMERCIAL'){ //$this->tipo == 'TIPO_A' "Se implementará despues"
			$temp->setVar("wo_registro.WO_DISPLAY_B_FACT", "");
			if($this->permiso == 'E')
				$temp->setVar("wo_registro.WO_FACTURA_OC", "<input name=\"b_factura_oc\" id=\"b_factura_oc\" onclick=\"document.getElementById('NRO_OC_INF_FACTURA').value = $cod_orden_compra;\"  value=\"'$ind'\" src=\"../../images_appl/b_dte_xml.png\" type=\"image\">");
			else
				$temp->setVar("wo_registro.WO_FACTURA_OC", "<img name=\"b_factura_oc\" id=\"b_factura_oc\" src=\"../../images_appl/b_dte_xml_d.png\" type=\"image\">");
		}else
			$temp->setVar("wo_registro.WO_DISPLAY_B_FACT", "none");
	}

	function redraw_item_empty(&$temp, $ind) {
		parent::redraw_item_empty($temp, $ind);
		if($this->origen == 'COMERCIAL')
			$temp->setVar("wo_registro.WO_DISPLAY_B_FACT", "");
		else
			$temp->setVar("wo_registro.WO_DISPLAY_B_FACT", "none");
	}

	function habilita_boton(&$temp, $boton, $habilita) {
		parent::habilita_boton($temp, $boton, $habilita);
		if($this->origen == 'COMERCIAL')
			$temp->setVar("WO_DISPLAY_B_FACT_TH", "");
		else
			$temp->setVar("WO_DISPLAY_B_FACT_TH", "none");	
	}
}
?>