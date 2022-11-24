<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/class_MySQLRD_biggi.php");

class reporte_biggi extends reporte {	
	var $sp = '';
	var $param;
	
	function reporte_biggi($sql, $xml, $labels=array(), $titulo, $con_logo, $vuelve_a_presentacion=false, $sp='', $param='',$orientation='P',$unit='pt',$format='letter') {
		$this->sp = $sp;
		$this->param = $param;
		$sql_param = "select convert(varchar, getdate(), 103) FECHA_HORA";
		
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$result = $db->build_results($sql_param);
		
		$labels['str_fecha_hora'] = $result[0]['FECHA_HORA'];
		
		parent::reporte($sql, $xml, $labels, $titulo, $con_logo, $vuelve_a_presentacion,$orientation,$unit,$format);			
	}
	function make_reporte() {
		$p = &new ReportParser();
		$p->parseRP($this->xml);
		$rdata = new MySQLRD_biggi($this->sql, $this->sp, $this->param);
		$pdf = PDF::makePDF(array($p), array($this->labels), array($rdata), $this->con_logo,$this->orientation,$this->unit,$this->format);		
		$pdf->SetTitle($this->titulo);
		$this->modifica_pdf($pdf);
		$pdf->Output($this->titulo, 'I');
	}
}
?>