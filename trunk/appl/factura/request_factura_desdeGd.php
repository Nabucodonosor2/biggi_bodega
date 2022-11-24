<?php

require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$cod_nota_venta = $_REQUEST['cod_nota_venta'];
$temp = new Template_appl('request_factura_desdeGd.htm');	
$temp->setVar("COD_NOTA_VENTA", $cod_nota_venta);
$K_ESTADO_CONFIRMADA = 4;
$K_ESTADO_CERRADA	 = 2;
$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

//valida que la NV este confirmada
$sql = "select * from NOTA_VENTA 
		where COD_NOTA_VENTA = ".$cod_nota_venta." 
		and	COD_ESTADO_NOTA_VENTA in(".$K_ESTADO_CONFIRMADA.", ".$K_ESTADO_CERRADA.")";
$result = $db->build_results($sql);
if (count($result) == 0){
	print '<html><body bgcolor="#D8D8D8" style="font-size:20;">Los Documentos asociados a Guías de Despacho no están Confirmadas.</body></html>';
}else{
	//busca las GD de la nota de venta
	$sql = "select  COD_GUIA_DESPACHO 
				,NRO_GUIA_DESPACHO
				,dbo.f_format_date(FECHA_GUIA_DESPACHO, 1) FECHA_GUIA_DESPACHO 
				,REFERENCIA 
				,NOM_EMPRESA
			from guia_despacho 
			where cod_doc=".$cod_nota_venta."
			  and cod_estado_doc_sii in (2,3)
			  and dbo.f_gd_pdte_por_facturar(COD_GUIA_DESPACHO) = 'S'";
					
	$result = $db->build_results($sql);
	if (count($result)==0)
		print '<html><body bgcolor="#D8D8D8" style="font-size:20;">No se han encontrado datos</body></html>';
	else
	{
		for ($i=0; $i < count($result); $i++) {
			$temp->gotoNext("GUIA_DESPACHO");	
			if ($i%2==0)
				$temp->setVar("GUIA_DESPACHO.DW_TR_CSS", datawindow::css_claro);
			else
				$temp->setVar("GUIA_DESPACHO.DW_TR_CSS", datawindow::css_oscuro);
				
				$temp->setVar("GUIA_DESPACHO.i", $i);
				$temp->setVar("GUIA_DESPACHO.DW_TR_ID", 'GUIA_DESPACHO_'.$i);	
				$temp->setVar("GUIA_DESPACHO.COD_GUIA_DESPACHO", $result[$i]['COD_GUIA_DESPACHO']);
				$temp->setVar("GUIA_DESPACHO.NRO_GUIA_DESPACHO", $result[$i]['NRO_GUIA_DESPACHO']);
				$temp->setVar("GUIA_DESPACHO.FECHA_GUIA_DESPACHO", $result[$i]['FECHA_GUIA_DESPACHO']);
				$temp->setVar("GUIA_DESPACHO.REFERENCIA", $result[$i]['REFERENCIA']);	
				$temp->setVar("GUIA_DESPACHO.NOM_EMPRESA", $result[$i]['NOM_EMPRESA']);				
		}
		
		print $temp->toString();	
	}
}

?>