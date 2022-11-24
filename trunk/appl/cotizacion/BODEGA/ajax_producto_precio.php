<?php
require_once(dirname(__FILE__)."/../../../../../commonlib/trunk/php/auto_load.php");

$cod_producto = $_REQUEST['cod_producto'];
$cod_producto = urldecode($cod_producto);
$cod_empresa = $_REQUEST['cod_empresa'];

if($cod_empresa == '')
$cod_empresa = 0;

$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

$sql_emp = "SELECT COD_EMPRESA
		FROM PRECIO_INT_EMP
		WHERE COD_EMPRESA = $cod_empresa";
		
$result_emp = $db->build_results($sql_emp);
$emp= $result_emp[0]['COD_EMPRESA'];	
if(count($result_emp) != 0){		
	$sql = "SELECT  PRECIO_VENTA_INTERNO PRECIO_INT
			FROM 	PRODUCTO
			WHERE COD_PRODUCTO = '$cod_producto'";
		
			$result = $db->build_results($sql);
			$precio = $result[0]['PRECIO_INT'];
			
			if($precio == 0.00){
				$sql = "SELECT  PRECIO_VENTA_PUBLICO PRECIO_PUB
						FROM 	PRODUCTO
						WHERE COD_PRODUCTO = '$cod_producto'";
					
						$result = $db->build_results($sql);
						$precio = $result[0]['PRECIO_PUB'];
		}
}else{
	$sql = "SELECT  PRECIO_VENTA_PUBLICO PRECIO_PUB
			FROM 	PRODUCTO
			WHERE COD_PRODUCTO = '$cod_producto'";
		
	$result = $db->build_results($sql);
	$precio = $result[0]['PRECIO_PUB'];
}

$precio_interno = number_format($precio, 0, ',', '.');	// da formato al precio
print urlencode($precio_interno);
?>