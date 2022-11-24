<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$tabla = $_REQUEST['tabla'];
$cod_item = $_REQUEST['cod_item'];
$cod_producto = $_REQUEST['cod_producto'];
$precio = $_REQUEST['precio'];

$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

// Usado en NV para colsultar las modificaciones precio y autorizaciones de TE en modo lectura
if ($cod_producto=='__SOLO_CONSULTA__' && $tabla=='ITEM_NOTA_VENTA') {
	$sql ="select COD_PRODUCTO
					,PRECIO
			from ITEM_NOTA_VENTA
			where COD_ITEM_NOTA_VENTA = $cod_item";	
	$result = $db->build_results($sql);
	$cod_producto = $result[0]['COD_PRODUCTO'];
	$precio = $result[0]['PRECIO'];
	$hablitar = false;
}
else 
	$hablitar = true;


$temp = new Template_appl('change_precio.htm');	
if ($cod_item) {
	// Historial de cambio de precios
	$sql = "SELECT 	U.NOM_USUARIO,
					dbo.f_format_date(M.FECHA_MODIFICA, 2) FECHA_MODIFICA,
					M.PRECIO_ANTERIOR,
					M.PRECIO_NUEVO,
					M.MOTIVO 
			FROM USUARIO U, ";
	if ($tabla=='ITEM_COTIZACION')
		$sql .= "MODIFICA_PRECIO_COTIZACION M 
			WHERE M.COD_ITEM_COTIZACION = $cod_item AND ";
	elseif ($tabla=='ITEM_NOTA_VENTA')
		$sql .= "MODIFICA_PRECIO_NOTA_VENTA M  
			WHERE M.COD_ITEM_NOTA_VENTA = $cod_item AND ";
	elseif ($tabla=='ITEM_COT_ARRIENDO')
		$sql .= "MODIFICA_PRECIO_COT_ARRIENDO M 
			WHERE M.COD_ITEM_COT_ARRIENDO = $cod_item AND ";
	$sql .= "U.COD_USUARIO = M.COD_USUARIO
			order by M.FECHA_MODIFICA desc";
	
	$dw_historial = new datawindow($sql, 'MODIFICA_PRECIO');
	$dw_historial->retrieve();
	$dw_historial->habilitar($temp, false);
}

// precios min, max y actual
//PORC_MODIFICA_PRECIO

$cod_usuario = session::get("COD_USUARIO");
$sql = "SELECT PORC_MODIFICA_PRECIO
				FROM USUARIO
				WHERE COD_USUARIO = $cod_usuario";
$result = $db->build_results($sql);
$porc_modifica_precio = $result[0]['PORC_MODIFICA_PRECIO'];

//PRECIO LISTA
$sql = "SELECT 	PRECIO_VENTA_PUBLICO,
				PRECIO_LIBRE,
				NOM_PRODUCTO
		FROM 	PRODUCTO
		WHERE 	COD_PRODUCTO = '$cod_producto'";
$result = $db->build_results($sql);
$precio_lista = $result[0]['PRECIO_VENTA_PUBLICO'];
$precio_libre = $result[0]['PRECIO_LIBRE'];
$nom_producto = $result[0]['NOM_PRODUCTO'];

$precio_min = round($precio_lista - ($precio_lista * $porc_modifica_precio/100), 0);
$precio_max = round($precio_lista + ($precio_lista * $porc_modifica_precio/100), 0);

if($precio_libre == 'S'){
	$precio_min = 0;
	$precio_max = 999999999;
}

$sql = "select $precio_min PRECIO_MIN
				,$precio_min PRECIO_MIN_H
				,$precio_max PRECIO_MAX
				,$precio_max PRECIO_MAX_H
				,$precio PRECIO_NEW
				,$precio PRECIO_ACTUAL_H
				,null MOTIVO_NEW
				,null DISABLE_OK";
$dw = new datawindow($sql);
$dw->add_control(new edit_precio('PRECIO_NEW'));
$dw->add_control(new static_num('PRECIO_MIN'));
$dw->add_control(new static_num('PRECIO_MAX'));
$dw->add_control(new edit_text('PRECIO_MIN_H', 10, 10, 'hidden'));
$dw->add_control(new edit_text('PRECIO_MAX_H', 10, 10, 'hidden'));
$dw->add_control(new edit_text('PRECIO_ACTUAL_H', 10, 10, 'hidden'));
$dw->add_control(new edit_text_multiline('MOTIVO_NEW', 60, 2));
$dw->retrieve();
if (!$hablitar) 
	$dw->set_item(0, 'DISABLE_OK', 'disabled');

$temp->setVar("COD_PRODUCTO", $cod_producto);
$temp->setVar("NOM_PRODUCTO", $nom_producto);	
$dw->habilitar($temp, $hablitar);

print $temp->toString();
?>