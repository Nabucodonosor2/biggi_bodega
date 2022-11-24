<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$tipo_doc = $_REQUEST['tipo_doc'];
$nro_doc = $_REQUEST['nro_doc'];

if ($tipo_doc == 'FACTURA') 
	$sql = "SELECT 	COD_EMPRESA, 
					F.COD_FACTURA COD_DOC
			FROM 	FACTURA F
			WHERE 	NRO_FACTURA = $nro_doc";
elseif ($tipo_doc == 'GUIA_DESPACHO') 		
	$sql = "SELECT 	COD_EMPRESA, 
					GD.COD_GUIA_DESPACHO COD_DOC
			FROM 	GUIA_DESPACHO GD
			WHERE 	NRO_GUIA_DESPACHO = $nro_doc";
elseif ($tipo_doc == 'ARRIENDO') 		
	$sql = "SELECT 	COD_EMPRESA, 
					COD_ARRIENDO COD_DOC
			FROM 	ARRIENDO
			WHERE 	COD_ARRIENDO = $nro_doc";
else {
	print 'ERROR';
	return;		
}

$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
$result = $db->build_results($sql);
$row_count = $db->count_rows();
$respuesta = ''; 
if ($row_count == 0)
	$respuesta = "0|";
else{
	$respuesta = $result[0]['COD_DOC']."|";
	$respuesta =  $respuesta.$result[0]['COD_EMPRESA']."|";
}	
	
print $respuesta;
?>