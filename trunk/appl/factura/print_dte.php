<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
$K_PARAM_HASH = 200;
$cod_factura = $_REQUEST["cod_factura"];
/**HACER COMMIT OFICIAL****/
//RECUPERAMOS LOS DATOS GUARDADO EN LA BD
$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
$Sqlpdf = " SELECT  33 DTE_ORIGEN
					,NRO_FACTURA
					,REPLACE(REPLACE(dbo.f_get_parametro(20),'.',''),'-8','') as RUTEMISOR
			FROM FACTURA
			WHERE COD_FACTURA  = $cod_factura";  
$Result_pdf = $db->build_results($Sqlpdf);

$dte_origen	= $Result_pdf[0]['DTE_ORIGEN'];
$folio	= $Result_pdf[0]['NRO_FACTURA'];
$emisor	= $Result_pdf[0]['RUTEMISOR'];

//LLamo a la nueva clase dte.
$dte = new dte();
$SqlHash = "SELECT dbo.f_get_parametro($K_PARAM_HASH) K_HASH";  
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
	
	$fname = dirname(__FILE__)."/Factura.pdf";
	$handle = fopen($fname,"w");
	fwrite($handle, $body);
	fclose($handle);

	require_once(dirname(__FILE__)."/../common_appl/FPDI-1.6.1/fpdi.php");
	
	$pdf = new FPDI();
	
	$pageCount = $pdf->setSourceFile($fname);
	$tplIdx = $pdf->importPage(1, '/MediaBox');
	
	$pdf->addPage();
	$pdf->useTemplate($tplIdx);	//, 10, 10, 90);
	
	/*********RECTANGULOS PARA TAPAR******/
	/*Cabecera*/
	$pdf->SetFillColor(255,255,255);
	$pdf->Rect(10, 50, 190, 25, "F");
	/*item*/
	$pdf->SetFillColor(255,255,255);
	$pdf->Rect(9, 75, 193, 111, "F");
	/*totales*/
	$pdf->SetFillColor(255,255,255);
	$pdf->Rect(150, 190, 50, 25, "F");
	/*****FIN RECTANGULOS PARA TAPAR***************/
	
	$pdf->SetFillColor(10,10,10);
	$pdf->Rect(11, 53, 189, 31, "");
	
	/******LINEA H 1*******/
	$pdf->Line(11,79,200,79);
	
	/******LINEA H 2*******/
	$pdf->Line(11,90,200,90);
	
	/******LINEA V 1*******/
	$pdf->Line(17,85,17,172);
	
	/******LINEA V 2*******/
	$pdf->Line(46,85,46,172);
	
	/******LINEA V 3*******/
	$pdf->Line(150,85,150,172);
	
	/******LINEA V 4*******/
	$pdf->Line(160,85,160,172);
	
	/******LINEA V 5*******/
	$pdf->Line(180,85,180,172);
	
	$pdf->SetFillColor(10,10,10);
	$pdf->Rect(11, 85, 189, 87, "");
	
	$pdf->SetFillColor(10,10,10);
	$pdf->Rect(11, 173, 161, 14, "");
	
	/*SELECT CABECERA*/
	$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

	$sql ="SELECT	CONVERT(varchar(20),F.FECHA_FACTURA,103) FECHA_FACTURA
					,(CAST(F.RUT AS NVARCHAR(8)))+'-'+(CAST (F.DIG_VERIF AS NVARCHAR(1))) as RUT_COMPLETO
					,F.NOM_EMPRESA
					,F.NRO_ORDEN_COMPRA
					,F.DIRECCION
					,F.NOM_CIUDAD
					,F.GIRO
					,F.NOM_COMUNA
					,F.TELEFONO
					,F.MAIL
					,F.REFERENCIA
					,F.OBS
					,F.SUBTOTAL
					,F.MONTO_DSCTO1
					,F.TOTAL_NETO
					,F.MONTO_IVA
					,F.TOTAL_CON_IVA
			FROM FACTURA F
			WHERE F.COD_FACTURA = $cod_factura";
	$result = $db->build_results($sql);
	
	$FECHA_FACTURA		= $result[0]['FECHA_FACTURA'];
	$RUT_COMPLETO		= $result[0]['RUT_COMPLETO'];
	$NOM_EMPRESA		= $result[0]['NOM_EMPRESA'];
	$NRO_ORDEN_COMPRA	= $result[0]['NRO_ORDEN_COMPRA'];
	$DIRECCION			= $result[0]['DIRECCION'];
	$NOM_CIUDAD			= $result[0]['NOM_CIUDAD'];
	$GIRO				= $result[0]['GIRO'];
	$NOM_COMUNA			= $result[0]['NOM_COMUNA'];
	$MAIL				= $result[0]['MAIL'];
	$TELEFONO			= $result[0]['TELEFONO'];
	$REFERENCIA			= $result[0]['REFERENCIA'];
	$OBS				= $result[0]['OBS'];
	$SUBTOTAL			= $result[0]['SUBTOTAL'];
	$MONTO_DSCTO1		= $result[0]['MONTO_DSCTO1'];
	$TOTAL_NETO			= $result[0]['TOTAL_NETO'];
	$MONTO_IVA			= $result[0]['MONTO_IVA'];
	$TOTAL_CON_IVA		= $result[0]['TOTAL_CON_IVA'];
     
	/*** nueva estructura**/
	$pdf->SetFont('Arial','B',8);
	$pdf->SetXY(13, 42);
	$pdf->MultiCell(20, 30,"FECHA");
	$pdf->SetXY(35, 42);
	$pdf->MultiCell(20, 30,":");
	$pdf->SetFont('Arial','',8);
	$pdf->SetXY(38, 42);
	$pdf->MultiCell(20, 30,$FECHA_FACTURA);
	$pdf->SetFont('Arial','B',8);
	$pdf->SetXY(78, 42);
	$pdf->MultiCell(40, 30,"");
	$pdf->SetXY(102, 42);
	$pdf->MultiCell(20, 30,"");
	$pdf->SetFont('Arial','',8);
	$pdf->SetXY(106, 42);
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
	$pdf->SetFont('Arial','',8);
	$pdf->SetXY(38, 59.5);
	$pdf->MultiCell(100, 3,$NOM_EMPRESA,0,"L");
	$pdf->SetFont('Arial','B',8);
	$pdf->SetXY(150, 46);
	$pdf->MultiCell(20, 30,"O.C.");
	$pdf->SetXY(165, 46);
	$pdf->MultiCell(20, 30,":");
	$pdf->SetFont('Arial','',8);
	$pdf->SetXY(170, 46);
	$pdf->MultiCell(20, 30,$NRO_ORDEN_COMPRA);
	
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
	$pdf->SetXY(13, 67);
	$pdf->MultiCell(40, 30,"REFERENCIA");
	$pdf->SetXY(35, 67);
	$pdf->MultiCell(20, 30,":");
	$pdf->SetXY(38, 67);
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
	$pdf->SetXY(132, 189);
	$pdf->MultiCell(40, 5,"SUBTOTAL $",0,"R");
	$pdf->SetXY(167, 189);
	$pdf->MultiCell(8, 5,":",0,"R");
	$pdf->SetFont('Arial','',8);
	$pdf->SetXY(175, 189);
	$pdf->MultiCell(25, 5,number_format($SUBTOTAL,0,'.','.'),0,"R");
	
	$pdf->SetFont('Arial','B',8);
	$pdf->SetXY(132, 194);
	$pdf->MultiCell(40, 5,"DESCUENTO $",0,"R");
	$pdf->SetXY(167, 194);
	$pdf->MultiCell(8, 5,":",0,"R");
	$pdf->SetFont('Arial','',8);
	$pdf->SetXY(175, 194);
	$pdf->MultiCell(25, 5,number_format($MONTO_DSCTO1,0,'.','.'),0,"R");
	
	$pdf->SetFont('Arial','B',8);
	$pdf->SetXY(132, 199);
	$pdf->MultiCell(40, 5,"TOTAL NETO $",0,"R");
	$pdf->SetXY(167, 199);
	$pdf->MultiCell(8, 5,":",0,"R");
	$pdf->SetFont('Arial','',8);
	$pdf->SetXY(175, 199);
	$pdf->MultiCell(25, 5,number_format($TOTAL_NETO,0,'.','.'),0,"R");
	
	$pdf->SetFont('Arial','B',8);
	$pdf->SetXY(132, 204);
	$pdf->MultiCell(40, 5,"19 % I.V.A",0,"R");
	$pdf->SetXY(167, 204);
	$pdf->MultiCell(8, 5,":",0,"R");
	$pdf->SetFont('Arial','',8);
	$pdf->SetXY(175, 204);
	$pdf->MultiCell(25, 5,number_format($MONTO_IVA,0,'.','.'),0,"R");
	
	$pdf->SetFont('Arial','B',8);
	$pdf->SetXY(132, 209);
	$pdf->MultiCell(40, 5,"TOTAL",0,"R");
	$pdf->SetXY(167, 209);
	$pdf->MultiCell(8, 5,":",0,"R");
	$pdf->SetFont('Arial','',8);
	$pdf->SetXY(175, 209);
	$pdf->MultiCell(25, 5,number_format($TOTAL_CON_IVA,0,'.','.'),0,"R");
	
	$pdf->SetFont('Arial','B',8);
	$pdf->SetXY(12, 73);
	$pdf->MultiCell(10, 30,"IT");
	$pdf->SetXY(25, 73);
	$pdf->MultiCell(20, 30,"MODELO");
	$pdf->SetXY(93, 73);
	$pdf->MultiCell(20, 30,"DETALLE");
	$pdf->SetXY(152, 73);
	$pdf->MultiCell(20, 30,"CT");
	$pdf->SetXY(164, 73);
	$pdf->MultiCell(20, 30,"P.UNIT.");
	$pdf->SetXY(184, 73);
	$pdf->MultiCell(20, 30,"TOTAL");
	
	/*************ITEMS***********/
	$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
      
	$SqlDetalles ="SELECT ROW_NUMBER()OVER(ORDER BY PRECIO DESC) AS NroLinDet
						,('INT1')AS TpoCodigo
						,ITF.COD_PRODUCTO AS VlrCodigo
						,LEN (ITF.COD_PRODUCTO) LENTM
						,ITF.NOM_PRODUCTO AS NmbItem 
						,LEN (ITF.NOM_PRODUCTO) LENT
						,ITF.CANTIDAD
						,ITF.PRECIO
						,(ITF.CANTIDAD * ITF.PRECIO) AS MONTO_TOTAL
					FROM ITEM_FACTURA ITF WHERE ITF.COD_FACTURA = $cod_factura";
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
	$pdf->Rect(10, 50, 190, 25, "F");
	
	$pdf->SetFillColor(255,255,255);
	$pdf->Rect(9, 75, 193, 111, "F");
	
	$pdf->SetFillColor(255,255,255);
	$pdf->Rect(150, 190, 50, 25, "F");
	/*****FIN RECTANGULOS PARA TAPAR***************/
	
	$pdf->SetFillColor(10,10,10);
	$pdf->Rect(11, 53, 189, 31, "");
	
	/******LINEA H 1*******/
	$pdf->Line(11,79,200,79);
	
	/******LINEA H 2*******/
	$pdf->Line(11,90,200,90);
	
	/******LINEA V 1*******/
	$pdf->Line(17,85,17,172);
	
	/******LINEA V 2*******/
	$pdf->Line(46,85,46,172);
	
	/******LINEA V 3*******/
	$pdf->Line(150,85,150,172);
	
	/******LINEA V 4*******/
	$pdf->Line(160,85,160,172);
	
	/******LINEA V 5*******/
	$pdf->Line(180,85,180,172);
	
	$pdf->SetFillColor(10,10,10);
	$pdf->Rect(11, 85, 189, 87, "");
	
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
	$pdf->MultiCell(20, 30,$FECHA_FACTURA);
	$pdf->SetFont('Arial','B',8);
	$pdf->SetXY(78, 42);
	$pdf->MultiCell(40, 30,"");
	$pdf->SetXY(102, 42);
	$pdf->MultiCell(20, 30,"");
	$pdf->SetFont('Arial','',8);
	$pdf->SetXY(106, 42);
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
	$pdf->SetFont('Arial','',8);
	$pdf->SetXY(38, 59.5);
	$pdf->MultiCell(100, 3,$NOM_EMPRESA,0,"L");
	$pdf->SetFont('Arial','B',8);
	$pdf->SetXY(150, 46);
	$pdf->MultiCell(20, 30,"O.C.");
	$pdf->SetXY(165, 46);
	$pdf->MultiCell(20, 30,":");
	$pdf->SetFont('Arial','',8);
	$pdf->SetXY(170, 46);
	$pdf->MultiCell(20, 30,$NRO_ORDEN_COMPRA);
	
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
	$pdf->SetXY(13, 67);
	$pdf->MultiCell(40, 30,"REFERENCIA");
	$pdf->SetXY(35, 67);
	$pdf->MultiCell(20, 30,":");
	$pdf->SetXY(38, 67);
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
	$pdf->SetXY(132, 189);
	$pdf->MultiCell(40, 5,"SUBTOTAL $",0,"R");
	$pdf->SetXY(167, 189);
	$pdf->MultiCell(8, 5,":",0,"R");
	$pdf->SetFont('Arial','',8);
	$pdf->SetXY(175, 189);
	$pdf->MultiCell(25, 5,number_format($SUBTOTAL,0,'.','.'),0,"R");
	
	$pdf->SetFont('Arial','B',8);
	$pdf->SetXY(132, 194);
	$pdf->MultiCell(40, 5,"DESCUENTO $",0,"R");
	$pdf->SetXY(167, 194);
	$pdf->MultiCell(8, 5,":",0,"R");
	$pdf->SetFont('Arial','',8);
	$pdf->SetXY(175, 194);
	$pdf->MultiCell(25, 5,number_format($MONTO_DSCTO1,0,'.','.'),0,"R");
	
	$pdf->SetFont('Arial','B',8);
	$pdf->SetXY(132, 199);
	$pdf->MultiCell(40, 5,"TOTAL NETO $",0,"R");
	$pdf->SetXY(167, 199);
	$pdf->MultiCell(8, 5,":",0,"R");
	$pdf->SetFont('Arial','',8);
	$pdf->SetXY(175, 199);
	$pdf->MultiCell(25, 5,number_format($TOTAL_NETO,0,'.','.'),0,"R");
	
	$pdf->SetFont('Arial','B',8);
	$pdf->SetXY(132, 204);
	$pdf->MultiCell(40, 5,"19 % I.V.A",0,"R");
	$pdf->SetXY(167, 204);
	$pdf->MultiCell(8, 5,":",0,"R");
	$pdf->SetFont('Arial','',8);
	$pdf->SetXY(175, 204);
	$pdf->MultiCell(25, 5,number_format($MONTO_IVA,0,'.','.'),0,"R");
	
	$pdf->SetFont('Arial','B',8);
	$pdf->SetXY(132, 209);
	$pdf->MultiCell(40, 5,"TOTAL",0,"R");
	$pdf->SetXY(167, 209);
	$pdf->MultiCell(8, 5,":",0,"R");
	$pdf->SetFont('Arial','',8);
	$pdf->SetXY(175, 209);
	$pdf->MultiCell(25, 5,number_format($TOTAL_CON_IVA,0,'.','.'),0,"R");
	
	$pdf->SetFont('Arial','B',8);
	$pdf->SetXY(12, 73);
	$pdf->MultiCell(10, 30,"IT");
	$pdf->SetXY(25, 73);
	$pdf->MultiCell(20, 30,"MODELO");
	$pdf->SetXY(93, 73);
	$pdf->MultiCell(20, 30,"DETALLE");
	$pdf->SetXY(152, 73);
	$pdf->MultiCell(20, 30,"CT");
	$pdf->SetXY(164, 73);
	$pdf->MultiCell(20, 30,"P.UNIT.");
	$pdf->SetXY(184, 73);
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