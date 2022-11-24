<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$K_ESTADO_INGRESO_PAGO_ANULADA = 3;
$K_ESTADO_INGRESO_PAGO_CONFIR  = 2;

$cod_empresa = $_REQUEST['cod_empresa'];

$temp = new Template_appl('usar_anticipo.htm');	

$sql = "SELECT 	'N' SELECCION
				, COD_INGRESO_PAGO
				, COD_INGRESO_PAGO COD_INGRESO_PAGO_H
				, convert(varchar(20), FECHA_CONFIRMA, 103)FECHA_CONFIRMA
				, convert(varchar(20), FECHA_CONFIRMA, 103)FECHA_CONFIRMA_H
				, OTRO_ANTICIPO
				, OTRO_ANTICIPO OTRO_ANTICIPO_H 
		FROM 	INGRESO_PAGO
		WHERE 	COD_EMPRESA = $cod_empresa
		AND 	COD_ESTADO_INGRESO_PAGO = $K_ESTADO_INGRESO_PAGO_CONFIR
		AND		OTRO_ANTICIPO > 0
		AND COD_INGRESO_PAGO not in  (select 	NRO_DOC  
										from 	DOC_INGRESO_PAGO DIP, INGRESO_PAGO IP 
										where 	NRO_DOC is not null 
										AND 	COD_ESTADO_INGRESO_PAGO <> $K_ESTADO_INGRESO_PAGO_ANULADA 
										AND 	DIP.COD_INGRESO_PAGO = IP.COD_INGRESO_PAGO
										AND		COD_TIPO_DOC_PAGO = 9)"; //ANTICIPO
				

$dw = new datawindow($sql, 'INGRESO_PAGO');
$dw->add_control(new edit_check_box('SELECCION','S','N'));
$dw->add_control(new edit_text_upper('COD_INGRESO_PAGO_H', 10, 10, 'hidden'));
$dw->add_control(new edit_text_upper('FECHA_CONFIRMA_H', 10, 10, 'hidden'));
$dw->add_control(new edit_text_upper('OTRO_ANTICIPO_H', 10, 10, 'hidden'));
$dw->add_control(new static_num('OTRO_ANTICIPO'));
$dw->retrieve();
$dw->habilitar($temp, true);

print $temp->toString();
?>