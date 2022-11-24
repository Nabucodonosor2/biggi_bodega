<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$cod_pago_faprov	= $_REQUEST['cod_pago_faprov'];
$cod_empresa		= $_REQUEST['cod_empresa'];
$cod_nc_prov		= trim($_REQUEST['cod_nc_prov'],';');

if($cod_empresa == '')
	$cod_empresa = 0;

$temp = new Template_appl("dlg_usar_nc.htm");
$sql = "exec spdw_pago_faprov_ncprov $cod_pago_faprov, $cod_empresa";
$dw = new datawindow($sql, 'PAGO_FAPROV_NCPROV');

$dw->add_control(new edit_check_box('SELECCION', 'S', 'N'));
$dw->add_control(new static_text('NRO_NCPROV'));
$dw->add_control(new edit_date('FECHA_NCPROV',0));
$dw->add_control(new static_num('TOTAL_CON_IVA',0));
$dw->add_control(new edit_text_hidden('COD_NCPROV',0));

$dw->set_entrable('NRO_NCPROV',false);
$dw->set_entrable('FECHA_NCPROV',false);
$dw->retrieve();
if($cod_nc_prov != ''){
	$cod_nc_record = explode(';',$cod_nc_prov);
	for($i=0 ; $i < count($cod_nc_record) ; $i++){
		for($j=0 ; $j < $dw->row_count() ; $j++){
			$cod_ncprov = $dw->get_item($j, 'COD_NCPROV');
			if($cod_ncprov == $cod_nc_record[$i]){
				$dw->set_item($j, 'SELECCION', 'S');
			}
		}
	}
}
$dw->habilitar($temp, true);	

print $temp->toString();
?>