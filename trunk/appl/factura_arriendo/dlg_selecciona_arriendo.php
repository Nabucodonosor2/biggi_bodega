<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$temp = new Template_appl('dlg_selecciona_arriendo.htm');	

$sql = "select null COD_EMPRESA
				,null RUT
				,null DIG_VERIF
				,null ALIAS
				,null NOM_EMPRESA
				,null SUMA_TOTAL";
$dw_empresa = new datawindow($sql);

$java_script = "help_empresa(this, 'C');";
$dw_empresa->add_control($control = new edit_num('COD_EMPRESA', 10, 10));
$control->set_onChange($java_script);
$control->con_separador_miles = false;

$dw_empresa->add_control($control = new edit_num('RUT', 10, 10));
$control->set_onChange($java_script);
$dw_empresa->add_control(new static_text('DIG_VERIF'));
		
$dw_empresa->add_control($control = new edit_text_upper('ALIAS', 37, 100));
$control->set_onChange($java_script);

$dw_empresa->add_control($control = new edit_text_upper('NOM_EMPRESA', 121, 100));
$control->set_onChange($java_script);

$dw_empresa->add_control($control = new static_num('SUMA_TOTAL'));

$dw_empresa->insert_row();
$dw_empresa->habilitar($temp, true);

print $temp->toString();
?>