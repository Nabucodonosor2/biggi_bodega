<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

class print_dw_resultado_resumen extends reporte {
	function print_dw_resultado_resumen($sql, $xml, $labels=array(), $titulo, $con_logo, $vuelve_a_presentacion=false) {
		parent::reporte($sql, $xml, $labels, $titulo, $con_logo, $vuelve_a_presentacion,'L');
	}
	
	function modifica_pdf(&$pdf) {
		//$pdf->Image(session::get('K_ROOT_DIR').'/images_appl/logo_reporte_horizontal.jpg', 0, 0,612,792);
		
		/*$pdf->SetFont('Arial','',8.5);
		$pdf->SetXY(30,15);
		$pdf->Cell(555, 15, 'RESUMEN', '', '','L');*/
	}
}
?>