<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$cod_cotizacion = $_REQUEST['cod_cotizacion'];

$temp = new Template_appl('que_precio_usa.htm');	

$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
$sql = "select  I.ITEM,
								I.COD_PRODUCTO,
								I.NOM_PRODUCTO,
								I.PRECIO,
								P.PRECIO_VENTA_PUBLICO
			  FROM ITEM_COTIZACION I, PRODUCTO P
			  WHERE I.COD_COTIZACION = $cod_cotizacion AND
			  			P.COD_PRODUCTO = I.COD_PRODUCTO AND
			  			I.PRECIO <> P.PRECIO_VENTA_PUBLICO AND
			  			P.PRECIO_LIBRE = 'N'";
$result = $db->build_results($sql);
for($i=0; $i<count($result); $i++){						
	$temp->gotoNext('PRODUCTO');
	if ($i % 2 == 0)
		$temp->setVar("PRODUCTO.DW_TR_CSS", datawindow::css_claro);
	else
		$temp->setVar("PRODUCTO.DW_TR_CSS", datawindow::css_oscuro);
	$temp->setVar("PRODUCTO.ITEM", $result[$i]['ITEM']);
	$temp->setVar("PRODUCTO.COD_PRODUCTO", $result[$i]['COD_PRODUCTO']);
	$temp->setVar("PRODUCTO.NOM_PRODUCTO", $result[$i]['NOM_PRODUCTO']);
	$temp->setVar("PRODUCTO.PRECIO", number_format($result[$i]['PRECIO'], 0, ',', '.'));
	$temp->setVar("PRODUCTO.PRECIO_VENTA_PUBLICO", number_format($result[$i]['PRECIO_VENTA_PUBLICO'], 0, ',', '.'));
}
	
$K_COT_USA_PRECIO_COT = '990505';
$cod_usuario = session::get("COD_USUARIO");
if (w_base::tiene_privilegio_opcion_usuario($K_COT_USA_PRECIO_COT, $cod_usuario))
	$temp->setVar("W_AUTORIZA",'<input type="button" value="Precio Cot." onclick="setWindowReturnValue(null); closeMe();" class="button">');
		
print $temp->toString();
?>
