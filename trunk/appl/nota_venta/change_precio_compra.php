<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$tabla = $_REQUEST['tabla'];
$cod_pre_orden_compra = $_REQUEST['cod_pre_orden_compra'];
$cod_producto = $_REQUEST['cod_producto'];
$precio = $_REQUEST['precio'];
$cod_proveedor = $_REQUEST['cod_proveedor'];

$temp = new Template_appl('change_precio.htm');	
$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
if ($cod_pre_orden_compra) {
	// Historial de cambio de precios
	$sql = "SELECT 	U.NOM_USUARIO,
					dbo.f_format_date(M.FECHA_MODIFICA, 2) FECHA_MODIFICA,
					M.PRECIO_ANTERIOR,
					M.PRECIO_NUEVO,
					M.MOTIVO 
			FROM USUARIO U, MODIFICA_PRECIO_ORDEN_COMPRA M
			WHERE M.COD_PRE_ORDEN_COMPRA = $cod_pre_orden_compra AND
					U.COD_USUARIO = M.COD_USUARIO";
	$result = $db->build_results($sql);
	$fields = $db->get_fields();
	for ($i=0 ; $i <count($result); $i++) {
		$temp->gotoNext("MODIFICA_PRECIO");		
	
		if ($i % 2 == 0)
			$temp->setVar("MODIFICA_PRECIO.DW_TR_CSS", datawindow::css_claro);
		else
			$temp->setVar("MODIFICA_PRECIO.DW_TR_CSS", datawindow::css_oscuro);
		
		$usuario = $result[$i]['NOM_USUARIO'];
		$temp->setVar("MODIFICA_PRECIO.NOM_USUARIO", $usuario);
		
		$fecha_mod = $result[$i]['FECHA_MODIFICA'];
		$temp->setVar("MODIFICA_PRECIO.FECHA_MODIFICA", $fecha_mod);
		
		$precio_old = $result[$i]['PRECIO_ANTERIOR'];
		$temp->setVar("MODIFICA_PRECIO.PRECIO_ANTERIOR", number_format($precio_old, 0, ',', '.'));
		
		$precio_new = $result[$i]['PRECIO_NUEVO'];
		$temp->setVar("MODIFICA_PRECIO.PRECIO_NUEVO", number_format($precio_new, 0, ',', '.'));
		
		$motivo = $result[$i]['MOTIVO'];
		$temp->setVar("MODIFICA_PRECIO.MOTIVO", $motivo);
	}
}

// precios min, max y actual
//PORC_MODIFICA_PRECIO

$cod_usuario = session::get("COD_USUARIO");
$sql = "SELECT PORC_MODIFICA_PRECIO_OC
				FROM USUARIO
				WHERE COD_USUARIO = $cod_usuario";
$result = $db->build_results($sql);
$porc_modifica_precio_oc = $result[0]['PORC_MODIFICA_PRECIO_OC'];

//PRECIO LISTA
		
$sql = "SELECT dbo.f_prod_get_precio_costo ('$cod_producto', $cod_proveedor, getdate()) PRECIO_COMPRA, 
			P.PRECIO_LIBRE 
		FROM PRODUCTO P, PRODUCTO_PROVEEDOR PP, EMPRESA E 
		WHERE PP.COD_PRODUCTO = '$cod_producto' AND
			P.COD_PRODUCTO = PP.COD_PRODUCTO AND 
			PP.ELIMINADO = 'N' AND 
			E.COD_EMPRESA = PP.COD_EMPRESA AND 
			E.COD_EMPRESA = $cod_proveedor"; 
						
$result = $db->build_results($sql);
$precio_lista = $result[0]['PRECIO_COMPRA'];
$precio_libre = $result[0]['PRECIO_LIBRE'];

$precio_min = round($precio_lista - ($precio_lista * $porc_modifica_precio_oc/100), 0);
$precio_max = round($precio_lista + ($precio_lista * $porc_modifica_precio_oc/100), 0);

if($precio_libre == 'S'){
	$precio_min = 0;
	$precio_max = 999999999;
}

$temp->setVar("PRECIO_MIN", number_format($precio_min, 0, ',', '.'));			
$temp->setVar("PRECIO_MIN_H", $precio_min);			
$temp->setVar("PRECIO_MAX", number_format($precio_max, 0, ',', '.'));			
$temp->setVar("PRECIO_MAX_H", $precio_max);			
$temp->setVar("PRECIO_ACTUAL", number_format($precio, 0, ',', ''));		
$temp->setVar("PRECIO_ACTUAL_H", number_format($precio, 0, ',', ''));			

print $temp->toString();
?>