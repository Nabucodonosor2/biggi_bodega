<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
/**HACER COMMIT OFICIAL****/
$cod_guia_despacho = $_REQUEST["cod_guia_despacho"];

//RECUPERAMOS LOS DATOS GUARDADO EN LA BD
$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
$Sqlpdf = " SELECT  52 DTE_ORIGEN
					,NRO_GUIA_DESPACHO
					,REPLACE(REPLACE(dbo.f_get_parametro(20),'.',''),'-8','') as RUTEMISOR
			FROM GUIA_DESPACHO
			WHERE COD_GUIA_DESPACHO  = $cod_guia_despacho";
			
$Result_pdf = $db->build_results($Sqlpdf);

$dte_origen	= $Result_pdf[0]['DTE_ORIGEN'];
$folio	= $Result_pdf[0]['NRO_GUIA_DESPACHO'];
$emisor	= $Result_pdf[0]['RUTEMISOR'];

//LLamo a la nueva clase dte.
$dte = new dte();

//Se le pasa como variable hash de la clase obtenida en parametros en la BD
$SqlHash = "SELECT dbo.f_get_parametro(200) K_HASH";  
$Datos_Hash = $db->build_results($SqlHash);
$dte->hash = $Datos_Hash[0]['K_HASH'];

$response_pdf = $dte->post_genera_pdf($dte_origen,$folio,$emisor);

//revisamos esxiste un error
$ERROR = explode('ERROR' ,$response_pdf);

if($ERROR[1] == ''){
	//imprimimos el pdf
	$body = strstr($response_pdf, '%');
	$header_body = strstr($response_pdf, '%',true);

	$header_Type = explode('Content-Type:' ,$header_body);
	$header_Disposition = explode('Content-Disposition:',$header_Type[0]);
	$header_length = explode ('Content-Length:',$header_Disposition[0]);
	$name_pdf = explode('filename=',$header_Disposition[1]); 
	/*
	header('Content-Type: '.$header_Type[1]);
	header('Content-Disposition: inline; filename='.$name_pdf[1]);
	
	print $body;*/
	
	$fname = dirname(__FILE__)."/Guia de Despacho.pdf";
	$handle = fopen($fname,"w");
	fwrite($handle, $body);
	fclose($handle);

	require_once(dirname(__FILE__)."/../common_appl/FPDI-1.6.1/fpdi.php");
	
	$pdf = new FPDI();
	
	//page1
	$pageCount = $pdf->setSourceFile($fname);
	$tplIdx = $pdf->importPage(1, '/MediaBox');
	
	$pdf->addPage();
	$pdf->useTemplate($tplIdx);	//, 10, 10, 90);
	
	/*********RECTANGULOS PARA TAPAR******/
	$pdf->SetFillColor(255,255,255);
	$pdf->Rect(10, 52, 190, 25, "F");
	
	$pdf->SetFillColor(255,255,255);
	$pdf->Rect(9, 77, 193, 111, "F");
	
	/*****FIN RECTANGULOS PARA TAPAR***************/
	
	$pdf->SetFillColor(10,10,10);
	$pdf->Rect(11, 53, 189, 36, "");
	
	/******LINEA H 1*******/
	$pdf->Line(11,83,200,83);
	
	/******LINEA H 2*******/
	$pdf->Line(11,95,200,95);
	
	/******LINEA V 1*******/
	$pdf->Line(17,90,17,172);
	
	/******LINEA V 2*******/
	$pdf->Line(46,90,46,172);
	
	/******LINEA V 3*******/
	$pdf->Line(150,90,150,172);
	
	/******LINEA V 4*******/
	$pdf->Line(160,90,160,172);
	
	/******LINEA V 5*******/
	$pdf->Line(180,90,180,172);
	
	$pdf->SetFillColor(10,10,10);
	$pdf->Rect(11, 90, 189, 82, "");
	
	$pdf->SetFillColor(10,10,10);
	$pdf->Rect(11, 173, 161, 14, "");
	
	/*SELECT CABECERA*/
	$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

	$sql ="select CONVERT (varchar, GD.FECHA_GUIA_DESPACHO,103) FECHA_GUIA_DESPACHO
					,(CAST(GD.RUT AS NVARCHAR(8)))+'-'+(CAST (GD.DIG_VERIF AS NVARCHAR(1))) as RUT_COMPLETO 
					,GD.NOM_EMPRESA
					,GD.NRO_ORDEN_COMPRA
					,GD.DIRECCION
					,CI.NOM_CIUDAD
					,GD.GIRO
					,C.NOM_COMUNA
					,GD.TELEFONO
					,GD.MAIL
					,upper(TGD.NOM_TIPO_GUIA_DESPACHO)NOM_TIPO_GUIA_DESPACHO
					,GD.REFERENCIA
					,GD.OBS
			from GUIA_DESPACHO GD, COMUNA C, CIUDAD CI, TIPO_GUIA_DESPACHO TGD
			where GD.COD_COMUNA = C.COD_COMUNA
			and GD.COD_CIUDAD = CI.COD_CIUDAD
			and GD.COD_TIPO_GUIA_DESPACHO = TGD.COD_TIPO_GUIA_DESPACHO
			and GD.COD_GUIA_DESPACHO =  $cod_guia_despacho";
	$result = $db->build_results($sql);
	
	$FECHA_GUIA_DESPACHO	= $result[0]['FECHA_GUIA_DESPACHO'];
	$RUT_COMPLETO			= $result[0]['RUT_COMPLETO'];
	$NOM_EMPRESA			= $result[0]['NOM_EMPRESA'];
	$NRO_ORDEN_COMPRA		= $result[0]['NRO_ORDEN_COMPRA'];
	$DIRECCION				= $result[0]['DIRECCION'];
	$NOM_CIUDAD				= $result[0]['NOM_CIUDAD'];
	$GIRO					= $result[0]['GIRO'];
	$NOM_COMUNA				= $result[0]['NOM_COMUNA'];
	$TELEFONO				= $result[0]['TELEFONO'];
	$MAIL					= $result[0]['MAIL'];
	$NOM_TIPO_GUIA_DESPACHO	= $result[0]['NOM_TIPO_GUIA_DESPACHO'];
	$REFERENCIA				= $result[0]['REFERENCIA'];
	$OBS					= $result[0]['OBS'];

	/*** nueva estructura**/
    $pdf->SetFont('Arial','B',8);
	$pdf->SetXY(13, 42);
	$pdf->MultiCell(20, 30,"FECHA");
	$pdf->SetXY(35, 42);
	$pdf->MultiCell(20, 30,":");
	$pdf->SetFont('Arial','',8);
	$pdf->SetXY(38, 42);
	$pdf->MultiCell(20, 30,$FECHA_GUIA_DESPACHO);
	$pdf->SetXY(78, 42);
	$pdf->MultiCell(40, 30,"");
	$pdf->SetXY(102, 42);
	$pdf->MultiCell(20, 30,"");
	$pdf->SetFont('Arial','B',8);
	$pdf->SetXY(150, 42);
	$pdf->MultiCell(20, 30,"RUT.");
	$pdf->SetXY(165, 42);
	$pdf->MultiCell(20, 30,":");
	$pdf->SetFont('Arial','',8);
	$pdf->SetXY(170, 42);
	$pdf->MultiCell(40, 30,$RUT_COMPLETO);
	
	$pdf->SetFont('Arial','B',8);
	$pdf->SetXY(13, 46);
	$pdf->MultiCell(40, 30,"SEÑOR (ES)");
	$pdf->SetXY(35, 46);
	$pdf->MultiCell(20, 30,":");
	$pdf->SetFont('Arial','B',8);
	$pdf->SetXY(38, 59.5);
	$pdf->MultiCell(100, 3,$NOM_EMPRESA,0,"L");
	$pdf->SetFont('Arial','B',8);
	$pdf->SetXY(150, 46);
	$pdf->MultiCell(20, 30,"O.C.");
	$pdf->SetXY(165, 46);
	$pdf->MultiCell(20, 30,":");
	$pdf->SetFont('Arial','',8);
	$pdf->SetXY(170, 46);
	$pdf->MultiCell(40, 30,$NRO_ORDEN_COMPRA);
	
	$pdf->SetFont('Arial','B',8);
	$pdf->SetXY(13, 53);
	$pdf->MultiCell(40, 30,"DIRECCIÓN");
	$pdf->SetXY(35, 53);
	$pdf->MultiCell(20, 30,":");
	$pdf->SetFont('Arial','',8);
	$pdf->SetXY(38, 53);
	$pdf->MultiCell(100, 30,$DIRECCION);
	$pdf->SetFont('Arial','B',8);
	$pdf->SetXY(150, 53);
	$pdf->MultiCell(40, 30,"CIUDAD");
	$pdf->SetXY(165, 53);
	$pdf->MultiCell(20, 30,":");
	$pdf->SetFont('Arial','',8);
	$pdf->SetXY(170, 53);
	$pdf->MultiCell(40, 30,$NOM_CIUDAD);
	
	$pdf->SetFont('Arial','B',8);
	$pdf->SetXY(13, 57);
	$pdf->MultiCell(40, 30,"GIRO");
	$pdf->SetXY(35, 57);
	$pdf->MultiCell(20, 30,":");
	$pdf->SetFont('Arial','',8);
	$pdf->SetXY(38, 57);
	$pdf->MultiCell(100, 30,$GIRO);
	$pdf->SetFont('Arial','B',8);
	$pdf->SetXY(150, 57);
	$pdf->MultiCell(40, 30,"COMUNA");
	$pdf->SetXY(165, 57);
	$pdf->MultiCell(20, 30,":");
	$pdf->SetFont('Arial','',8);
	$pdf->SetXY(170, 57);
	$pdf->MultiCell(40, 30,$NOM_COMUNA);
	
	$pdf->SetFont('Arial','B',8);
	$pdf->SetXY(13, 61);
	$pdf->MultiCell(40, 30,"E-MAIL");
	$pdf->SetXY(35, 61);
	$pdf->MultiCell(20, 30,":");
	$pdf->SetFont('Arial','',8);
	$pdf->SetXY(38, 61);
	$pdf->MultiCell(100, 30,$MAIL);
	$pdf->SetFont('Arial','B',8);
	$pdf->SetXY(150, 61);
	$pdf->MultiCell(40, 30,"FONO");
	$pdf->SetXY(165, 61);
	$pdf->MultiCell(20, 30,":");
	$pdf->SetFont('Arial','',8);
	$pdf->SetXY(170, 61);
	$pdf->MultiCell(40, 30,$TELEFONO);
	
	$pdf->SetFont('Arial','B',8);
	$pdf->SetXY(13, 65);
	$pdf->MultiCell(40, 30,"TIPO DESPACHO");
	$pdf->SetXY(42, 65);
	$pdf->MultiCell(20, 30,":");
	$pdf->SetFont('Arial','',8);
	$pdf->SetXY(45, 65);
	$pdf->MultiCell(100, 30,$NOM_TIPO_GUIA_DESPACHO);
	
	$pdf->SetFont('Arial','B',8);
	$pdf->SetXY(13, 71);
	$pdf->MultiCell(40, 30,"REFERENCIA");
	$pdf->SetXY(35, 71);
	$pdf->MultiCell(20, 30,":");
	$pdf->SetXY(38, 71);
	$pdf->MultiCell(100, 30,$REFERENCIA);
	
	$pdf->SetFont('Arial','B',8);
	$pdf->SetXY(13, 161);
	$pdf->MultiCell(40, 30,"OBERVACIONES");
	$pdf->SetXY(37, 161);
	$pdf->MultiCell(20, 30,":");
	$pdf->SetFont('Arial','',8);
	$pdf->SetXY(40, 174);
	$pdf->MultiCell(133, 3,$OBS);
	
	/*No cuenta con totales*/
	
	/*************ITEMS***********/
	
	$pdf->SetFont('Arial','B',8);
	$pdf->SetXY(12, 78);
	$pdf->MultiCell(10, 30,"IT");
	$pdf->SetXY(25, 78);
	$pdf->MultiCell(20, 30,"MODELO");
	$pdf->SetXY(93, 78);
	$pdf->MultiCell(20, 30,"DETALLE");
	$pdf->SetXY(152, 78);
	$pdf->MultiCell(20, 30,"CT");
	$pdf->SetXY(164, 78);
	$pdf->MultiCell(20, 30,"P.UNIT.");
	$pdf->SetXY(184, 78);
	$pdf->MultiCell(20, 30,"TOTAL");
	
	$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

	$SqlDetalles ="SELECT ROW_NUMBER()OVER(ORDER BY PRECIO DESC) AS NroLinDet
                   			,('INT1')AS TpoCodigo
                   			,ITGD.NOM_PRODUCTO AS NmbItem
                   			,LEN (ITGD.NOM_PRODUCTO) LENT
                   			,ITGD.COD_PRODUCTO AS VlrCodigo
                   			,LEN (ITGD.COD_PRODUCTO) LENTM
                   			,ITGD.CANTIDAD
                     		,ITGD.PRECIO
                            ,(ITGD.CANTIDAD * ITGD.PRECIO) AS MONTO_TOTAL
                    FROM ITEM_GUIA_DESPACHO ITGD
					WHERE ITGD.COD_GUIA_DESPACHO = $cod_guia_despacho";
	$result2 = $db->build_results($SqlDetalles);
	
	$x = 1;
	$i = 2;
	$y = $pdf->GetY()-21; 
		
	foreach($result2 as $row){
		$pdf->SetFont('Arial','',8);
		$pdf->SetXY($x+8, $y+(5*$i));
		$pdf->MultiCell(10, 3, $row['NroLinDet'],0,'C');
		$pdf->SetXY($x+17, $y+(5*$i));
		$pdf->MultiCell(27, 3, $row['VlrCodigo'],0,'C');
		$pdf->SetXY($x+45, $y+(5*$i));
		$pdf->MultiCell(101, 3, $row['NmbItem'],0,'L');
		$pdf->SetXY($x+148, $y+(5*$i));
		$pdf->MultiCell(12, 3, $row['CANTIDAD'],0,'C');
		$pdf->SetXY($x+155, $y+(5*$i));
		$pdf->MultiCell(24, 3, number_format($row['PRECIO'],0,'.','.'),0,'R');
		$pdf->SetXY($x+175, $y+(5*$i));
		$pdf->MultiCell(24, 3, number_format($row['MONTO_TOTAL'],0,'.','.'),0,'R');
		
		if($row['LENTM']>16 && $row['LENTM']<32){
			$i = $i+0.5;      
		}else if ($row['LENTM']> 32){
			$i = $i+1.5;       
		}     
		if($row['LENT']>60&& $row['LENT']<120){
			$i = $i+0.5;      
		}else if ($row['LENT']> 120){
			$i = $i+1.5;       
		}
		
		$i++;			
	}
	
	
	//page2
	//$tplIdx = $pdf->importPage(2, '/MediaBox');
	$pdf->addPage();
	$pdf->useTemplate($tplIdx);	
	
	/*********RECTANGULOS PARA TAPAR******/
	$pdf->SetFillColor(255,255,255);
	$pdf->Rect(10, 52, 190, 25, "F");
	
	$pdf->SetFillColor(255,255,255);
	$pdf->Rect(9, 77, 193, 111, "F");
	
	/*****FIN RECTANGULOS PARA TAPAR***************/
	
	$pdf->SetFillColor(10,10,10);
	$pdf->Rect(11, 53, 189, 36, "");
	
	/******LINEA H 1*******/
	$pdf->Line(11,83,200,83);
	
	/******LINEA H 2*******/
	$pdf->Line(11,95,200,95);
	
	/******LINEA V 1*******/
	$pdf->Line(17,90,17,172);
	
	/******LINEA V 2*******/
	$pdf->Line(46,90,46,172);
	
	/******LINEA V 3*******/
	$pdf->Line(150,90,150,172);
	
	/******LINEA V 4*******/
	$pdf->Line(160,90,160,172);
	
	/******LINEA V 5*******/
	$pdf->Line(180,90,180,172);
	
	$pdf->SetFillColor(10,10,10);
	$pdf->Rect(11, 90, 189, 82, "");
	
	$pdf->SetFillColor(10,10,10);
	$pdf->Rect(11, 173, 161, 14, "");
	
	/*** nueva estructura**/
	$pdf->SetFont('Arial','B',8);
	$pdf->SetXY(13, 42);
	$pdf->MultiCell(20, 30,"FECHA");
	$pdf->SetXY(35, 42);
	$pdf->MultiCell(20, 30,":");
	$pdf->SetFont('Arial','',8);
	$pdf->SetXY(38, 42);
	$pdf->MultiCell(20, 30,$FECHA_GUIA_DESPACHO);
	$pdf->SetXY(78, 42);
	$pdf->MultiCell(40, 30,"");
	$pdf->SetXY(102, 42);
	$pdf->MultiCell(20, 30,"");
	$pdf->SetFont('Arial','B',8);
	$pdf->SetXY(150, 42);
	$pdf->MultiCell(20, 30,"RUT.");
	$pdf->SetXY(165, 42);
	$pdf->MultiCell(20, 30,":");
	$pdf->SetFont('Arial','',8);
	$pdf->SetXY(170, 42);
	$pdf->MultiCell(40, 30,$RUT_COMPLETO);
	
	$pdf->SetFont('Arial','B',8);
	$pdf->SetXY(13, 46);
	$pdf->MultiCell(40, 30,"SEÑOR (ES)");
	$pdf->SetXY(35, 46);
	$pdf->MultiCell(20, 30,":");
	$pdf->SetFont('Arial','B',8);
	$pdf->SetXY(38, 59.5);
	$pdf->MultiCell(100, 3,$NOM_EMPRESA,0,"L");
	$pdf->SetFont('Arial','B',8);
	$pdf->SetXY(150, 46);
	$pdf->MultiCell(20, 30,"O.C.");
	$pdf->SetXY(165, 46);
	$pdf->MultiCell(20, 30,":");
	$pdf->SetFont('Arial','',8);
	$pdf->SetXY(170, 46);
	$pdf->MultiCell(40, 30,$NRO_ORDEN_COMPRA);
	
	$pdf->SetFont('Arial','B',8);
	$pdf->SetXY(13, 53);
	$pdf->MultiCell(40, 30,"DIRECCIÓN");
	$pdf->SetXY(35, 53);
	$pdf->MultiCell(20, 30,":");
	$pdf->SetFont('Arial','',8);
	$pdf->SetXY(38, 53);
	$pdf->MultiCell(100, 30,$DIRECCION);
	$pdf->SetFont('Arial','B',8);
	$pdf->SetXY(150, 53);
	$pdf->MultiCell(40, 30,"CIUDAD");
	$pdf->SetXY(165, 53);
	$pdf->MultiCell(20, 30,":");
	$pdf->SetFont('Arial','',8);
	$pdf->SetXY(170, 53);
	$pdf->MultiCell(40, 30,$NOM_CIUDAD);
	
	$pdf->SetFont('Arial','B',8);
	$pdf->SetXY(13, 57);
	$pdf->MultiCell(40, 30,"GIRO");
	$pdf->SetXY(35, 57);
	$pdf->MultiCell(20, 30,":");
	$pdf->SetFont('Arial','',8);
	$pdf->SetXY(38, 57);
	$pdf->MultiCell(100, 30,$GIRO);
	$pdf->SetFont('Arial','B',8);
	$pdf->SetXY(150, 57);
	$pdf->MultiCell(40, 30,"COMUNA");
	$pdf->SetXY(165, 57);
	$pdf->MultiCell(20, 30,":");
	$pdf->SetFont('Arial','',8);
	$pdf->SetXY(170, 57);
	$pdf->MultiCell(40, 30,$NOM_COMUNA);
	
	$pdf->SetFont('Arial','B',8);
	$pdf->SetXY(13, 61);
	$pdf->MultiCell(40, 30,"E-MAIL");
	$pdf->SetXY(35, 61);
	$pdf->MultiCell(20, 30,":");
	$pdf->SetFont('Arial','',8);
	$pdf->SetXY(38, 61);
	$pdf->MultiCell(100, 30,$MAIL);
	$pdf->SetFont('Arial','B',8);
	$pdf->SetXY(150, 61);
	$pdf->MultiCell(40, 30,"FONO");
	$pdf->SetXY(165, 61);
	$pdf->MultiCell(20, 30,":");
	$pdf->SetFont('Arial','',8);
	$pdf->SetXY(170, 61);
	$pdf->MultiCell(40, 30,$TELEFONO);
	
	$pdf->SetFont('Arial','B',8);
	$pdf->SetXY(13, 65);
	$pdf->MultiCell(40, 30,"TIPO DESPACHO");
	$pdf->SetXY(42, 65);
	$pdf->MultiCell(20, 30,":");
	$pdf->SetFont('Arial','',8);
	$pdf->SetXY(45, 65);
	$pdf->MultiCell(100, 30,$NOM_TIPO_GUIA_DESPACHO);
	
	$pdf->SetFont('Arial','B',8);
	$pdf->SetXY(13, 71);
	$pdf->MultiCell(40, 30,"REFERENCIA");
	$pdf->SetXY(35, 71);
	$pdf->MultiCell(20, 30,":");
	$pdf->SetXY(38, 71);
	$pdf->MultiCell(100, 30,$REFERENCIA);
	
	$pdf->SetFont('Arial','B',8);
	$pdf->SetXY(13, 161);
	$pdf->MultiCell(40, 30,"OBERVACIONES");
	$pdf->SetXY(37, 161);
	$pdf->MultiCell(20, 30,":");
	$pdf->SetFont('Arial','',8);
	$pdf->SetXY(40, 174);
	$pdf->MultiCell(133, 3,$OBS);
		
	$pdf->SetFont('Arial','B',8);
	$pdf->SetXY(12, 78);
	$pdf->MultiCell(10, 30,"IT");
	$pdf->SetXY(25, 78);
	$pdf->MultiCell(20, 30,"MODELO");
	$pdf->SetXY(93, 78);
	$pdf->MultiCell(20, 30,"DETALLE");
	$pdf->SetXY(152, 78);
	$pdf->MultiCell(20, 30,"CT");
	$pdf->SetXY(164, 78);
	$pdf->MultiCell(20, 30,"P.UNIT.");
	$pdf->SetXY(184, 78);
	$pdf->MultiCell(20, 30,"TOTAL");
	
	/*************ITEMS***********/
	$x = 1;
	$i = 2;
	$y = $pdf->GetY()-21; 
		
	foreach($result2 as $row){
		$pdf->SetFont('Arial','',8);
		$pdf->SetXY($x+8, $y+(5*$i));
		$pdf->MultiCell(10, 3, $row['NroLinDet'],0,'C');
		$pdf->SetXY($x+17, $y+(5*$i));
		$pdf->MultiCell(27, 3, $row['VlrCodigo'],0,'C');
		$pdf->SetXY($x+45, $y+(5*$i));
		$pdf->MultiCell(101, 3, $row['NmbItem'],0,'L');
		$pdf->SetXY($x+148, $y+(5*$i));
		$pdf->MultiCell(12, 3, $row['CANTIDAD'],0,'C');
		$pdf->SetXY($x+155, $y+(5*$i));
		$pdf->MultiCell(24, 3, number_format($row['PRECIO'],0,'.','.'),0,'R');
		$pdf->SetXY($x+175, $y+(5*$i));
		$pdf->MultiCell(24, 3, number_format($row['MONTO_TOTAL'],0,'.','.'),0,'R');
		
		if($row['LENTM']>16 && $row['LENTM']<32){
			$i = $i+0.5;      
		}else if ($row['LENTM']> 32){
			$i = $i+1.5;       
		}     
		if($row['LENT']>60&& $row['LENT']<120){
			$i = $i+0.5;      
		}else if ($row['LENT']> 120){
			$i = $i+1.5;       
		}
		
		$i++;
	}
	
	$pdf->Output();
	
}else{
	print $response_pdf;
}
?>