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
					,left(EM.NOM_EMPRESA, 33) FC_NOM_EMPRESA
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
		$pdf->Cell(550,17,"ANEXO TRASPASO SOFTLAND Nº ".$cod_envio_softland, 0, '', 'C');
		$pdf->SetFont('Arial','', 9);
		$pdf->SetXY(28, 60);
		$pdf->Cell(300,17,"SISTEMA EMISOR: ".$sistema_emisor, 0, '', 'L');
		$pdf->SetXY(378, 60);
		$pdf->Cell(200,17,"USUARIO EMISOR: ".$usuario_emisor, 0, '', 'R');
		$pdf->SetXY(22, 71);
		$pdf->SetFont('Arial','', 9);
		
		$pdf->SetXY(28,80);
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
				$y_ini = 90;
				$pdf->SetXY(28, 40);
				$pdf->SetTextColor(0, 0, 0);
				$pdf->SetFont('Arial','', 16);
				$pdf->Cell(550,17,"ANEXO TRASPASO SOFTLAND Nº ".$cod_envio_softland, 0, '', 'C');
				$pdf->SetFont('Arial','', 9);
				$pdf->SetXY(28, 60);
				$pdf->Cell(300,17,"SISTEMA EMISOR: ".$sistema_emisor, 0, '', 'L');
				$pdf->SetXY(378, 60);
				$pdf->Cell(200,17,"USUARIO EMISOR: ".$usuario_emisor, 0, '', 'R');
				$pdf->SetXY(22, 71);
				$pdf->SetFont('Arial','', 9);
				$pdf->SetXY(28,80);
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
		$pdf->Cell(180,15, 'TOTALES', '', '','R');
		$pdf->Cell(70,15, number_format($auxsum_total_neto, 0, ',', '.'), 'LTRB', '','R');
		$pdf->Cell(70,15, number_format($auxsum_monto_iva, 0, ',', '.'), 'LTRB', '','R'); 
		$pdf->Cell(75,15,number_format($auxsum_total_con_iva, 0, ',', '.') , 'LTRB', '','R');
		$pdf->Cell(75,15,number_format('', 0, ',', '.') , '', '','R');		

		
/* 		$pdf->Image(dirname(__FILE__).'/../../images_appl/ficha_tecnica.jpg', 25, 28,612*$factor,792*$factor);
		
		$pdf->SetXY(22, 40);
		$pdf->SetTextColor(0, 0, 0);
		$pdf->SetFont('FuturaBook','', 18);
		$pdf->Cell(70,17,'Ficha Técnica', 0, '', 'L');
		$pdf->SetXY(22, 71);
		$pdf->SetFont('FuturaBook','', 24);
		$x_t1 = $pdf->GetStringWidth($result[0]['NOM_PRODUCTOT1']);
		
		$pdf->Cell(70,17,$result[0]['NOM_PRODUCTOT1'], 0, '', 'L');
		$pdf->SetTextColor(162, 162, 162);
		$pdf->SetXY($x_t1+33, 71);
		$pdf->Cell(70,17,$result[0]['NOM_PRODUCTOT2'], 0, '', 'L');
		$pdf->SetFont('FuturaBook','', 14);
		$pdf->SetXY(22, 94);
		$pdf->Cell(70,17,$result[0]['NOM_PRODUCTOT3'], 0, '', 'L');
		
		$pdf->SetFont('FuturaBook','', 16);
		$pdf->SetTextColor(0, 0, 0);
		$pdf->SetXY(491, 52);
		$pdf->Cell(70,17,'MODELO', 0, '', 'L');
		$pdf->SetTextColor(255, 255, 255);
		$pdf->SetXY(493, 89);
		$pdf->Cell(70,17,$cod_producto, 0, '', 'C');
		
		if(file_exists($this->folder_producto($cod_producto).'/'.$this->cod_producto_char($cod_producto).'_FT1.jpg')){
			$sql = "SELECT COORDENADA_X
						  ,COORDENADA_Y
					FROM FOTO_FICHA_FOLLETO
					WHERE COD_PRODUCTO = '$cod_producto'
					AND NOM_FOTO = 'FICHA_FOTO1'";
			$result = $db->build_results($sql);
			$POS_X = $result[0]['COORDENADA_X'];
			$POS_Y = $result[0]['COORDENADA_Y'];
		
			$factor = 0;
			$pdf->Image($this->folder_producto($cod_producto).'/'.$this->cod_producto_char($cod_producto).'_FT1.jpg' , 28+$POS_X, 140+$POS_Y, 700*$factor, 740*$factor);
		}else
			$pdf->Image(dirname(__FILE__).'/../../../../producto_imagen/parametro/foto_no_disponible.jpg' , 28+$POS_X, 140+$POS_Y, 555, 491);
			
		if(file_exists($this->folder_producto($cod_producto).'/'.$this->cod_producto_char($cod_producto).'_FT1.jpg')){
			$sql = "SELECT COORDENADA_X
						  ,COORDENADA_Y
						  ,USA_FOTO_ANTIGUA
					FROM FOTO_FICHA_FOLLETO FFF
						,PRODUCTO P
					WHERE P.COD_PRODUCTO = '$cod_producto'
					AND P.COD_PRODUCTO = FFF.COD_PRODUCTO
					AND NOM_FOTO = 'FICHA_FOTO2'";
			$result = $db->build_results($sql);
			$POS_X				= $result[0]['COORDENADA_X'];
			$POS_Y				= $result[0]['COORDENADA_Y'];
			$USA_FOTO_ANTIGUA	= $result[0]['USA_FOTO_ANTIGUA'];
			
			// Las 2 lineas siguientes son la configuracion para usar FOTO nueva (500*500x120dpi) en el arean chica.
			//$factor = 0.52;
			//$pdf->Image($this->folder_producto($cod_producto).'/'.$this->cod_producto_char($cod_producto).'_FT2.jpg' , 323+$POS_X, 405+$POS_Y, 500*$factor, 500*$factor);
	

			if($USA_FOTO_ANTIGUA == 'S'){
				$factor = 0.60;
				$pdf->Image($this->folder_producto($cod_producto).'/'.$this->cod_producto_char($cod_producto).'_CAT1.jpg' , 323+$POS_X, 405+$POS_Y, 300*$factor, 400*$factor);
			}else{

				if(file_exists($this->folder_producto($cod_producto).'/'.$this->cod_producto_char($cod_producto).'_FT2.jpg')){
					$factor = 0.52;
					$pdf->Image($this->folder_producto($cod_producto).'/'.$this->cod_producto_char($cod_producto).'_FT2.jpg' , 323+$POS_X, 405+$POS_Y, 500*$factor, 500*$factor);
				}
			}	

		}else
			$pdf->Image(dirname(__FILE__).'/../../../../producto_imagen/parametro/foto_no_disponible.jpg' , 320+$POS_X, 397+$POS_Y, 263, 234);	
		

		$pdf->SetTextColor(0, 0, 0);
		$pdf->SetFont('FuturaBook','', 10);
		$pdf->SetXY(328, 645);
		$pdf->Cell(70,17,'*Imagen referencial', 0, '', 'L');
		

		$pdf->SetTextColor(0, 0, 0);
		$pdf->SetFont('FuturaBook','', 18);
		$pdf->SetXY(328, 665);
		$pdf->Cell(70,17,'ESPECIFICACIONES', 0, '', 'L');
		$pdf->SetTextColor(255, 0, 0);
		$pdf->SetXY(493, 665);
		$pdf->Cell(70,17,'TÉCNICAS', 0, '', 'L');

		$pdf->SetTextColor(0, 0, 0);
		
		$sql = "SELECT LARGO
					  ,ANCHO
					  ,ALTO
					  ,PESO
				FROM PRODUCTO
				WHERE COD_PRODUCTO = '$cod_producto'";
		$result = $db->build_results($sql);

		$pdf->SetFont('FuturaBook','', 9);
		$pdf->SetXY(22, 752);
		$pdf->MultiCell(565,8,'Dimensiones expresadas en centímetros (cm) (medidas de referencia para su instalación). BIGGI se reserva el derecho de realizar cambios en sus productos o en la información contenida en esta ficha técnica, sin previo aviso.', '0', 'L');


		//header
		$pdf->SetFont('FuturaBook','', 8);
		$pdf->SetXY(205, 701);
		$pdf->Cell(46,17,'LARGO', 0, '', 'C');
		$pdf->SetXY(251, 701);
		$pdf->Cell(46,17,'ANCHO', 0, '', 'C');
		$pdf->SetXY(297, 701);
		$pdf->Cell(46,17,'ALTO', 0, '', 'C');
		$pdf->SetXY(343, 701);
		$pdf->Cell(45,17,'PESO', 0, '', 'C');
		
		//u de medida
		$pdf->SetTextColor(0, 0, 0);
		$pdf->SetFont('FuturaBook','', 6);
		$pdf->SetXY(205, 708);
		$pdf->Cell(46,17,'(CM)', 0, '', 'C');
		$pdf->SetXY(251, 708);
		$pdf->Cell(46,17,'(CM)', 0, '', 'C');
		$pdf->SetXY(297, 708);
		$pdf->Cell(46,17,'(CM)', 0, '', 'C');
		$pdf->SetXY(343, 708);
		$pdf->Cell(45,17,'(KG)', 0, '', 'C');


		//leyenda datos referenciales		
		//$pdf->SetXY(22, 745);
		//$pdf->Cell(46,17,'Dimensiones expresadas en centímetros (cm) (medidas de referencia para su instalación). BIGGI se reserva del derecho de realizar cambios en sus productos o en l información contenida en esta ficha técnica,', 0, '', 'L');
		//$pdf->SetXY(22, 750);
		//$pdf->Cell(46,17,'sin previo aviso.', 0, '', 'L');


		
		//	           (   ,  ,                                    ,  ,   , fill)

		//valores
		$pdf->SetTextColor(255, 255, 255);
		$pdf->SetFont('FuturaBook','', 10);
		$pdf->SetXY(205, 721);
		$pdf->Cell(46,17,$result[0]['LARGO'], 0, '', 'C');
		$pdf->SetXY(251, 721);
		$pdf->Cell(46,17,$result[0]['ANCHO'], 0, '', 'C');
		$pdf->SetXY(297, 721);
		$pdf->Cell(46,17,$result[0]['ALTO'], 0, '', 'C');
		$pdf->SetXY(343, 721);
		$pdf->Cell(46,17,$result[0]['PESO'], 0, '', 'C');
		
		$sql_esp = "SELECT E.NOM_CAMPO
					FROM PRODUCTO_ESPECIFICACION  PE
						,ESPECIFICACION E 
					WHERE PE.COD_PRODUCTO = '$cod_producto' 
					AND E.COD_ESPECIFICACION = PE.COD_ESPECIFICACION 
					ORDER BY PE.ORDEN ASC";
		$result_esp = $db->build_results($sql_esp);
		
		for($k=0 ; $k < count($result_esp) ; $k++){
			if($k == 0)
				$posX = 388;
			else if($k == 1)
				$posX = 452;
			else if($k == 2)
				$posX = 517;	
		
			$nom_campo = "p.".$result_esp[$k]['NOM_CAMPO'];
			
			$sql_esp2 = "SELECT E.LABEL
					            ,LABEL_UNIDAD
					            ,$nom_campo DATO
					     FROM PRODUCTO_ESPECIFICACION  PE
					     	 ,ESPECIFICACION E
					     	 ,PRODUCTO P
					     WHERE PE.COD_PRODUCTO = '$cod_producto'
					     AND E.COD_ESPECIFICACION = PE.COD_ESPECIFICACION
					     AND P.COD_PRODUCTO  = PE.COD_PRODUCTO
					     AND E.NOM_CAMPO = '".$result_esp[$k]['NOM_CAMPO']."'";
			$result_esp2 = $db->build_results($sql_esp2);		     

			$pdf->SetFont('FuturaBook','', 8);
			$pdf->SetTextColor(0, 0, 0);
			$pdf->SetXY($posX, 701);
			$pdf->Cell(64,17,$result_esp2[0]['LABEL'], 0, '', 'C');
			$pdf->SetFont('FuturaBook','', 6);
			$pdf->SetXY($posX, 708);
			if($result_esp2[0]['LABEL_UNIDAD'] == '')
				$pdf->Cell(64,17,'', 0, '', 'C');
			else	
				$pdf->Cell(64,17,'('.$result_esp2[0]['LABEL_UNIDAD'].')', 0, '', 'C');
			$pdf->SetFont('FuturaBook','', 10);
			$pdf->SetTextColor(255, 255, 255);
			$pdf->SetXY($posX, 721);
			$pdf->Cell(64,17,$result_esp2[0]['DATO'], 0, '', 'C'); 
		}*/
    }
}
?>