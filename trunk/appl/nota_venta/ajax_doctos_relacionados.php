<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$cod_item_nv = $_REQUEST["cod_item_nv"]; 
$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

$kl_estado_oc_anulada = 2;
$kl_estado_doc_impresa = 2;
$kl_estado_doc_enviada = 3;
$resultado = '';
//busca OC
$sql_oc = "SELECT DISTINCT (OC.COD_ORDEN_COMPRA) 
	FROM ITEM_ORDEN_COMPRA IT, ORDEN_COMPRA OC
			WHERE IT.COD_ITEM_NOTA_VENTA = $cod_item_nv
				AND OC.COD_ORDEN_COMPRA = IT.COD_ORDEN_COMPRA
				AND OC.COD_ESTADO_ORDEN_COMPRA <> $kl_estado_oc_anulada";

$result_oc = $db->build_results($sql_oc);
$cod_orden_compra = '';
$glosa_oc = '';
for($i=0;$i <count($result_oc);$i++){
	if ($i==0)
		$glosa_oc = "- OC: ";
	$cod_orden_compra .= $result_oc[$i]['COD_ORDEN_COMPRA'].',';
}
if (count($result_oc) > 0)
	$resultado = $glosa_oc.substr($cod_orden_compra, 0, -1). "\n";


//busca GD
$sql_gd = "SELECT DISTINCT (NRO_GUIA_DESPACHO)
		FROM ITEM_GUIA_DESPACHO IT, GUIA_DESPACHO GD
		WHERE IT.COD_ITEM_DOC = $cod_item_nv 
			AND TIPO_DOC = 'ITEM_NOTA_VENTA'
			AND GD.COD_GUIA_DESPACHO = IT.COD_GUIA_DESPACHO
			AND GD.COD_ESTADO_DOC_SII IN ($kl_estado_doc_impresa, $kl_estado_doc_enviada)";
			
$result_gd = $db->build_results($sql_gd);
$nro_guia_despacho = '';
$glosa_gd = '';
for($i=0;$i <count($result_gd);$i++){
	if ($i==0)
		$glosa_gd = "- GD: ";
	$nro_guia_despacho .= $result_gd[$i]['NRO_GUIA_DESPACHO'].',';
}
if (count($result_gd) > 0)
	$resultado .= $glosa_gd.substr($nro_guia_despacho, 0, -1). "\n";


//busca FA
//--desde GD
/* faltan las facturas desded la GD
$sql_fa_desde_gd = "SELECT * 
				FROM ITEM_FACTURA IT, FACTURA F
				WHERE IT.COD_ITEM_DOC = 11812 
					AND IT.TIPO_DOC = 'ITEM_GUIA_DESPACHO'
					AND F.COD_FACTURA = IT.COD_FACTURA
					AND F.COD_ESTADO_DOC_SII IN ($kl_estado_doc_impresa, $kl_estado_doc_enviada)";
*/
//--desde NV
$sql_fa_desde_nv = "SELECT DISTINCT (NRO_FACTURA)
				FROM ITEM_FACTURA IT, FACTURA F
				WHERE IT.COD_ITEM_DOC = $cod_item_nv 
					AND IT.TIPO_DOC = 'ITEM_NOTA_VENTA'
					AND F.COD_FACTURA = IT.COD_FACTURA
					AND F.COD_ESTADO_DOC_SII IN ($kl_estado_doc_impresa, $kl_estado_doc_enviada)";

$result_fa_desde_nv = $db->build_results($sql_fa_desde_nv);
$nro_factura = '';
$glosa_fa = '';
for($i=0;$i <count($result_fa_desde_nv);$i++){
	if ($i=0)
		$glosa_fa == "- FA: ";
	$nro_factura .= $result_fa_desde_nv[$i]['NRO_FACTURA'].',';
}
if (count($result_fa_desde_nv) > 0)
	$resultado .= $glosa_fa.substr($nro_factura, 0, -1). "\n";

print urlencode($resultado);
?>