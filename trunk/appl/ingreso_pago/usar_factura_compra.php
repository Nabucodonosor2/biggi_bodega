<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$K_ESTADO_APROBADA = 2;
$K_TIPO_DOC_PAGO_FAPROV = 11;
$K_ESTADO_INGRESO_PAGO_ANULADA = 3;

$cod_empresa = $_REQUEST['cod_empresa'];

$temp = new Template_appl('usar_factura_compra.htm');											
										
$sql = "SELECT 	'N' SELECCION
				, F.NRO_FAPROV
				, convert(varchar, F.FECHA_FAPROV, 103) FECHA_FAPROV
				, F.TOTAL_CON_IVA
		FROM 	FAPROV F
		WHERE 	F.COD_EMPRESA = $cod_empresa
		  AND 	F.COD_ESTADO_FAPROV = $K_ESTADO_APROBADA
		  AND 	F.NRO_FAPROV not in (select 	NRO_DOC 
									 from 	DOC_INGRESO_PAGO DIP, INGRESO_PAGO IP 
									 where 	NRO_DOC is not null
								  	   AND 	DIP.COD_TIPO_DOC_PAGO = $K_TIPO_DOC_PAGO_FAPROV
								  	   AND 	IP.COD_ESTADO_INGRESO_PAGO <> $K_ESTADO_INGRESO_PAGO_ANULADA 
								  	   AND 	DIP.COD_INGRESO_PAGO = IP.COD_INGRESO_PAGO
								  	   AND   IP.COD_EMPRESA = F.COD_EMPRESA)";
				

$dw = new datawindow($sql, 'INGRESO_PAGO');
$dw->add_control(new edit_check_box('SELECCION','S','N'));
$dw->add_control(new static_text('NRO_FAPROV'));
$dw->add_control(new static_text('FECHA_FAPROV'));
$dw->add_control(new static_num('TOTAL_CON_IVA'));
$dw->retrieve();
$dw->habilitar($temp, true);

print $temp->toString();
?>