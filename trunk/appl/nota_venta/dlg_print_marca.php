<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$cod_nota_venta = $_REQUEST['cod_nota_venta'];
$temp = new Template_appl('dlg_print_marca.htm');	
$temp->setVar("COD_NOTA_VENTA", $cod_nota_venta);

$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
$sql = "SELECT  COD_ITEM_NOTA_VENTA
				,ITEM
				,COD_PRODUCTO
				,NOM_PRODUCTO
				,CANTIDAD
				,0 CANTIDAD_MARCA				       
		FROM    ITEM_NOTA_VENTA
		WHERE COD_NOTA_VENTA = ".$cod_nota_venta;
$result = $db->build_results($sql);
for ($i=0; $i < count($result); $i++) {
	$temp->gotoNext("ITEM_NOTA_VENTA");	
	if ($i%2==0)
		$temp->setVar("ITEM_NOTA_VENTA.DW_TR_CSS", datawindow::css_claro);
	else
		$temp->setVar("ITEM_NOTA_VENTA.DW_TR_CSS", datawindow::css_oscuro);
		
	$temp->setVar("ITEM_NOTA_VENTA.DW_TR_ID", 'ITEM_NOTA_VENTA_'.$i);		
	$temp->setVar("ITEM_NOTA_VENTA.COD_ITEM_NOTA_VENTA", $result[$i]['COD_ITEM_NOTA_VENTA']);
	$temp->setVar("ITEM_NOTA_VENTA.ITEM", $result[$i]['ITEM']);
	$temp->setVar("ITEM_NOTA_VENTA.COD_PRODUCTO", $result[$i]['COD_PRODUCTO']);
	$temp->setVar("ITEM_NOTA_VENTA.NOM_PRODUCTO", $result[$i]['NOM_PRODUCTO']);
	$temp->setVar("ITEM_NOTA_VENTA.CANTIDAD", $result[$i]['CANTIDAD']);	
	$temp->setVar("ITEM_NOTA_VENTA.CANTIDAD_NV",'CANTIDAD_NV_'.$i);		
	$temp->setVar("ITEM_NOTA_VENTA.CANTIDAD_MARCA",'CANTIDAD_MARCA_'.$i);		
	$temp->setVar("ITEM_NOTA_VENTA.ITEM_NV",'ITEM_NV_'.$i);	
	$temp->setVar("ITEM_NOTA_VENTA.NRO_INICIO",$i);	
}

print $temp->toString();
?>