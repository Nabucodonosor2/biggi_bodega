<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
include(dirname(__FILE__)."/../../appl.ini");
class print_anexo_softland{
	function __construct(){
	}
	function print_anexo($cod_envio_softland, $pdf){
	   
    	$pdf->AddFont('FuturaBook','','futurabook.php');
    	$pdf->AddPage();
		$pdf->SetAutoPageBreak(true,0);
		$titulo = "Traspaso Softland ".$cod_envio_softland;
		$pdf->SetTitle($titulo);
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		
		$sql = "select 'S' FC_SELECCION
					,F.NRO_FAPROV FC_NRO_FACTURA
					,convert(varchar, F.FECHA_FAPROV, 103) FC_FECHA_FACTURA 
					,Left (EM.NOM_EMPRESA, 30) FC_NOM_EMPRESA
					,F.TOTAL_NETO FC_TOTAL_NETO
					,F.MONTO_IVA  FC_MONTO_IVA
					,F.TOTAL_CON_IVA FC_TOTAL_CON_IVA 
					,E.COD_ENVIO_SOFTLAND FC_COD_ENVIO_SOFTLAND
					,E.COD_ENVIO_FAPROV FC_COD_ENVIO_FAPROV
					,E.COD_FAPROV FC_COD_FAPROV
					,E.NRO_CORRELATIVO_INTERNO FC_CORRELATIVO
					,dbo.f_get_parametro(6) EMISOR
					,UU.NOM_USUARIO
				from ENVIO_FAPROV E, FAPROV F LEFT OUTER JOIN CUENTA_COMPRA C on C.COD_CUENTA_COMPRA = F.COD_CUENTA_COMPRA, EMPRESA EM, USUARIO UU, ENVIO_SOFTLAND ESS
				where E.COD_ENVIO_SOFTLAND = $cod_envio_softland
				  and F.COD_FAPROV = E.COD_FAPROV
				  and EM.COD_EMPRESA = F.COD_EMPRESA
				  and ESS.COD_ENVIO_SOFTLAND = $cod_envio_softland
				  and UU.COD_USUARIO = ESS.COD_USUARIO
				order by EM.NOM_EMPRESA, E.NRO_CORRELATIVO_INTERNO, F.NRO_FAPROV";
		
		$result = $db->build_results($sql);
		$row = count($result);					
		$sistema_emisor = $result[1]['EMISOR'];
		$usuario_emisor = $result[1]['NOM_USUARIO'];
		
		$pdf->SetXY(28, 40);
		$pdf->SetTextColor(0, 0, 0);
		$pdf->SetFont('Arial','', 16);
		$pdf->Cell(560,17,"ANEXO TRASPASO SOFTLAND Nº ".$cod_envio_softland, 0, '', 'C');
		$pdf->SetFont('Arial','', 9);
		$pdf->SetXY(28, 60);
		$pdf->Cell(560,17,"SISTEMA EMISOR: ".$sistema_emisor, 0, '', 'L');
		$pdf->SetXY(28, 60);
		$pdf->Cell(560,17,"USUARIO EMISOR: ".$usuario_emisor, 0, '', 'R');
		$pdf->SetXY(28, 85);
		$pdf->Cell(560,15,"[FACTURAS DE COMPRA]", 1, '', 'C');
		
		$pdf->SetFont('Arial','', 9);
		$pdf->SetXY(28,100);
		$pdf->Cell(40,15, 'NRO FA', 'LTRB', '','L');
		$pdf->Cell(50,15, 'FECHA', 'LTRB', '','C');
		$pdf->Cell(180,15, 'RASON SOCIAL', 'LTRB', '','C');
		$pdf->Cell(70,15, 'TOTAL NETO', 'LTRB', '','R');
		$pdf->Cell(70,15, 'MONTO IVA', 'LTRB', '','R'); 
		$pdf->Cell(75,15, 'TOTAL CON IVA', 'LTRB', '','R');
		$pdf->Cell(75,15, 'CORRELATIVO', 'LTRB', '','R');
		
		$y_ini = $pdf->GetY(); 
		$pdf->SetFont('Arial','', 9);
		
		for($i=0 ; $i < $row ; $i++){

		    $y_ini = $y_ini+15.3;
		    $pdf->SetXY(28,$y_ini);
		    $pdf->Cell(40,15, $result[$i]['FC_NRO_FACTURA'], 'LTRB', '','L');
		    $pdf->Cell(50,15, $result[$i]['FC_FECHA_FACTURA'], 'LTRB', '','C');
		    $pdf->Cell(180,15, $result[$i]['FC_NOM_EMPRESA'], 'LTRB', '','L');
		    $pdf->Cell(70,15, number_format($result[$i]['FC_TOTAL_NETO'], 0, ',', '.'), 'LTRB', '','R');
		    $pdf->Cell(70,15, number_format($result[$i]['FC_MONTO_IVA'], 0, ',', '.'), 'LTRB', '','R'); 
		    $pdf->Cell(75,15,number_format($result[$i]['FC_TOTAL_CON_IVA'], 0, ',', '.') , 'LTRB', '','R');
			$pdf->Cell(75,15,number_format($result[$i]['FC_CORRELATIVO'], 0, ',', '.') , 'LTRB', '','R');
			
			$auxsum_total_neto = $auxsum_total_neto + $result[$i]['FC_TOTAL_NETO'];
			$auxsum_monto_iva = $auxsum_monto_iva + $result[$i]['FC_MONTO_IVA'];
			$auxsum_total_con_iva = $auxsum_total_con_iva + $result[$i]['FC_TOTAL_CON_IVA'];
			
			if($pdf->GetY() > 680){
				$pdf->AddPage();

				$pdf->SetXY(28, 40);
				$pdf->SetTextColor(0, 0, 0);
				$pdf->SetFont('Arial','', 16);
				$pdf->Cell(560,17,"ANEXO TRASPASO SOFTLAND Nº ".$cod_envio_softland, 0, '', 'C');
				$pdf->SetFont('Arial','', 9);
				$pdf->SetXY(28, 60);
				$pdf->Cell(560,17,"SISTEMA EMISOR: ".$sistema_emisor, 0, '', 'L');
				$pdf->SetXY(28, 60);
				$pdf->Cell(560,17,"USUARIO EMISOR: ".$usuario_emisor, 0, '', 'R');
				$pdf->SetXY(28, 85);
				$pdf->Cell(560,15,"[FACTURAS DE COMPRA]", 1, '', 'C');
				$pdf->SetFont('Arial','', 9);
				
				$pdf->SetXY(28,100);
				$pdf->Cell(40,15, 'NRO FA', 'LTRB', '','L');
				$pdf->Cell(50,15, 'FECHA', 'LTRB', '','C');
				$pdf->Cell(180,15, 'RASON SOCIAL', 'LTRB', '','C');
				$pdf->Cell(70,15, 'TOTAL NETO', 'LTRB', '','R');
				$pdf->Cell(70,15, 'MONTO IVA', 'LTRB', '','R'); 
				$pdf->Cell(75,15, 'TOTAL CON IVA', 'LTRB', '','R');
				$pdf->Cell(75,15, 'CORRELATIVO', 'LTRB', '','R');
				$y_ini = $pdf->GetY(); 				
			}
			
		}
		$y_ini = $pdf->GetY();
		$y_ini = $y_ini+15.3;
		$pdf->SetXY(28,$y_ini);
		$pdf->Cell(40,15, '', '', '','L');
		$pdf->Cell(50,15, '', '', '','C');
		$pdf->Cell(180,15, 'TOTALES FC', '', '','R');
		$pdf->Cell(70,15, number_format($auxsum_total_neto, 0, ',', '.'), 'LTRB', '','R');
		$pdf->Cell(70,15, number_format($auxsum_monto_iva, 0, ',', '.'), 'LTRB', '','R'); 
		$pdf->Cell(75,15,number_format($auxsum_total_con_iva, 0, ',', '.') , 'LTRB', '','R');
		$pdf->Cell(75,15,number_format('', 0, ',', '.') , '', '','R');		


		$auxsum_total_neto = 0;
		$auxsum_monto_iva = 0;
		$auxsum_total_con_iva = 0;
		
		$sql = "select 'S' FC_SELECCION
					,F.NRO_NCPROV FC_NRO_FACTURA
					,convert(varchar, F.FECHA_NCPROV, 103) FC_FECHA_FACTURA 
					,Left (EM.NOM_EMPRESA, 30) FC_NOM_EMPRESA
					,F.TOTAL_NETO FC_TOTAL_NETO
					,F.MONTO_IVA  FC_MONTO_IVA
					,F.TOTAL_CON_IVA FC_TOTAL_CON_IVA 
					,E.COD_ENVIO_SOFTLAND FC_COD_ENVIO_SOFTLAND
					,E.COD_ENVIO_NCPROV FC_COD_ENVIO_FAPROV
					,E.COD_NCPROV FC_COD_FAPROV
					,E.NRO_CORRELATIVO_INTERNO FC_CORRELATIVO
					,dbo.f_get_parametro(6) EMISOR
					,UU.NOM_USUARIO
				from ENVIO_NCPROV E, NCPROV F LEFT OUTER JOIN CUENTA_COMPRA C on C.COD_CUENTA_COMPRA = F.COD_CUENTA_COMPRA, EMPRESA EM, USUARIO UU, ENVIO_SOFTLAND ESS
				where E.COD_ENVIO_SOFTLAND = $cod_envio_softland
				  and F.COD_NCPROV = E.COD_NCPROV
				  and EM.COD_EMPRESA = F.COD_EMPRESA
				  and ESS.COD_ENVIO_SOFTLAND = $cod_envio_softland
				  and UU.COD_USUARIO = ESS.COD_USUARIO
				order by EM.NOM_EMPRESA, E.NRO_CORRELATIVO_INTERNO, F.NRO_NCPROV";

		$result = $db->build_results($sql);
		$row = count($result);					
		$sistema_emisor = $result[1]['EMISOR'];
		$usuario_emisor = $result[1]['NOM_USUARIO'];	


		
		if($pdf->GetY() > 600){
			
			$pdf->AddPage();
			$pdf->SetXY(28, 40);
			$pdf->SetTextColor(0, 0, 0);
			$pdf->SetFont('Arial','', 16);
			$pdf->Cell(560,17,"ANEXO TRASPASO SOFTLAND Nº ".$cod_envio_softland, 0, '', 'C');
			$pdf->SetFont('Arial','', 9);
			$pdf->SetXY(28, 60);
			$pdf->Cell(560,17,"SISTEMA EMISOR: ".$sistema_emisor, 0, '', 'L');
			$pdf->SetXY(28, 60);
			$pdf->Cell(560,17,"USUARIO EMISOR: ".$usuario_emisor, 0, '', 'R');
		
		}
		
		$y_ini = $pdf->GetY();

		$pdf->SetFont('Arial','', 9);
		$pdf->SetXY(28, $y_ini+35);
		$pdf->Cell(560,15,"[NOTAS DE CREDITO COMPRA]", 1, '', 'C');
		$pdf->SetXY(28,$y_ini+50);
		$pdf->Cell(40,15, 'NRO NC', 'LTRB', '','L');
		$pdf->Cell(50,15, 'FECHA', 'LTRB', '','C');
		$pdf->Cell(180,15, 'RASON SOCIAL', 'LTRB', '','C');
		$pdf->Cell(70,15, 'TOTAL NETO', 'LTRB', '','R');
		$pdf->Cell(70,15, 'MONTO IVA', 'LTRB', '','R'); 
		$pdf->Cell(75,15, 'TOTAL CON IVA', 'LTRB', '','R');
		$pdf->Cell(75,15, 'CORRELATIVO', 'LTRB', '','R');
		
		$y_ini = $pdf->GetY();
		
		for($i=0 ; $i < $row ; $i++){

		    $y_ini = $y_ini+15;
		    $pdf->SetXY(28,$y_ini);
		    $pdf->Cell(40,15, $result[$i]['FC_NRO_FACTURA'], 'LTRB', '','L');
		    $pdf->Cell(50,15, $result[$i]['FC_FECHA_FACTURA'], 'LTRB', '','C');
		    $pdf->Cell(180,15, $result[$i]['FC_NOM_EMPRESA'], 'LTRB', '','L');
		    $pdf->Cell(70,15, number_format($result[$i]['FC_TOTAL_NETO'], 0, ',', '.'), 'LTRB', '','R');
		    $pdf->Cell(70,15, number_format($result[$i]['FC_MONTO_IVA'], 0, ',', '.'), 'LTRB', '','R'); 
		    $pdf->Cell(75,15,number_format($result[$i]['FC_TOTAL_CON_IVA'], 0, ',', '.') , 'LTRB', '','R');
			$pdf->Cell(75,15,number_format($result[$i]['FC_CORRELATIVO'], 0, ',', '.') , 'LTRB', '','R');
			
			$auxsum_total_neto = $auxsum_total_neto + $result[$i]['FC_TOTAL_NETO'];
			$auxsum_monto_iva = $auxsum_monto_iva + $result[$i]['FC_MONTO_IVA'];
			$auxsum_total_con_iva = $auxsum_total_con_iva + $result[$i]['FC_TOTAL_CON_IVA'];

			if($pdf->GetY() > 650){
				$pdf->AddPage();

				$pdf->SetXY(28, 40);
				$pdf->SetTextColor(0, 0, 0);
				$pdf->SetFont('Arial','', 16);
				$pdf->Cell(560,17,"ANEXO TRASPASO SOFTLAND Nº ".$cod_envio_softland, 0, '', 'C');
				$pdf->SetFont('Arial','', 9);
				$pdf->SetXY(28, 60);
				$pdf->Cell(560,17,"SISTEMA EMISOR: ".$sistema_emisor, 0, '', 'L');
				$pdf->SetXY(28, 60);
				$pdf->Cell(560,17,"USUARIO EMISOR: ".$usuario_emisor, 0, '', 'R');
				
			
				$y_ini = $pdf->GetY();

				$pdf->SetFont('Arial','', 9);
				$pdf->SetXY(28, $y_ini+35);
				$pdf->Cell(560,15,"[NOTAS DE CREDITO COMPRA]", 1, '', 'C');
				$pdf->SetXY(28,$y_ini+50);
				$pdf->Cell(40,15, 'NRO NC', 'LTRB', '','L');
				$pdf->Cell(50,15, 'FECHA', 'LTRB', '','C');
				$pdf->Cell(180,15, 'RASON SOCIAL', 'LTRB', '','C');
				$pdf->Cell(70,15, 'TOTAL NETO', 'LTRB', '','R');
				$pdf->Cell(70,15, 'MONTO IVA', 'LTRB', '','R'); 
				$pdf->Cell(75,15, 'TOTAL CON IVA', 'LTRB', '','R');
				$pdf->Cell(75,15, 'CORRELATIVO', 'LTRB', '','R');	
			}


		}	
		$y_ini = $pdf->GetY();
		$y_ini = $y_ini+15.3;
		$pdf->SetXY(28,$y_ini);
		$pdf->Cell(40,15, '', '', '','L');
		$pdf->Cell(50,15, '', '', '','C');
		$pdf->Cell(180,15, 'TOTALES FC', '', '','R');
		$pdf->Cell(70,15, number_format($auxsum_total_neto, 0, ',', '.'), 'LTRB', '','R');
		$pdf->Cell(70,15, number_format($auxsum_monto_iva, 0, ',', '.'), 'LTRB', '','R'); 
		$pdf->Cell(75,15,number_format($auxsum_total_con_iva, 0, ',', '.') , 'LTRB', '','R');
		$pdf->Cell(75,15,number_format('', 0, ',', '.') , '', '','R');		

    }
}
?>