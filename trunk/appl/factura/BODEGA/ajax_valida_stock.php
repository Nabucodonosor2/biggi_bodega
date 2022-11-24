<?php
require_once(dirname(__FILE__)."/../../../../../commonlib/trunk/php/auto_load.php");
	
$cod_producto	= $_REQUEST['cod_producto'];
$cantidad		= $_REQUEST['cantidad'];
$cod_usuario	= session::get("COD_USUARIO");

if($cod_producto == '')
	 $cod_producto = 'NULL';	 
$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

$sql="SELECT COD_PERFIL
	  FROM USUARIO
	  WHERE COD_USUARIO = $cod_usuario";
$result	= $db->build_results($sql); 

$sql_autoriza="SELECT AUTORIZA_MENU
			   FROM AUTORIZA_MENU
			   WHERE COD_PERFIL = ".$result[0]['COD_PERFIL']."
			   AND COD_ITEM_MENU = '992050'";

$sql_stock="SELECT dbo.f_bodega_stock(COD_PRODUCTO, 2, GETDATE()) STOCK
		  		  ,MANEJA_INVENTARIO
		    FROM PRODUCTO
		    WHERE COD_PRODUCTO = '$cod_producto'";			   
			   
$result_autoriza	= $db->build_results($sql_autoriza);  
$result_stock		= $db->build_results($sql_stock);
	
if($result_autoriza[0]['AUTORIZA_MENU'] == 'E'){
	if($cod_producto <> 'E' &&				//EMBALAJE EN JABAS DE MADERA
		$cod_producto <> 'TE' &&			//TRABAJO ESPECIAL 
			$cod_producto <> 'I' &&			//INSTALACION Y PUESTA EN MARCHA
				$cod_producto <> 'F' &&		//FLETE
					$result_stock[0]['MANEJA_INVENTARIO'] <> 'N'){

		if($result_stock[0]['STOCK'] > 0){
			if($cantidad > $result_stock[0]['STOCK'])
				print 'ALERTA_MAYOR_CANTIDAD';				
			else
				print '';
		}else	
			print 'ALERTA_NO_TIENE_STOCK';							
						
	}else{
		print '';
	}
}else{
	if($cod_producto <> 'E' &&				//EMBALAJE EN JABAS DE MADERA
		$cod_producto <> 'TE' &&			//TRABAJO ESPECIAL 
			$cod_producto <> 'I' &&			//INSTALACION Y PUESTA EN MARCHA
				$cod_producto <> 'F' &&		//FLETE
					$result_stock[0]['MANEJA_INVENTARIO'] <> 'N'){
		if($result_stock[0]['STOCK'] > 0)
			if($cantidad > $result_stock[0]['STOCK'])
				print 'MAYOR_CANTIDAD';
			else
				print '';
		else{
			print 'NO_TIENE_STOCK';
		}	
	}else{
		print '';
	}		
}

?>