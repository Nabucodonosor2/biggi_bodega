<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/TCPDF-master/tcpdf.php");

$K_PARAM_HASH = 200;
$cod_documento = $_REQUEST["cod_documento"];
$ES_CEDIBLE = $_REQUEST["ES_CEDIBLE"];
$DTE_ORIGEN = $_REQUEST["DTE_ORIGEN"];
/**HACER COMMIT OFICIAL****/


switch ($DTE_ORIGEN) {
			
	/////////////   FACTURA   //////////////
	case 33 :
	//RECUPERAMOS LOS DATOS GUARDADO EN LA BD
	$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
	$Sqlpdf = " SELECT  NRO_FACTURA
						,REPLACE(REPLACE(dbo.f_get_parametro(20),'.',''),'-8','') as RUTEMISOR
						,XML_DTE
				FROM FACTURA
				WHERE COD_FACTURA  = $cod_documento";  
	$Result_pdf = $db->build_results($Sqlpdf);
	
	$folio	= $Result_pdf[0]['NRO_FACTURA'];
	$emisor	= $Result_pdf[0]['RUTEMISOR'];
	
	$XML_DTE    = $Result_pdf[0]['XML_DTE'];
		
	$XML_DTE = base64_decode($XML_DTE);
	$xml_resolucion = simplexml_load_string($XML_DTE);
	$resolucion		= $xml_resolucion->SetDTE->Caratula->NroResol;
	$fecha_resolucion = $xml_resolucion->SetDTE->Caratula->FchResol;
		
	$XML_DTE = ereg_replace("[^A-Za-z0-9]", "", $XML_DTE);	
	$XML_DTE = strstr($XML_DTE, '<TED'); //separo el xml en el string "<TED"
	$len = strlen(strstr($XML_DTE, '</TED')); //realiazo la lectura en donde termina el tag "</TED" len
	$XML_DTE = substr($XML_DTE,0,-$len+6);   //resto resto del len y suno 6 cacarterers del </TED>
		
	$pdf = new TCPDF('P', 'mm', 'LETTER', true, 'UTF-8', false);
	$pdf->SetCreator('Isaias');
	$pdf->SetAuthor('Isaias');
	$pdf->SetTitle('FACTURA');
	$pdf->SetSubject('DOCUMENTOS TRIBUTARIOS ELECTRONICOS');
	$pdf->SetKeywords('DTE');
	$pdf->setPrintHeader(false);//PARA EVITAR QUE SE DIBUJE UNA LINEA EN LA CABECERA
	$pdf->setPrintFooter(false);//PARA EVITAR QUE SE DIBUJE UNA LINEA EN EL PIE DE PAGINA
	$pdf->SetFooterMargin(0);
	$pdf->SetAutoPageBreak(false, $margin=0);
	
	if($ES_CEDIBLE == 'N'){
		$pdf->AddPage();
			
		$x_timbre = 20; 
		$x = 20; 
		$y = 222; 
		$w = 70;
		$ecl = version_compare(phpversion(), '7.0.0', '<') ? -1 : 5;
		$style = array(
		                'border' => false,
		                'padding' => 0,
		                'hpadding' => 0,
		                'vpadding' => 0,
		                'module_width' => 1, // width of a single module in points
		                'module_height' => 1, // height of a single module in points
		                'fgcolor' => array(0,0,0),
		                'bgcolor' => false//, // [255,255,255]
		            );
			            
		$pdf->write2DBarcode($XML_DTE, 'PDF417,,'.$ecl, $x_timbre, $y, $w, 0, $style, 'B');
		$pdf->SetFont('helvetica','B',5.40);	
		$pdf->Text(40, 259,utf8_encode('Timbre Electrónico SII'));
		$pdf->Text(22, 262,utf8_encode("Resolución $resolucion del $fecha_resolucion Verifique este documento en www.sii.cl"));	
		$imagen = dirname(__FILE__)."/../../images_appl/BIGGI_LOGO_DTE.png";
		$pdf->Image($imagen,9,5,67,28);
		$pdf->SetDrawColor(255,0,0);	
		$pdf->SetLineWidth(1);
		$pdf->Rect(136, 10, 69, 30);
		$pdf->SetTextColor(255,0,0);
		$pdf->SetFont('helvetica','B',12.25);	
		$pdf->Text(149, 15,'R.U.T.: 91.462.001-5');
		$pdf->Text(144, 22,'FACTURA ELECTRONICA');
		$pdf->Text(159, 29,utf8_encode('N°: ').$folio);
		$pdf->SetFont('helvetica','B',8.75);
		$pdf->Text(149, 45,'S.I.I - SANTIAGO CENTRO');
		$pdf->SetTextColor(0,0,0);
		$pdf->Text(10, 35,'COMERCIAL BIGGI CHILE S.A.');
		$pdf->SetFont('helvetica','',7.15);
		$pdf->Text(10, 40,'ARTEFACTOS PARA LA MANIPULACION COCCION Y TRANSPORTE DE ALIMENTOS');
		$pdf->Text(10, 45,utf8_encode('PORTUGAL N° 1726 - SANTIAGO - CHILE'));
		$pdf->Text(10, 50,'FONOS: (56-2)2412-6200 - 25552849 - FAX(56-2)24126201 - 25512750');
		$pdf->SetLineWidth(0.3);
		$pdf->SetDrawColor(0,0,0);
		$pdf->RoundedRect(10, 55, 196, 37, 3.5);
				
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
						,F.MONTO_DSCTO2
						,F.TOTAL_NETO
						,F.MONTO_IVA
						,F.TOTAL_CON_IVA
						,F.NOM_FORMA_PAGO
						,F.COD_DOC
						,U.NOM_USUARIO
						,F.CANCELADA
						,F.GENERA_SALIDA
						,dbo.f_fa_nros_guia_despacho(COD_FACTURA)GUIA_DESPACHO 
						,F.RETIRADO_POR
						,(CAST(F.RUT_RETIRADO_POR AS NVARCHAR(8)))+'-'+(CAST (F.DIG_VERIF_RETIRADO_POR AS NVARCHAR(1))) as RUT_RETIRADO
						,F.PATENTE
						,F.PORC_DSCTO1
						,F.PORC_DSCTO2
				FROM FACTURA F, USUARIO U
				WHERE F.COD_FACTURA = $cod_documento
				AND U.COD_USUARIO = F.COD_USUARIO";
		$result = $db->build_results($sql);
		
		$FECHA_FACTURA		= utf8_encode($result[0]['FECHA_FACTURA']);
		$RUT_COMPLETO		= utf8_encode($result[0]['RUT_COMPLETO']);
		$NOM_EMPRESA		= utf8_encode($result[0]['NOM_EMPRESA']);
		$NRO_ORDEN_COMPRA	= utf8_encode($result[0]['NRO_ORDEN_COMPRA']);
		$DIRECCION			= utf8_encode($result[0]['DIRECCION']);
		$NOM_CIUDAD			= utf8_encode($result[0]['NOM_CIUDAD']);
		$GIRO				= utf8_encode($result[0]['GIRO']);
		$NOM_COMUNA			= utf8_encode($result[0]['NOM_COMUNA']);
		$MAIL				= utf8_encode($result[0]['MAIL']);
		$TELEFONO			= utf8_encode($result[0]['TELEFONO']);
		$REFERENCIA			= utf8_encode($result[0]['REFERENCIA']);
		$OBS				= utf8_encode($result[0]['OBS']);
		$SUBTOTAL			= utf8_encode($result[0]['SUBTOTAL']);
		$MONTO_DSCTO1		= utf8_encode($result[0]['MONTO_DSCTO1']);
		$MONTO_DSCTO2		= utf8_encode($result[0]['MONTO_DSCTO2']);
		$TOTAL_NETO			= utf8_encode($result[0]['TOTAL_NETO']);
		$MONTO_IVA			= utf8_encode($result[0]['MONTO_IVA']);
		$TOTAL_CON_IVA		= utf8_encode($result[0]['TOTAL_CON_IVA']);
		$NOM_FORMA_PAGO		= utf8_encode($result[0]['NOM_FORMA_PAGO']);
		$EMISOR				= utf8_encode($result[0]['NOM_USUARIO']);
		$NRO_NV				= utf8_encode($result[0]['COD_DOC']);
		$GUIA_DESPACHO		= utf8_encode($result[0]['GUIA_DESPACHO']);
		$CANCELADA			= $result[0]['CANCELADA'];
		$GENERA_SALIDA		= $result[0]['GENERA_SALIDA'];
		$TOTAL_EN_PALABRAS =  Numbers_Words::toWords($TOTAL_CON_IVA,"es"); 
		$TOTAL_EN_PALABRAS = strtr($TOTAL_EN_PALABRAS, "áéíóú", "aeiou");
		$TOTAL_EN_PALABRAS = strtoupper($TOTAL_EN_PALABRAS);
		$RUT_RETIRADO		= utf8_encode($result[0]['RUT_RETIRADO']);
		$RUT_RETIRADO		= (strlen($RUT_RETIRADO)==1) ? "" : "$RUT_RETIRADO";
		$RETIRADO_POR		= utf8_encode($result[0]['RETIRADO_POR']);
		$PATENTE			= utf8_encode($result[0]['PATENTE']);
		$PORC_DSCTO1		= $result[0]['PORC_DSCTO1'];
		$PORC_DSCTO2		= $result[0]['PORC_DSCTO2'];
		
		$pdf->SetFont('helvetica','B',6.95);
		$pdf->SetXY(12, 56);
		$pdf->MultiCell(20, 30,"FECHA",0);
		$pdf->SetXY(35, 56);
		$pdf->MultiCell(20, 30,":");
		$pdf->SetFont('helvetica','',7.15);
		$pdf->SetXY(38, 56);
		$pdf->MultiCell(20, 30,$FECHA_FACTURA);
		$pdf->SetFont('helvetica','B',7.15);
		$pdf->SetXY(145, 56);
		$pdf->MultiCell(20, 30,"RUT.");
		$pdf->SetXY(160, 56);
		$pdf->MultiCell(20, 30,":");
		$pdf->SetXY(163, 56);
		$pdf->MultiCell(40, 30,$RUT_COMPLETO);
		
		$pdf->SetFont('helvetica','B',6.95);
		$pdf->SetXY(12, 60);
		$pdf->MultiCell(40, 30,utf8_encode("SEÑOR(ES)"));
		$pdf->SetXY(35, 60);
		$pdf->MultiCell(20, 30,":");
		$pdf->SetXY(38, 60);
		$pdf->MultiCell(100, 2,substr($NOM_EMPRESA,0,186),0,"L"); 
		$pdf->SetFont('helvetica','B',6.95);
		$pdf->SetXY(145, 60);
		$pdf->MultiCell(20, 30,"O.C.");
		$pdf->SetXY(160, 60);
		$pdf->MultiCell(20, 60,":");
		$pdf->SetFont('helvetica','',7.15);
		$pdf->SetXY(163, 60);
		$pdf->MultiCell(20, 30,$NRO_ORDEN_COMPRA);
		
		$pdf->SetFont('helvetica','B',6.95);
		$pdf->SetXY(12, 67);
		$pdf->MultiCell(40, 30,utf8_encode("DIRECCIÓN"));
		$pdf->SetXY(35, 67);
		$pdf->MultiCell(20, 1,":");
		$pdf->SetFont('helvetica','',7.15);
		$pdf->SetXY(38, 67);
		$pdf->MultiCell(95, 3,substr($DIRECCION,0,186),0,'L'); 
		$pdf->SetFont('helvetica','B',6.95);
		$pdf->SetXY(145, 67);
		$pdf->MultiCell(40, 30,"CIUDAD");
		$pdf->SetXY(160, 67);
		$pdf->MultiCell(20, 30,":");
		$pdf->SetFont('helvetica','',7.15);
		$pdf->SetXY(163, 67);
		$pdf->MultiCell(40, 30,$NOM_CIUDAD);
		
		$pdf->SetFont('helvetica','B',6.95);
		$pdf->SetXY(12, 73);
		$pdf->MultiCell(40, 1,"GIRO");
		$pdf->SetXY(35, 73);
		$pdf->MultiCell(20, 1,":");
		$pdf->SetFont('helvetica','',7.15);
		$pdf->SetXY(38, 73);
		$pdf->MultiCell(100, 1,substr($GIRO,0,65),0,'L'); 
		$pdf->SetFont('helvetica','B',6.95);
		$pdf->SetXY(145, 73);
		$pdf->MultiCell(40, 30,"COMUNA");
		$pdf->SetXY(160, 73);
		$pdf->MultiCell(20, 30,":");
		$pdf->SetFont('helvetica','',7.15);
		$pdf->SetXY(163, 73);
		$pdf->MultiCell(40, 1,$NOM_COMUNA,0,'L');
		
		$pdf->SetFont('helvetica','B',6.95);
		$pdf->SetXY(12, 77);
		$pdf->MultiCell(40, 1,"E-MAIL");
		$pdf->SetXY(35, 77);
		$pdf->MultiCell(20, 1,":");
		$pdf->SetFont('helvetica','',7.15);
		$pdf->SetXY(38, 77);
		$pdf->MultiCell(110, 1,substr($MAIL,0,75),0,'L');
		$pdf->SetFont('helvetica','B',6.95);
		$pdf->SetXY(145, 77);
		$pdf->MultiCell(40, 30,"FONO");
		$pdf->SetXY(160, 77);
		$pdf->MultiCell(20, 30,":");
		$pdf->SetFont('helvetica','',7.15);
		$pdf->SetXY(163, 77);
		$pdf->MultiCell(36, 1,$TELEFONO,0,'L'); 
		
		$pdf->SetFont('helvetica','B',6.95);
		$pdf->SetXY(12, 81);
		$pdf->MultiCell(40, 1,"COND. DE VENTA",0,'L');
		$pdf->SetXY(35, 81);
		$pdf->MultiCell(20, 30,":");
		$pdf->SetFont('helvetica','',7.15);
		$pdf->SetXY(38, 81);
		$pdf->MultiCell(100, 1,$NOM_FORMA_PAGO,0,'L');
		$pdf->SetFont('helvetica','B',6.95);
		$pdf->SetXY(145, 81);
		$pdf->MultiCell(40, 1,"EMISOR");
		$pdf->SetXY(160, 81);
		$pdf->MultiCell(20, 30,":");
		$pdf->SetFont('helvetica','',7.15);
		$pdf->SetXY(163, 81);
		$pdf->MultiCell(50, 1,substr($EMISOR,0,27),0,'L'); 
		
		$pdf->Line(10,85,206,85);
		
		$pdf->SetFont('helvetica','B',6.95);
		$pdf->SetXY(12, 85,5);
		$pdf->MultiCell(40, 1,"REFERENCIA",0,'L');
		$pdf->SetXY(35, 85.5);
		$pdf->MultiCell(3, 1,":",0);
		$pdf->SetXY(38, 85.5);
		$pdf->MultiCell(90, 2,$REFERENCIA,0,'L');  
		$pdf->Text(129,85.5,utf8_encode("N° NV : $NRO_NV")); 
		$pdf->Text(154,85.5,utf8_encode("GUIA DESPACHO N°:")); 
		$pdf->SetXY(179.5, 85.5);
		$pdf->MultiCell(26, 6.5,$GUIA_DESPACHO,0,'L');
		
		$pdf->RoundedRect(10, 92, 196, 77.5, 3.5);
		$pdf->SetFont('helvetica','B',8);
		$pdf->SetXY(10, 93);
		$pdf->MultiCell(10, 1,"IT",0,'C');
		$pdf->Line(20,92,20,169.5);
		$pdf->SetXY(20, 93);
		$pdf->MultiCell(15, 1,"CT",0,'C');
		$pdf->Line(35,92,35,169.5);
		$pdf->SetXY(35, 93);
		$pdf->MultiCell(23, 1,"MODELO",0,'C');
		$pdf->Line(58,92,58,169.5);
		$pdf->SetXY(58, 93);
		$pdf->MultiCell(103, 1,"DETALLE",0,'C');
		$pdf->Line(161,92,161,169.5);
		$pdf->SetXY(161, 93);
		$pdf->MultiCell(21, 1,"P.UNIT.",0,'C');
		$pdf->Line(182,92,182,169.5);
		$pdf->SetXY(182, 93);
		$pdf->MultiCell(24, 1,"TOTAL",0,'C');
		
		$pdf->Line(10,97,206,97);
		
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
						FROM ITEM_FACTURA ITF WHERE ITF.COD_FACTURA = $cod_documento";
		$result2 = $db->build_results($SqlDetalles);
		
		$x = 2;
		$i = 2;
		$y = $pdf->GetY()-7.5; 
			
		foreach($result2 as $row){
			$pdf->SetFont('helvetica','',6.25);
			$pdf->SetXY($x+8, $y+(4*$i));
			$pdf->MultiCell(10, 1, $row['NroLinDet'],0,'C');
			$pdf->SetXY($x+18, $y+(4*$i));
			$pdf->MultiCell(15, 1, $row['CANTIDAD'],0,'C'); 
			$pdf->SetXY($x+33, $y+(4*$i));
			$pdf->MultiCell(23, 1,substr($row['VlrCodigo'],0,15) ,0,'C'); 
			$pdf->SetXY($x+56, $y+(4*$i));
			$pdf->MultiCell(103, 1,substr(utf8_encode($row['NmbItem']),0,85) ,0,'L'); 
			$pdf->SetXY($x+159, $y+(4*$i));
			$pdf->MultiCell(21, 1, number_format($row['PRECIO'],0,'.','.'),0,'R');
			$pdf->SetXY($x+180, $y+(4*$i));
			$pdf->MultiCell(24, 1, number_format($row['MONTO_TOTAL'],0,'.','.'),0,'R');
		
			$i++;			
		}
		/*****************************************PIE PAGINA************************************************/
		/*************OBSERVACIONES********/
		$pdf->RoundedRect(10, 171.5, 140, 47, 3.5);
		$pdf->SetFont('helvetica','B',6.95);	
		$pdf->Text(15, 173,"SON : ");
		$pdf->SetFont('helvetica','',7.15);
		$pdf->SetXY(23, 173);
		$pdf->MultiCell(124, 3,"$TOTAL_EN_PALABRAS PESOS.",0,'L');
		$pdf->SetFont('helvetica','B',6.95);
		$pdf->Text(12, 180.7,"NOTAS : ");
		$pdf->SetFont('helvetica','',7.15);
		$pdf->SetXY(23, 180.7);
		$pdf->MultiCell(124,30,substr($OBS,0,735) ,0,'L');
		$pdf->SetFont('helvetica','B',6.95);
		$pdf->Text(12, 213,"ESTADO PAGO : ");
		if($CANCELADA == 'S'){
			$pdf->SetFont('helvetica','',7.15);
			$pdf->Text(35, 213,"CANCELADA");
		}
		$pdf->SetFont('helvetica','B',6.95);
		$pdf->Text(100, 213,"ESTADO SALIDA : ");
		if($GENERA_SALIDA == 'S'){
			$pdf->SetFont('helvetica','',7.15);
			$pdf->SetXY(127, 213);
			$pdf->MultiCell(20,1,"DESPACHADO",1,'C');
		}
		/*************TOTALES***********/
		$pdf->RoundedRect(155, 171.5, 51, 33, 3.5);
		$pdf->Line(189,171.5,189,204.5);
		$pdf->SetFont('helvetica','B',6.95);	
		$pdf->Text(174, 173,"SUBTOTAL");
		$pdf->SetFont('helvetica','',7.15);
		$pdf->SetXY(180, 173);
		$pdf->MultiCell(26, 1,number_format($SUBTOTAL,0,'.','.'),0,"R");
		$pdf->Line(155,177,206,177);
		$pdf->SetFont('helvetica','',6.95);	
		$pdf->Text(161.6, 178.2,number_format($PORC_DSCTO1,2,'.','.').' %',0,"R");
		$pdf->SetFont('helvetica','B',6.95);	
		$pdf->Text(171.8, 178.2,"DESCUENTO");
		$pdf->SetFont('helvetica','',7.15);
		$pdf->SetXY(180, 178.2);
		$pdf->MultiCell(26, 1,number_format($MONTO_DSCTO1,0,'.','.'),0,"R");
		$pdf->Line(155,182.5,206,182.5);
		$pdf->SetFont('helvetica','',6.95);
		$pdf->Text(155, 183.5,number_format($PORC_DSCTO2,2,'.','.').' %',0,"R");
		$pdf->SetFont('helvetica','B',6.95);
		$pdf->Text(165, 183.5,"DESCUENTO ADIC.");
		$pdf->SetFont('helvetica','',7.15);
		$pdf->SetXY(180, 183.5);
		$pdf->MultiCell(26, 1,number_format($MONTO_DSCTO2,0,'.','.'),0,"R");
		$pdf->Line(155,188,206,188);
		$pdf->SetFont('helvetica','B',6.95);
		$pdf->Text(171.8, 189,"TOTAL NETO");
		$pdf->SetFont('helvetica','',7.15);
		$pdf->SetXY(180, 189);
		$pdf->MultiCell(26, 1,number_format($TOTAL_NETO,0,'.','.'),0,"R");
		$pdf->Line(155,193.5,206,193.5);
		$pdf->SetFont('helvetica','B',6.95);
		$pdf->Text(176, 194.5,utf8_encode("19% I.V.A."));
		$pdf->SetFont('helvetica','',7.15);
		$pdf->SetXY(180, 194.5);
		$pdf->MultiCell(26, 1,number_format($MONTO_IVA,0,'.','.'),0,"R");
		$pdf->Line(155,199,206,199);
		$pdf->SetFont('helvetica','B',6.95);
		$pdf->Text(179, 200,"TOTAL");
		$pdf->SetFont('helvetica','',7.15);
		$pdf->SetXY(180, 200);
		$pdf->MultiCell(26, 1,number_format($TOTAL_CON_IVA,0,'.','.'),0,"R");
		
		$pdf->RoundedRect(96, 225, 109, 33, 3.5);
		$pdf->SetFont('helvetica','B',6.95);
		$pdf->Text(99, 230,"NOMBRE:");
		$pdf->SetFont('helvetica','',7.15);
		$pdf->Text(114, 230,substr($RETIRADO_POR,0,28));
		$pdf->Line(114,234,160,234);
		$pdf->SetFont('helvetica','B',6.95);
		$pdf->Text(161, 230,"FIRMA:");
		$pdf->Line(173,234,198,234);
		$pdf->Text(99, 239,"FECHA:");
		$pdf->SetFont('helvetica','',7.15);
		$pdf->Text(125, 239,$FECHA_FACTURA);
		$pdf->Line(114,243,160,243);
		$pdf->SetFont('helvetica','B',6.95);
		$pdf->Text(161,239,"R.U.T.:");
		$pdf->SetFont('helvetica','',7.15);
		$pdf->Text(173,239,$RUT_RETIRADO);
		$pdf->Text(173,244,$PATENTE);
		$pdf->Line(173,243,198,243);
		$pdf->SetFont('helvetica','B',6.95);
		$pdf->Text(99, 248,"RECINTO:");
		$pdf->Line(114,252,198,252);
		$pdf->SetFont('helvetica','',5.40);
		$pdf->SetXY(99, 253);
		$pdf->MultiCell(103, 2,utf8_encode("El acuse de recibo que se declara en este acto, de acuerdo a lo dispuesto en la letra b) del art. 4°, y la letra c) del Art. 5° de la Ley 19.983, acredita que la entega de mercadería(s) o servicio(s) prestado(s) ha(n) sido recibido(s)"),0,"L");
		
		$pdf->SetDrawColor(255,0,0);	
		$pdf->SetLineWidth(4);
		$pdf->Line(10,268,206,268);
		$pdf->SetFont('helvetica','',7.15);
		$pdf->SetTextColor(255,255,255);
		$pdf->Text(97, 266.5,"www.biggi.cl");
	}else{
		/*************COPIA CEDIBLE **************/
		$pdf->AddPage();
			
		$x_timbre = 20; 
		$x = 20; 
		$y = 222; 
		$w = 70;
		$ecl = version_compare(phpversion(), '7.0.0', '<') ? -1 : 5;
		$style = array(
		                'border' => false,
		                'padding' => 0,
		                'hpadding' => 0,
		                'vpadding' => 0,
		                'module_width' => 1, // width of a single module in points
		                'module_height' => 1, // height of a single module in points
		                'fgcolor' => array(0,0,0),
		                'bgcolor' => false//, // [255,255,255]
		            );
			            
		$pdf->write2DBarcode($XML_DTE, 'PDF417,,'.$ecl, $x_timbre, $y, $w, 0, $style, 'B');
		$pdf->SetFont('helvetica','B',5.40);	
		$pdf->Text(40, 259,utf8_encode('Timbre Electrónico SII'));
		$pdf->Text(22, 262,utf8_encode("Resolución $resolucion del $fecha_resolucion Verifique este documento en www.sii.cl"));	
		$imagen = dirname(__FILE__)."/../../images_appl/BIGGI_LOGO_DTE.png";
		$pdf->Image($imagen,9,5,67,28);
		$pdf->SetDrawColor(255,0,0);	
		$pdf->SetLineWidth(1);
		$pdf->Rect(136, 10, 69, 30);
		$pdf->SetTextColor(255,0,0);
		$pdf->SetFont('helvetica','B',12.25);	
		$pdf->Text(149, 15,'R.U.T.: 91.462.001-5');
		$pdf->Text(144, 22,'FACTURA ELECTRONICA');
		$pdf->Text(159, 29,utf8_encode('N°: ').$folio);
		$pdf->SetFont('helvetica','B',8.75);
		$pdf->Text(149, 45,'S.I.I - SANTIAGO CENTRO');
		$pdf->SetTextColor(0,0,0);
		$pdf->Text(10, 35,'COMERCIAL BIGGI CHILE S.A.');
		$pdf->SetFont('helvetica','',7.15);
		$pdf->Text(10, 40,'ARTEFACTOS PARA LA MANIPULACION COCCION Y TRANSPORTE DE ALIMENTOS');
		$pdf->Text(10, 45,utf8_encode('PORTUGAL N° 1726 - SANTIAGO - CHILE'));
		$pdf->Text(10, 50,'FONOS: (56-2)2412-6200 - 25552849 - FAX(56-2)24126201 - 25512750');
		$pdf->SetLineWidth(0.3);
		$pdf->SetDrawColor(0,0,0);
		$pdf->RoundedRect(10, 55, 196, 37, 3.5);
				
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
						,F.MONTO_DSCTO2
						,F.TOTAL_NETO
						,F.MONTO_IVA
						,F.TOTAL_CON_IVA
						,F.NOM_FORMA_PAGO
						,F.COD_DOC
						,U.NOM_USUARIO
						,F.CANCELADA
						,F.GENERA_SALIDA
						,dbo.f_fa_nros_guia_despacho(COD_FACTURA)GUIA_DESPACHO 
						,F.RETIRADO_POR
						,(CAST(F.RUT_RETIRADO_POR AS NVARCHAR(8)))+'-'+(CAST (F.DIG_VERIF_RETIRADO_POR AS NVARCHAR(1))) as RUT_RETIRADO
						,F.PATENTE
						,F.PORC_DSCTO1
						,F.PORC_DSCTO2
				FROM FACTURA F, USUARIO U
				WHERE F.COD_FACTURA = $cod_documento
				AND U.COD_USUARIO = F.COD_USUARIO";
		$result = $db->build_results($sql);
		
		$FECHA_FACTURA		= utf8_encode($result[0]['FECHA_FACTURA']);
		$RUT_COMPLETO		= utf8_encode($result[0]['RUT_COMPLETO']);
		$NOM_EMPRESA		= utf8_encode($result[0]['NOM_EMPRESA']);
		$NRO_ORDEN_COMPRA	= utf8_encode($result[0]['NRO_ORDEN_COMPRA']);
		$DIRECCION			= utf8_encode($result[0]['DIRECCION']);
		$NOM_CIUDAD			= utf8_encode($result[0]['NOM_CIUDAD']);
		$GIRO				= utf8_encode($result[0]['GIRO']);
		$NOM_COMUNA			= utf8_encode($result[0]['NOM_COMUNA']);
		$MAIL				= utf8_encode($result[0]['MAIL']);
		$TELEFONO			= utf8_encode($result[0]['TELEFONO']);
		$REFERENCIA			= utf8_encode($result[0]['REFERENCIA']);
		$OBS				= utf8_encode($result[0]['OBS']);
		$SUBTOTAL			= utf8_encode($result[0]['SUBTOTAL']);
		$MONTO_DSCTO1		= utf8_encode($result[0]['MONTO_DSCTO1']);
		$MONTO_DSCTO2		= utf8_encode($result[0]['MONTO_DSCTO2']);
		$TOTAL_NETO			= utf8_encode($result[0]['TOTAL_NETO']);
		$MONTO_IVA			= utf8_encode($result[0]['MONTO_IVA']);
		$TOTAL_CON_IVA		= utf8_encode($result[0]['TOTAL_CON_IVA']);
		$NOM_FORMA_PAGO		= utf8_encode($result[0]['NOM_FORMA_PAGO']);
		$EMISOR				= utf8_encode($result[0]['NOM_USUARIO']);
		$NRO_NV				= utf8_encode($result[0]['COD_DOC']);
		$GUIA_DESPACHO		= utf8_encode($result[0]['GUIA_DESPACHO']);
		$CANCELADA			= $result[0]['CANCELADA'];
		$GENERA_SALIDA		= $result[0]['GENERA_SALIDA'];
		$TOTAL_EN_PALABRAS =  Numbers_Words::toWords($TOTAL_CON_IVA,"es"); 
		$TOTAL_EN_PALABRAS = strtr($TOTAL_EN_PALABRAS, "áéíóú", "aeiou");
		$TOTAL_EN_PALABRAS = strtoupper($TOTAL_EN_PALABRAS);
		$RUT_RETIRADO		= utf8_encode($result[0]['RUT_RETIRADO']);
		$RUT_RETIRADO		= (strlen($RUT_RETIRADO)==1) ? "" : "$RUT_RETIRADO";
		$RETIRADO_POR		= utf8_encode($result[0]['RETIRADO_POR']);
		$PATENTE			= utf8_encode($result[0]['PATENTE']);
		$PORC_DSCTO1		= $result[0]['PORC_DSCTO1'];
		$PORC_DSCTO2		= $result[0]['PORC_DSCTO2'];
	
		$pdf->SetFont('helvetica','B',6.95);
		$pdf->SetXY(12, 56);
		$pdf->MultiCell(20, 30,"FECHA",0);
		$pdf->SetXY(35, 56);
		$pdf->MultiCell(20, 30,":");
		$pdf->SetFont('helvetica','',7.15);
		$pdf->SetXY(38, 56);
		$pdf->MultiCell(20, 30,$FECHA_FACTURA);
		$pdf->SetFont('helvetica','B',7.15);
		$pdf->SetXY(145, 56);
		$pdf->MultiCell(20, 30,"RUT.");
		$pdf->SetXY(160, 56);
		$pdf->MultiCell(20, 30,":");
		$pdf->SetXY(163, 56);
		$pdf->MultiCell(40, 30,$RUT_COMPLETO);
		
		$pdf->SetFont('helvetica','B',6.95);
		$pdf->SetXY(12, 60);
		$pdf->MultiCell(40, 30,utf8_encode("SEÑOR(ES)"));
		$pdf->SetXY(35, 60);
		$pdf->MultiCell(20, 30,":");
		$pdf->SetXY(38, 60);
		$pdf->MultiCell(100, 2,substr($NOM_EMPRESA,0,186),0,"L"); 
		$pdf->SetFont('helvetica','B',6.95);
		$pdf->SetXY(145, 60);
		$pdf->MultiCell(20, 30,"O.C.");
		$pdf->SetXY(160, 60);
		$pdf->MultiCell(20, 60,":");
		$pdf->SetFont('helvetica','',7.15);
		$pdf->SetXY(163, 60);
		$pdf->MultiCell(20, 30,$NRO_ORDEN_COMPRA);
		
		$pdf->SetFont('helvetica','B',6.95);
		$pdf->SetXY(12, 67);
		$pdf->MultiCell(40, 30,utf8_encode("DIRECCIÓN"));
		$pdf->SetXY(35, 67);
		$pdf->MultiCell(20, 1,":");
		$pdf->SetFont('helvetica','',7.15);
		$pdf->SetXY(38, 67);
		$pdf->MultiCell(95, 3,substr($DIRECCION,0,186),0,'L'); 
		$pdf->SetFont('helvetica','B',6.95);
		$pdf->SetXY(145, 67);
		$pdf->MultiCell(40, 30,"CIUDAD");
		$pdf->SetXY(160, 67);
		$pdf->MultiCell(20, 30,":");
		$pdf->SetFont('helvetica','',7.15);
		$pdf->SetXY(163, 67);
		$pdf->MultiCell(40, 30,$NOM_CIUDAD);
		
		$pdf->SetFont('helvetica','B',6.95);
		$pdf->SetXY(12, 73);
		$pdf->MultiCell(40, 1,"GIRO");
		$pdf->SetXY(35, 73);
		$pdf->MultiCell(20, 1,":");
		$pdf->SetFont('helvetica','',7.15);
		$pdf->SetXY(38, 73);
		$pdf->MultiCell(100, 1,substr($GIRO,0,65),0,'L'); 
		$pdf->SetFont('helvetica','B',6.95);
		$pdf->SetXY(145, 73);
		$pdf->MultiCell(40, 30,"COMUNA");
		$pdf->SetXY(160, 73);
		$pdf->MultiCell(20, 30,":");
		$pdf->SetFont('helvetica','',7.15);
		$pdf->SetXY(163, 73);
		$pdf->MultiCell(40, 1,$NOM_COMUNA,0,'L');
		
		$pdf->SetFont('helvetica','B',6.95);
		$pdf->SetXY(12, 77);
		$pdf->MultiCell(40, 1,"E-MAIL");
		$pdf->SetXY(35, 77);
		$pdf->MultiCell(20, 1,":");
		$pdf->SetFont('helvetica','',7.15);
		$pdf->SetXY(38, 77);
		$pdf->MultiCell(110, 1,substr($MAIL,0,75),0,'L');
		$pdf->SetFont('helvetica','B',6.95);
		$pdf->SetXY(145, 77);
		$pdf->MultiCell(40, 30,"FONO");
		$pdf->SetXY(160, 77);
		$pdf->MultiCell(20, 30,":");
		$pdf->SetFont('helvetica','',7.15);
		$pdf->SetXY(163, 77);
		$pdf->MultiCell(36, 1,$TELEFONO,0,'L'); 
		
		$pdf->SetFont('helvetica','B',6.95);
		$pdf->SetXY(12, 81);
		$pdf->MultiCell(40, 1,"COND. DE VENTA",0,'L');
		$pdf->SetXY(35, 81);
		$pdf->MultiCell(20, 30,":");
		$pdf->SetFont('helvetica','',7.15);
		$pdf->SetXY(38, 81);
		$pdf->MultiCell(100, 1,$NOM_FORMA_PAGO,0,'L');
		$pdf->SetFont('helvetica','B',6.95);
		$pdf->SetXY(145, 81);
		$pdf->MultiCell(40, 1,"EMISOR");
		$pdf->SetXY(160, 81);
		$pdf->MultiCell(20, 30,":");
		$pdf->SetFont('helvetica','',7.15);
		$pdf->SetXY(163, 81);
		$pdf->MultiCell(50, 1,substr($EMISOR,0,27),0,'L'); 
		
		$pdf->Line(10,85,206,85);
		
		$pdf->SetFont('helvetica','B',6.95);
		$pdf->SetXY(12, 85,5);
		$pdf->MultiCell(40, 1,"REFERENCIA",0,'L');
		$pdf->SetXY(35, 85.5);
		$pdf->MultiCell(3, 1,":",0);
		$pdf->SetXY(38, 85.5);
		$pdf->MultiCell(90, 2,$REFERENCIA,0,'L');  
		$pdf->Text(129,85.5,utf8_encode("N° NV : $NRO_NV")); 
		$pdf->Text(154,85.5,utf8_encode("GUIA DESPACHO N°:")); 
		$pdf->SetXY(179.5, 85.5);
		$pdf->MultiCell(26, 6.5,$GUIA_DESPACHO,0,'L');
		
		$pdf->RoundedRect(10, 92, 196, 77.5, 3.5);
		$pdf->SetFont('helvetica','B',8);
		$pdf->SetXY(10, 93);
		$pdf->MultiCell(10, 1,"IT",0,'C');
		$pdf->Line(20,92,20,169.5);
		$pdf->SetXY(20, 93);
		$pdf->MultiCell(15, 1,"CT",0,'C');
		$pdf->Line(35,92,35,169.5);
		$pdf->SetXY(35, 93);
		$pdf->MultiCell(23, 1,"MODELO",0,'C');
		$pdf->Line(58,92,58,169.5);
		$pdf->SetXY(58, 93);
		$pdf->MultiCell(103, 1,"DETALLE",0,'C');
		$pdf->Line(161,92,161,169.5);
		$pdf->SetXY(161, 93);
		$pdf->MultiCell(21, 1,"P.UNIT.",0,'C');
		$pdf->Line(182,92,182,169.5);
		$pdf->SetXY(182, 93);
		$pdf->MultiCell(24, 1,"TOTAL",0,'C');
		
		$pdf->Line(10,97,206,97);
		
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
						FROM ITEM_FACTURA ITF WHERE ITF.COD_FACTURA = $cod_documento";
		$result2 = $db->build_results($SqlDetalles);
		
		$x = 2;
		$i = 2;
		$y = $pdf->GetY()-7.5; 
			
		foreach($result2 as $row){
			$pdf->SetFont('helvetica','',6.25);
			$pdf->SetXY($x+8, $y+(4*$i));
			$pdf->MultiCell(10, 1, $row['NroLinDet'],0,'C');
			$pdf->SetXY($x+18, $y+(4*$i));
			$pdf->MultiCell(15, 1, $row['CANTIDAD'],0,'C'); 
			$pdf->SetXY($x+33, $y+(4*$i));
			$pdf->MultiCell(23, 1,substr($row['VlrCodigo'],0,15) ,0,'C'); 
			$pdf->SetXY($x+56, $y+(4*$i));
			$pdf->MultiCell(103, 1,substr(utf8_encode($row['NmbItem']),0,85) ,0,'L'); 
			$pdf->SetXY($x+159, $y+(4*$i));
			$pdf->MultiCell(21, 1, number_format($row['PRECIO'],0,'.','.'),0,'R');
			$pdf->SetXY($x+180, $y+(4*$i));
			$pdf->MultiCell(24, 1, number_format($row['MONTO_TOTAL'],0,'.','.'),0,'R');
		
			$i++;			
		}
		/*****************************************PIE PAGINA************************************************/
		/*************OBSERVACIONES********/
		$pdf->RoundedRect(10, 171.5, 140, 47, 3.5);
		$pdf->SetFont('helvetica','B',6.95);	
		$pdf->Text(15, 173,"SON : ");
		$pdf->SetFont('helvetica','',7.15);
		$pdf->SetXY(23, 173);
		$pdf->MultiCell(124, 3,"$TOTAL_EN_PALABRAS PESOS.",0,'L');
		$pdf->SetFont('helvetica','B',6.95);
		$pdf->Text(12, 180.7,"NOTAS : ");
		$pdf->SetFont('helvetica','',7.15);
		$pdf->SetXY(23, 180.7);
		$pdf->MultiCell(124,30,substr($OBS,0,735) ,0,'L');
		$pdf->SetFont('helvetica','B',6.95);
		$pdf->Text(12, 213,"ESTADO PAGO : ");
		if($CANCELADA == 'S'){
			$pdf->SetFont('helvetica','',7.15);
			$pdf->Text(35, 213,"CANCELADA");
		}
		$pdf->SetFont('helvetica','B',6.95);
		$pdf->Text(100, 213,"ESTADO SALIDA : ");
		if($GENERA_SALIDA == 'S'){
			$pdf->SetFont('helvetica','',7.15);
			$pdf->SetXY(127, 213);
			$pdf->MultiCell(20,1,"DESPACHADO",1,'C');
		}
		/*************TOTALES***********/
		$pdf->RoundedRect(155, 171.5, 51, 33, 3.5);
		$pdf->Line(189,171.5,189,204.5);
		$pdf->SetFont('helvetica','B',6.95);	
		$pdf->Text(174, 173,"SUBTOTAL");
		$pdf->SetFont('helvetica','',7.15);
		$pdf->SetXY(180, 173);
		$pdf->MultiCell(26, 1,number_format($SUBTOTAL,0,'.','.'),0,"R");
		$pdf->Line(155,177,206,177);
		$pdf->SetFont('helvetica','',6.95);	
		$pdf->Text(161.6, 178.2,number_format($PORC_DSCTO1,2,'.','.').' %',0,"R");
		$pdf->SetFont('helvetica','B',6.95);	
		$pdf->Text(171.8, 178.2,"DESCUENTO");
		$pdf->SetFont('helvetica','',7.15);
		$pdf->SetXY(180, 178.2);
		$pdf->MultiCell(26, 1,number_format($MONTO_DSCTO1,0,'.','.'),0,"R");
		$pdf->Line(155,182.5,206,182.5);
		$pdf->SetFont('helvetica','',6.95);
		$pdf->Text(155, 183.5,number_format($PORC_DSCTO2,2,'.','.').' %',0,"R");
		$pdf->SetFont('helvetica','B',6.95);
		$pdf->Text(165, 183.5,"DESCUENTO ADIC.");
		$pdf->SetFont('helvetica','',7.15);
		$pdf->SetXY(180, 183.5);
		$pdf->MultiCell(26, 1,number_format($MONTO_DSCTO2,0,'.','.'),0,"R");
		$pdf->Line(155,188,206,188);
		$pdf->SetFont('helvetica','B',6.95);
		$pdf->Text(171.8, 189,"TOTAL NETO");
		$pdf->SetFont('helvetica','',7.15);
		$pdf->SetXY(180, 189);
		$pdf->MultiCell(26, 1,number_format($TOTAL_NETO,0,'.','.'),0,"R");
		$pdf->Line(155,193.5,206,193.5);
		$pdf->SetFont('helvetica','B',6.95);
		$pdf->Text(176, 194.5,utf8_encode("19% I.V.A."));
		$pdf->SetFont('helvetica','',7.15);
		$pdf->SetXY(180, 194.5);
		$pdf->MultiCell(26, 1,number_format($MONTO_IVA,0,'.','.'),0,"R");
		$pdf->Line(155,199,206,199);
		$pdf->SetFont('helvetica','B',6.95);
		$pdf->Text(179, 200,"TOTAL");
		$pdf->SetFont('helvetica','',7.15);
		$pdf->SetXY(180, 200);
		$pdf->MultiCell(26, 1,number_format($TOTAL_CON_IVA,0,'.','.'),0,"R");
		
		$pdf->RoundedRect(96, 225, 109, 33, 3.5);
		$pdf->SetFont('helvetica','B',6.95);
		$pdf->Text(99, 230,"NOMBRE:");
		$pdf->SetFont('helvetica','',7.15);
		$pdf->Text(114, 230,substr($RETIRADO_POR,0,28));
		$pdf->Line(114,234,160,234);
		$pdf->SetFont('helvetica','B',6.95);
		$pdf->Text(161, 230,"FIRMA:");
		$pdf->Line(173,234,198,234);
		$pdf->Text(99, 239,"FECHA:");
		$pdf->SetFont('helvetica','',7.15);
		$pdf->Text(125, 239,$FECHA_FACTURA);
		$pdf->Line(114,243,160,243);
		$pdf->SetFont('helvetica','B',6.95);
		$pdf->Text(161,239,"R.U.T.:");
		$pdf->SetFont('helvetica','',7.15);
		$pdf->Text(173,239,$RUT_RETIRADO);
		$pdf->Text(173,244,$PATENTE);
		$pdf->Line(173,243,198,243);
		$pdf->SetFont('helvetica','B',6.95);
		$pdf->Text(99, 248,"RECINTO:");
		$pdf->Line(114,252,198,252);
		$pdf->SetFont('helvetica','',5.40);
		$pdf->SetXY(99, 253);
		$pdf->MultiCell(103, 2,utf8_encode("El acuse de recibo que se declara en este acto, de acuerdo a lo dispuesto en la letra b) del art. 4°, y la letra c) del Art. 5° de la Ley 19.983, acredita que la entega de mercadería(s) o servicio(s) prestado(s) ha(n) sido recibido(s)"),0,"L");
		
		$pdf->SetDrawColor(255,0,0);	
		$pdf->SetLineWidth(4);
		$pdf->Line(10,268,206,268);
		$pdf->SetFont('helvetica','',7.15);
		$pdf->SetTextColor(255,255,255);
		$pdf->Text(97, 266.5,"www.biggi.cl");
		
		$pdf->SetFont('helvetica','B',6.95);
		$pdf->SetDrawColor(0,0,0);	
		$pdf->SetLineWidth(0.3);
		$pdf->SetTextColor(0,0,0);
		$pdf->SetXY(192.7, 270.5);
		$pdf->Cell(13, 1,'CEDIBLE',1,"C");
	}
	$pdf->Output("33_$folio.pdf", 'I');	
	break;
	/////////////   GUIA DESPACHO   //////////////
	case 52 :
	//RECUPERAMOS LOS DATOS GUARDADO EN LA BD
	$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
	$Sqlpdf = " SELECT  NRO_GUIA_DESPACHO
						,REPLACE(REPLACE(dbo.f_get_parametro(20),'.',''),'-8','') as RUTEMISOR
						,XML_DTE
				FROM GUIA_DESPACHO
				WHERE COD_GUIA_DESPACHO  = $cod_documento";  
	$Result_pdf = $db->build_results($Sqlpdf);
	
	$folio	= $Result_pdf[0]['NRO_GUIA_DESPACHO'];
	$emisor	= $Result_pdf[0]['RUTEMISOR'];
	
	$XML_DTE    = $Result_pdf[0]['XML_DTE'];
		
	$XML_DTE = base64_decode($XML_DTE);
	$xml_resolucion = simplexml_load_string($XML_DTE);
	$resolucion		= $xml_resolucion->SetDTE->Caratula->NroResol;
	$fecha_resolucion = $xml_resolucion->SetDTE->Caratula->FchResol;
		
	$XML_DTE = ereg_replace("[^A-Za-z0-9]", "", $XML_DTE);	
	$XML_DTE = strstr($XML_DTE, '<TED'); //separo el xml en el string "<TED"
	$len = strlen(strstr($XML_DTE, '</TED')); //realiazo la lectura en donde termina el tag "</TED" len
	$XML_DTE = substr($XML_DTE,0,-$len+6);   //resto resto del len y suno 6 cacarterers del </TED>
		
	$pdf = new TCPDF('P', 'mm', 'LETTER', true, 'UTF-8', false);
	$pdf->SetCreator('Isaias');
	$pdf->SetAuthor('Isaias');
	$pdf->SetTitle('GUIA');
	$pdf->SetSubject('DOCUMENTOS TRIBUTARIOS ELECTRONICOS');
	$pdf->SetKeywords('DTE');
	$pdf->setPrintHeader(false);//PARA EVITAR QUE SE DIBUJE UNA LINEA EN LA CABECERA
	$pdf->setPrintFooter(false);//PARA EVITAR QUE SE DIBUJE UNA LINEA EN EL PIE DE PAGINA
	$pdf->SetFooterMargin(0);
	$pdf->SetAutoPageBreak(false, $margin=0);
	
	$pdf->AddPage();
		
	$x_timbre = 20; 
	$x = 20; 
	$y = 222; 
	$w = 70;
	$ecl = version_compare(phpversion(), '7.0.0', '<') ? -1 : 5;
	$style = array(
	                'border' => false,
	                'padding' => 0,
	                'hpadding' => 0,
	                'vpadding' => 0,
	                'module_width' => 1, // width of a single module in points
	                'module_height' => 1, // height of a single module in points
	                'fgcolor' => array(0,0,0),
	                'bgcolor' => false//, // [255,255,255]
	            );
		            
	$pdf->write2DBarcode($XML_DTE, 'PDF417,,'.$ecl, $x_timbre, $y, $w, 0, $style, 'B');
	$pdf->SetFont('helvetica','B',5.40);	
	$pdf->Text(40, 259,utf8_encode('Timbre Electrónico SII'));
	$pdf->Text(22, 262,utf8_encode("Resolución $resolucion del $fecha_resolucion Verifique este documento en www.sii.cl"));	
	$imagen = dirname(__FILE__)."/../../images_appl/BIGGI_LOGO_DTE.png";
	$pdf->Image($imagen,9,5,67,28);
	$pdf->SetDrawColor(255,0,0);	
	$pdf->SetLineWidth(1);
	$pdf->Rect(136, 10, 69, 34);
	$pdf->SetTextColor(255,0,0);
	$pdf->SetFont('helvetica','B',12.25);	
	$pdf->Text(149, 15,'R.U.T.: 91.462.001-5');
	$pdf->Text(148, 22,'GUIA DE DESPACHO');
	$pdf->Text(154, 29,'ELECTRONICA');
	$pdf->Text(159, 36,utf8_encode('N°: ').$folio);
	$pdf->SetFont('helvetica','B',8.75);
	$pdf->Text(149, 45,'S.I.I - SANTIAGO CENTRO');
	$pdf->SetTextColor(0,0,0);
	$pdf->Text(10, 35,'COMERCIAL BIGGI CHILE S.A.');
	$pdf->SetFont('helvetica','',7.15);
	$pdf->Text(10, 40,'ARTEFACTOS PARA LA MANIPULACION COCCION Y TRANSPORTE DE ALIMENTOS');
	$pdf->Text(10, 45,utf8_encode('PORTUGAL N° 1726 - SANTIAGO - CHILE'));
	$pdf->Text(10, 50,'FONOS: (56-2)2412-6200 - 25552849 - FAX(56-2)24126201 - 25512750');
	$pdf->SetLineWidth(0.3);
	$pdf->SetDrawColor(0,0,0);
	$pdf->RoundedRect(10, 55, 196, 37, 3.5);
			
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
					,GD.GENERA_SALIDA
					,U.NOM_USUARIO
					,(SELECT NRO_FACTURA FROM FACTURA WHERE COD_FACTURA = GD.COD_FACTURA) NRO_FACTURA
					,(SELECT SUM((CANTIDAD * PRECIO))FROM ITEM_GUIA_DESPACHO WHERE COD_GUIA_DESPACHO = GD.COD_GUIA_DESPACHO)TOTAL_NETO
					,GD.COD_DOC
					,GD.RETIRADO_POR
					,(CAST(GD.RUT_RETIRADO_POR AS NVARCHAR(8)))+'-'+(CAST (GD.DIG_VERIF_RETIRADO_POR AS NVARCHAR(1))) as RUT_RETIRADO
					,GD.PATENTE
			from GUIA_DESPACHO GD, COMUNA C, CIUDAD CI, TIPO_GUIA_DESPACHO TGD, USUARIO U
			where GD.COD_COMUNA = C.COD_COMUNA
			and GD.COD_CIUDAD = CI.COD_CIUDAD
			and GD.COD_TIPO_GUIA_DESPACHO = TGD.COD_TIPO_GUIA_DESPACHO
			and GD.COD_GUIA_DESPACHO =  $cod_documento
			AND U.COD_USUARIO = GD.COD_USUARIO";
	$result = $db->build_results($sql);
	
	$FECHA_GUIA_DESPACHO	= utf8_encode($result[0]['FECHA_GUIA_DESPACHO']);
	$RUT_COMPLETO			= utf8_encode($result[0]['RUT_COMPLETO']);
	$NOM_EMPRESA			= utf8_encode($result[0]['NOM_EMPRESA']);
	$NRO_ORDEN_COMPRA		= utf8_encode($result[0]['NRO_ORDEN_COMPRA']);
	$DIRECCION				= utf8_encode($result[0]['DIRECCION']);
	$NOM_CIUDAD				= utf8_encode($result[0]['NOM_CIUDAD']);
	$GIRO					= utf8_encode($result[0]['GIRO']);
	$NOM_COMUNA				= utf8_encode($result[0]['NOM_COMUNA']);
	$TELEFONO				= utf8_encode($result[0]['TELEFONO']);
	$MAIL					= utf8_encode($result[0]['MAIL']);
	$NOM_TIPO_GUIA_DESPACHO	= utf8_encode($result[0]['NOM_TIPO_GUIA_DESPACHO']);
	$REFERENCIA				= utf8_encode($result[0]['REFERENCIA']);
	$OBS					= utf8_encode($result[0]['OBS']);
	$GENERA_SALIDA			= $result[0]['GENERA_SALIDA'];
	$NRO_NV					= utf8_encode($result[0]['COD_DOC']);
	$NRO_FACTURA			= $result[0]['NRO_FACTURA'];
	$TOTAL_NETO				= utf8_encode($result[0]['TOTAL_NETO']);
	$RUT_RETIRADO			= utf8_encode($result[0]['RUT_RETIRADO']);
	$RUT_RETIRADO			= (strlen($RUT_RETIRADO)==1) ? "" : "$RUT_RETIRADO";
	$RETIRADO_POR			= utf8_encode($result[0]['RETIRADO_POR']);
	$PATENTE				= utf8_encode($result[0]['PATENTE']);
	$EMISOR					= utf8_encode($result[0]['NOM_USUARIO']);
	
	$pdf->SetFont('helvetica','B',6.95);
	$pdf->SetXY(12, 56);
	$pdf->MultiCell(20, 30,"FECHA",0);
	$pdf->SetXY(35, 56);
	$pdf->MultiCell(20, 30,":");
	$pdf->SetFont('helvetica','',7.15);
	$pdf->SetXY(38, 56);
	$pdf->MultiCell(20, 30,$FECHA_GUIA_DESPACHO);
	$pdf->SetFont('helvetica','B',7.15);
	$pdf->SetXY(145, 56);
	$pdf->MultiCell(20, 30,"RUT.");
	$pdf->SetXY(160, 56);
	$pdf->MultiCell(20, 30,":");
	$pdf->SetXY(163, 56);
	$pdf->MultiCell(40, 30,$RUT_COMPLETO);
	
	$pdf->SetFont('helvetica','B',6.95);
	$pdf->SetXY(12, 60);
	$pdf->MultiCell(40, 30,utf8_encode("SEÑOR(ES)"));
	$pdf->SetXY(35, 60);
	$pdf->MultiCell(20, 30,":");
	$pdf->SetXY(38, 60);
	$pdf->MultiCell(100, 2,substr($NOM_EMPRESA,0,186),0,"L"); 
	$pdf->SetFont('helvetica','B',6.95);
	$pdf->SetXY(145, 60);
	$pdf->MultiCell(20, 30,"O.C.");
	$pdf->SetXY(160, 60);
	$pdf->MultiCell(20, 60,":");
	$pdf->SetFont('helvetica','',7.15);
	$pdf->SetXY(163, 60);
	$pdf->MultiCell(20, 30,$NRO_ORDEN_COMPRA);
	
	$pdf->SetFont('helvetica','B',6.95);
	$pdf->SetXY(12, 67);
	$pdf->MultiCell(40, 30,utf8_encode("DIRECCIÓN"));
	$pdf->SetXY(35, 67);
	$pdf->MultiCell(20, 1,":");
	$pdf->SetFont('helvetica','',7.15);
	$pdf->SetXY(38, 67);
	$pdf->MultiCell(95, 3,substr($DIRECCION,0,186),0,'L'); 
	$pdf->SetFont('helvetica','B',6.95);
	$pdf->SetXY(145, 67);
	$pdf->MultiCell(40, 30,"CIUDAD");
	$pdf->SetXY(160, 67);
	$pdf->MultiCell(20, 30,":");
	$pdf->SetFont('helvetica','',7.15);
	$pdf->SetXY(163, 67);
	$pdf->MultiCell(40, 30,$NOM_CIUDAD);
	
	$pdf->SetFont('helvetica','B',6.95);
	$pdf->SetXY(12, 73);
	$pdf->MultiCell(40, 1,"GIRO");
	$pdf->SetXY(35, 73);
	$pdf->MultiCell(20, 1,":");
	$pdf->SetFont('helvetica','',7.15);
	$pdf->SetXY(38, 73);
	$pdf->MultiCell(100, 1,substr($GIRO,0,65),0,'L'); 
	$pdf->SetFont('helvetica','B',6.95);
	$pdf->SetXY(145, 73);
	$pdf->MultiCell(40, 30,"COMUNA");
	$pdf->SetXY(160, 73);
	$pdf->MultiCell(20, 30,":");
	$pdf->SetFont('helvetica','',7.15);
	$pdf->SetXY(163, 73);
	$pdf->MultiCell(40, 1,$NOM_COMUNA,0,'L');
	
	$pdf->SetFont('helvetica','B',6.95);
	$pdf->SetXY(12, 77);
	$pdf->MultiCell(40, 1,"E-MAIL");
	$pdf->SetXY(35, 77);
	$pdf->MultiCell(20, 1,":");
	$pdf->SetFont('helvetica','',7.15);
	$pdf->SetXY(38, 77);
	$pdf->MultiCell(110, 1,substr($MAIL,0,75),0,'L');
	$pdf->SetFont('helvetica','B',6.95);
	$pdf->SetXY(145, 77);
	$pdf->MultiCell(40, 30,"FONO");
	$pdf->SetXY(160, 77);
	$pdf->MultiCell(20, 30,":");
	$pdf->SetFont('helvetica','',7.15);
	$pdf->SetXY(163, 77);
	$pdf->MultiCell(36, 1,$TELEFONO,0,'L'); 
	
	$pdf->SetFont('helvetica','B',6.95);
	$pdf->SetXY(12, 81);
	$pdf->MultiCell(40, 1,"COND. DE VENTA",0,'L');
	$pdf->SetXY(35, 81);
	$pdf->MultiCell(20, 30,":");
	$pdf->SetFont('helvetica','',7.15);
	$pdf->SetXY(38, 81);
	$pdf->MultiCell(100, 1,$NOM_FORMA_PAGO,0,'L');
	$pdf->SetFont('helvetica','B',6.95);
	$pdf->SetXY(145, 81);
	$pdf->MultiCell(40, 1,"EMISOR");
	$pdf->SetXY(160, 81);
	$pdf->MultiCell(20, 30,":");
	$pdf->SetFont('helvetica','',7.15);
	$pdf->SetXY(163, 81);
	$pdf->MultiCell(50, 1,substr($EMISOR,0,27),0,'L'); 
	
	$pdf->Line(10,85,206,85);
	
	$pdf->SetFont('helvetica','B',6.95);
	$pdf->SetXY(12, 85,5);
	$pdf->MultiCell(40, 1,"REFERENCIA",0,'L');
	$pdf->SetXY(35, 85.5);
	$pdf->MultiCell(3, 1,":",0);
	$pdf->SetXY(38, 85.5);
	$pdf->MultiCell(90, 2,$REFERENCIA,0,'L');  
	$pdf->Text(129,85.5,utf8_encode("N° NV : $NRO_NV")); 
	$pdf->Text(154,85.5,utf8_encode("FACTURA N°:")); 
	$pdf->SetXY(179.5, 85.5);
	$pdf->MultiCell(26, 6.5,$NRO_FACTURA,0,'L');
	
	$pdf->RoundedRect(10, 92, 196, 77.5, 3.5);
	$pdf->SetFont('helvetica','B',8);
	$pdf->SetXY(10, 93);
	$pdf->MultiCell(10, 1,"IT",0,'C');
	$pdf->Line(20,92,20,169.5);
	$pdf->SetXY(20, 93);
	$pdf->MultiCell(15, 1,"CT",0,'C');
	$pdf->Line(35,92,35,169.5);
	$pdf->SetXY(35, 93);
	$pdf->MultiCell(23, 1,"MODELO",0,'C');
	$pdf->Line(58,92,58,169.5);
	$pdf->SetXY(58, 93);
	$pdf->MultiCell(103, 1,"DETALLE",0,'C');
	$pdf->Line(161,92,161,169.5);
	$pdf->SetXY(161, 93);
	$pdf->MultiCell(21, 1,"P.UNIT.",0,'C');
	$pdf->Line(182,92,182,169.5);
	$pdf->SetXY(182, 93);
	$pdf->MultiCell(24, 1,"TOTAL",0,'C');
	
	$pdf->Line(10,97,206,97);
	
	/*************ITEMS***********/
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
					WHERE ITGD.COD_GUIA_DESPACHO = $cod_documento";
										
	$result2 = $db->build_results($SqlDetalles);
	
	$x = 2;
	$i = 2;
	$y = $pdf->GetY()-7.5; 
		
	foreach($result2 as $row){
		$pdf->SetFont('helvetica','',6.25);
		$pdf->SetXY($x+8, $y+(4*$i));
		$pdf->MultiCell(10, 1, $row['NroLinDet'],0,'C');
		$pdf->SetXY($x+18, $y+(4*$i));
		$pdf->MultiCell(15, 1, $row['CANTIDAD'],0,'C'); 
		$pdf->SetXY($x+33, $y+(4*$i));
		$pdf->MultiCell(23, 1,substr($row['VlrCodigo'],0,15) ,0,'C'); 
		$pdf->SetXY($x+56, $y+(4*$i));
		$pdf->MultiCell(103, 1,substr(utf8_encode($row['NmbItem']),0,85) ,0,'L'); 
		$pdf->SetXY($x+159, $y+(4*$i));
		$pdf->MultiCell(21, 1, number_format($row['PRECIO'],0,'.','.'),0,'R');
		$pdf->SetXY($x+180, $y+(4*$i));
		$pdf->MultiCell(24, 1, number_format($row['MONTO_TOTAL'],0,'.','.'),0,'R');
		
		$i++;			
	}
	/************************************************PIE PAGINA**********************************************/
	/*************OBSERVACIONES********/
	$pdf->RoundedRect(10, 171.5, 140, 47, 3.5);
	$pdf->SetFont('helvetica','B',6.95);
	$pdf->Text(12, 173,"NOTAS : ");
	$pdf->SetFont('helvetica','',7.15);
	$pdf->SetXY(23, 173);
	$pdf->MultiCell(124,34,substr($OBS,0,620) ,0,'L');
	$pdf->SetFont('helvetica','B',6.95);
	$pdf->Text(12, 213,"ESTADO SALIDA : ");
	if($GENERA_SALIDA == 'S'){
		$pdf->SetFont('helvetica','',7.15);
		$pdf->SetXY(35, 213);
		$pdf->MultiCell(20,1,"DESPACHADO",1,'C');
	}
	/*************TOTALES***********/
	$pdf->RoundedRect(160, 171.5, 46, 6, 3.5);
	$pdf->Line(182,171.5,182,177.5);
	$pdf->SetFont('helvetica','B',6.95);	
	$pdf->Text(169, 173,"TOTAL");
	$pdf->SetFont('helvetica','',7.15);
	$pdf->SetXY(180, 173);
	$pdf->MultiCell(26, 1,number_format($TOTAL_NETO,0,'.','.'),0,"R");
	
	
	$pdf->RoundedRect(96, 225, 109, 33, 3.5);
	$pdf->SetFont('helvetica','B',6.95);
	$pdf->Text(99, 230,"NOMBRE:");
	$pdf->SetFont('helvetica','',7.15);
	$pdf->Text(114, 230,substr($RETIRADO_POR,0,28));
	$pdf->Line(114,234,160,234);
	$pdf->SetFont('helvetica','B',6.95);
	$pdf->Text(161, 230,"FIRMA:");
	$pdf->Line(173,234,198,234);
	$pdf->Text(99, 239,"FECHA:");
	$pdf->SetFont('helvetica','',7.15);
	$pdf->Text(125, 239,$FECHA_GUIA_DESPACHO);
	$pdf->Line(114,243,160,243);
	$pdf->SetFont('helvetica','B',6.95);
	$pdf->Text(161,239,"R.U.T.:");
	$pdf->SetFont('helvetica','',7.15);
	$pdf->Text(173,239,$RUT_RETIRADO);
	$pdf->Text(173,244,$PATENTE);
	$pdf->Line(173,243,198,243);
	$pdf->SetFont('helvetica','B',6.95);
	$pdf->Text(99, 248,"RECINTO:");
	$pdf->Line(114,252,198,252);
	$pdf->SetFont('helvetica','',5.40);
	$pdf->SetXY(99, 253);
	$pdf->MultiCell(103, 2,utf8_encode("El acuse de recibo que se declara en este acto, de acuerdo a lo dispuesto en la letra b) del art. 4°, y la letra c) del Art. 5° de la Ley 19.983, acredita que la entega de mercadería(s) o servicio(s) prestado(s) ha(n) sido recibido(s)"),0,"L");
	
	$pdf->SetDrawColor(255,0,0);	
	$pdf->SetLineWidth(4);
	$pdf->Line(10,268,206,268);
	$pdf->SetFont('helvetica','',7.15);
	$pdf->SetTextColor(255,255,255);
	$pdf->Text(97, 266.5,"www.biggi.cl");
	
	/*************COPIA CEDIBLE **************/
	$pdf->AddPage();
		
	$x_timbre = 20; 
	$x = 20; 
	$y = 222; 
	$w = 70;
	$ecl = version_compare(phpversion(), '7.0.0', '<') ? -1 : 5;
	$style = array(
	                'border' => false,
	                'padding' => 0,
	                'hpadding' => 0,
	                'vpadding' => 0,
	                'module_width' => 1, // width of a single module in points
	                'module_height' => 1, // height of a single module in points
	                'fgcolor' => array(0,0,0),
	                'bgcolor' => false//, // [255,255,255]
	            );
		            
	$pdf->write2DBarcode($XML_DTE, 'PDF417,,'.$ecl, $x_timbre, $y, $w, 0, $style, 'B');
	$pdf->SetFont('helvetica','B',5.40);	
	$pdf->Text(40, 259,utf8_encode('Timbre Electrónico SII'));
	$pdf->Text(22, 262,utf8_encode("Resolución $resolucion del $fecha_resolucion Verifique este documento en www.sii.cl"));	
	$imagen = dirname(__FILE__)."/../../images_appl/BIGGI_LOGO_DTE.png";
	$pdf->Image($imagen,9,5,67,28);
	$pdf->SetDrawColor(255,0,0);	
	$pdf->SetLineWidth(1);
	$pdf->Rect(136, 10, 69, 34);
	$pdf->SetTextColor(255,0,0);
	$pdf->SetFont('helvetica','B',12.25);	
	$pdf->Text(149, 15,'R.U.T.: 91.462.001-5');
	$pdf->Text(148, 22,'GUIA DE DESPACHO');
	$pdf->Text(154, 29,'ELECTRONICA');
	$pdf->Text(159, 36,utf8_encode('N°: ').$folio);
	$pdf->SetFont('helvetica','B',8.75);
	$pdf->Text(149, 45,'S.I.I - SANTIAGO CENTRO');
	$pdf->SetTextColor(0,0,0);
	$pdf->Text(10, 35,'COMERCIAL BIGGI CHILE S.A.');
	$pdf->SetFont('helvetica','',7.15);
	$pdf->Text(10, 40,'ARTEFACTOS PARA LA MANIPULACION COCCION Y TRANSPORTE DE ALIMENTOS');
	$pdf->Text(10, 45,utf8_encode('PORTUGAL N° 1726 - SANTIAGO - CHILE'));
	$pdf->Text(10, 50,'FONOS: (56-2)2412-6200 - 25552849 - FAX(56-2)24126201 - 25512750');
	$pdf->SetLineWidth(0.3);
	$pdf->SetDrawColor(0,0,0);
	$pdf->RoundedRect(10, 55, 196, 37, 3.5);
			
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
					,GD.GENERA_SALIDA
					,U.NOM_USUARIO
					,(SELECT NRO_FACTURA FROM FACTURA WHERE COD_FACTURA = GD.COD_FACTURA) NRO_FACTURA
					,(SELECT SUM((CANTIDAD * PRECIO))FROM ITEM_GUIA_DESPACHO WHERE COD_GUIA_DESPACHO = GD.COD_GUIA_DESPACHO)TOTAL_NETO
					,GD.COD_DOC
					,GD.RETIRADO_POR
					,(CAST(GD.RUT_RETIRADO_POR AS NVARCHAR(8)))+'-'+(CAST (GD.DIG_VERIF_RETIRADO_POR AS NVARCHAR(1))) as RUT_RETIRADO
					,GD.PATENTE
			from GUIA_DESPACHO GD, COMUNA C, CIUDAD CI, TIPO_GUIA_DESPACHO TGD, USUARIO U
			where GD.COD_COMUNA = C.COD_COMUNA
			and GD.COD_CIUDAD = CI.COD_CIUDAD
			and GD.COD_TIPO_GUIA_DESPACHO = TGD.COD_TIPO_GUIA_DESPACHO
			and GD.COD_GUIA_DESPACHO =  $cod_documento
			AND U.COD_USUARIO = GD.COD_USUARIO";
	$result = $db->build_results($sql);
	
	$FECHA_GUIA_DESPACHO	= utf8_encode($result[0]['FECHA_GUIA_DESPACHO']);
	$RUT_COMPLETO			= utf8_encode($result[0]['RUT_COMPLETO']);
	$NOM_EMPRESA			= utf8_encode($result[0]['NOM_EMPRESA']);
	$NRO_ORDEN_COMPRA		= utf8_encode($result[0]['NRO_ORDEN_COMPRA']);
	$DIRECCION				= utf8_encode($result[0]['DIRECCION']);
	$NOM_CIUDAD				= utf8_encode($result[0]['NOM_CIUDAD']);
	$GIRO					= utf8_encode($result[0]['GIRO']);
	$NOM_COMUNA				= utf8_encode($result[0]['NOM_COMUNA']);
	$TELEFONO				= utf8_encode($result[0]['TELEFONO']);
	$MAIL					= utf8_encode($result[0]['MAIL']);
	$NOM_TIPO_GUIA_DESPACHO	= utf8_encode($result[0]['NOM_TIPO_GUIA_DESPACHO']);
	$REFERENCIA				= utf8_encode($result[0]['REFERENCIA']);
	$OBS					= utf8_encode($result[0]['OBS']);
	$GENERA_SALIDA			= $result[0]['GENERA_SALIDA'];
	$NRO_NV					= utf8_encode($result[0]['COD_DOC']);
	$NRO_FACTURA			= $result[0]['NRO_FACTURA'];
	$TOTAL_NETO				= utf8_encode($result[0]['TOTAL_NETO']);
	$RUT_RETIRADO			= utf8_encode($result[0]['RUT_RETIRADO']);
	$RUT_RETIRADO			= (strlen($RUT_RETIRADO)==1) ? "" : "$RUT_RETIRADO";
	$RETIRADO_POR			= utf8_encode($result[0]['RETIRADO_POR']);
	$PATENTE				= utf8_encode($result[0]['PATENTE']);
	$EMISOR					= utf8_encode($result[0]['NOM_USUARIO']);
	
	$pdf->SetFont('helvetica','B',6.95);
	$pdf->SetXY(12, 56);
	$pdf->MultiCell(20, 30,"FECHA",0);
	$pdf->SetXY(35, 56);
	$pdf->MultiCell(20, 30,":");
	$pdf->SetFont('helvetica','',7.15);
	$pdf->SetXY(38, 56);
	$pdf->MultiCell(20, 30,$FECHA_GUIA_DESPACHO);
	$pdf->SetFont('helvetica','B',7.15);
	$pdf->SetXY(145, 56);
	$pdf->MultiCell(20, 30,"RUT.");
	$pdf->SetXY(160, 56);
	$pdf->MultiCell(20, 30,":");
	$pdf->SetXY(163, 56);
	$pdf->MultiCell(40, 30,$RUT_COMPLETO);
	
	$pdf->SetFont('helvetica','B',6.95);
	$pdf->SetXY(12, 60);
	$pdf->MultiCell(40, 30,utf8_encode("SEÑOR(ES)"));
	$pdf->SetXY(35, 60);
	$pdf->MultiCell(20, 30,":");
	$pdf->SetXY(38, 60);
	$pdf->MultiCell(100, 2,substr($NOM_EMPRESA,0,186),0,"L"); 
	$pdf->SetFont('helvetica','B',6.95);
	$pdf->SetXY(145, 60);
	$pdf->MultiCell(20, 30,"O.C.");
	$pdf->SetXY(160, 60);
	$pdf->MultiCell(20, 60,":");
	$pdf->SetFont('helvetica','',7.15);
	$pdf->SetXY(163, 60);
	$pdf->MultiCell(20, 30,$NRO_ORDEN_COMPRA);
	
	$pdf->SetFont('helvetica','B',6.95);
	$pdf->SetXY(12, 67);
	$pdf->MultiCell(40, 30,utf8_encode("DIRECCIÓN"));
	$pdf->SetXY(35, 67);
	$pdf->MultiCell(20, 1,":");
	$pdf->SetFont('helvetica','',7.15);
	$pdf->SetXY(38, 67);
	$pdf->MultiCell(95, 3,substr($DIRECCION,0,186),0,'L'); 
	$pdf->SetFont('helvetica','B',6.95);
	$pdf->SetXY(145, 67);
	$pdf->MultiCell(40, 30,"CIUDAD");
	$pdf->SetXY(160, 67);
	$pdf->MultiCell(20, 30,":");
	$pdf->SetFont('helvetica','',7.15);
	$pdf->SetXY(163, 67);
	$pdf->MultiCell(40, 30,$NOM_CIUDAD);
	
	$pdf->SetFont('helvetica','B',6.95);
	$pdf->SetXY(12, 73);
	$pdf->MultiCell(40, 1,"GIRO");
	$pdf->SetXY(35, 73);
	$pdf->MultiCell(20, 1,":");
	$pdf->SetFont('helvetica','',7.15);
	$pdf->SetXY(38, 73);
	$pdf->MultiCell(100, 1,substr($GIRO,0,65),0,'L'); 
	$pdf->SetFont('helvetica','B',6.95);
	$pdf->SetXY(145, 73);
	$pdf->MultiCell(40, 30,"COMUNA");
	$pdf->SetXY(160, 73);
	$pdf->MultiCell(20, 30,":");
	$pdf->SetFont('helvetica','',7.15);
	$pdf->SetXY(163, 73);
	$pdf->MultiCell(40, 1,$NOM_COMUNA,0,'L');
	
	$pdf->SetFont('helvetica','B',6.95);
	$pdf->SetXY(12, 77);
	$pdf->MultiCell(40, 1,"E-MAIL");
	$pdf->SetXY(35, 77);
	$pdf->MultiCell(20, 1,":");
	$pdf->SetFont('helvetica','',7.15);
	$pdf->SetXY(38, 77);
	$pdf->MultiCell(110, 1,substr($MAIL,0,75),0,'L');
	$pdf->SetFont('helvetica','B',6.95);
	$pdf->SetXY(145, 77);
	$pdf->MultiCell(40, 30,"FONO");
	$pdf->SetXY(160, 77);
	$pdf->MultiCell(20, 30,":");
	$pdf->SetFont('helvetica','',7.15);
	$pdf->SetXY(163, 77);
	$pdf->MultiCell(36, 1,$TELEFONO,0,'L'); 
	
	$pdf->SetFont('helvetica','B',6.95);
	$pdf->SetXY(12, 81);
	$pdf->MultiCell(40, 1,"COND. DE VENTA",0,'L');
	$pdf->SetXY(35, 81);
	$pdf->MultiCell(20, 30,":");
	$pdf->SetFont('helvetica','',7.15);
	$pdf->SetXY(38, 81);
	$pdf->MultiCell(100, 1,$NOM_FORMA_PAGO,0,'L');
	$pdf->SetFont('helvetica','B',6.95);
	$pdf->SetXY(145, 81);
	$pdf->MultiCell(40, 1,"EMISOR");
	$pdf->SetXY(160, 81);
	$pdf->MultiCell(20, 30,":");
	$pdf->SetFont('helvetica','',7.15);
	$pdf->SetXY(163, 81);
	$pdf->MultiCell(50, 1,substr($EMISOR,0,27),0,'L'); 
	
	$pdf->Line(10,85,206,85);
	
	$pdf->SetFont('helvetica','B',6.95);
	$pdf->SetXY(12, 85,5);
	$pdf->MultiCell(40, 1,"REFERENCIA",0,'L');
	$pdf->SetXY(35, 85.5);
	$pdf->MultiCell(3, 1,":",0);
	$pdf->SetXY(38, 85.5);
	$pdf->MultiCell(90, 2,$REFERENCIA,0,'L');  
	$pdf->Text(129,85.5,utf8_encode("N° NV : $NRO_NV")); 
	$pdf->Text(154,85.5,utf8_encode("FACTURA N°:")); 
	$pdf->SetXY(179.5, 85.5);
	$pdf->MultiCell(26, 6.5,$NRO_FACTURA,0,'L');
	
	$pdf->RoundedRect(10, 92, 196, 77.5, 3.5);
	$pdf->SetFont('helvetica','B',8);
	$pdf->SetXY(10, 93);
	$pdf->MultiCell(10, 1,"IT",0,'C');
	$pdf->Line(20,92,20,169.5);
	$pdf->SetXY(20, 93);
	$pdf->MultiCell(15, 1,"CT",0,'C');
	$pdf->Line(35,92,35,169.5);
	$pdf->SetXY(35, 93);
	$pdf->MultiCell(23, 1,"MODELO",0,'C');
	$pdf->Line(58,92,58,169.5);
	$pdf->SetXY(58, 93);
	$pdf->MultiCell(103, 1,"DETALLE",0,'C');
	$pdf->Line(161,92,161,169.5);
	$pdf->SetXY(161, 93);
	$pdf->MultiCell(21, 1,"P.UNIT.",0,'C');
	$pdf->Line(182,92,182,169.5);
	$pdf->SetXY(182, 93);
	$pdf->MultiCell(24, 1,"TOTAL",0,'C');
	
	$pdf->Line(10,97,206,97);
	
	/*************ITEMS***********/
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
					WHERE ITGD.COD_GUIA_DESPACHO = $cod_documento";
										
	$result2 = $db->build_results($SqlDetalles);
	
	$x = 2;
	$i = 2;
	$y = $pdf->GetY()-7.5; 
		
	foreach($result2 as $row){
		$pdf->SetFont('helvetica','',6.25);
		$pdf->SetXY($x+8, $y+(4*$i));
		$pdf->MultiCell(10, 1, $row['NroLinDet'],0,'C');
		$pdf->SetXY($x+18, $y+(4*$i));
		$pdf->MultiCell(15, 1, $row['CANTIDAD'],0,'C'); 
		$pdf->SetXY($x+33, $y+(4*$i));
		$pdf->MultiCell(23, 1,substr($row['VlrCodigo'],0,15) ,0,'C'); 
		$pdf->SetXY($x+56, $y+(4*$i));
		$pdf->MultiCell(103, 1,substr(utf8_encode($row['NmbItem']),0,85) ,0,'L'); 
		$pdf->SetXY($x+159, $y+(4*$i));
		$pdf->MultiCell(21, 1, number_format($row['PRECIO'],0,'.','.'),0,'R');
		$pdf->SetXY($x+180, $y+(4*$i));
		$pdf->MultiCell(24, 1, number_format($row['MONTO_TOTAL'],0,'.','.'),0,'R');
		
		$i++;			
	}
	/************************************************PIE PAGINA**********************************************/
	/*************OBSERVACIONES********/
	$pdf->RoundedRect(10, 171.5, 140, 47, 3.5);
	$pdf->SetFont('helvetica','B',6.95);
	$pdf->Text(12, 173,"NOTAS : ");
	$pdf->SetFont('helvetica','',7.15);
	$pdf->SetXY(23, 173);
	$pdf->MultiCell(124,34,substr($OBS,0,620) ,0,'L');
	$pdf->SetFont('helvetica','B',6.95);
	$pdf->Text(12, 213,"ESTADO SALIDA : ");
	if($GENERA_SALIDA == 'S'){
		$pdf->SetFont('helvetica','',7.15);
		$pdf->SetXY(35, 213);
		$pdf->MultiCell(20,1,"DESPACHADO",1,'C');
	}
	/*************TOTALES***********/
	$pdf->RoundedRect(160, 171.5, 46, 6, 3.5);
	$pdf->Line(182,171.5,182,177.5);
	$pdf->SetFont('helvetica','B',6.95);	
	$pdf->Text(169, 173,"TOTAL");
	$pdf->SetFont('helvetica','',7.15);
	$pdf->SetXY(180, 173);
	$pdf->MultiCell(26, 1,number_format($TOTAL_NETO,0,'.','.'),0,"R");
	
	
	$pdf->RoundedRect(96, 225, 109, 33, 3.5);
	$pdf->SetFont('helvetica','B',6.95);
	$pdf->Text(99, 230,"NOMBRE:");
	$pdf->SetFont('helvetica','',7.15);
	$pdf->Text(114, 230,substr($RETIRADO_POR,0,28));
	$pdf->Line(114,234,160,234);
	$pdf->SetFont('helvetica','B',6.95);
	$pdf->Text(161, 230,"FIRMA:");
	$pdf->Line(173,234,198,234);
	$pdf->Text(99, 239,"FECHA:");
	$pdf->SetFont('helvetica','',7.15);
	$pdf->Text(125, 239,$FECHA_GUIA_DESPACHO);
	$pdf->Line(114,243,160,243);
	$pdf->SetFont('helvetica','B',6.95);
	$pdf->Text(161,239,"R.U.T.:");
	$pdf->SetFont('helvetica','',7.15);
	$pdf->Text(173,239,$RUT_RETIRADO);
	$pdf->Text(173,244,$PATENTE);
	$pdf->Line(173,243,198,243);
	$pdf->SetFont('helvetica','B',6.95);
	$pdf->Text(99, 248,"RECINTO:");
	$pdf->Line(114,252,198,252);
	$pdf->SetFont('helvetica','',5.40);
	$pdf->SetXY(99, 253);
	$pdf->MultiCell(103, 2,utf8_encode("El acuse de recibo que se declara en este acto, de acuerdo a lo dispuesto en la letra b) del art. 4°, y la letra c) del Art. 5° de la Ley 19.983, acredita que la entega de mercadería(s) o servicio(s) prestado(s) ha(n) sido recibido(s)"),0,"L");
	
	$pdf->SetDrawColor(255,0,0);	
	$pdf->SetLineWidth(4);
	$pdf->Line(10,268,206,268);
	$pdf->SetFont('helvetica','',7.15);
	$pdf->SetTextColor(255,255,255);
	$pdf->Text(97, 266.5,"www.biggi.cl");
	
	$pdf->SetFont('helvetica','B',6.95);
	$pdf->SetDrawColor(0,0,0);	
	$pdf->SetLineWidth(0.3);
	$pdf->SetTextColor(0,0,0);
	$pdf->SetXY(192.7, 270.5);
	$pdf->Cell(13, 1,'CEDIBLE',1,"C");
	
	$pdf->Output("52_$folio.pdf", 'I');	
	break;
	/////////////   NOTA CREDITO   //////////////
	case 61 :
	//RECUPERAMOS LOS DATOS GUARDADO EN LA BD
	$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
	$Sqlpdf = " SELECT  NRO_NOTA_CREDITO
						,REPLACE(REPLACE(dbo.f_get_parametro(20),'.',''),'-8','') as RUTEMISOR
						,XML_DTE
				FROM NOTA_CREDITO
				WHERE COD_NOTA_CREDITO = $cod_documento";  
	$Result_pdf = $db->build_results($Sqlpdf);
	
	$folio	= $Result_pdf[0]['NRO_NOTA_CREDITO'];
	$emisor	= $Result_pdf[0]['RUTEMISOR'];
	
	$XML_DTE    = $Result_pdf[0]['XML_DTE'];
		
	$XML_DTE = base64_decode($XML_DTE);
	$xml_resolucion = simplexml_load_string($XML_DTE);
	$resolucion		= $xml_resolucion->SetDTE->Caratula->NroResol;
	$fecha_resolucion = $xml_resolucion->SetDTE->Caratula->FchResol;
		
	$XML_DTE = ereg_replace("[^A-Za-z0-9]", "", $XML_DTE);	
	$XML_DTE = strstr($XML_DTE, '<TED'); //separo el xml en el string "<TED"
	$len = strlen(strstr($XML_DTE, '</TED')); //realiazo la lectura en donde termina el tag "</TED" len
	$XML_DTE = substr($XML_DTE,0,-$len+6);   //resto resto del len y suno 6 cacarterers del </TED>
		
	$pdf = new TCPDF('P', 'mm', 'LETTER', true, 'UTF-8', false);
	$pdf->SetCreator('Isaias');
	$pdf->SetAuthor('Isaias');
	$pdf->SetTitle('NOTA CREDITO');
	$pdf->SetSubject('DOCUMENTOS TRIBUTARIOS ELECTRONICOS');
	$pdf->SetKeywords('DTE');
	$pdf->setPrintHeader(false);//PARA EVITAR QUE SE DIBUJE UNA LINEA EN LA CABECERA
	$pdf->setPrintFooter(false);//PARA EVITAR QUE SE DIBUJE UNA LINEA EN EL PIE DE PAGINA
	$pdf->SetFooterMargin(0);
	$pdf->SetAutoPageBreak(false, $margin=0);
	
	$pdf->AddPage();
	
	$x_timbre = 20; 
	$x = 20; 
	$y = 222; 
	$w = 70;
	$ecl = version_compare(phpversion(), '7.0.0', '<') ? -1 : 5;
	$style = array(
	                'border' => false,
	                'padding' => 0,
	                'hpadding' => 0,
	                'vpadding' => 0,
	                'module_width' => 1, // width of a single module in points
	                'module_height' => 1, // height of a single module in points
	                'fgcolor' => array(0,0,0),
	                'bgcolor' => false//, // [255,255,255]
	            );
		            
	$pdf->write2DBarcode($XML_DTE, 'PDF417,,'.$ecl, $x_timbre, $y+7, $w, 0, $style, 'B');
	$pdf->SetFont('helvetica','B',5.40);	
	$pdf->Text(40, 259+7,utf8_encode('Timbre Electrónico SII'));
	$pdf->Text(22, 262+7,utf8_encode("Resolución $resolucion del $fecha_resolucion Verifique este documento en www.sii.cl"));
		
	$imagen = dirname(__FILE__)."/../../images_appl/BIGGI_LOGO_DTE.png";
	$pdf->Image($imagen,9,0.5,60,28);
	$pdf->SetDrawColor(255,0,0);	
	$pdf->SetLineWidth(1);
	$pdf->Rect(136, 3, 69, 33);
	$pdf->SetTextColor(255,0,0);
	$pdf->SetFont('helvetica','B',12.25);	
	$pdf->Text(149, 15-7,'R.U.T.: 91.462.001-5');
	$pdf->Text(148, 22-7,'NOTA DE CREDITO');
	$pdf->Text(154, 29-7,'ELECTRONICA');
	$pdf->Text(159, 36-7,utf8_encode('N°: ').$folio);
	$pdf->SetFont('helvetica','B',8.75);
	$pdf->Text(149, 45-7,'S.I.I - SANTIAGO CENTRO');
	$pdf->SetTextColor(0,0,0);
	$pdf->Text(10, 35-7,'COMERCIAL BIGGI CHILE S.A.');
	$pdf->SetFont('helvetica','',7.15);
	$pdf->Text(10, 40-7,'ARTEFACTOS PARA LA MANIPULACION COCCION Y TRANSPORTE DE ALIMENTOS');
	$pdf->Text(10, 45-7,utf8_encode('PORTUGAL N° 1726 - SANTIAGO - CHILE'));
	$pdf->Text(10, 50-7,'FONOS: (56-2)2412-6200 - 25552849 - FAX(56-2)24126201 - 25512750');
	$pdf->SetLineWidth(0.3);
	$pdf->SetDrawColor(0,0,0);
	$pdf->RoundedRect(10, 55-7, 196, 37, 3.5);
			
	/*SELECT CABECERA*/
	$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

	$sql ="SELECT convert(varchar(10),N.FECHA_NOTA_CREDITO,103) FECHA_NOTA_CREDITO
					,(CAST(N.RUT AS NVARCHAR(8)))+'-'+(CAST (N.DIG_VERIF AS NVARCHAR(1))) as RUT_COMPLETO
					,N.NOM_EMPRESA
					,FA.NRO_ORDEN_COMPRA 
					,N.DIRECCION
					,CI.NOM_CIUDAD
					,N.GIRO
					,C.NOM_COMUNA
					,N.MAIL
					,N.TELEFONO
					,N.REFERENCIA
					,FA.NRO_FACTURA
					,CONVERT(VARCHAR(20),FA.FECHA_FACTURA,103)FECHA_FACTURA
					,TNC.NOM_TIPO_NOTA_CREDITO
					,N.OBS
					,N.SUBTOTAL
					,N.MONTO_DSCTO1
					,N.MONTO_DSCTO2
					,N.TOTAL_NETO
					,N.MONTO_IVA
					,N.TOTAL_CON_IVA
					,U.NOM_USUARIO
					,(SELECT COD_DOC FROM FACTURA WHERE COD_FACTURA = N.COD_DOC)NRO_NV
					,N.COD_DOC
					,N.COD_TIPO_NC_INTERNO_SII
					,N.PORC_IVA
					,N.PORC_DSCTO1
					,N.PORC_DSCTO2
					,case when N.MONTO_IVA = 0 
						then N.TOTAL_NETO
						else null
					end	TOTAL_EXENTO
			FROM NOTA_CREDITO N, FACTURA FA,COMUNA C, CIUDAD CI,TIPO_NOTA_CREDITO TNC, USUARIO U
			WHERE N.COD_NOTA_CREDITO = $cod_documento
			AND N.COD_DOC = FA.COD_FACTURA
			AND C.COD_COMUNA = N.COD_COMUNA
			AND N.COD_CIUDAD = CI.COD_CIUDAD
			and N.COD_TIPO_NOTA_CREDITO = TNC.COD_TIPO_NOTA_CREDITO
			AND U.COD_USUARIO = N.COD_USUARIO";
	$result = $db->build_results($sql);
	
	$FECHA_NOTA_CREDITO		= utf8_encode($result[0]['FECHA_NOTA_CREDITO']);
	$RUT_COMPLETO			= utf8_encode($result[0]['RUT_COMPLETO']);
	$NOM_EMPRESA			= utf8_encode($result[0]['NOM_EMPRESA']);
	$NRO_ORDEN_COMPRA		= utf8_encode($result[0]['NRO_ORDEN_COMPRA']);
	$DIRECCION				= utf8_encode($result[0]['DIRECCION']);
	$NOM_CIUDAD				= utf8_encode($result[0]['NOM_CIUDAD']);
	$GIRO					= utf8_encode($result[0]['GIRO']);
	$NOM_COMUNA				= utf8_encode($result[0]['NOM_COMUNA']);
	$MAIL					= utf8_encode($result[0]['MAIL']);
	$TELEFONO				= utf8_encode($result[0]['TELEFONO']);
	$REFERENCIA				= utf8_encode($result[0]['REFERENCIA']);
	$NRO_FACTURA			= utf8_encode($result[0]['NRO_FACTURA']);
	$FECHA_FACTURA			= utf8_encode($result[0]['FECHA_FACTURA']);
	$NOM_TIPO_NOTA_CREDITO	= utf8_encode($result[0]['NOM_TIPO_NOTA_CREDITO']);
	$OBS					= utf8_encode($result[0]['OBS']);
	$SUBTOTAL				= utf8_encode($result[0]['SUBTOTAL']);
	$MONTO_DSCTO1			= utf8_encode($result[0]['MONTO_DSCTO1']);
	$MONTO_DSCTO2			= utf8_encode($result[0]['MONTO_DSCTO2']);
	$TOTAL_NETO				= utf8_encode($result[0]['TOTAL_NETO']);
	$MONTO_IVA				= utf8_encode($result[0]['MONTO_IVA']);
	$TOTAL_CON_IVA			= utf8_encode($result[0]['TOTAL_CON_IVA']);
	$EMISOR					= utf8_encode($result[0]['NOM_USUARIO']);
	$NRO_NV					= utf8_encode($result[0]['NRO_NV']);
	$FOLIO_FACTURA			= utf8_encode($result[0]['COD_DOC']);
	$COD_TIPO_NC			= $result[0]['COD_TIPO_NC_INTERNO_SII'];
	$PORC_IVA				= $result[0]['PORC_IVA'];
			$PORC_DSCTO1			= $result[0]['PORC_DSCTO1'];
	$PORC_DSCTO2			= $result[0]['PORC_DSCTO2'];
	$TOTAL_EXENTO			= $result[0]['TOTAL_EXENTO'];
	
	if ($TOTAL_EXENTO == '') 
		$TOTAL_EXENTO = '';
	else
		$TOTAL_EXENTO = number_format($TOTAL_EXENTO,0,'.','.');
		
	$TOTAL_EN_PALABRAS =  Numbers_Words::toWords($TOTAL_CON_IVA,"es"); 
	$TOTAL_EN_PALABRAS = strtr($TOTAL_EN_PALABRAS, "áéíóú", "aeiou");
	$TOTAL_EN_PALABRAS = strtoupper($TOTAL_EN_PALABRAS);
	
	
	
	$pdf->SetFont('helvetica','B',6.95);
	$pdf->SetXY(12, 56-7);
	$pdf->MultiCell(20, 30,"FECHA",0);
	$pdf->SetXY(35, 56-7);
	$pdf->MultiCell(20, 30,":");
	$pdf->SetFont('helvetica','',7.15);
	$pdf->SetXY(38, 56-7);
	$pdf->MultiCell(20, 30,$FECHA_NOTA_CREDITO);
	$pdf->SetFont('helvetica','B',7.15);
	$pdf->SetXY(145, 56-7);
	$pdf->MultiCell(20, 30,"RUT.");
	$pdf->SetXY(160, 56-7);
	$pdf->MultiCell(20, 30,":");
	$pdf->SetXY(163, 56-7);
	$pdf->MultiCell(40, 30,$RUT_COMPLETO);
	
	$pdf->SetFont('helvetica','B',6.95);
	$pdf->SetXY(12, 60-7);
	$pdf->MultiCell(40, 30,utf8_encode("SEÑOR(ES)"));
	$pdf->SetXY(35, 60-7);
	$pdf->MultiCell(20, 30,":");
	$pdf->SetXY(38, 60-7);
	$pdf->MultiCell(100, 2,substr($NOM_EMPRESA,0,186),0,"L"); 
	$pdf->SetFont('helvetica','B',6.95);
	$pdf->SetXY(145, 60-7);
	$pdf->MultiCell(20, 30,"O.C.");
	$pdf->SetXY(160, 60-7);
	$pdf->MultiCell(20, 60,":");
	$pdf->SetFont('helvetica','',7.15);
	$pdf->SetXY(163, 60-7);
	$pdf->MultiCell(20, 30,$NRO_ORDEN_COMPRA);
	
	$pdf->SetFont('helvetica','B',6.95);
	$pdf->SetXY(12, 67-7);
	$pdf->MultiCell(40, 30,utf8_encode("DIRECCIÓN"));
	$pdf->SetXY(35, 67-7);
	$pdf->MultiCell(20, 1,":");
	$pdf->SetFont('helvetica','',7.15);
	$pdf->SetXY(38, 67-7);
	$pdf->MultiCell(95, 3,substr($DIRECCION,0,186),0,'L'); 
	$pdf->SetFont('helvetica','B',6.95);
	$pdf->SetXY(145, 67-7);
	$pdf->MultiCell(40, 30,"CIUDAD");
	$pdf->SetXY(160, 67-7);
	$pdf->MultiCell(20, 30,":");
	$pdf->SetFont('helvetica','',7.15);
	$pdf->SetXY(163, 67-7);
	$pdf->MultiCell(40, 30,$NOM_CIUDAD);
	
	$pdf->SetFont('helvetica','B',6.95);
	$pdf->SetXY(12, 73-7);
	$pdf->MultiCell(40, 1,"GIRO");
	$pdf->SetXY(35, 73-7);
	$pdf->MultiCell(20, 1,":");
	$pdf->SetFont('helvetica','',7.15);
	$pdf->SetXY(38, 73-7);
	$pdf->MultiCell(100, 1,substr($GIRO,0,65),0,'L'); 
	$pdf->SetFont('helvetica','B',6.95);
	$pdf->SetXY(145, 73-7);
	$pdf->MultiCell(40, 30,"COMUNA");
	$pdf->SetXY(160, 73-7);
	$pdf->MultiCell(20, 30,":");
	$pdf->SetFont('helvetica','',7.15);
	$pdf->SetXY(163, 73-7);
	$pdf->MultiCell(40, 1,$NOM_COMUNA,0,'L');
	
	$pdf->SetFont('helvetica','B',6.95);
	$pdf->SetXY(12, 77-7);
	$pdf->MultiCell(40, 1,"E-MAIL");
	$pdf->SetXY(35, 77-7);
	$pdf->MultiCell(20, 1,":");
	$pdf->SetFont('helvetica','',7.15);
	$pdf->SetXY(38, 77-7);
	$pdf->MultiCell(110, 1,substr($MAIL,0,75),0,'L');
	$pdf->SetFont('helvetica','B',6.95);
	$pdf->SetXY(145, 77-7);
	$pdf->MultiCell(40, 30,"FONO");
	$pdf->SetXY(160, 77-7);
	$pdf->MultiCell(20, 30,":");
	$pdf->SetFont('helvetica','',7.15);
	$pdf->SetXY(163, 77-7);
	$pdf->MultiCell(36, 1,$TELEFONO,0,'L'); 
	
	$pdf->SetFont('helvetica','B',6.95);
	$pdf->SetXY(12, 81-7);
	$pdf->MultiCell(40, 1,"COND. DE VENTA",0,'L');
	$pdf->SetXY(35, 81-7);
	$pdf->MultiCell(20, 30,":");
	$pdf->SetFont('helvetica','',7.15);
	$pdf->SetXY(38, 81-7);
	$pdf->MultiCell(100, 1,$NOM_FORMA_PAGO,0,'L');
	$pdf->SetFont('helvetica','B',6.95);
	$pdf->SetXY(145, 81-7);
	$pdf->MultiCell(40, 1,"EMISOR");
	$pdf->SetXY(160, 81-7);
	$pdf->MultiCell(20, 30,":");
	$pdf->SetFont('helvetica','',7.15);
	$pdf->SetXY(163, 81-7);
	$pdf->MultiCell(50, 1,substr($EMISOR,0,27),0,'L'); 
	
	$pdf->SetFont('helvetica','B',6.95);
	$pdf->SetXY(12, 85.5-7);
	$pdf->MultiCell(40, 1,"REFERENCIA",0,'L');
	$pdf->SetXY(35, 85.5-7);
	$pdf->MultiCell(3, 1,":",0);
	$pdf->SetXY(38, 85.5-7);
	$pdf->MultiCell(90, 2,$REFERENCIA,0,'L');  
	$pdf->Text(129,85.5-7,utf8_encode("N° NV : $NRO_NV")); 
	
	$pdf->Line(10,78,206,78);
	
	$pdf->SetFont('helvetica','B',6.95);
	$pdf->Text(10,86.5,"TIPO REFERENCIA");
	$pdf->Text(70,86.5,"FOLIO"); 
	$pdf->Text(100,86.5,"FECHA"); 
	$pdf->Text(140,86.5,"MOTIVO REFERENCIA"); 
	
	$pdf->SetFont('helvetica','',7.15);
	if($PORC_IVA > 0)
		$pdf->Text(10,90.5,utf8_encode('Factura Afecta Electrónica'));
	else
		$pdf->Text(10,90.5,utf8_encode('Factura Exenta Electrónica'));	
	$pdf->Text(70,90.5,$NRO_FACTURA);
	$pdf->Text(100,90.5,$FECHA_FACTURA);
	if($COD_TIPO_NC == 1 || $COD_TIPO_NC == 1)
		$pdf->Text(140,90.5,'Anula documento de referencia');
	else if($COD_TIPO_NC == 3 || $COD_TIPO_NC == 4)	
		$pdf->Text(140,90.5,'Corrige montos');
	else if($COD_TIPO_NC == 5)	
		$pdf->Text(140,90.5,'Corrige texto documento de referencia');
	
	$pdf->RoundedRect(10, 92+7, 196, 77.5, 3.5);
	$pdf->SetFont('helvetica','B',8);
	$pdf->SetXY(10, 93+7);
	$pdf->MultiCell(10, 1,"IT",0,'C');
	$pdf->Line(20,92+7,20,169.5+7);
	$pdf->SetXY(20, 93+7);
	$pdf->MultiCell(15, 1,"CT",0,'C');
	$pdf->Line(35,92+7,35,169.5+7);
	$pdf->SetXY(35, 93+7);
	$pdf->MultiCell(23, 1,"MODELO",0,'C');
	$pdf->Line(58,92+7,58,169.5+7);
	$pdf->SetXY(58, 93+7);
	$pdf->MultiCell(103, 1,"DETALLE",0,'C');
	$pdf->Line(161,92+7,161,169.5+7);
	$pdf->SetXY(161, 93+7);
	$pdf->MultiCell(21, 1,"P.UNIT.",0,'C');
	$pdf->Line(182,92+7,182,169.5+7);
	$pdf->SetXY(182, 93+7);
	$pdf->MultiCell(24, 1,"TOTAL",0,'C');
	
	$pdf->Line(10,85+19,206,85+19);
	
	/*************ITEMS***********/
	$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
      
	$SqlDetalles ="SELECT ROW_NUMBER()OVER(ORDER BY PRECIO DESC) AS NroLinDet,
						('INT1')AS TpoCodigo,
						ITC.NOM_PRODUCTO AS NmbItem,
						ITC.COD_PRODUCTO AS VlrCodigo,
						LEN (ITC.COD_PRODUCTO) LENTM,
						ITC.CANTIDAD, 
						ITC.PRECIO,
						(ITC.CANTIDAD * ITC.PRECIO) AS MONTO_TOTAL
					FROM ITEM_NOTA_CREDITO ITC WHERE ITC.COD_NOTA_CREDITO = $cod_documento";
	$result2 = $db->build_results($SqlDetalles);
	
	$x = 2;
	$i = 2;
	$y = $pdf->GetY()-7.5; 
		
	foreach($result2 as $row){
		$pdf->SetFont('helvetica','',6.25);
		$pdf->SetXY($x+8, $y+(4*$i));
		$pdf->MultiCell(10, 1, $row['NroLinDet'],0,'C');
		$pdf->SetXY($x+18, $y+(4*$i));
		$pdf->MultiCell(15, 1, $row['CANTIDAD'],0,'C'); 
		$pdf->SetXY($x+33, $y+(4*$i));
		$pdf->MultiCell(23, 1,substr($row['VlrCodigo'],0,15) ,0,'C'); 
		$pdf->SetXY($x+56, $y+(4*$i));
		$pdf->MultiCell(103, 1,substr(utf8_encode($row['NmbItem']),0,85) ,0,'L'); 
		$pdf->SetXY($x+159, $y+(4*$i));
		$pdf->MultiCell(21, 1, number_format($row['PRECIO'],0,'.','.'),0,'R');
		$pdf->SetXY($x+180, $y+(4*$i));
		$pdf->MultiCell(24, 1, number_format($row['MONTO_TOTAL'],0,'.','.'),0,'R');
	
		$i++;			
	}
	/*****************************************PIE PAGINA************************************************/
	/*************OBSERVACIONES********/
	$pdf->RoundedRect(10, 171.5+7, 140, 47, 3.5);
	$pdf->SetFont('helvetica','B',6.95);	
	$pdf->Text(15, 173+7,"SON : ");
	$pdf->SetFont('helvetica','',7.15);
	$pdf->SetXY(23, 173+7);
	$pdf->MultiCell(124, 3,"$TOTAL_EN_PALABRAS PESOS.",0,'L');
	$pdf->SetFont('helvetica','B',6.95);
	$pdf->Text(12, 180.7+7,"NOTAS : ");
	$pdf->SetFont('helvetica','',7.15);
	$pdf->SetXY(23, 180.7+7);
	$pdf->MultiCell(124,30,substr($OBS,0,735) ,0,'L');
	$pdf->SetFont('helvetica','B',6.95);
	$pdf->Text(12, 213+7,"ESTADO PAGO : ");
	$pdf->Text(100, 213+7,"ESTADO SALIDA : ");
	
	/*************TOTALES***********/
	$pdf->RoundedRect(155, 171.5+7, 51, 38.5, 3.5);
	$pdf->Line(189,171.5+7,189,210+7);
	$pdf->SetFont('helvetica','B',6.95);	
	$pdf->Text(174, 173+7,"SUBTOTAL");
	$pdf->SetFont('helvetica','',7.15);
	$pdf->SetXY(180, 173+7);
	$pdf->MultiCell(26, 1,number_format($SUBTOTAL,0,'.','.'),0,"R");
	$pdf->Line(155,177+7,206,177+7);
	$pdf->SetFont('helvetica','',6.95);	
	$pdf->Text(161.6, 185.2,number_format($PORC_DSCTO1,2,'.','.').' %',0,"R");
	$pdf->SetFont('helvetica','B',6.95);	
	$pdf->Text(171.8, 178.2+7,"DESCUENTO");
	$pdf->SetFont('helvetica','',7.15);
	$pdf->SetXY(180, 178.2+7);
	$pdf->MultiCell(26, 1,number_format($MONTO_DSCTO1,0,'.','.'),0,"R");
	$pdf->Line(155,182.5+7,206,182.5+7);
	$pdf->SetFont('helvetica','',6.95);
	$pdf->Text(155, 190.5,number_format($PORC_DSCTO2,2,'.','.').' %',0,"R");
	$pdf->SetFont('helvetica','B',6.95);
	$pdf->Text(165, 183.5+7,"DESCUENTO ADIC.");
	$pdf->SetFont('helvetica','',7.15);
	$pdf->SetXY(180, 183.5+7);
	$pdf->MultiCell(26, 1,number_format($MONTO_DSCTO2,0,'.','.'),0,"R");
	$pdf->Line(155,188+7,206,188+7);
	$pdf->SetFont('helvetica','B',6.95);
	$pdf->Text(168.5, 189+7,"TOTAL EXENTO");
	$pdf->SetFont('helvetica','',7.15);
	$pdf->SetXY(180, 189+7);
	$pdf->MultiCell(26, 1,$TOTAL_EXENTO,0,"R");
	$pdf->Line(155,193.5+7,206,193.5+7);
	$pdf->SetFont('helvetica','B',6.95);
	$pdf->Text(171.8, 194.5+7,"TOTAL NETO");
	$pdf->SetFont('helvetica','',7.15);
	$pdf->SetXY(180, 194.5+7);
	$pdf->MultiCell(26, 1,number_format($TOTAL_NETO,0,'.','.'),0,"R");
	$pdf->Line(155,198.5+7,206,198.5+7);
	$pdf->SetFont('helvetica','B',6.95);
	$pdf->Text(176, 199.5+7,utf8_encode("19% I.V.A."));
	$pdf->SetFont('helvetica','',7.15);
	$pdf->SetXY(180, 200+7);
	$pdf->MultiCell(26, 1,number_format($MONTO_IVA,0,'.','.'),0,"R");
	$pdf->Line(155,204.5+7,206,204.5+7);
	$pdf->SetFont('helvetica','B',6.95);
	$pdf->Text(179, 205.5+7,"TOTAL");
	$pdf->SetFont('helvetica','',7.15);
	$pdf->SetXY(180, 205.5+7);
	$pdf->MultiCell(26, 1,number_format($TOTAL_CON_IVA,0,'.','.'),0,"R");
	
	$pdf->SetDrawColor(255,0,0);	
	$pdf->SetLineWidth(4);
	$pdf->Line(10,275,206,275);
	$pdf->SetFont('helvetica','',7.15);
	$pdf->SetTextColor(255,255,255);
	$pdf->Text(97, 273.5,"www.biggi.cl");
	
	/*************COPIA CEDIBLE **************/
	$pdf->AddPage();
	
	$x_timbre = 20; 
	$x = 20; 
	$y = 222; 
	$w = 70;
	$ecl = version_compare(phpversion(), '7.0.0', '<') ? -1 : 5;
	$style = array(
	                'border' => false,
	                'padding' => 0,
	                'hpadding' => 0,
	                'vpadding' => 0,
	                'module_width' => 1, // width of a single module in points
	                'module_height' => 1, // height of a single module in points
	                'fgcolor' => array(0,0,0),
	                'bgcolor' => false//, // [255,255,255]
	            );
		            
	$pdf->write2DBarcode($XML_DTE, 'PDF417,,'.$ecl, $x_timbre, $y+7, $w, 0, $style, 'B');
	$pdf->SetFont('helvetica','B',5.40);	
	$pdf->Text(40, 259+7,utf8_encode('Timbre Electrónico SII'));
	$pdf->Text(22, 262+7,utf8_encode("Resolución $resolucion del $fecha_resolucion Verifique este documento en www.sii.cl"));
		
	$imagen = dirname(__FILE__)."/../../images_appl/BIGGI_LOGO_DTE.png";
	$pdf->Image($imagen,9,0.5,60,28);
	$pdf->SetDrawColor(255,0,0);	
	$pdf->SetLineWidth(1);
	$pdf->Rect(136, 3, 69, 33);
	$pdf->SetTextColor(255,0,0);
	$pdf->SetFont('helvetica','B',12.25);	
	$pdf->Text(149, 15-7,'R.U.T.: 91.462.001-5');
	$pdf->Text(148, 22-7,'NOTA DE CREDITO');
	$pdf->Text(154, 29-7,'ELECTRONICA');
	$pdf->Text(159, 36-7,utf8_encode('N°: ').$folio);
	$pdf->SetFont('helvetica','B',8.75);
	$pdf->Text(149, 45-7,'S.I.I - SANTIAGO CENTRO');
	$pdf->SetTextColor(0,0,0);
	$pdf->Text(10, 35-7,'COMERCIAL BIGGI CHILE S.A.');
	$pdf->SetFont('helvetica','',7.15);
	$pdf->Text(10, 40-7,'ARTEFACTOS PARA LA MANIPULACION COCCION Y TRANSPORTE DE ALIMENTOS');
	$pdf->Text(10, 45-7,utf8_encode('PORTUGAL N° 1726 - SANTIAGO - CHILE'));
	$pdf->Text(10, 50-7,'FONOS: (56-2)2412-6200 - 25552849 - FAX(56-2)24126201 - 25512750');
	$pdf->SetLineWidth(0.3);
	$pdf->SetDrawColor(0,0,0);
	$pdf->RoundedRect(10, 55-7, 196, 37, 3.5);
			
	/*SELECT CABECERA*/
	$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

	$sql ="SELECT convert(varchar(10),N.FECHA_NOTA_CREDITO,103) FECHA_NOTA_CREDITO
					,(CAST(N.RUT AS NVARCHAR(8)))+'-'+(CAST (N.DIG_VERIF AS NVARCHAR(1))) as RUT_COMPLETO
					,N.NOM_EMPRESA
					,FA.NRO_ORDEN_COMPRA 
					,N.DIRECCION
					,CI.NOM_CIUDAD
					,N.GIRO
					,C.NOM_COMUNA
					,N.MAIL
					,N.TELEFONO
					,N.REFERENCIA
					,FA.NRO_FACTURA
					,CONVERT(VARCHAR(20),FA.FECHA_FACTURA,103)FECHA_FACTURA
					,TNC.NOM_TIPO_NOTA_CREDITO
					,N.OBS
					,N.SUBTOTAL
					,N.MONTO_DSCTO1
					,N.MONTO_DSCTO2
					,N.TOTAL_NETO
					,N.MONTO_IVA
					,N.TOTAL_CON_IVA
					,U.NOM_USUARIO
					,(SELECT COD_DOC FROM FACTURA WHERE COD_FACTURA = N.COD_DOC)NRO_NV
					,N.COD_DOC
					,N.COD_TIPO_NC_INTERNO_SII
					,N.PORC_IVA
					,N.PORC_DSCTO1
					,N.PORC_DSCTO2
					,case when N.MONTO_IVA = 0 
						then N.TOTAL_NETO
						else null
					end	TOTAL_EXENTO
			FROM NOTA_CREDITO N, FACTURA FA,COMUNA C, CIUDAD CI,TIPO_NOTA_CREDITO TNC, USUARIO U
			WHERE N.COD_NOTA_CREDITO = $cod_documento
			AND N.COD_DOC = FA.COD_FACTURA
			AND C.COD_COMUNA = N.COD_COMUNA
			AND N.COD_CIUDAD = CI.COD_CIUDAD
			and N.COD_TIPO_NOTA_CREDITO = TNC.COD_TIPO_NOTA_CREDITO
			AND U.COD_USUARIO = N.COD_USUARIO";
	$result = $db->build_results($sql);
	
	$FECHA_NOTA_CREDITO		= utf8_encode($result[0]['FECHA_NOTA_CREDITO']);
	$RUT_COMPLETO			= utf8_encode($result[0]['RUT_COMPLETO']);
	$NOM_EMPRESA			= utf8_encode($result[0]['NOM_EMPRESA']);
	$NRO_ORDEN_COMPRA		= utf8_encode($result[0]['NRO_ORDEN_COMPRA']);
	$DIRECCION				= utf8_encode($result[0]['DIRECCION']);
	$NOM_CIUDAD				= utf8_encode($result[0]['NOM_CIUDAD']);
	$GIRO					= utf8_encode($result[0]['GIRO']);
	$NOM_COMUNA				= utf8_encode($result[0]['NOM_COMUNA']);
	$MAIL					= utf8_encode($result[0]['MAIL']);
	$TELEFONO				= utf8_encode($result[0]['TELEFONO']);
	$REFERENCIA				= utf8_encode($result[0]['REFERENCIA']);
	$NRO_FACTURA			= utf8_encode($result[0]['NRO_FACTURA']);
	$FECHA_FACTURA			= utf8_encode($result[0]['FECHA_FACTURA']);
	$NOM_TIPO_NOTA_CREDITO	= utf8_encode($result[0]['NOM_TIPO_NOTA_CREDITO']);
	$OBS					= utf8_encode($result[0]['OBS']);
	$SUBTOTAL				= utf8_encode($result[0]['SUBTOTAL']);
	$MONTO_DSCTO1			= utf8_encode($result[0]['MONTO_DSCTO1']);
	$MONTO_DSCTO2			= utf8_encode($result[0]['MONTO_DSCTO2']);
	$TOTAL_NETO				= utf8_encode($result[0]['TOTAL_NETO']);
	$MONTO_IVA				= utf8_encode($result[0]['MONTO_IVA']);
	$TOTAL_CON_IVA			= utf8_encode($result[0]['TOTAL_CON_IVA']);
	$EMISOR					= utf8_encode($result[0]['NOM_USUARIO']);
	$NRO_NV					= utf8_encode($result[0]['NRO_NV']);
	$FOLIO_FACTURA			= utf8_encode($result[0]['COD_DOC']);
	$COD_TIPO_NC			= $result[0]['COD_TIPO_NC_INTERNO_SII'];
	$PORC_IVA				= $result[0]['PORC_IVA'];
			$PORC_DSCTO1			= $result[0]['PORC_DSCTO1'];
	$PORC_DSCTO2			= $result[0]['PORC_DSCTO2'];
	$TOTAL_EXENTO			= $result[0]['TOTAL_EXENTO'];
	
	if ($TOTAL_EXENTO == '') 
		$TOTAL_EXENTO = '';
	else
		$TOTAL_EXENTO = number_format($TOTAL_EXENTO,0,'.','.');
		
	$TOTAL_EN_PALABRAS =  Numbers_Words::toWords($TOTAL_CON_IVA,"es"); 
	$TOTAL_EN_PALABRAS = strtr($TOTAL_EN_PALABRAS, "áéíóú", "aeiou");
	$TOTAL_EN_PALABRAS = strtoupper($TOTAL_EN_PALABRAS);
	
	
	
	$pdf->SetFont('helvetica','B',6.95);
	$pdf->SetXY(12, 56-7);
	$pdf->MultiCell(20, 30,"FECHA",0);
	$pdf->SetXY(35, 56-7);
	$pdf->MultiCell(20, 30,":");
	$pdf->SetFont('helvetica','',7.15);
	$pdf->SetXY(38, 56-7);
	$pdf->MultiCell(20, 30,$FECHA_NOTA_CREDITO);
	$pdf->SetFont('helvetica','B',7.15);
	$pdf->SetXY(145, 56-7);
	$pdf->MultiCell(20, 30,"RUT.");
	$pdf->SetXY(160, 56-7);
	$pdf->MultiCell(20, 30,":");
	$pdf->SetXY(163, 56-7);
	$pdf->MultiCell(40, 30,$RUT_COMPLETO);
	
	$pdf->SetFont('helvetica','B',6.95);
	$pdf->SetXY(12, 60-7);
	$pdf->MultiCell(40, 30,utf8_encode("SEÑOR(ES)"));
	$pdf->SetXY(35, 60-7);
	$pdf->MultiCell(20, 30,":");
	$pdf->SetXY(38, 60-7);
	$pdf->MultiCell(100, 2,substr($NOM_EMPRESA,0,186),0,"L"); 
	$pdf->SetFont('helvetica','B',6.95);
	$pdf->SetXY(145, 60-7);
	$pdf->MultiCell(20, 30,"O.C.");
	$pdf->SetXY(160, 60-7);
	$pdf->MultiCell(20, 60,":");
	$pdf->SetFont('helvetica','',7.15);
	$pdf->SetXY(163, 60-7);
	$pdf->MultiCell(20, 30,$NRO_ORDEN_COMPRA);
	
	$pdf->SetFont('helvetica','B',6.95);
	$pdf->SetXY(12, 67-7);
	$pdf->MultiCell(40, 30,utf8_encode("DIRECCIÓN"));
	$pdf->SetXY(35, 67-7);
	$pdf->MultiCell(20, 1,":");
	$pdf->SetFont('helvetica','',7.15);
	$pdf->SetXY(38, 67-7);
	$pdf->MultiCell(95, 3,substr($DIRECCION,0,186),0,'L'); 
	$pdf->SetFont('helvetica','B',6.95);
	$pdf->SetXY(145, 67-7);
	$pdf->MultiCell(40, 30,"CIUDAD");
	$pdf->SetXY(160, 67-7);
	$pdf->MultiCell(20, 30,":");
	$pdf->SetFont('helvetica','',7.15);
	$pdf->SetXY(163, 67-7);
	$pdf->MultiCell(40, 30,$NOM_CIUDAD);
	
	$pdf->SetFont('helvetica','B',6.95);
	$pdf->SetXY(12, 73-7);
	$pdf->MultiCell(40, 1,"GIRO");
	$pdf->SetXY(35, 73-7);
	$pdf->MultiCell(20, 1,":");
	$pdf->SetFont('helvetica','',7.15);
	$pdf->SetXY(38, 73-7);
	$pdf->MultiCell(100, 1,substr($GIRO,0,65),0,'L'); 
	$pdf->SetFont('helvetica','B',6.95);
	$pdf->SetXY(145, 73-7);
	$pdf->MultiCell(40, 30,"COMUNA");
	$pdf->SetXY(160, 73-7);
	$pdf->MultiCell(20, 30,":");
	$pdf->SetFont('helvetica','',7.15);
	$pdf->SetXY(163, 73-7);
	$pdf->MultiCell(40, 1,$NOM_COMUNA,0,'L');
	
	$pdf->SetFont('helvetica','B',6.95);
	$pdf->SetXY(12, 77-7);
	$pdf->MultiCell(40, 1,"E-MAIL");
	$pdf->SetXY(35, 77-7);
	$pdf->MultiCell(20, 1,":");
	$pdf->SetFont('helvetica','',7.15);
	$pdf->SetXY(38, 77-7);
	$pdf->MultiCell(110, 1,substr($MAIL,0,75),0,'L');
	$pdf->SetFont('helvetica','B',6.95);
	$pdf->SetXY(145, 77-7);
	$pdf->MultiCell(40, 30,"FONO");
	$pdf->SetXY(160, 77-7);
	$pdf->MultiCell(20, 30,":");
	$pdf->SetFont('helvetica','',7.15);
	$pdf->SetXY(163, 77-7);
	$pdf->MultiCell(36, 1,$TELEFONO,0,'L'); 
	
	$pdf->SetFont('helvetica','B',6.95);
	$pdf->SetXY(12, 81-7);
	$pdf->MultiCell(40, 1,"COND. DE VENTA",0,'L');
	$pdf->SetXY(35, 81-7);
	$pdf->MultiCell(20, 30,":");
	$pdf->SetFont('helvetica','',7.15);
	$pdf->SetXY(38, 81-7);
	$pdf->MultiCell(100, 1,$NOM_FORMA_PAGO,0,'L');
	$pdf->SetFont('helvetica','B',6.95);
	$pdf->SetXY(145, 81-7);
	$pdf->MultiCell(40, 1,"EMISOR");
	$pdf->SetXY(160, 81-7);
	$pdf->MultiCell(20, 30,":");
	$pdf->SetFont('helvetica','',7.15);
	$pdf->SetXY(163, 81-7);
	$pdf->MultiCell(50, 1,substr($EMISOR,0,27),0,'L'); 
	
	$pdf->SetFont('helvetica','B',6.95);
	$pdf->SetXY(12, 85.5-7);
	$pdf->MultiCell(40, 1,"REFERENCIA",0,'L');
	$pdf->SetXY(35, 85.5-7);
	$pdf->MultiCell(3, 1,":",0);
	$pdf->SetXY(38, 85.5-7);
	$pdf->MultiCell(90, 2,$REFERENCIA,0,'L');  
	$pdf->Text(129,85.5-7,utf8_encode("N° NV : $NRO_NV")); 
	
	$pdf->Line(10,78,206,78);
	
	$pdf->SetFont('helvetica','B',6.95);
	$pdf->Text(10,86.5,"TIPO REFERENCIA");
	$pdf->Text(70,86.5,"FOLIO"); 
	$pdf->Text(100,86.5,"FECHA"); 
	$pdf->Text(140,86.5,"MOTIVO REFERENCIA"); 
	
	$pdf->SetFont('helvetica','',7.15);
	if($PORC_IVA > 0)
		$pdf->Text(10,90.5,utf8_encode('Factura Afecta Electrónica'));
	else
		$pdf->Text(10,90.5,utf8_encode('Factura Exenta Electrónica'));	
	$pdf->Text(70,90.5,$NRO_FACTURA);
	$pdf->Text(100,90.5,$FECHA_FACTURA);
	if($COD_TIPO_NC == 1 || $COD_TIPO_NC == 1)
		$pdf->Text(140,90.5,'Anula documento de referencia');
	else if($COD_TIPO_NC == 3 || $COD_TIPO_NC == 4)	
		$pdf->Text(140,90.5,'Corrige montos');
	else if($COD_TIPO_NC == 5)	
		$pdf->Text(140,90.5,'Corrige texto documento de referencia');
	
	$pdf->RoundedRect(10, 92+7, 196, 77.5, 3.5);
	$pdf->SetFont('helvetica','B',8);
	$pdf->SetXY(10, 93+7);
	$pdf->MultiCell(10, 1,"IT",0,'C');
	$pdf->Line(20,92+7,20,169.5+7);
	$pdf->SetXY(20, 93+7);
	$pdf->MultiCell(15, 1,"CT",0,'C');
	$pdf->Line(35,92+7,35,169.5+7);
	$pdf->SetXY(35, 93+7);
	$pdf->MultiCell(23, 1,"MODELO",0,'C');
	$pdf->Line(58,92+7,58,169.5+7);
	$pdf->SetXY(58, 93+7);
	$pdf->MultiCell(103, 1,"DETALLE",0,'C');
	$pdf->Line(161,92+7,161,169.5+7);
	$pdf->SetXY(161, 93+7);
	$pdf->MultiCell(21, 1,"P.UNIT.",0,'C');
	$pdf->Line(182,92+7,182,169.5+7);
	$pdf->SetXY(182, 93+7);
	$pdf->MultiCell(24, 1,"TOTAL",0,'C');
	
	$pdf->Line(10,85+19,206,85+19);
	
	/*************ITEMS***********/
	$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
      
	$SqlDetalles ="SELECT ROW_NUMBER()OVER(ORDER BY PRECIO DESC) AS NroLinDet,
						('INT1')AS TpoCodigo,
						ITC.NOM_PRODUCTO AS NmbItem,
						ITC.COD_PRODUCTO AS VlrCodigo,
						LEN (ITC.COD_PRODUCTO) LENTM,
						ITC.CANTIDAD, 
						ITC.PRECIO,
						(ITC.CANTIDAD * ITC.PRECIO) AS MONTO_TOTAL
					FROM ITEM_NOTA_CREDITO ITC WHERE ITC.COD_NOTA_CREDITO = $cod_documento";
	$result2 = $db->build_results($SqlDetalles);
	
	$x = 2;
	$i = 2;
	$y = $pdf->GetY()-7.5; 
		
	foreach($result2 as $row){
		$pdf->SetFont('helvetica','',6.25);
		$pdf->SetXY($x+8, $y+(4*$i));
		$pdf->MultiCell(10, 1, $row['NroLinDet'],0,'C');
		$pdf->SetXY($x+18, $y+(4*$i));
		$pdf->MultiCell(15, 1, $row['CANTIDAD'],0,'C'); 
		$pdf->SetXY($x+33, $y+(4*$i));
		$pdf->MultiCell(23, 1,substr($row['VlrCodigo'],0,15) ,0,'C'); 
		$pdf->SetXY($x+56, $y+(4*$i));
		$pdf->MultiCell(103, 1,substr(utf8_encode($row['NmbItem']),0,85) ,0,'L'); 
		$pdf->SetXY($x+159, $y+(4*$i));
		$pdf->MultiCell(21, 1, number_format($row['PRECIO'],0,'.','.'),0,'R');
		$pdf->SetXY($x+180, $y+(4*$i));
		$pdf->MultiCell(24, 1, number_format($row['MONTO_TOTAL'],0,'.','.'),0,'R');
	
		$i++;			
	}
	/*****************************************PIE PAGINA************************************************/
	/*************OBSERVACIONES********/
	$pdf->RoundedRect(10, 171.5+7, 140, 47, 3.5);
	$pdf->SetFont('helvetica','B',6.95);	
	$pdf->Text(15, 173+7,"SON : ");
	$pdf->SetFont('helvetica','',7.15);
	$pdf->SetXY(23, 173+7);
	$pdf->MultiCell(124, 3,"$TOTAL_EN_PALABRAS PESOS.",0,'L');
	$pdf->SetFont('helvetica','B',6.95);
	$pdf->Text(12, 180.7+7,"NOTAS : ");
	$pdf->SetFont('helvetica','',7.15);
	$pdf->SetXY(23, 180.7+7);
	$pdf->MultiCell(124,30,substr($OBS,0,735) ,0,'L');
	$pdf->SetFont('helvetica','B',6.95);
	$pdf->Text(12, 213+7,"ESTADO PAGO : ");
	$pdf->Text(100, 213+7,"ESTADO SALIDA : ");
	
	/*************TOTALES***********/
	$pdf->RoundedRect(155, 171.5+7, 51, 38.5, 3.5);
	$pdf->Line(189,171.5+7,189,210+7);
	$pdf->SetFont('helvetica','B',6.95);	
	$pdf->Text(174, 173+7,"SUBTOTAL");
	$pdf->SetFont('helvetica','',7.15);
	$pdf->SetXY(180, 173+7);
	$pdf->MultiCell(26, 1,number_format($SUBTOTAL,0,'.','.'),0,"R");
	$pdf->Line(155,177+7,206,177+7);
	$pdf->SetFont('helvetica','',6.95);	
	$pdf->Text(161.6, 185.2,number_format($PORC_DSCTO1,2,'.','.').' %',0,"R");
	$pdf->SetFont('helvetica','B',6.95);	
	$pdf->Text(171.8, 178.2+7,"DESCUENTO");
	$pdf->SetFont('helvetica','',7.15);
	$pdf->SetXY(180, 178.2+7);
	$pdf->MultiCell(26, 1,number_format($MONTO_DSCTO1,0,'.','.'),0,"R");
	$pdf->Line(155,182.5+7,206,182.5+7);
	$pdf->SetFont('helvetica','',6.95);
	$pdf->Text(155, 190.5,number_format($PORC_DSCTO2,2,'.','.').' %',0,"R");
	$pdf->SetFont('helvetica','B',6.95);
	$pdf->Text(165, 183.5+7,"DESCUENTO ADIC.");
	$pdf->SetFont('helvetica','',7.15);
	$pdf->SetXY(180, 183.5+7);
	$pdf->MultiCell(26, 1,number_format($MONTO_DSCTO2,0,'.','.'),0,"R");
	$pdf->Line(155,188+7,206,188+7);
	$pdf->SetFont('helvetica','B',6.95);
	$pdf->Text(168.5, 189+7,"TOTAL EXENTO");
	$pdf->SetFont('helvetica','',7.15);
	$pdf->SetXY(180, 189+7);
	$pdf->MultiCell(26, 1,$TOTAL_EXENTO,0,"R");
	$pdf->Line(155,193.5+7,206,193.5+7);
	$pdf->SetFont('helvetica','B',6.95);
	$pdf->Text(171.8, 194.5+7,"TOTAL NETO");
	$pdf->SetFont('helvetica','',7.15);
	$pdf->SetXY(180, 194.5+7);
	$pdf->MultiCell(26, 1,number_format($TOTAL_NETO,0,'.','.'),0,"R");
	$pdf->Line(155,198.5+7,206,198.5+7);
	$pdf->SetFont('helvetica','B',6.95);
	$pdf->Text(176, 199.5+7,utf8_encode("19% I.V.A."));
	$pdf->SetFont('helvetica','',7.15);
	$pdf->SetXY(180, 200+7);
	$pdf->MultiCell(26, 1,number_format($MONTO_IVA,0,'.','.'),0,"R");
	$pdf->Line(155,204.5+7,206,204.5+7);
	$pdf->SetFont('helvetica','B',6.95);
	$pdf->Text(179, 205.5+7,"TOTAL");
	$pdf->SetFont('helvetica','',7.15);
	$pdf->SetXY(180, 205.5+7);
	$pdf->MultiCell(26, 1,number_format($TOTAL_CON_IVA,0,'.','.'),0,"R");
	
	$pdf->SetDrawColor(255,0,0);	
	$pdf->SetLineWidth(4);
	$pdf->Line(10,268,206,268);
	$pdf->SetFont('helvetica','',7.15);
	$pdf->SetTextColor(255,255,255);
	$pdf->Text(97, 266.5,"www.biggi.cl");
	
	$pdf->SetFont('helvetica','B',6.95);
	$pdf->SetDrawColor(0,0,0);	
	$pdf->SetLineWidth(0.3);
	$pdf->SetTextColor(0,0,0);
	$pdf->SetXY(192.7, 270.5);
	$pdf->Cell(13, 1,'CEDIBLE',1,"C");
	
	$pdf->Output("61_$folio.pdf", 'I');
	break;
}
?>