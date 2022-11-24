<?php
require_once(dirname(__FILE__)."/../../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../../ws_client_biggi/class_client_biggi.php");

$cantidad			= str_replace(",", ".", $_REQUEST["cantidad"]);
$cod_item_doc		= $_REQUEST["cod_item_doc"];
$cod_item_factura	= $_REQUEST["cod_item_factura"];
$cod_orden_compra	= $_REQUEST["cod_orden_compra"];
$ws_origen			= $_REQUEST["ws_origen"];
$cod_producto		= $_REQUEST["cod_producto"];
$cantidad_max		= 0;
$cod_usuario		= session::get("COD_USUARIO");

//Validar si tiene stock
$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
$sql = "SELECT COD_PERFIL 
		FROM USUARIO 
		WHERE COD_USUARIO = $cod_usuario";
$result = $db->build_results($sql);
$cod_perfil = $result[0]['COD_PERFIL'];

$sql="SELECT AUTORIZA_MENU
   	  FROM AUTORIZA_MENU
   	  WHERE COD_PERFIL = $cod_perfil
   	  AND COD_ITEM_MENU = '992050'";
$result_aut	= $db->build_results($sql);

if($result_aut[0]['AUTORIZA_MENU'] == 'N'){
	$sql="SELECT dbo.f_bodega_stock('$cod_producto', 2, GETDATE()) STOCK";
	$result = $db->build_results($sql);
	
	if($cantidad > $result[0]['STOCK'])
		return print 'STOCK';
}	
//////////////////////////////////////////////
	
//obtener la cantidad de la OC
if($ws_origen == 'TODOINOX'){
	$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
	$sql = "select SISTEMA, URL_WS, USER_WS,PASSWROD_WS  from PARAMETRO_WS
			where SISTEMA = 'TODOINOX' ";
	$result = $db->build_results($sql);
	
	$user_ws		= $result[0]['USER_WS'];
	$passwrod_ws	= $result[0]['PASSWROD_WS'];
	$url_ws			= $result[0]['URL_WS'];
	
	$biggi = new client_biggi("$user_ws", "$passwrod_ws", "$url_ws");
}
if($ws_origen == 'COMERCIAL'){
	$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
	$sql = "select SISTEMA, URL_WS, USER_WS,PASSWROD_WS  from PARAMETRO_WS
			where SISTEMA = 'COMERCIAL' ";
	$result = $db->build_results($sql);
	
	$user_ws		= $result[0]['USER_WS'];
	$passwrod_ws	= $result[0]['PASSWROD_WS'];
	$url_ws			= $result[0]['URL_WS'];
	
	$biggi = new client_biggi("$user_ws", "$passwrod_ws", "$url_ws");
}
if($ws_origen == 'RENTAL'){
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
				
for ($i=0; $i < count($result['ITEM_ORDEN_COMPRA']); $i++){
	$cod_item_oc = $result['ITEM_ORDEN_COMPRA'][$i]['COD_ITEM_ORDEN_COMPRA'];
	if($cod_item_doc == $cod_item_oc)
		$cantidad_max = $result['ITEM_ORDEN_COMPRA'][$i]['CANTIDAD'];
}

$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
if ($cod_item_factura == ''){
	$sql = "SELECT $cantidad_max - ISNULL(SUM(CANTIDAD), 0) CANTIDAD 
			FROM ITEM_FACTURA
			WHERE COD_ITEM_DOC = $cod_item_doc";
			
	if($ws_origen == 'TODOINOX')
		$sql .= " AND TIPO_DOC = 'ITEM_ORDEN_COMPRA_TODOINOX'";
	else if($ws_origen == 'COMERCIAL')
		$sql .= " AND TIPO_DOC = 'ITEM_ORDEN_COMPRA_COMERCIAL'";
	else //RENTAL
		$sql .= " AND TIPO_DOC = 'ITEM_ORDEN_COMPRA_RENTAL'";
}else{
	$sql = "SELECT (SELECT $cantidad_max - ISNULL(SUM(CANTIDAD), 0) 
					FROM ITEM_FACTURA
					WHERE COD_ITEM_DOC = $cod_item_doc";
					
	if($ws_origen == 'TODOINOX')
		$sql .= " AND TIPO_DOC = 'ITEM_ORDEN_COMPRA_TODOINOX'";
	else if($ws_origen == 'COMERCIAL')
		$sql .= " AND TIPO_DOC = 'ITEM_ORDEN_COMPRA_COMERCIAL'";
	else //RENTAL
		$sql .= " AND TIPO_DOC = 'ITEM_ORDEN_COMPRA_RENTAL'";
									
	$sql.=") + I.CANTIDAD CANTIDAD
		   FROM ITEM_FACTURA I
		   WHERE COD_ITEM_FACTURA = $cod_item_factura";
}		   

$result_cant = $db->build_results($sql);		
if($cantidad > $result_cant[0]['CANTIDAD'])
	print 'ES_MAYOR|'.$result_cant[0]['CANTIDAD'];
else
	print 'ES_MENOR';
?>