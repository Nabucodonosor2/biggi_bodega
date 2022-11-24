<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$K_ESTADO_IMPRESA_DOC_SII = 2;
$K_ESTADO_ENVIADA_DOC_SII = 3;
$K_TIPO_DOC_PAGO_NC = 7;
$K_ESTADO_INGRESO_PAGO_ANULADA = 3;

$cod_empresa = $_REQUEST['cod_empresa'];

$temp = new Template_appl('usar_nota_credito.htm');											
										
$sql = "SELECT 	'N' SELECCION
				, NRO_NOTA_CREDITO
				, NRO_NOTA_CREDITO NRO_NOTA_CREDITO_H
				, convert(varchar(20), FECHA_NOTA_CREDITO, 103) FECHA_NOTA_CREDITO
				, convert(varchar(20), FECHA_NOTA_CREDITO, 103) FECHA_NOTA_CREDITO_H
				, TOTAL_CON_IVA
				, TOTAL_CON_IVA TOTAL_CON_IVA_H
		FROM 	NOTA_CREDITO
		WHERE 	COD_EMPRESA = $cod_empresa
		AND 	COD_ESTADO_DOC_SII in ($K_ESTADO_IMPRESA_DOC_SII, $K_ESTADO_ENVIADA_DOC_SII)
		AND 	DISPONIBLE_INGRESO_PAGO = 'S'
		AND NRO_NOTA_CREDITO not in  (select 	NRO_DOC 
												from 	DOC_INGRESO_PAGO DIP, INGRESO_PAGO IP 
										where 	NRO_DOC is not null
										AND DIP.COD_TIPO_DOC_PAGO = $K_TIPO_DOC_PAGO_NC
										AND IP.COD_ESTADO_INGRESO_PAGO <> $K_ESTADO_INGRESO_PAGO_ANULADA 
										AND DIP.COD_INGRESO_PAGO = IP.COD_INGRESO_PAGO)";
				

$dw = new datawindow($sql, 'INGRESO_PAGO');
$dw->add_control(new edit_check_box('SELECCION','S','N'));
$dw->add_control(new edit_text_upper('NRO_NOTA_CREDITO_H', 10, 10, 'hidden'));
$dw->add_control(new edit_text_upper('FECHA_NOTA_CREDITO_H', 10, 10, 'hidden'));
$dw->add_control(new edit_text_upper('TOTAL_CON_IVA_H', 10, 10, 'hidden'));
$dw->add_control(new static_num('TOTAL_CON_IVA'));
$dw->retrieve();
$dw->habilitar($temp, true);

print $temp->toString();
?>