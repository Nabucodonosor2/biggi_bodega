<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../ws_client_biggi/class_client_biggi.php");
include(dirname(__FILE__)."/../../appl.ini");

class wo_inf_oc_por_facturar_servindus_base extends w_informe_pantalla {
	function wo_inf_oc_por_facturar_servindus_base(){
		/////////////////////////////////////////////////////////
		$cod_usuario = session::get("COD_USUARIO");
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
   		$db->EXECUTE_SP("spi_inf_oc_por_facturar_servindus", "$cod_usuario");
		////////////////////////////////////////////////////////

   		///////////////////////////// COMUNICACION CON WEB SERVICE ///////////////////////////
			$sql = "SELECT SISTEMA
						  ,URL_WS
						  ,USER_WS
						  ,PASSWROD_WS
					FROM PARAMETRO_WS
					WHERE SISTEMA = 'SERVINDUS'";
			$result = $db->build_results($sql);
			
			$user_ws		= $result[0]['USER_WS'];
			$passwrod_ws	= $result[0]['PASSWROD_WS'];
			$url_ws			= $result[0]['URL_WS'];
   		 
   		$biggi = new client_biggi("$user_ws", "$passwrod_ws", "$url_ws");
   		$lista_item_oc 	= '';
   		
   		///////////--SE CONCATENA TODO EL SELECT CON COMA PARA ENVIARLO A cli_oc_facturada --\\\\\\\\\\\
   		$sql_item = "SELECT COD_ITEM_ORDEN_COMPRA FROM INF_OC_POR_FACTURAR_TDNX
			     WHERE COD_USUARIO = $cod_usuario";
		$result_item = $db->build_results($sql_item);
		
		for($i=0;$i<count($result_item);$i++){
			$lista_item_oc .= ','.$result_item[$i]['COD_ITEM_ORDEN_COMPRA'];
		}

   		////////////////-----------------------------------------------------\\\\\\\\\\\\\\\\\
		$result = $biggi->cli_oc_facturada_serv($lista_item_oc,K_CLIENTE);
		
		for($i=0;$i<count($result);$i++){
   			$param = $result[$i]['ITEM'].",".$result[$i]['CANT_FA'];
   			//se utiliza el mismo procedimiento de tdnx para servindus
   			if(K_CLIENTE == 'BODEGA')
	   			$db->EXECUTE_SP("spu_inf_oc_por_facturar_tdnx","$param");
	   		else if(K_CLIENTE == 'TODOINOX'){
	   			$db->EXECUTE_SP("spu_inf_oc_por_facturar_serv","$param");
	   		}	
   		}
   		 
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
				FROM inf_oc_por_facturar_tdnx
				WHERE COD_USUARIO = $cod_usuario
				AND CANT_POR_FACT > 0
				order by FECHA_ORDEN_COMPRA ASC, COD_ORDEN_COMPRA ASC";
		
		parent::w_informe_pantalla('inf_oc_por_facturar_servindus', $sql, $_REQUEST['cod_item_menu']);
		
		// headers
		$this->add_header(new header_num('COD_ORDEN_COMPRA', 'COD_ORDEN_COMPRA', 'N° OC'));
		$this->add_header(new header_date('FECHA_ORDEN_COMPRA', 'FECHA_ORDEN_COMPRA', 'Fecha OC'));

		$sql = "SELECT DISTINCT COD_USUARIO_VENDEDOR,NOM_USUARIO FROM INF_OC_POR_FACTURAR_TDNX order by COD_USUARIO_VENDEDOR";
		$this->add_header($control = new header_drop_down_string('COD_USUARIO_VENDEDOR', "COD_USUARIO_VENDEDOR", 'V1',$sql));
		$control->field_bd_order = 'NOM_USUARIO';
		$this->add_header(new header_text('COD_PRODUCTO', 'COD_PRODUCTO', 'Cod. Producto'));
		$this->add_header(new header_text('NOM_PRODUCTO', "NOM_PRODUCTO", 'Descripción'));
		$this->add_header(new header_num('CANTIDAD_OC', 'CANTIDAD_OC', 'Ct.'));
		$this->add_header(new header_num('CANT_FA', 'CANT_FA', 'Ct. Facturada'));
		$this->add_header(new header_num('CANT_POR_FACT', 'CANT_POR_FACT', 'Ct. por Facturar'));
		
		if(K_CLIENTE == 'BODEGA')
			$titulo = 'Solicitud';
		else if(K_CLIENTE == 'TODOINOX')
			$titulo = '-';
		
		$this->add_header(new header_num('COD_NOTA_VENTA', 'COD_NOTA_VENTA', $titulo));
	}
	function detalle_record($rec_no) {
		session::set('DESDE_wo_orden_compra', 'desde output');	// para indicar que viene del output
		session::set('DESDE_wo_inf_oc_por_facturar_servindus', 'true');
		$ROOT = $this->root_url;
		$url = $ROOT.'appl/orden_compra';
		header ('Location:'.$url.'/wi_orden_compra.php?rec_no='.$rec_no.'&cod_item_menu=1520');
	}
}
// Se separa por K_CLIENTE
$file_name = dirname(__FILE__)."/".K_CLIENTE."/class_wo_inf_oc_por_facturar_servindus.php";
if(file_exists($file_name)) 
	require_once($file_name);
else {
	class wo_inf_oc_por_facturar_servindus extends wo_inf_oc_por_facturar_servindus_base{
		function wo_inf_oc_por_facturar_servindus(){
			parent::wo_inf_oc_por_facturar_servindus_base(); 
		}
	}
}
?>