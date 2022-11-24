<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/barcode/barcode.php");
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/barcode/c128bobject.php");

class OC_PDF extends PDF {
	var $posY_subtotal = 0;
	var $barcode_dibujado = false;
	var $sql_print = "";
	
	function OC_PDF($t,$d,$rd, $con_logo, $orientation,$unit,$format,$sql){
		$this->sql_print = $sql;
		parent::PDF($t,$d,$rd, $con_logo, $orientation,$unit,$format);
	}
	
	function Cell($w,$h=0,$txt='',$border=0,$ln=0,$align='',$fill=0,$link='',$rowheight=0) {
		if ($txt=='Dirección Factura:') {
			$this->posY_subtotal = $this->GetY();
		}
		parent::Cell($w,$h,$txt,$border,$ln,$align,$fill,$link,$rowheight);
	}
	function draw_barcode(){
		$print_etiqueta = false;
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$result = $db->build_results($this->sql_print);
		
		$cod_orden_compra_bd	= $result[0]['COD_ORDEN_COMPRA'];
		
		$cod_orden_compra		= '000000';
		$count_cod_oc			= strlen($cod_orden_compra_bd);
		$relleno_cod_oc			= substr($cod_orden_compra, 0, -$count_cod_oc);
		$cod_orden_compra		= $relleno_cod_oc.$cod_orden_compra_bd;
		
		if(K_CLIENTE == 'COMERCIAL'){
			$cadena = $cod_orden_compra.'-91462001X';
			if ($result[0]['CC_EMPRESA'] == 1){
				//COMERCIAL
				$this->SetFont('Helvetica','',8);
				$factor = 0.5;
				$this->Image(dirname(__FILE__)."/../../images_appl/sello_comercial.jpg",520,175, $factor * 120, $factor * 60);
			}
			else if ($result[0]['CC_EMPRESA'] == 4){
				//Cdr
				$this->SetFont('Helvetica','',8);
				$factor = 0.5;
				$this->Image(dirname(__FILE__)."/../../images_appl/sello_cdr.jpg",520,175, $factor * 120, $factor * 60);
			}else if ($result[0]['CC_EMPRESA'] == 5){
				//COMPASS
				$this->SetFont('Helvetica','',8);
				$factor = 0.5;
				$this->Image(dirname(__FILE__)."/../../images_appl/sello_compass.jpg",520,175, $factor * 120, $factor * 60);
			}else if ($result[0]['CC_EMPRESA'] == 6){
				//SODEXHO
				$this->SetFont('Helvetica','',8);
				$factor = 0.5;
				$this->Image(dirname(__FILE__)."/../../images_appl/sello_sodexho.jpg",520,175, $factor * 120, $factor * 60);
			}else if ($result[0]['CC_EMPRESA'] == 7){
				//TO
				$this->SetFont('Helvetica','',8);
				$factor = 0.5;
				$this->Image(dirname(__FILE__)."/../../images_appl/sello_todoinox.jpg",520,175, $factor * 120, $factor * 60);
			}else{
				//SACO COMERCIAL
				$this->SetFont('Helvetica','',8);
				$factor = 0.5;
				$this->Image(dirname(__FILE__)."/../../images_appl/sello_comercial.jpg",520,175, $factor * 120, $factor * 60);
			}
			if($cod_orden_compra_bd > 175780)
				$print_etiqueta = true;
				
		}else if(K_CLIENTE == 'BODEGA'){
			$cadena = $cod_orden_compra.'-80112900X';
			
				//BODEGA
				$this->SetFont('Helvetica','',8);
				$factor = 0.5;
				$this->Image(dirname(__FILE__)."/../../images_appl/sello_bodega_biggi.jpg",520,175, $factor * 120, $factor * 60);
			
			if($cod_orden_compra_bd > 55676)
				$print_etiqueta = true;
		}else if(K_CLIENTE == 'RENTAL'){
			$cadena = $cod_orden_compra.'-91462001R';
			//RENTAL
				$this->SetFont('Helvetica','',8);
				$factor = 0.5;
				$this->Image(dirname(__FILE__)."/../../images_appl/sello_rental.jpg",520,175, $factor * 120, $factor * 60);
			
			if($cod_orden_compra_bd > 65555)
				$print_etiqueta = true;
		}else{ //todoinox	
			$cadena = $cod_orden_compra.'-89257000X';
			//RENTAL
				$this->SetFont('Helvetica','',8);
				$factor = 0.5;
				$this->Image(dirname(__FILE__)."/../../images_appl/sello_todoinox.jpg",520,175, $factor * 120, $factor * 60);
			
			if($cod_orden_compra_bd > 22231)
				$print_etiqueta = true;
		}
		
		if($print_etiqueta == true){
			$style = 40;
			$width = 220;
			$height = 100;
			$xres = 1;
			$font = 1;
			$obj = new C128BObject($width, $height, $style, $cadena);
			if ($obj){
				$obj->SetFont($font);
				$obj->DrawObject($xres);
				$nombre_temp = tempnam(dirname(__FILE__)."/../../../tmp", "tmp");			
				$obj->FlushObjectToFile ($nombre_temp);
				$obj->DestroyObject();
				unset($obj);
				
				$factor = 0.6;
				//imagen codigo de barra ********************************************************************
				$anguloS1 = 220;   //eje x
				$anguloS2 = 114;     //eje y
				$anguloI1 = 310;    //ancho
				$anguloI2 = 40;    //alto
				$this->Image($nombre_temp.".jpg", $anguloS1, $anguloS2, $factor * $anguloI1, $factor * $anguloI2);
			}
		}	
		$this->barcode_dibujado = true;
	}
	function AddPage($orientation=''){
		if ($this->posY_subtotal != 0 && $this->barcode_dibujado==false){
			$this->draw_barcode();
		}
		parent::AddPage($orientation);
	}
}
class print_reporte extends reporte{	
	function print_reporte($sql, $xml, $labels=array(), $titulo, $con_logo, $vuelve_a_presentacion=false) {

		parent::reporte($sql, $xml, $labels, $titulo, $con_logo, $vuelve_a_presentacion);			
	}
	function make_reporte() {
		$p = new ReportParser();
		$p->parseRP($this->xml);
		$rdata = new MySQLRD($this->sql);
		$pdf = new OC_PDF(array($p), array($this->labels), array($rdata), $this->con_logo,$this->orientation,$this->unit,$this->format,$this->sql);
		if ($pdf->barcode_dibujado==false) {
			$pdf->draw_barcode();
		}
		
		$pdf->SetTitle($this->titulo);
		$pdf->Output($this->titulo, 'I');
	}
}
?>