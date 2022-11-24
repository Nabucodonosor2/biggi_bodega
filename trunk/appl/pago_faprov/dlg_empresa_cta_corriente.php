<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$temp = new Template_appl("dlg_empresa_cta_corriente.htm");

$sql = "SELECT NULL COD_EMPRESA
			  ,NULL RUT
			  ,NULL DIG_VERIF
			  ,NULL ALIAS
			  ,NULL NOM_EMPRESA
			  ,'' COD_CUENTA_CORRIENTE";

$dw = new datawindow($sql, '', false, false);

$java_script = "help_empresa(this, 'P');";
$dw->add_control($control = new edit_num('COD_EMPRESA', 10, 10));
$control->set_onChange($java_script);
$control->con_separador_miles = false;

$dw->add_control($control = new edit_num('RUT', 10, 10));
$control->set_onChange($java_script);

$dw->add_control(new static_text('DIG_VERIF'));

$dw->add_control($control = new edit_text_upper('ALIAS', 37, 100));
$control->set_onChange($java_script);

$dw->add_control($control = new edit_text_upper('NOM_EMPRESA', 88, 100));
$control->set_onChange($java_script);

$sql_cta_corriente	= "SELECT COD_CUENTA_CORRIENTE
							 ,NOM_CUENTA_CORRIENTE 
					   FROM CUENTA_CORRIENTE
					   ORDER BY ORDEN";								
$dw->add_control(new drop_down_dw('COD_CUENTA_CORRIENTE', $sql_cta_corriente,125, '', false));

$dw->retrieve();
$dw->habilitar($temp, true);	

print $temp->toString();
?>