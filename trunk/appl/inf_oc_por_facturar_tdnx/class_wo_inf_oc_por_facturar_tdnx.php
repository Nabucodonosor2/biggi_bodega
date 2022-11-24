
<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../ws_client_biggi/class_client_biggi.php");
include(dirname(__FILE__)."/../../appl.ini");

class wo_inf_oc_por_facturar_tdnx_base extends w_informe_pantalla {
	function wo_inf_oc_por_facturar_tdnx_base() 
	{
		$inventario = session::get("inf_oc_por_facturar_tdnx.INVENTARIO");
		session::un_set("inf_oc_por_facturar_tdnx.INVENTARIO");
		/////////////////////////////////////////////////////////
		$cod_usuario = session::get("COD_USUARIO");
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
   		$db->EXECUTE_SP("spi_inf_oc_por_facturar_tdnx", "$cod_usuario,$inventario");
		////////////////////////////////////////////////////////
		
   		///////////////////////////// COMUNICACION CON WEB SERVICE ///////////////////////////
   		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
			$sql = "select SISTEMA, URL_WS, USER_WS,PASSWROD_WS  from PARAMETRO_WS
					where SISTEMA = 'TODOINOX' ";
			$result = $db->build_results($sql);
			
			$user_ws		= $result[0]['USER_WS'];
			$passwrod_ws	= $result[0]['PASSWROD_WS'];
			$url_ws			= $result[0]['URL_WS'];
   		 
   		$biggi = new client_biggi("$user_ws", "$passwrod_ws", "$url_ws");
   		$lista_item_oc 	= '';
   		
   		///////////--SE CONCATENA TODO EL SELECT CON COMA PARA ENVIARLO A cli_oc_facturada --\\\\\\\\\\\
   		
   		$sql_item = "SELECT COD_ITEM_ORDEN_COMPRA FROM INF_OC_POR_FACTURAR_TDNX";
		$result_item = $db->build_results($sql_item);
		
		for($i=0;$i<count($result_item);$i++)
		{
			$lista_item_oc .= ','.$result_item[$i]['COD_ITEM_ORDEN_COMPRA'];
		}
   		
   		////////////////-----------------------------------------------------\\\\\\\\\\\\\\\\\
		$result = $biggi->cli_oc_facturada($lista_item_oc,K_CLIENTE);
		
		for($i=0;$i<count($result);$i++)
   		{
   			$param = $result[$i]['ITEM'].",".$result[$i]['CANT_FA'];
   			$db->EXECUTE_SP("spu_inf_oc_por_facturar_tdnx ","$param");
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
				order by COD_ORDEN_COMPRA DESC";
		
		parent::w_informe_pantalla('inf_oc_por_facturar_tdnx', $sql, $_REQUEST['cod_item_menu']);
		
		// headers
		$this->add_header(new header_num('COD_ORDEN_COMPRA', 'COD_ORDEN_COMPRA', 'NUM OC'));
		$this->add_header(new header_date('FECHA_ORDEN_COMPRA', 'FECHA_ORDEN_COMPRA', 'Fecha OC'));
		$this->add_header(new header_num('COD_NOTA_VENTA', 'COD_NOTA_VENTA', 'Cod. Solicitud'));
		$this->add_header(new header_text('COD_USUARIO_VENDEDOR', "COD_USUARIO_VENDEDOR", 'V1'));
		$this->add_header(new header_num('COD_PRODUCTO', 'COD_PRODUCTO', 'Num productoC'));
		$this->add_header(new header_text('NOM_PRODUCTO', "NOM_PRODUCTO", 'Nombre Product'));
		$this->add_header(new header_num('CANTIDAD_OC', 'CANTIDAD_OC', 'Cant OC'));
		$this->add_header(new header_text('CANT_FA', 'CANT_FA', 'Cant Facturada'));
		$this->add_header(new header_text('CANT_POR_FACT', 'CANT_POR_FACT', 'Cant Por Facturar'));
	}
	function detalle_record($rec_no) {
		session::set('DESDE_wo_orden_compra', 'desde output');	// para indicar que viene del output
		session::set('DESDE_wo_inf_oc_por_facturar_tdnx', 'true');
		$ROOT = $this->root_url;
		$url = $ROOT.'appl/orden_compra';
		header ('Location:'.$url.'/wi_orden_compra.php?rec_no='.$rec_no.'&cod_item_menu=1520');
	}

}
// Se separa por K_CLIENTE
$file_name = dirname(__FILE__)."/".K_CLIENTE."/class_wo_inf_oc_por_facturar_tdnx.php";
if (file_exists($file_name)) 
	require_once($file_name);
else {
	class wo_inf_oc_por_facturar_tdnx extends wo_inf_oc_por_facturar_tdnx_base {
		function wo_inf_oc_por_facturar_tdnx() {
			parent::wo_inf_oc_por_facturar_tdnx_base(); 
		}
	}
}
?>