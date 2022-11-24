<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$cod_producto = $_REQUEST['cod_producto'];
$cod_empresa = $_REQUEST['cod_empresa'];

if ($cod_empresa == '')//se crea la OC a mano y todavía no se ingresa el proveedor
	$sql_prod = "SELECT dbo.f_prod_get_precio_costo ('$cod_producto', dbo.f_nv_get_first_proveedor ('$cod_producto') , getdate()) PRECIO";
else
	$sql_prod = "SELECT dbo.f_prod_get_precio_costo ('$cod_producto', $cod_empresa , getdate()) PRECIO";

$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
$result = $db->build_results($sql_prod);
$respuesta = ''; 
if (count($result) == 0)
	$respuesta = '0';	// No hay datos
else
	$respuesta = $result[0]['PRECIO'];
		
print $respuesta;
?>