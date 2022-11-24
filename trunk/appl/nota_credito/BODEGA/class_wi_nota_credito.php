<?php
////////////////////////////////////////
/////////// BODEGA_BIGGI ///////////////
////////////////////////////////////////
class wi_nota_credito extends wi_nota_credito_base {
	function wi_nota_credito($cod_item_menu) {
		parent::wi_nota_credito_base($cod_item_menu); 
	}
	function new_record() {
		parent::new_record();
		$this->dws['dw_nota_credito']->set_item(0, 'GENERA_ENTRADA', 'S');
		$this->dws['dw_nota_credito']->set_item(0, 'COD_BODEGA', 2);	// equipo terminado
	}
}
class print_nota_credito extends print_nota_credito_base {	
	function print_nota_credito($sql, $xml, $labels=array(), $titulo, $con_logo, $vuelve_a_presentacion=false) {
		parent::print_nota_credito_base($sql, $xml, $labels, $titulo, $con_logo, $vuelve_a_presentacion);			
	}
	
	function modifica_pdf(&$pdf) {
		$pdf->AutoPageBreak=false;
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$result = $db->build_results($this->sql);
		$count = count($result);

		$fecha = $result[0]['FECHA_NOTA_CREDITO'];		
		// CABECERA		
		$cod_nota_credito = $result[0]['COD_NOTA_CREDITO'];		
		$nro_nota_credito = $result[0]['NRO_NOTA_CREDITO'];
		$nom_empresa = $result[0]['NOM_EMPRESA'];
		$rut = number_format($result[0]['RUT'], 0, ',', '.')."-".$result[0]['DIG_VERIF'];
		$direccion = $result[0]['DIRECCION'];
		$comuna = $result[0]['NOM_COMUNA'];		
		$ciudad = $result[0]['NOM_CIUDAD'];		
		$giro = $result[0]['GIRO'];
		$fono = $result[0]['TELEFONO'];
		$total_en_palabras = $result[0]['TOTAL_EN_PALABRAS'];
		$subtotal = number_format($result[0]['SUBTOTAL'], 0, ',', '.');
		$porc_dscto1 = number_format($result[0]['PORC_DSCTO1'], 1, ',', '.');
		$monto_dscto1 = number_format($result[0]['MONTO_DSCTO1'], 0, ',', '.');
		$porc_dscto2 = number_format($result[0]['PORC_DSCTO2'], 1, ',', '.');
		$monto_dscto2 = number_format($result[0]['MONTO_DSCTO2'], 0, ',', '.');
		$total_dscto = number_format($result[0]['TOTAL_DSCTO'], 0, ',', '.');
		$neto = number_format($result[0]['TOTAL_NETO'], 0, ',', '.');
		$porc_iva = number_format($result[0]['PORC_IVA'], 0, ',', '.');
		$monto_iva = number_format($result[0]['MONTO_IVA'], 0, ',', '.');
		$total_con_iva = number_format($result[0]['TOTAL_CON_IVA'], 0, ',', '.');
		$REFERENCIA	= 'REF: '.substr ($result[0]['REFERENCIA'], 0, 68);
		$NRO_FACTURA	= $result[0]['NRO_FACTURA'];
		$INI_USUARIO	= $result[0]['INI_USUARIO'];

		// DIBUJANDO LA CABECERA	
		$pdf->SetFont('Arial','',11);
		$pdf->Text(74, 121,$fecha);
		
		$pdf->SetFont('Arial','',8);
		$pdf->Text(424, 95, $nro_nota_credito);
		
		$pdf->SetXY(69,143);
		$pdf->SetFont('Arial','B',11);
		$pdf->MultiCell(250,10,"$nom_empresa");
		
		$pdf->Text(435, 150, $rut);
		
		$pdf->SetFont('Arial','',11);
		$pdf->SetXY(69,196);
		$pdf->MultiCell(250,9,"$direccion");	
		
		$pdf->Text(435, 200, $comuna);
		
		$pdf->Text(56, 233, $ciudad);

		$pdf->SetXY(211, 206);
		$pdf->MultiCell(150,9,"$giro");	

		$pdf->Text(435, 233, $fono);		
		$pdf->SetFont('Arial','B',10);
		
		$pdf->Text(150, 310, "$REFERENCIA");
		$pdf->SetFont('Arial','',10);
		
		//DIBUJANDO LOS ITEMS DE LA NOTA_CREDITO
		for($i=0; $i<$count; $i++){
			$item = $result[$i]['ITEM'];
			$cantidad = $result[$i]['CANTIDAD'];
			$modelo = $result[$i]['COD_PRODUCTO'];
			$detalle = substr ($result[$i]['NOM_PRODUCTO'], 0, 45);
			$p_unitario = number_format($result[$i]['PRECIO'], 0, ',', '.');
			$total = number_format($result[$i]['TOTAL_NC'], 0, ',', '.');
			
			// por cada pasada le asigna una nueva posicion	
			$pdf->Text(24, 323+(15*$i), $item);			
			$pdf->Text(54, 323+(15*$i), $cantidad);
			$pdf->Text(88, 323+(15*$i), $modelo);
			
			$pdf->SetXY(139, 320+(15*$i));
			$pdf->Cell(4,1,"$detalle");

			$pdf->SetXY(395, 316+(15*$i));
			$pdf->MultiCell(80,5, $p_unitario,0, 'R');		
			$pdf->SetXY(475, 316+(15*$i));
			$pdf->MultiCell(80,5, $total,0, 'R');
}					
		
		// DIBUJANDO TOTALES
		$pdf->SetFont('Arial','',12);
		$pdf->Text(98, 630, 'Son: '.$total_en_palabras.' pesos.');
		
		// EL SIGUIENTE INVENTO ES PARA CONFIGURAR MAS RAPIDO LA NC
		$x = 85;
		$y = 145;
		
		if($total_dscto <> 0){//tiene dscto
			if(($monto_dscto1 <> 0 && $monto_dscto2 == 0) || ($monto_dscto2 <> 0 && $monto_dscto1 == 0)){//solo tiene un DSCTO 1
				$pdf->SetXY($x+346, $y+490);
				$pdf->SetFont('Arial','',9);
				$pdf->MultiCell(80,4, 'SUBTOTAL  $ ',0, 'R');
			
				$pdf->SetXY($x+378, $y+490);
				$pdf->SetFont('Arial','B',11);
				$pdf->MultiCell(105,4,$subtotal,0, 'R');

				if($monto_dscto1 <> 0){
					$pdf->SetXY($x+343, $y+505);
					$pdf->SetFont('Arial','',9);
					$pdf->MultiCell(80,4,$porc_dscto1.' % DSCTO  $ ',0, 'R');

					$pdf->SetXY($x+378, $y+505);
					$pdf->SetFont('Arial','B',11);
					$pdf->MultiCell(105,4,$monto_dscto1, 0, 'R');
				}
				else{
					$pdf->SetXY($x+333, $y+505);
					$pdf->SetFont('A4ial','',9);
					$pdf->MultiCell(80,4,$porc_dscto2.' % DSCTO: $ ',0, 'R');

					$pdf->SetXY($x+378, $y+505);
					$pdf->SetFont('Arial','B',11);
					$pdf->MultiCell(105,4,$monto_dscto2, 0, 'R');				
				}		
			}else if($monto_dscto1 <> 0 && $monto_dscto2 <> 0){//tiene ambos DSCTO

				$pdf->SetXY($x+346, $y+475);
				$pdf->SetFont('Arial','',9);
				$pdf->MultiCell(80,4, 'SUBTOTAL  $ ',0, 'R');
			
				$pdf->SetXY($x+378, $y+475);
				$pdf->SetFont('Arial','B',11);
				$pdf->MultiCell(105,4,$subtotal,0, 'R');

				$pdf->SetXY($x+340, $y+490);
				$pdf->SetFont('Arial','',9);
				$pdf->MultiCell(85,4,$porc_dscto1.' % DSCTO1 $ ',0, 'R');

				$pdf->SetXY($x+378, $y+490);
				$pdf->SetFont('Arial','B',11);
				$pdf->MultiCell(105,4,$monto_dscto1, 0, 'R');	
				
				$pdf->SetXY($x+346, $y+505);
				$pdf->SetFont('Arial','',9);
				$pdf->MultiCell(80,4,$porc_dscto2.' % DSCTO2 $ ',0, 'R');

				$pdf->SetXY($x+378, $y+505);
				$pdf->SetFont('Arial','B',11);
				$pdf->MultiCell(105,4,$monto_dscto2, 0, 'R');
			}
		}

				
		$pdf->SetXY($x+346, $y+520);
		$pdf->SetFont('Arial','',10);
		$pdf->MultiCell(80,4, 'TOTAL NETO $ ',0, 'R');
		$pdf->SetXY($x+378, $y+520);
		$pdf->SetFont('Arial','B',11);
		$pdf->MultiCell(105,4,$neto,0, 'R');
		$pdf->SetXY($x+346, $y+535);
		$pdf->SetFont('Arial','',9);
		$pdf->MultiCell(80,4, $porc_iva.' % IVA  $ ',0, 'R');
		$pdf->SetXY($x+378, $y+535);
		$pdf->SetFont('Arial','B',11);
		$pdf->MultiCell(105,4,$monto_iva,0, 'R');
		$pdf->Rect($x+360, $y+544, 120, 2, 'f');
		$pdf->SetXY($x+346, $y+555);
		$pdf->SetFont('Arial','',10);
		$pdf->MultiCell(80,4,'TOTAL  $ ',0, 'R');
		$pdf->SetXY($x+378, $y+555);
		$pdf->SetFont('Arial','B',11);
		$pdf->MultiCell(105,4,$total_con_iva,0, 'R');	
		
		
		


		//DIBUJANDO PERSONA QUE RETIRA PRODUCTOS 			
		$pdf->SetFont('Arial','',13);
		$pdf->Text(43, 685, $NRO_FACTURA);
		
	}
}
?>